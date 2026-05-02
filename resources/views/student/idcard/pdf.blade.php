<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student ID Cards — {{ $schoolInfo?->school_name }}</title>
<style>
/* ── Reset ── */
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    background: #ffffff;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    color-adjust: exact;
}

/*
    PVC CR-80 card: 85.6 mm × 54 mm
    At 96 dpi: 323 × 204 px
    We render at a comfortable A4-friendly size:
    Front+Back pair side by side, 2 pairs per A4 page (portrait)
*/

.page {
    width: 210mm;
    padding: 8mm 6mm;
    background: #fff;
}

.pair-row {
    display: flex;
    gap: 6mm;
    justify-content: center;
    margin-bottom: 6mm;
    page-break-inside: avoid;
    break-inside: avoid;
}

/* Cut guides */
.cut-row {
    text-align: center;
    margin: 2mm 0 4mm;
    page-break-inside: avoid;
}
.cut-line {
    border: none;
    border-top: 0.4pt dashed #b0bec5;
    margin: 0 10mm;
}
.cut-label {
    font-size: 6pt;
    color: #b0bec5;
    letter-spacing: 1px;
    margin-top: 1mm;
}

/* Card wrapper — mirrors the blade but DomPDF-safe (no flex gap issues) */
.id-card {
    width: 82mm;
    border-radius: 3mm;
    overflow: hidden;
    position: relative;
    background: #ffffff;
    border: 0.3mm solid #e2e8f0;
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 7pt;
}

.accent-top {
    height: 2mm;
    background: #1e3a5f; /* solid fallback for DomPDF gradient limit */
}
.accent-bottom {
    height: 2mm;
    background: #2169ad;
}

