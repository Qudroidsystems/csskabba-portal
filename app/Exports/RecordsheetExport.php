<?php

namespace App\Exports;

use App\Models\Assessment;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\SchoolInformation;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class RecordsheetExport implements FromView, ShouldAutoSize, WithStyles, WithEvents
{
    use Exportable;

    protected $schoolclassId;
    protected $subjectclassId;
    protected $termId;
    protected $sessionId;
    protected $staffId;
    protected $assessments;

    public function __construct($schoolclassId, $subjectclassId, $termId, $sessionId, $staffId)
    {
        $this->schoolclassId  = $schoolclassId;
        $this->subjectclassId = $subjectclassId;
        $this->termId         = $termId;
        $this->sessionId      = $sessionId;
        $this->staffId        = $staffId;

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

    public function styles(Worksheet $sheet)
    {
        // Header rows bold
        $lastCol = chr(ord('A') + 2 + $this->assessments->count() + 4); // sn+adm+name + assessments + total+bf+cum+grade+pos+remark
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
            },
        ];
    }
}
