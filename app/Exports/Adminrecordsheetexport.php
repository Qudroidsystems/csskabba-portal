<?php

namespace App\Exports;

use App\Models\Assessment;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\SchoolInformation;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\Subjectclass;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdminRecordsheetExport implements FromView, ShouldAutoSize, WithStyles, WithEvents, WithProperties
{
    use Exportable;

    protected int    $schoolclassId;
    protected int    $subjectclassId;
    protected int    $termId;
    protected int    $sessionId;
    protected int    $staffId;
    protected        $assessments;
    protected string $password;

    public function __construct(
        int $schoolclassId,
        int $subjectclassId,
        int $termId,
        int $sessionId,
        int $staffId
    ) {
        $this->schoolclassId  = $schoolclassId;
        $this->subjectclassId = $subjectclassId;
        $this->termId         = $termId;
        $this->sessionId      = $sessionId;
        $this->staffId        = $staffId;
        $this->password       = $this->generateFilePassword();

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

    // -------------------------------------------------------------------------
    // Password
    // -------------------------------------------------------------------------

    protected function generateFilePassword(): string
    {
        $subjectClass = Subjectclass::with('subject')->find($this->subjectclassId);
        $schoolclass  = Schoolclass::find($this->schoolclassId);
        $term         = Schoolterm::find($this->termId);
        $session      = Schoolsession::find($this->sessionId);

        $subjectCode = ($subjectClass && $subjectClass->subject)
            ? $subjectClass->subject->subject_code
            : 'SUBJ';
        $className   = $schoolclass ? $schoolclass->schoolclass : 'CLASS';
        $termName    = $term ? substr(preg_replace('/[^a-zA-Z]/', '', $term->term), 0, 3) : 'TRM';
        $sessionYear = $session ? preg_replace('/[^0-9]/', '', $session->session) : date('Y');

        $password = strtoupper($subjectCode . '_' . $className . '_' . $termName . '_' . $sessionYear);

        return preg_replace('/[^A-Z0-9_]/', '', $password);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    // -------------------------------------------------------------------------
    // View
    // -------------------------------------------------------------------------

    public function view(): View
    {
        // Use the same join pattern as AdminScoreEntryController::getBroadsheets()
        $broadsheets = Broadsheets::query()
            ->where('broadsheets.staff_id', $this->staffId)
            ->where('broadsheets.term_id', $this->termId)
            ->where('broadsheets.subjectclass_id', $this->subjectclassId)
            ->with('assessmentScores')
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->join('subjectclass', function ($join) {
                $join->on('subjectclass.id', '=', 'broadsheets.subjectclass_id')
                    ->on('broadsheet_records.subject_id', '=', 'subjectclass.subjectid')
                    ->on('broadsheet_records.schoolclass_id', '=', 'subjectclass.schoolclassid')
                    ->where('subjectclass.id', $this->subjectclassId);
            })
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheets.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records.session_id')
            ->where('broadsheet_records.session_id', $this->sessionId)
            ->where('schoolclass.id', $this->schoolclassId)
            ->orderBy('studentRegistration.lastname')
            ->orderBy('studentRegistration.firstname')
            ->get([
                'broadsheets.id',
                'studentRegistration.admissionNO as admissionno',
                'studentRegistration.firstname as fname',
                'studentRegistration.lastname as lname',
                'studentRegistration.othername as mname',
                'subject.subject',
                'subject.subject_code',
                'schoolclass.schoolclass',
                'schoolarm.arm',
                'schoolterm.term',
                'schoolsession.session',
                'subjectclass.id as subjectclid',
                'broadsheets.staff_id',
                'broadsheets.term_id',
                'broadsheet_records.session_id as sessionid',
                'users.name as staffname',
                'studentpicture.picture',
                'broadsheets.total',
                'broadsheets.bf',
                'broadsheets.cum',
                'broadsheets.grade',
                'broadsheets.subject_position_class as position',
                'broadsheets.remark',
                'broadsheets.avg',
                'broadsheets.cmin',
                'broadsheets.cmax',
            ]);

        $school = SchoolInformation::first();

        // Reuse the teacher export view — it only cares about $broadsheets, $assessments, $school
        return view('exports.studentscoresheet', [
            'broadsheets' => $broadsheets,
            'assessments' => $this->assessments,
            'school'      => $school,
        ]);
    }

    // -------------------------------------------------------------------------
    // Properties
    // -------------------------------------------------------------------------

    public function properties(): array
    {
        $subjectClass = Subjectclass::with('subject')->find($this->subjectclassId);
        $schoolclass  = Schoolclass::find($this->schoolclassId);
        $term         = Schoolterm::find($this->termId);
        $session      = Schoolsession::find($this->sessionId);

        $subjectName = ($subjectClass && $subjectClass->subject) ? $subjectClass->subject->subject : 'subject';
        $className   = $schoolclass ? $schoolclass->schoolclass : 'class';
        $termName    = $term ? $term->term : 'term';
        $sessionName = $session ? $session->session : 'session';

        return [
            'creator'        => auth()->user()->name ?? 'Admin',
            'lastModifiedBy' => auth()->user()->name ?? 'Admin',
            'title'          => "[ADMIN] {$subjectName}_{$className}_{$termName}_{$sessionName}_Scoresheet",
            'description'    => "Admin-exported scoresheet. Password: {$this->password}",
            'subject'        => $subjectName,
            'keywords'       => 'scoresheet,marks,excel,export,admin,password_protected',
            'category'       => 'Education',
        ];
    }

    // -------------------------------------------------------------------------
    // Styles
    // -------------------------------------------------------------------------

    public function styles(Worksheet $sheet)
    {
        $assessmentCount = $this->assessments->count();
        $lastColIndex    = 2 + $assessmentCount + 6; // A=0, so col index from D onwards
        $lastCol         = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIndex + 1);

        // Unlock all data cells first
        $sheet->getStyle("A7:{$lastCol}1000")
            ->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);

        // Lock header rows 1-6
        $sheet->getStyle("A1:{$lastCol}6")
            ->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);

        // Lock non-editable data columns: SN(A), Adm(B), Name(C)
        foreach (['A', 'B', 'C'] as $col) {
            $sheet->getStyle("{$col}7:{$col}1000")
                ->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
        }

        // Lock calculated columns: Total, BF, Cum, Grade, Position, Remark
        // These start after the assessment columns (column D = index 4 = offset 3)
        $calcStartIndex = 4 + $assessmentCount; // 1-based: D=4
        foreach (range(0, 5) as $offset) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($calcStartIndex + $offset);
            $sheet->getStyle("{$col}7:{$col}1000")
                ->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
        }

        // Bold headers
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        $sheet->getStyle("A2:{$lastCol}2")->getFont()->setBold(true);
        $sheet->getStyle("A3:{$lastCol}3")->getFont()->setBold(true);
        $sheet->getStyle("A6:{$lastCol}6")->getFont()->setBold(true);

        $sheet->freezePane('A7');

        return [];
    }

    // -------------------------------------------------------------------------
    // Events
    // -------------------------------------------------------------------------

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('A7');

                // Sheet-level protection (locks locked cells)
                $sheet->getProtection()->setSheet(true);
                $sheet->getProtection()->setPassword($this->password);

                // Workbook-level protection (prompts on open)
                $spreadsheet = $sheet->getParent();
                $spreadsheet->getSecurity()->setLockWindows(true);
                $spreadsheet->getSecurity()->setLockStructure(true);
                $spreadsheet->getSecurity()->setWorkbookPassword($this->password);
            },
        ];
    }
}
