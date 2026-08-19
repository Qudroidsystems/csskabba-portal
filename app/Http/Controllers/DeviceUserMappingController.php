<?php

namespace App\Http\Controllers;

use App\Models\DeviceAttendanceLog;
use App\Models\DeviceUserMapping;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceUserMappingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View device-mappings',   ['only' => ['index', 'unmapped', 'search']]);
        $this->middleware('permission:Create device-mappings', ['only' => ['store', 'bulkImport', 'quickAssign']]);
        $this->middleware('permission:Delete device-mappings', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = DeviceUserMapping::query()->latest();

        if ($request->filled('type')) {
            $query->where('person_type', $request->type);
        }
        if ($request->filled('q')) {
            $query->where('device_pin', 'like', "%{$request->q}%");
        }

        $mappings = $query->paginate(50)->withQueryString();

        // Attach display names without N+1 across two different source tables
        $studentIds = $mappings->where('person_type', 'student')->pluck('person_id');
        $staffIds   = $mappings->where('person_type', 'staff')->pluck('person_id');

        $students = Student::whereIn('id', $studentIds)->get()->keyBy('id');
        $staff    = Staff::with('user')->whereIn('id', $staffIds)->get()->keyBy('id');

        $mappings->getCollection()->transform(function ($m) use ($students, $staff) {
            if ($m->person_type === 'student') {
                $p = $students->get($m->person_id);
                $m->display_name = $p ? "{$p->lastname} {$p->firstname} ({$p->admissionNo})" : 'Unknown student';
            } else {
                $p = $staff->get($m->person_id);
                $m->display_name = $p ? ($p->full_name . " ({$p->employmentid})") : 'Unknown staff';
            }
            return $m;
        });

        $unmappedCount = $this->unmappedPinsQuery()->count();

        $pagetitle = 'Device PIN Mappings';
        return view('attendance.admin.device-mappings', compact('mappings', 'unmappedCount', 'pagetitle'));
    }

    // ── Searchable dropdown source for the manual-add form ──────────────
    public function search(Request $request)
    {
        $type = $request->input('type');
        $q    = $request->input('q', '');

        if ($type === 'student') {
            $results = Student::where(function ($qq) use ($q) {
                    $qq->where('firstname', 'like', "%{$q}%")
                       ->orWhere('lastname', 'like', "%{$q}%")
                       ->orWhere('admissionNo', 'like', "%{$q}%");
                })
                ->limit(20)
                ->get(['id', 'firstname', 'lastname', 'admissionNo'])
                ->map(fn($s) => [
                    'id'   => $s->id,
                    'text' => "{$s->lastname} {$s->firstname} ({$s->admissionNo})",
                ]);
        } else {
            $results = Staff::with('user')
                ->where(function ($qq) use ($q) {
                    $qq->whereHas('user', fn($uq) => $uq->where('name', 'like', "%{$q}%"))
                       ->orWhere('employmentid', 'like', "%{$q}%");
                })
                ->limit(20)
                ->get()
                ->map(fn($s) => [
                    'id'   => $s->id,
                    'text' => $s->full_name . " ({$s->employmentid})",
                ]);
        }

        return response()->json($results);
    }

    // ── Manual single add ────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_serial' => 'required|string',
            'device_pin'    => 'required|integer',
            'person_type'   => 'required|in:student,staff',
            'person_id'     => 'required|integer',
        ]);

        try {
            $mapping = DeviceUserMapping::updateOrCreate(
                ['device_serial' => $validated['device_serial'], 'device_pin' => $validated['device_pin']],
                ['person_type' => $validated['person_type'], 'person_id' => $validated['person_id'], 'active' => true]
            );

            // Retroactively process any pending/unmapped logs for this pin now that it's mapped
            $this->reprocessPendingLogs($validated['device_serial'], $validated['device_pin']);

            return response()->json(['success' => true, 'message' => 'Mapping saved.', 'data' => $mapping]);
        } catch (\Exception $e) {
            Log::error('DeviceUserMapping store error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        DeviceUserMapping::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Mapping removed.']);
    }

    // ── Bulk CSV import ───────────────────────────────────────────────────
    // Expected CSV columns: device_pin, person_type, identifier
    // identifier = admissionNo for students, employmentid for staff
    public function bulkImport(Request $request)
    {
        $request->validate([
            'device_serial' => 'required|string',
            'csv_file'      => 'required|file|mimes:csv,txt',
        ]);

        $path   = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');
        fgetcsv($handle); // skip header row

        $created = 0;
        $failed  = 0;
        $errors  = [];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                [$pin, $type, $identifier] = array_pad($row, 3, null);
                $pin        = trim((string) $pin);
                $type       = strtolower(trim((string) $type));
                $identifier = trim((string) $identifier);

                if (!$pin || !in_array($type, ['student', 'staff']) || !$identifier) {
                    $failed++;
                    $errors[] = 'Invalid row: ' . implode(',', $row);
                    continue;
                }

                $person = $type === 'student'
                    ? Student::where('admissionNo', $identifier)->first()
                    : Staff::where('employmentid', $identifier)->first();

                if (!$person) {
                    $failed++;
                    $errors[] = "No {$type} found for identifier '{$identifier}'";
                    continue;
                }

                DeviceUserMapping::updateOrCreate(
                    ['device_serial' => $request->device_serial, 'device_pin' => $pin],
                    ['person_type' => $type, 'person_id' => $person->id, 'active' => true]
                );
                $created++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            Log::error('Bulk import error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
        fclose($handle);

        return response()->json([
            'success' => true,
            'message' => "Imported {$created} mapping(s), {$failed} failed.",
            'errors'  => array_slice($errors, 0, 20),
        ]);
    }

    // ── Unmapped PIN queue ────────────────────────────────────────────────
    private function unmappedPinsQuery()
    {
        return DeviceAttendanceLog::where('processing_status', 'unmapped')
            ->select('device_serial', 'device_pin')
            ->selectRaw('COUNT(*) as punch_count')
            ->selectRaw('MAX(punch_time) as last_seen')
            ->groupBy('device_serial', 'device_pin');
    }

    public function unmapped()
    {
        $unmapped  = $this->unmappedPinsQuery()->orderByDesc('last_seen')->get();
        $pagetitle = 'Unmapped Device PINs';
        return view('attendance.admin.device-unmapped', compact('unmapped', 'pagetitle'));
    }

    public function quickAssign(Request $request)
    {
        $validated = $request->validate([
            'device_serial' => 'required|string',
            'device_pin'    => 'required|integer',
            'person_type'   => 'required|in:student,staff',
            'person_id'     => 'required|integer',
        ]);

        DeviceUserMapping::updateOrCreate(
            ['device_serial' => $validated['device_serial'], 'device_pin' => $validated['device_pin']],
            ['person_type' => $validated['person_type'], 'person_id' => $validated['person_id'], 'active' => true]
        );

        $this->reprocessPendingLogs($validated['device_serial'], $validated['device_pin']);

        return response()->json(['success' => true, 'message' => 'Assigned and past punches reprocessed.']);
    }

    private function reprocessPendingLogs(string $deviceSerial, int $pin): void
    {
        $logs = DeviceAttendanceLog::where('device_serial', $deviceSerial)
            ->where('device_pin', $pin)
            ->whereIn('processing_status', ['unmapped', 'pending', 'error'])
            ->get();

        $processor = app(\App\Services\DeviceAttendanceProcessor::class);
        foreach ($logs as $log) {
            $processor->process($log);
        }
    }
}
