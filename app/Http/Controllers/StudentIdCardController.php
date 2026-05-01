<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\SchoolInformation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Picqer\Barcode\BarcodeGeneratorPNG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StudentIdCardController extends Controller
{
    public function __construct()
    {
        $this->middleware("permission:View id card|Generate id card|Print id card");
    }

    public function index()
    {
        $pagetitle = "Student ID Card Generator";

        $schoolclasses = \App\Models\Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->selectRaw("schoolclass.id, CONCAT(schoolclass.schoolclass, ' - ', schoolarm.arm) as class_display")
            ->orderBy('schoolclass.schoolclass')
            ->get();

        $schoolsessions = \App\Models\Schoolsessions::select('id', 'session')->get();
        $schoolterms    = \App\Models\Schoolterm::select('id', 'term')->get();

        $schoolInfo = SchoolInformation::getActiveSchool();

        return view('student.idcard.index', compact(
            'pagetitle', 'schoolclasses', 'schoolsessions', 'schoolterms', 'schoolInfo'
        ));
    }

    /**
     * AJAX: Load Students for ID Card Selection
     */
    public function loadStudents(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $search  = $request->get('search', '');
            $classId = $request->get('class_id');

            $query = Student::query()
                ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('student_current_term', 'student_current_term.studentId', '=', 'studentRegistration.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'student_current_term.schoolclassId')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
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
                ->where('student_current_term.is_current', true)
                ->orderBy('studentRegistration.lastname');

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('studentRegistration.firstname', 'LIKE', "%{$search}%")
                      ->orWhere('studentRegistration.lastname', 'LIKE', "%{$search}%")
                      ->orWhere('studentRegistration.admissionNo', 'LIKE', "%{$search}%");
                });
            }

            if (!empty($classId)) {
                $query->where('student_current_term.schoolclassId', $classId);
            }

            $students = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data'    => $students
            ]);

        } catch (\Exception $e) {
            Log::error('ID Card loadStudents: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load students'], 500);
        }
    }

    /**
     * Preview ID Cards (AJAX)
     */
    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:studentRegistration,id',
            'orientation' => 'required|in:portrait,landscape',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $students = Student::whereIn('id', $request->student_ids)
            ->with(['picture', 'currentTerm.schoolClass.armRelation'])
            ->get();

        $schoolInfo = SchoolInformation::getActiveSchool();
        $orientation = $request->orientation;

        $html = view('student.idcard.preview', compact('students', 'schoolInfo', 'orientation'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
            'count'   => $students->count()
        ]);
    }

    /**
     * Download ID Cards as PDF
     */
    public function download(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:studentRegistration,id',
            'orientation' => 'required|in:portrait,landscape',
        ]);

        $students = Student::whereIn('id', $request->student_ids)
            ->with(['picture', 'currentTerm.schoolClass.armRelation'])
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No students selected.');
        }

        $schoolInfo = SchoolInformation::getActiveSchool();
        $orientation = $request->orientation;

        $pdf = Pdf::loadView('student.idcard.pdf', compact('students', 'schoolInfo', 'orientation'))
            ->setPaper($orientation === 'landscape' ? [0, 0, 842, 595] : 'A4', $orientation) // Landscape = wider
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        $filename = 'student-id-cards-' . now()->format('Y-m-d-His') . '.pdf';

        return $pdf->download($filename);
    }
}