/* Header */
.card-header {
    background: #1e3a5f;
    padding: 3mm 3mm 10mm;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.card-header .school-name {
    color: #ffffff;
    font-size: 7.5pt;
    font-weight: bold;
    margin-top: 2mm;
    line-height: 1.3;
}
.card-header .school-motto {
    color: rgba(255,255,255,0.72);
    font-size: 5.5pt;
    font-style: italic;
    margin-top: 1mm;
}
.card-header .id-badge {
    display: inline-block;
    background: rgba(255,255,255,0.18);
    border: 0.3mm solid rgba(255,255,255,0.35);
    color: #fff;
    font-size: 5pt;
    font-weight: bold;
    letter-spacing: 1.5pt;
    padding: 1mm 3mm;
    border-radius: 5mm;
    margin-top: 2mm;
}

/* Logo in header */
.logo-circle {
    width: 14mm; height: 14mm;
    border-radius: 50%;
    border: 0.5mm solid rgba(255,255,255,0.5);
    overflow: hidden;
    margin: 0 auto 1.5mm;
    background: rgba(255,255,255,0.12);
    display: block;
}
.logo-circle img { width: 100%; height: 100%; object-fit: contain; }

/* Photo */
.photo-wrap {
    text-align: center;
    margin-top: -7mm;
    margin-bottom: 1.5mm;
    position: relative;
    z-index: 5;
}
.photo-circle {
    width: 20mm; height: 20mm;
    border-radius: 50%;
    border: 1mm solid #ffffff;
    outline: 0.7mm solid #2169ad;
    overflow: hidden;
    display: inline-block;
    background: #dbeafe;
    vertical-align: middle;
}
.photo-circle img { width: 100%; height: 100%; object-fit: cover; }
.initials-circle {
    width: 20mm; height: 20mm;
    border-radius: 50%;
    background: #dbeafe;
    border: 1mm solid #fff;
    outline: 0.7mm solid #2169ad;
    display: inline-flex;
    align-items: center; justify-content: center;
    font-size: 12pt; font-weight: bold; color: #2169ad;
}

/* Name & Adm */
.student-name {
    text-align: center;
    font-size: 8pt;
    font-weight: bold;
    color: #1e2937;
    padding: 0 3mm;
    line-height: 1.3;
}
.adm-badge {
    text-align: center;
    margin: 1.5mm auto;
}
.adm-label {
    display: inline;
    background: #1e3a5f;
    color: #fff;
    font-size: 5pt;
    font-weight: bold;
    padding: 0.8mm 2mm;
}
.adm-value {
    display: inline;
    background: #eff6ff;
    color: #2169ad;
    font-size: 5.5pt;
    font-weight: bold;
    padding: 0.8mm 2mm;
    border: 0.3mm solid #bfdbfe;
}

/* Info table */
.info-table {
    margin: 1.5mm 3mm 0;
    border: 0.3mm solid #e2e8f0;
    border-radius: 2mm;
    overflow: hidden;
}
.info-row {
    display: block; /* DomPDF uses block, not flex */
    overflow: hidden;
    border-bottom: 0.3mm solid #e2e8f0;
}
.info-row:last-child { border-bottom: none; }
.info-label {
    float: left;
    width: 22mm;
    background: #eef2ff;
    padding: 1.2mm 2mm;
    font-size: 5pt;
    font-weight: bold;
    color: #4338ca;
    text-transform: uppercase;
    letter-spacing: 0.3pt;
}
.info-value {
    margin-left: 22mm;
    padding: 1.2mm 2mm;
    font-size: 5.5pt;
    font-weight: bold;
    color: #1e2937;
}

/* QR */
.qr-wrap { text-align: center; padding: 2mm 0 1.5mm; }
.qr-wrap img { width: 14mm; height: 14mm; }
.qr-label { font-size: 4.5pt; color: #94a3b8; letter-spacing: 0.8pt; margin-top: 0.5mm; }

/* ── BACK CARD ── */
.mag-strip { height: 5mm; background: #1a1a2e; }

.student-chip {
    margin: 2mm 3mm 0;
    background: #eef2ff;
    border-left: 1mm solid #2169ad;
    border-radius: 1.5mm;
    padding: 1.5mm 2.5mm;
}
.chip-name  { font-size: 6.5pt; font-weight: bold; color: #1e3a5f; }
.chip-sub   { font-size: 5pt;   font-weight: bold; color: #4338ca; margin-top: 0.5mm; }

.terms-head { font-weight: bold; font-size: 5.5pt; color: #1e3a5f;
    text-transform: uppercase; letter-spacing: 0.4pt; margin-bottom: 1mm; }
.terms-list { padding-left: 4mm; color: #4b5563; font-size: 5pt; line-height: 1.6; }

.contact-box {
    margin: 2mm 3mm 0;
    background: #f0f4ff;
    border-left: 1mm solid #2169ad;
    border-radius: 1.5mm;
    padding: 1.5mm 2.5mm;
}
.contact-head { font-size: 4.5pt; font-weight: bold; color: #2169ad;
    text-transform: uppercase; letter-spacing: 0.8pt; margin-bottom: 1mm; }
.contact-line { font-size: 5pt; color: #374151; margin-bottom: 0.8mm; }

.sig-row { overflow: hidden; margin: 2.5mm 3mm 0; }
.sig-col  {
    float: left; width: 48%; text-align: center;
}
.sig-col:last-child { float: right; }
.sig-line { border-bottom: 0.5mm solid #1e3a5f; height: 7mm; margin: 0 2mm; }
.sig-label { font-size: 4.5pt; color: #6b7280; margin-top: 0.8mm; }

.issued-by { text-align: center; margin-top: 2mm; }
.issued-by .label  { font-size: 4.5pt; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5pt; }
.issued-by .school { font-size: 6pt; font-weight: bold; color: #1e3a5f; }
.issued-by .role   { font-size: 4.5pt; color: #6b7280; }

.barcode-wrap { text-align: center; padding: 2mm 0 1mm; }
.barcode-wrap img { height: 10mm; max-width: 55mm; }
.barcode-adm  { font-size: 5.5pt; font-weight: bold; color: #1e3a5f;
    letter-spacing: 1.5pt; margin-top: 0.5mm; }
.barcode-sub  { font-size: 4.5pt; color: #94a3b8; margin-top: 0.5mm; }

@media print {
    body { background: #fff; }
    .page { padding: 5mm; }
}
</style>
</head>
<body>
@php
    $schoolName = $schoolInfo?->school_name ?? 'School Name';
    $logoUrl    = ($schoolInfo && $schoolInfo->school_logo)
                    ? $schoolInfo->getLogoUrlAttribute() : null;
    $expiry     = now()->addYear()->format('F Y');

    // Barcode generator
    $barcodeGen = null;
    if (class_exists(\Picqer\Barcode\BarcodeGeneratorPNG::class)) {
        $barcodeGen = new \Picqer\Barcode\BarcodeGeneratorPNG();
    }
@endphp

<div class="page">
@foreach($students as $index => $student)
@php
    $firstname   = $student->firstname  ?? '';
    $lastname    = $student->lastname   ?? '';
    $othername   = $student->othername  ?? '';
    $fullname    = trim("$firstname $othername $lastname");
    $initials    = strtoupper(substr($firstname,0,1).substr($lastname,0,1));
    $classArm    = trim(($student->schoolclass ?? '') . ' ' . ($student->arm ?? ''));
    $admNo       = $student->admissionNo ?? '';
    $dob         = !empty($student->dateofbirth)
                    ? \Carbon\Carbon::parse($student->dateofbirth)->format('d M Y') : 'N/A';
    $admDate     = !empty($student->admission_date)
                    ? \Carbon\Carbon::parse($student->admission_date)->format('d M Y') : 'N/A';
    $photoUrl    = $student->picture
                    ? asset('storage/images/student_avatars/'.$student->picture) : null;

    $rows = array_filter([
        ['Class',        $classArm],
        ['Gender',       $student->gender        ?? ''],
        ['Date of Birth',$dob],
        ['Blood Group',  $student->blood_group   ?? ''],
        ['Nationality',  $student->nationality   ?? ''],
        ['State',        $student->state         ?? ''],
        ['L.G.A',        $student->local         ?? ''],
        ['Session',      $student->session       ?? ''],
        ['Adm. Date',    $admDate],
        ['Category',     $student->student_category ?? ''],
        ['Status',       $student->student_status   ?? ''],
    ], fn($r) => !empty(trim($r[1])) && trim($r[1]) !== 'N/A');

    $payload = base64_encode(json_encode(['id'=>$student->id,'adm'=>$admNo,'ts'=>now()->timestamp]));
    $qrB64   = base64_encode(
        \SimpleSoftwareIO\QrCode\Facades\QrCode::size(110)->format('png')
            ->generate(route('student-id-cards.verify', ['token' => $payload]))
    );

    $barcodeB64 = null;
    if ($barcodeGen) {
        $barcodeB64 = base64_encode(
            $barcodeGen->getBarcode($admNo, $barcodeGen::TYPE_CODE_128, 2, 40)
        );
    }

    $phone   = $schoolInfo?->school_phone   ?? '';
    $email   = $schoolInfo?->school_email   ?? '';
    $website = $schoolInfo?->school_website ?? '';
@endphp

<div class="pair-row">

    {{-- ════ FRONT ════ --}}
    <div class="id-card">
        <div class="accent-top"></div>

        <div class="card-header">
            @if($logoUrl)
            <div class="logo-circle"><img src="{{ $logoUrl }}" alt="logo"></div>
            @endif
            <div class="school-name">{{ $schoolName }}</div>
            @if(!empty($schoolInfo?->school_motto))
            <div class="school-motto">{{ $schoolInfo->school_motto }}</div>
            @endif
            <div class="id-badge">STUDENT ID CARD</div>
        </div>

        <div class="photo-wrap">
            @if($photoUrl)
            <div class="photo-circle">
                <img src="{{ $photoUrl }}" alt="{{ $fullname }}">
            </div>
            @else
            <div class="initials-circle">{{ $initials }}</div>
            @endif
        </div>

        <div class="student-name">{{ $fullname }}</div>
        <div class="adm-badge">
            <span class="adm-label">ADM NO</span><span class="adm-value">{{ $admNo }}</span>
        </div>

        <div class="info-table">
            @foreach(array_values($rows) as $i => $row)
            <div class="info-row" style="{{ $i === count($rows)-1 ? 'border-bottom:none;' : '' }}">
                <div class="info-label">{{ $row[0] }}</div>
                <div class="info-value">{{ $row[1] }}</div>
                <div style="clear:both;"></div>
            </div>
            @endforeach
        </div>

        <div class="qr-wrap">
            <img src="data:image/png;base64,{{ $qrB64 }}" alt="QR">
            <div class="qr-label">SCAN TO VERIFY</div>
        </div>

        <div class="accent-bottom"></div>
    </div>

    {{-- ════ BACK ════ --}}
    <div class="id-card">
        <div class="accent-top"></div>

        <div class="card-header" style="padding-bottom:3mm;">
            <div style="color:rgba(255,255,255,.75);font-size:5pt;font-weight:bold;letter-spacing:2pt;">
                STUDENT IDENTITY CARD
            </div>
            <div class="school-name">{{ $schoolName }}</div>
            @if(!empty($schoolInfo?->school_address))
            <div style="color:rgba(255,255,255,.62);font-size:5pt;margin-top:1mm;">
                {{ $schoolInfo->school_address }}
            </div>
            @endif
        </div>

        <div class="mag-strip"></div>

        <div class="student-chip">
            <div class="chip-name">{{ $fullname }}</div>
            <div class="chip-sub">{{ $classArm ?: 'N/A' }} &nbsp;|&nbsp; {{ $admNo }}</div>
        </div>

        <div style="padding:2mm 3mm 0;">
            <div class="terms-head">Terms &amp; Conditions</div>
            <ul class="terms-list">
                <li>Property of {{ $schoolName }}. Must be carried at all times on premises.</li>
                <li>Report loss immediately. Replacement fee applies.</li>
                <li>Not transferable. Misuse attracts disciplinary action.</li>
                <li>Return on completion or withdrawal of enrolment.</li>
            </ul>
        </div>

        @if($phone || $email || $website)
        <div class="contact-box">
            <div class="contact-head">Contact</div>
            @if($phone)<div class="contact-line">Tel: {{ $phone }}</div>@endif
            @if($email)<div class="contact-line">Email: {{ $email }}</div>@endif
            @if($website)<div class="contact-line">Web: {{ $website }}</div>@endif
        </div>
        @endif

        <div class="sig-row" style="overflow:hidden;">
            <div class="sig-col">
                <div class="sig-line"></div>
                <div class="sig-label">Cardholder's Signature</div>
            </div>
            <div class="sig-col">
                <div class="sig-line"></div>
                <div class="sig-label">Authorised Signature</div>
            </div>
        </div>

        <div class="issued-by">
            <div class="label">Issued By</div>
            <div class="school">{{ $schoolName }}</div>
            <div class="role">Admin Officer</div>
        </div>

        <div class="barcode-wrap">
            @if($barcodeB64)
                <img src="data:image/png;base64,{{ $barcodeB64 }}" alt="barcode">
            @else
                <div style="font-family:monospace;font-size:16pt;letter-spacing:-1px;color:#1e3a5f;">
                    ||| {{ $admNo }} |||
                </div>
            @endif
            <div class="barcode-adm">{{ $admNo }}</div>
            <div class="barcode-sub">Valid Until: {{ $expiry }} &bull; {{ now()->year }}</div>
        </div>

        <div class="accent-bottom"></div>
    </div>

</div>{{-- /.pair-row --}}

@if(!$loop->last)
<div class="cut-row">
    <hr class="cut-line">
    <div class="cut-label">✂ &nbsp; CUT HERE &nbsp; ✂</div>
</div>
@endif

@endforeach
</div>{{-- /.page --}}
</body>
</html>
