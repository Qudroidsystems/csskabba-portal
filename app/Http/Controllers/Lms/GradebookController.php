<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Lms\Concerns\InteractsWithLms;
use App\Models\LmsCourse;
use App\Services\Lms\ProgressService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradebookController extends Controller
{
    use InteractsWithLms;

    public function __construct(protected ProgressService $progress)
    {
        $this->middleware('auth');
        $this->middleware('permission:Manage courses|Grade coursework');
    }

    public function show(LmsCourse $course)
    {
        $this->authorizeGrade($course);
        return view('lms.gradebook', [
            'pagetitle' => 'Gradebook — ' . $course->title,
            'course'    => $course,
            'rows'      => $this->progress->gradebook($course),
        ]);
    }

    public function export(LmsCourse $course): StreamedResponse
    {
        $this->authorizeGrade($course);
        $rows = $this->progress->gradebook($course);
        $filename = 'gradebook-' . $course->id . '-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Admission No', 'Student', 'Progress %', 'Quiz avg %', 'Assignment avg %', 'Status']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['admissionNo'], $r['name'], $r['progress'],
                    $r['quiz_avg'] ?? '', $r['assignment_avg'] ?? '', $r['status'],
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
