<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Generates a locked-down spreadsheet template for batch student uploads.
 *
 * Column layout (0-indexed) — MUST stay in sync with App\Imports\StudentsImport:
 *
 *  0  admissionno        8  placeofbirth      16 termid (locked)      24 mother_name
 *  1  surname            9  nationality        17 sessionid (locked)  25 mother_phone
 *  2  firstname          10 state              18 father_title        26 mother_occupation
 *  3  othername          11 local              19 father_name         27 mother_office_address
 *  4  gender             12 religion           20 father_phone        28 parent_address
 *  5  homeaddress        13 lastschool         21 office_address      29 parent_religion
 *  6  dob                14 lastclass          22 father_occupation
 *  7  age                15 schoolclassid (locked) 23 mother_title
 */
class StudentBatchTemplateExport implements FromArray, WithHeadings, WithTitle, WithColumnWidths, WithEvents
{
    protected const EDITABLE_COLUMNS = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
        'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD',
    ];

    protected const LOCKED_COLUMNS = ['P', 'Q', 'R'];

    protected const TOTAL_COLUMNS = 30;

    protected int $schoolclassid;
    protected int $termid;
    protected int $sessionid;
    protected int $rows;
    protected string $className;
    protected string $termName;
    protected string $sessionName;

    public function __construct(
        int $schoolclassid,
        int $termid,
        int $sessionid,
        int $rows,
        string $className,
        string $termName,
        string $sessionName
    ) {
        $this->schoolclassid = $schoolclassid;
        $this->termid        = $termid;
        $this->sessionid     = $sessionid;
        $this->rows          = max(1, min(500, $rows));
        $this->className     = $className;
        $this->termName      = $termName;
        $this->sessionName   = $sessionName;
    }

    public function title(): string
    {
        return 'Student Data';
    }

    /**
     * Blank data rows, pre-filled with the locked class/term/session IDs
     * in columns P, Q, R (indexes 15, 16, 17).
     */
    public function array(): array
    {
        $blankRow = array_fill(0, self::TOTAL_COLUMNS, '');
        $blankRow[15] = $this->schoolclassid;
        $blankRow[16] = $this->termid;
        $blankRow[17] = $this->sessionid;

        return array_fill(0, $this->rows, $blankRow);
    }

    public function headings(): array
    {
        return [
            'Admission No*', 'Surname*', 'First Name*', 'Other Name', 'Gender*',
            'Home Address', 'Date of Birth (YYYY-MM-DD)*', 'Age', 'Place of Birth',
            'Nationality', 'State of Origin', 'LGA', 'Religion', 'Last School', 'Last Class',
            'Class ID (locked)', 'Term ID (locked)', 'Session ID (locked)',
            'Father Title', 'Father Name', 'Father Phone', 'Office Address', 'Father Occupation',
            'Mother Title', 'Mother Name', 'Mother Phone', 'Mother Occupation',
            'Mother Office Address', 'Parent Address', 'Parent Religion',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16, 'B' => 16, 'C' => 16, 'D' => 16, 'E' => 10,
            'F' => 24, 'G' => 16, 'H' => 8,  'I' => 18, 'J' => 16,
            'K' => 18, 'L' => 18, 'M' => 14, 'N' => 20, 'O' => 14,
            'P' => 12, 'Q' => 12, 'R' => 12,
            'S' => 12, 'T' => 18, 'U' => 16, 'V' => 20, 'W' => 18,
            'X' => 12, 'Y' => 18, 'Z' => 16, 'AA' => 18, 'AB' => 22,
            'AC' => 20, 'AD' => 16,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $this->rows + 1; // +1 for header row
                $lastCol = 'AD';

                // ----- Header styling -----
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4361EE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->freezePane('A2');

                // ----- Locked columns: grey fill so it's visually obvious -----
                foreach (self::LOCKED_COLUMNS as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$lastRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9ECEF']],
                        'font' => ['color' => ['rgb' => '6C757D']],
                    ]);
                }

                // ----- Sheet protection: unlock everything except P/Q/R -----
                foreach (self::EDITABLE_COLUMNS as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                        ->getProtection()
                        ->setLocked(Protection::PROTECTION_UNPROTECTED);
                }
                $sheet->getProtection()->setSheet(true);
                // No password — this protection is to stop accidental edits to the
                // ID columns, not to secure the file.

                // ----- Gender dropdown (column E) -----
                for ($row = 2; $row <= $lastRow; $row++) {
                    $v = $sheet->getCell("E{$row}")->getDataValidation();
                    $v->setType(DataValidation::TYPE_LIST);
                    $v->setErrorStyle(DataValidation::STYLE_STOP);
                    $v->setAllowBlank(true);
                    $v->setShowDropDown(true);
                    $v->setShowErrorMessage(true);
                    $v->setErrorTitle('Invalid Gender');
                    $v->setError('Please select Male or Female from the dropdown.');
                    $v->setFormula1('"Male,Female"');
                }

                // ----- Hidden "Lists" sheet for the State dropdown (37 items — too long for an inline list) -----
                $spreadsheet = $sheet->getParent();
                $listSheet   = $spreadsheet->createSheet();
                $listSheet->setTitle('Lists');

                $states = [
                    'Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno',
                    'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'FCT', 'Gombe', 'Imo',
                    'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi', 'Kwara', 'Lagos', 'Nasarawa',
                    'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo', 'Plateau', 'Rivers', 'Sokoto', 'Taraba', 'Yobe', 'Zamfara',
                ];
                foreach ($states as $i => $stateName) {
                    $listSheet->setCellValue('A' . ($i + 1), $stateName);
                }
                $listSheet->getColumnDimension('A')->setWidth(20);
                $listSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                $stateRange = 'Lists!$A$1:$A$' . count($states);
                for ($row = 2; $row <= $lastRow; $row++) {
                    $v = $sheet->getCell("K{$row}")->getDataValidation();
                    $v->setType(DataValidation::TYPE_LIST);
                    $v->setErrorStyle(DataValidation::STYLE_WARNING);
                    $v->setAllowBlank(true);
                    $v->setShowDropDown(true);
                    $v->setShowErrorMessage(true);
                    $v->setErrorTitle('Unrecognised State');
                    $v->setError('This state is not in the standard list — double check spelling.');
                    $v->setFormula1($stateRange);
                }

                // ----- Instructions sheet -----
                $infoSheet = $spreadsheet->createSheet();
                $infoSheet->setTitle('Instructions');
                $infoSheet->fromArray([
                    ['Batch Upload Template'],
                    [''],
                    ['Class', $this->className],
                    ['Term', $this->termName],
                    ['Session', $this->sessionName],
                    [''],
                    ['Instructions:'],
                    ['1. Fill in one row per student on the "Student Data" sheet.'],
                    ['2. Do NOT edit the grey Class ID / Term ID / Session ID columns — they are locked and pre-filled.'],
                    ['3. Date of Birth must be in YYYY-MM-DD format.'],
                    ['4. Gender and State have dropdown lists — please use them instead of typing freely.'],
                    ['5. Save the file and upload it back through the Batch Upload screen.'],
                ], null, 'A1');
                $infoSheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14]]);
                $infoSheet->getStyle('A3:A5')->applyFromArray(['font' => ['bold' => true]]);
                $infoSheet->getColumnDimension('A')->setWidth(28);
                $infoSheet->getColumnDimension('B')->setWidth(40);

                // Move Instructions to the front so it's the first thing the admin sees
                $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($infoSheet));
                $spreadsheet->insertSheet($infoSheet, 0);
                $spreadsheet->setActiveSheetIndex(0);
            },
        ];
    }
}