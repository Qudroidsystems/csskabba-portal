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
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
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

    public function __construct($schoolclassId, $subjectclassId, $termId, $sessionId, $staffId)
    {
        $this->schoolclassId  = $schoolclassId;
        $this->subjectclassId = $subjectclassId;
        $this->termId         = $termId;
        $this->sessionId      = $sessionId;
        $this->staffId        = $staffId;

        // Use a fixed password or generate one based on teacher/session
        // This password will be required to OPEN the Excel file
        $this->password = $this->generateFilePassword();

        // Load dynamic assessments
        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassId);
        $this->assessments = collect();
        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $this->assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->orderBy('id')
                ->get();
        }
    }

    /**
     * Generate a password for the Excel file
     * You can customize this logic
     */
    protected function generateFilePassword()
    {
        // Option 1: Use a fixed school-wide password
        // return env('EXCEL_OPEN_PASSWORD', 'ClaretSchool2024!');

        // Option 2: Generate password based on subject, class, term, session
        $subjectClass = \App\Models\Subjectclass::find($this->subjectclassId);
        $schoolclass = \App\Models\Schoolclass::find($this->schoolclassId);
        $term = \App\Models\SchoolTerm::find($this->termId);
        $session = \App\Models\SchoolSession::find($this->sessionId);

        $subjectCode = $subjectClass && $subjectClass->subject ? $subjectClass->subject->subject_code : 'SUBJ';
        $className = $schoolclass ? $schoolclass->schoolclass : 'CLASS';
        $termName = $term ? substr($term->term, 0, 3) : 'TRM';
        $sessionYear = $session ? $session->session : date('Y');

        // Generate password: e.g., "CDN_JSS1_1ST_2025"
        $password = strtoupper($subjectCode . '_' . $className . '_' . $termName . '_' . $sessionYear);
        $password = preg_replace('/[^A-Z0-9_]/', '', $password);

        return $password;
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
        $subjectClass = Subjectclass::find($this->subjectclassId);
        $schoolclass = Schoolclass::find($this->schoolclassId);
        $term = Schoolterm::find($this->termId);
        $session = Schoolsession::find($this->sessionId);

        $subjectName = $subjectClass && $subjectClass->subject ? $subjectClass->subject->subject : 'subject';
        $className = $schoolclass ? $schoolclass->schoolclass : 'class';
        $termName = $term ? $term->term : 'term';
        $sessionName = $session ? $session->session : 'session';

        return [
            'creator'        => auth()->user()->name ?? 'Teacher',
            'lastModifiedBy' => auth()->user()->name ?? 'Teacher',
            'title'          => "{$subjectName}_{$className}_{$termName}_{$sessionName}_Scoresheet",
            'description'    => "Password protected scoresheet for {$subjectName} - {$className} - {$termName} - {$sessionName}",
            'subject'        => $subjectName,
            'keywords'       => 'scoresheet,marks,excel,export,password_protected',
            'category'       => 'Education',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Get the last column letter
        $lastCol = chr(ord('A') + 2 + $this->assessments->count() + 6);

        // Unlock assessment score cells only
        // First, lock all cells
        $sheet->getStyle('A1:' . $lastCol . '1000')->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);

        // Unlock only the assessment score columns (columns D, E, F etc. for data rows)
        $assessmentStartCol = 3; // D column (index 3)
        for ($i = 0; $i < $this->assessments->count(); $i++) {
            $colLetter = chr(ord('D') + $i);
            $sheet->getStyle($colLetter . '7:' . $colLetter . '1000')->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
        }

        // Apply bold styling to headers
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
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('A7');

                // Enable sheet protection with password
                // This prevents editing of locked cells
                $sheet->getProtection()->setSheet(true);
                $sheet->getProtection()->setPassword($this->password);

                // Also protect the workbook structure with the same password
                // This prompts for password when opening the file
                $spreadsheet = $event->sheet->getDelegate()->getParent();
                $spreadsheet->getSecurity()->setLockWindows(true);
                $spreadsheet->getSecurity()->setLockStructure(true);
                $spreadsheet->getSecurity()->setWorkbookPassword($this->password);
            },
        ];
    }

    /**
     * Get the password (for display if needed)
     */
    public function getPassword()
    {
        return $this->password;
    }
}
