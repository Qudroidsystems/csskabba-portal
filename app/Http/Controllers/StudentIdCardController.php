<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SchoolInformation;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentIdCardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View id card|Generate id card|Print id card');
    }

    /* ═══════════════════════════════════════════════════════════
       INDEX
    ═══════════════════════════════════════════════════════════ */
    public function index()
    {
        $pagetitle = 'Student ID Card Generator';

        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->selectRaw("schoolclass.id, CONCAT(schoolclass.schoolclass, ' - ', schoolarm.arm) as class_display")
            ->orderBy('schoolclass.schoolclass')
            ->get();

        $schoolInfo = SchoolInformation::getActiveSchool();

        return view('student.idcard.index', compact('pagetitle', 'schoolclasses', 'schoolInfo'));
    }

    /* ═══════════════════════════════════════════════════════════
       LOAD STUDENTS  (GET — paginated, filtered)
    ═══════════════════════════════════════════════════════════ */
    public function loadStudents(Request $request)
    {
        try {
            $perPage = (int) $request->get('per_page', 20);
            $search  = trim($request->get('search', ''));
            $classId = $request->get('class_id');

            /*
             * Join studentclass (not student_current_term) — mirrors the
             * working StudentController pattern to avoid empty result sets.
             * groupBy prevents ONLY_FULL_GROUP_BY errors from the LEFT JOINs.
             */
            $query = Student::query()
                ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('studentclass',   'studentclass.studentId',   '=', 'studentRegistration.id')
                ->leftJoin('schoolclass',    'schoolclass.id',            '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolarm',      'schoolarm.id',              '=', 'schoolclass.arm')
                ->select([
                    'studentRegistration.id',
                    'studentRegistration.admissionNo',
                    'studentRegistration.firstname',
                    'studentRegistration.lastname',
                    'studentRegistration.othername',
                    'studentRegistration.gender',
                    'studentpicture.picture',
                    'schoolclass.schoolclass',
                    'schoolarm.arm',
                ])
                ->groupBy([
                    'studentRegistration.id',
                    'studentRegistration.admissionNo',
                    'studentRegistration.firstname',
                    'studentRegistration.lastname',
                    'studentRegistration.othername',
                    'studentRegistration.gender',
                    'studentpicture.picture',
                    'schoolclass.schoolclass',
                    'schoolarm.arm',
                ])
                ->orderBy('studentRegistration.lastname');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('studentRegistration.firstname',  'LIKE', "%{$search}%")
                      ->orWhere('studentRegistration.lastname', 'LIKE', "%{$search}%")
                      ->orWhere('studentRegistration.admissionNo', 'LIKE', "%{$search}%");
                });
            }

            if (!empty($classId)) {
                $query->where('studentclass.schoolclassid', $classId);
            }

            $students = $query->paginate($perPage);

            return response()->json(['success' => true, 'data' => $students]);

        } catch (\Exception $e) {
            Log::error('ID Card loadStudents error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       PREVIEW  (POST — returns rendered HTML)
    ═══════════════════════════════════════════════════════════ */
    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:studentRegistration,id',
            'orientation'   => 'required|in:portrait,landscape',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $students = $this->fetchStudentsForCards($request->student_ids);

            $schoolInfo  = SchoolInformation::getActiveSchool();
            $orientation = $request->orientation;

            $html = view('student.idcard.preview', compact('students', 'schoolInfo', 'orientation'))->render();

            return response()->json([
                'success' => true,
                'html'    => $html,
                'count'   => $students->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('ID Card preview error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       DOWNLOAD  (POST — streams PDF)
    ═══════════════════════════════════════════════════════════ */
    public function download(Request $request)
    {
        $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:studentRegistration,id',
            'orientation'   => 'required|in:portrait,landscape',
        ]);

        try {
            $students = $this->fetchStudentsForCards($request->student_ids);

            if ($students->isEmpty()) {
                return back()->with('error', 'No students found for the selected IDs.');
            }

            $schoolInfo  = SchoolInformation::getActiveSchool();
            $orientation = $request->orientation;

            $paperSize = $orientation === 'landscape' ? [0, 0, 841.89, 595.28] : 'A4';

            $pdf = Pdf::loadView('student.idcard.pdf', compact('students', 'schoolInfo', 'orientation'))
                ->setPaper($paperSize, $orientation)
                ->setOptions([
                    'isRemoteEnabled'      => true,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont'          => 'DejaVu Sans',
                    'dpi'                  => 150,
                ]);

            $filename = 'student-id-cards-' . now()->format('Y-m-d-His') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('ID Card download error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /* ═══════════════════════════════════════════════════════════
       SHARED HELPER — fetch full student data for cards
    ═══════════════════════════════════════════════════════════ */
    private function fetchStudentsForCards(array $ids)
    {
        return Student::whereIn('studentRegistration.id', $ids)
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('studentclass',   'studentclass.studentId',   '=', 'studentRegistration.id')
            ->leftJoin('schoolclass',    'schoolclass.id',            '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm',      'schoolarm.id',              '=', 'schoolclass.arm')
            ->leftJoin('schoolsession',  'schoolsession.id',          '=', 'studentclass.sessionid')
            ->leftJoin('schoolterm',     'schoolterm.id',             '=', 'studentclass.termid')
            ->select([
                'studentRegistration.id',
                'studentRegistration.admissionNo',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.othername',
                'studentRegistration.gender',
                'studentRegistration.dateofbirth',
                'studentRegistration.blood_group',
                'studentRegistration.phone_number',
                'studentRegistration.email',
                'studentpicture.picture',
                'schoolclass.schoolclass',
                'schoolarm.arm',
                'schoolsession.session',
                'schoolterm.term',
            ])
            ->groupBy([
                'studentRegistration.id',
                'studentRegistration.admissionNo',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.othername',
                'studentRegistration.gender',
                'studentRegistration.dateofbirth',
                'studentRegistration.blood_group',
                'studentRegistration.phone_number',
                'studentRegistration.email',
                'studentpicture.picture',
                'schoolclass.schoolclass',
                'schoolarm.arm',
                'schoolsession.session',
                'schoolterm.term',
            ])
            ->orderBy('studentRegistration.lastname')
            ->get();
    }
}
