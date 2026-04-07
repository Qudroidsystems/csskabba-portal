<?php

namespace App\Exports;

use App\Models\Assessment;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\SchoolInformation;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Subjectclass;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RecordsheetExport implements FromView, ShouldAutoSize, WithStyles, WithEvents, WithProperties
{
    use Exportable;

    protected $schoolclassId;
    protected $subjectclassId;
    protected $termId;
    protected $sessionId;
    protected $staffId;
    protected $assessments;
    protected $password;

    public function __construct($schoolclassId, $subjectclassId, $termId, $sessionId, $staffId, $password = null)
    {
        $this->schoolclassId  = $schoolclassId;
        $this->subjectclassId = $subjectclassId;
        $this->termId         = $termId;
        $this->sessionId      = $sessionId;
        $this->staffId        = $staffId;
        $this->password       = $password;

        // Load dynamic assessments once for reuse in view + styles
        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassId);
        $this->assessments = collect();
        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $this->assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->orderBy('id')
                ->get();
        }
    }

    public function view(): View
    {
        $broadsheets = Broadsheets::where('broadsheets.subjectclass_id', $this->subjectclassId)
            ->where('broadsheets.staff_id', $this->staffId)
            ->where('broadsheets.term_id', $this->termId)
            ->with('assessmentScores')
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheets.subjectclass_id')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheets.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records.session_id')
            ->where('broadsheet_records.session_id', $this->sessionId)
            ->orderBy('studentRegistration.lastname')
            ->orderBy('studentRegistration.firstname')
            ->get([
                'broadsheets.id',
                'studentRegistration.admissionNO as admissionno',
                'studentRegistration.firstname as fname',
                'studentRegistration.lastname as lname',
                'studentRegistration.othername as mname',
                'subject.subject', 'subject.subject_code',
                'schoolclass.schoolclass', 'schoolarm.arm',
                'schoolterm.term', 'schoolsession.session',
                'subjectclass.id as subjectclid', 'broadsheets.staff_id', 'broadsheets.term_id',
                'broadsheet_records.session_id as sessionid', 'users.name as staffname',
                'studentpicture.picture',
                'broadsheets.total', 'broadsheets.bf', 'broadsheets.cum',
                'broadsheets.grade', 'broadsheets.subject_position_class as position',
                'broadsheets.remark', 'broadsheets.avg', 'broadsheets.cmin', 'broadsheets.cmax',
            ]);

        $school = SchoolInformation::getActiveSchool();

        return view('exports.studentscoresheet', [
            'broadsheets' => $broadsheets,
            'assessments' => $this->assessments,
            'school'      => $school,
        ]);
    }

    public function properties(): array
    {
        // Get subject, class, term, session for filename
        $subjectClass = Subjectclass::find($this->subjectclassId);
        $schoolclass = Schoolclass::find($this->schoolclassId);
        $term = Schoolterm::find($this->termId);
        $session = Schoolsession::find($this->sessionId);

        $subjectName = $subjectClass && $subjectClass->subject ? $subjectClass->subject->subject : 'subject';
        $className = $schoolclass ? $schoolclass->schoolclass : 'class';
        $termName = $term ? $term->term : 'term';
        $sessionName = $session ? $session->session : 'session';

        // Clean up names for filename
        $subjectName = preg_replace('/[^a-zA-Z0-9-]/', '_', $subjectName);
        $className = preg_replace('/[^a-zA-Z0-9-]/', '_', $className);
        $termName = preg_replace('/[^a-zA-Z0-9-]/', '_', $termName);
        $sessionName = preg_replace('/[^a-zA-Z0-9-]/', '_', $sessionName);

        return [
            'creator'        => auth()->user()->name ?? 'Teacher',
            'lastModifiedBy' => auth()->user()->name ?? 'Teacher',
            'title'          => "{$subjectName}_{$className}_{$termName}_{$sessionName}_Scoresheet",
            'description'    => "Scoresheet for {$subjectName} - {$className} - {$termName} - {$sessionName}",
            'subject'        => $subjectName,
            'keywords'       => 'scoresheet,marks,excel,export',
            'category'       => 'Education',
            'manager'        => 'School Administrator',
            'company'        => 'School Management System',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Lock all cells by default
        $sheet->getProtection()->setSheet(true);

        // Get the last column letter
        $lastCol = chr(ord('A') + 2 + $this->assessments->count() + 4);

        // Lock all cells in the data area (rows 7 and below, assessment columns only)
        // Unlock only the assessment score cells (columns D, E, F for data rows)
        $assessmentColumns = ['D', 'E', 'F']; // Adjust based on your structure

        // First, lock the entire sheet
        $sheet->getStyle('A1:' . $lastCol . '1000')->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);

        // Then unlock only the assessment score cells in data rows
        foreach ($assessmentColumns as $col) {
            $sheet->getStyle($col . '7:' . $col . '1000')->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
        }

        // Header rows styling
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        $sheet->getStyle("A2:{$lastCol}2")->getFont()->setBold(true);
        $sheet->getStyle("A3:{$lastCol}3")->getFont()->setBold(true);
        $sheet->getStyle("A6:{$lastCol}6")->getFont()->setBold(true);

        // Freeze pane
        $sheet->freezePane('A7');

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->freezePane('A7');

                // Set password protection if provided
                if ($this->password) {
                    $event->sheet->getDelegate()->getProtection()->setPassword($this->password);
                }
            },
        ];
    }
}
