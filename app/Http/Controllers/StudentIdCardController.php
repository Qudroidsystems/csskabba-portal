<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SchoolInformation;
use App\Models\Schoolclass;
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

    /* ==========================================
       INDEX - Main Page
    ========================================== */
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

    /* ==========================================
       LOAD STUDENTS (AJAX - Pagination & Filter)
    ========================================== */
    public function loadStudents(Request $request)
    {
        try {
            $perPage = (int) $request->get('per_page', 20);
            $search  = trim($request->get('search', ''));
            $classId = $request->get('class_id');

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
            Log::error('ID Card loadStudents error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load students'], 500);
        }
    }

    /* ==========================================
       PREVIEW (Returns HTML for Modal)
    ========================================== */
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
            $students   = $this->fetchStudentsForCards($request->student_ids);
            $schoolInfo = SchoolInformation::getActiveSchool();
            $orientation = $request->orientation;

            $html = view('student.idcard.preview', compact('students', 'schoolInfo', 'orientation'))->render();

            return response()->json([
                'success' => true,
                'html'    => $html,
                'count'   => $students->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('ID Card preview error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate preview'], 500);
        }
    }

    /* ==========================================
       DOWNLOAD PDF
    ========================================== */
    public function download(Request $request)
    {
        $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:studentRegistration,id',
            'orientation'   => 'required|in:portrait,landscape',
        ]);

        try {
            $students   = $this->fetchStudentsForCards($request->student_ids);
            $schoolInfo = SchoolInformation::getActiveSchool();
            $orientation = $request->orientation;

            if ($students->isEmpty()) {
                return back()->with('error', 'No students found for the selected IDs.');
            }

            $paperSize = $orientation === 'landscape'
                ? [0, 0, 841.89, 595.28]
                : 'A4';

            $pdf = Pdf::loadView('student.idcard.pdf', compact('students', 'schoolInfo', 'orientation'))
                ->setPaper($paperSize, $orientation)
                ->setOptions([
                    'isRemoteEnabled'      => true,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont'          => 'DejaVu Sans',
                    'dpi'                  => 300,
                    'enable_remote'        => true,
                ]);

            $filename = 'student-id-cards-' . now()->format('Y-m-d-His') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('ID Card download error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /* ==========================================
       SHARED HELPER
    ========================================== */
    private function fetchStudentsForCards(array $ids)
    {
        return Student::whereIn('studentRegistration.id', $ids)
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('studentclass',   'studentclass.studentId',   '=', 'studentRegistration.id')
            ->leftJoin('schoolclass',    'schoolclass.id',           '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm',      'schoolarm.id',             '=', 'schoolclass.arm')
            ->leftJoin('schoolsession',  'schoolsession.id',         '=', 'studentclass.sessionid')
            ->leftJoin('schoolterm',     'schoolterm.id',            '=', 'studentclass.termid')
            ->select([
                'studentRegistration.id',
                'studentRegistration.admissionNo',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.othername',
                'studentRegistration.gender',
                'studentRegistration.dateofbirth',
                'studentpicture.picture as picture',
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
