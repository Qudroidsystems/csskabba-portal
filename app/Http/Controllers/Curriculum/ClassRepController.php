<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\Controller;
use App\Models\ClassRep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Admin assigns up to two class reps per arm. Reps confirm that topics were
 * actually taught (see TopicConfirmController).
 */
class ClassRepController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:Manage topics');
    }

    public function index(Request $request)
    {
        $arm = $request->input('class');
        $currentSession = DB::table('schoolsession')->where('status', 'Current')->value('id');

        $reps = collect();
        $students = collect();
        if ($arm) {
            $reps = DB::table('class_reps as cr')
                ->leftJoin('studentRegistration as s', 's.id', '=', 'cr.student_id')
                ->where('cr.schoolclass_id', $arm)
                ->selectRaw("cr.*, TRIM(CONCAT(COALESCE(s.firstname,''),' ',COALESCE(s.lastname,''))) as name, s.admissionNo")
                ->get();

            $students = DB::table('studentclass as sc')
                ->join('studentRegistration as s', 's.id', '=', 'sc.studentId')
                ->where('sc.schoolclassid', $arm)
                ->when($currentSession, fn ($q) => $q->where('sc.sessionid', $currentSession))
                ->orderBy('s.lastname')->orderBy('s.firstname')
                ->selectRaw("s.id, TRIM(CONCAT(s.firstname,' ',s.lastname)) as name, s.admissionNo")
                ->get();
        }

        return view('curriculum.reps.index', [
            'pagetitle' => 'Class Reps',
            'arms'      => $this->arms(),
            'arm'       => $arm,
            'reps'      => $reps,
            'students'  => $students,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'     => 'required|integer',
            'schoolclass_id' => 'required|integer',
        ]);
        $count = ClassRep::where('schoolclass_id', $data['schoolclass_id'])->count();
        if ($count >= 2) return back()->with('error', 'An arm can have at most two class reps.');
        if (ClassRep::where('schoolclass_id', $data['schoolclass_id'])->where('student_id', $data['student_id'])->exists()) {
            return back()->with('error', 'That student is already a rep for this arm.');
        }

        ClassRep::create([
            'student_id'     => $data['student_id'],
            'schoolclass_id' => $data['schoolclass_id'],
            'session_id'     => DB::table('schoolsession')->where('status', 'Current')->value('id'),
            'term_id'        => DB::table('schoolterm')->where('status', 1)->value('id'),
            'assigned_by'    => Auth::id(),
        ]);
        return back()->with('success', 'Class rep assigned.');
    }

    public function destroy(ClassRep $rep)
    {
        $rep->delete();
        return back()->with('success', 'Class rep removed.');
    }

    protected function arms()
    {
        return DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->selectRaw("schoolclass.id, TRIM(CONCAT(schoolclass.schoolclass,' ',COALESCE(schoolarm.arm,''))) as name")
            ->orderBy('schoolclass.schoolclass')->get();
    }
}
