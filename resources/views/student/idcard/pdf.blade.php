<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Student ID Cards — {{ $schoolInfo?->school_name }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

@font-face {
    font-family: 'Nunito';
    /* Falls back to system sans-serif if Nunito not embedded */
}

body {
    font-family: 'Nunito', 'Segoe UI', Arial, sans-serif;
    background: #ffffff;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.pdf-page {
    width: 210mm;   /* A4 */
    min-height: 297mm;
    padding: 12mm;
    background: #fff;
    display: flex;
    flex-wrap: wrap;
    gap: 8mm;
    align-content: flex-start;
    justify-content: center;
}

/* Each id-card-wrap scales down slightly for 2-up layout on A4 */
.id-card-wrap {
    /* override the preview size to fit 2 pairs per A4 row */
    width: 82mm !important;
    height: 130mm !important;
    border-radius: 4mm !important;
}

/* Adjust internal font sizes proportionally when printing */
.id-card-wrap * {
    /* keep relative sizing intact – let px values scale via transform */
}

.card-pair {
    display: flex;
    flex-direction: column;
    gap: 3mm;
    align-items: center;
    page-break-inside: avoid;
    break-inside: avoid;
}

.side-label {
    font-size: 7px;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 1mm;
    text-align: center;
}

.cut-mark {
    width: 100%;
    border-top: 0.5px dashed #cbd5e1;
    margin: 4mm 0;
}

.cut-label {
    text-align: center;
    font-size: 6px;
    color: #cbd5e1;
    margin-top: -3mm;
    margin-bottom: 2mm;
    letter-spacing: 1px;
}

@media print {
    body { background: #fff; }
    .pdf-page { padding: 8mm; gap: 5mm; }
    .cut-mark { border-color: #e2e8f0; }
}
</style>
</head>
<body>
<div class="pdf-page">
    @foreach($students as $student)
    <div class="card-pair">
        <div class="side-label">&#9654; Front</div>
        @include('student.idcard.card-front', [
            'student'    => $student,
            'schoolInfo' => $schoolInfo ?? null,
        ])
        <div class="side-label" style="margin-top:2mm;">&#9664; Back</div>
        @include('student.idcard.card-back', [
            'student'    => $student,
            'schoolInfo' => $schoolInfo ?? null,
        ])
    </div>

    @if(!$loop->last && ($loop->index + 1) % 2 === 0)
    <div style="width:100%;"><div class="cut-mark"></div><div class="cut-label">✂ CUT HERE</div></div>
    @endif
    @endforeach
</div>
</body>
</html>
