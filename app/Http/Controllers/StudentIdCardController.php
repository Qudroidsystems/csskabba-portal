<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Schoolclass;
use App\Models\SchoolInformation;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentIdCardController extends Controller
{
    /**
     * Show the ID Card Generator page.
     */
    public function index()
    {
        $schoolclasses = Schoolclass::orderBy('schoolclass')->get();
        $pagetitle     = 'Student ID Card Generator';
        return view('student.idcard.index', compact('schoolclasses', 'pagetitle'));
    }

    /**
     * AJAX: Load paginated students with filters.
     * GET /student-id-cards/load-students
     */
    public function loadStudents(Request $request)
    {
        $query = Student::query()
            ->with(['picture', 'currentTerm.schoolClass.armRelation'])
            ->orderBy('firstname');

        // Search by name or admission number
        if ($search = trim($request->search)) {
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname',  'like', "%{$search}%")
                  ->orWhere('admissionNo', 'like', "%{$search}%");
            });
        }

        // Filter by class
        if ($classId = $request->class_id) {
            $query->whereHas('currentTerm', function ($q) use ($classId) {
                $q->where('schoolclass_id', $classId);
            });
        }

        $perPage = in_array((int)$request->per_page, [20, 40, 60, 100])
            ? (int)$request->per_page
            : 20;

        $paginated = $query->paginate($perPage);

        // Flatten the data for the frontend grid
        $data = $paginated->getCollection()->map(function (Student $s) {
            $schoolClass = optional($s->currentTerm?->schoolClass);
            $arm         = optional($schoolClass->armRelation);

            return [
                'id'          => $s->id,
                'firstname'   => $s->firstname,
                'lastname'    => $s->lastname,
                'admissionNo' => $s->admissionNo,
                'gender'      => $s->gender,
                'picture'     => $s->picture?->picture,
                'schoolclass' => $schoolClass->schoolclass,
                'arm'         => $arm->arm,
            ];
        });

        $paginated->setCollection($data);

        return response()->json([
            'success' => true,
            'data'    => $paginated,
        ]);
    }

    /**
     * AJAX: Render front+back preview HTML for selected students.
     * POST /student-id-cards/preview
     */
    public function preview(Request $request)
    {
        $request->validate([
            'student_ids'   => 'required|array|max:50',
            'student_ids.*' => 'integer|exists:students,id',
            'orientation'   => 'in:portrait,landscape',
        ]);

        $students = $this->fetchStudents($request->student_ids);

        if ($students->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No students found.']);
        }

        $schoolInfo  = SchoolInformation::first();
        $orientation = $request->orientation ?? 'portrait';

        $html = view('student.idcard.preview', compact('students', 'schoolInfo', 'orientation'))
            ->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
            'count'   => $students->count(),
        ]);
    }

    /**
     * PDF download — PVC-print-ready.
     * POST /student-id-cards/download
     */
    public function download(Request $request)
    {
        $request->validate([
            'student_ids'   => 'required|array|max:200',
            'student_ids.*' => 'integer|exists:students,id',
            'orientation'   => 'in:portrait,landscape',
        ]);

        $students    = $this->fetchStudents($request->student_ids);
        $schoolInfo  = SchoolInformation::first();
        $orientation = $request->orientation ?? 'portrait';

        $pdf = Pdf::loadView('student.idcard.pdf', compact('students', 'schoolInfo', 'orientation'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi'                          => 150,
                'defaultFont'                  => 'sans-serif',
                'isHtml5ParserEnabled'         => true,
                'isRemoteEnabled'              => true,   // for photo URLs
                'isFontSubsettingEnabled'      => true,
                'defaultPaperSize'             => 'a4',
                'chroot'                       => public_path(),
            ]);

        $filename = 'student-id-cards-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    /* ── helpers ── */

    private function fetchStudents(array $ids)
    {
        return Student::with(['picture', 'currentTerm.schoolClass.armRelation'])
            ->whereIn('id', $ids)
            ->orderBy('firstname')
            ->get();
    }
}
