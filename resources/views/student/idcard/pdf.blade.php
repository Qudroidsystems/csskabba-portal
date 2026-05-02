<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student ID Cards — {{ $schoolInfo?->school_name }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    background: #ffffff;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

/*
    CR-80 PVC card: 85.6mm × 54mm
    We render at 2× for quality: 171.2mm × 108mm
    Two pairs per A4 page (210mm wide, 297mm tall)
    Each pair = front + back side by side = 171.2mm + 6mm gap = ~178mm → fits
    Two pairs stacked + cut marks = ~108 + 8 + 108 + 8 = ~232mm → fits on A4
*/

.page-wrap {
    width: 210mm;
    padding: 8mm 6mm;
    background: #fff;
}

/* Each front+back pair */
.card-pair {
    display: table;
    width: 100%;
    margin-bottom: 0;
    page-break-inside: avoid;
    break-inside: avoid;
}
.card-pair-inner {
    display: table-row;
}
.card-cell {
    display: table-cell;
    vertical-align: top;
    padding: 0 3mm;
}
.card-label {
    font-size: 6pt;
    font-weight: bold;
    color: #94a3b8;
    letter-spacing: 1pt;
    text-transform: uppercase;
    text-align: center;
    margin-bottom: 1.5mm;
}

/* Cut guide */
.cut-guide {
    text-align: center;
    padding: 2mm 0;
    page-break-inside: avoid;
}
.cut-line {
    border: none;
    border-top: 0.5pt dashed #cbd5e1;
    margin: 0 8mm;
}
.cut-text {
    font-size: 5.5pt;
    color: #cbd5e1;
    letter-spacing: 1pt;
    margin-top: 1mm;
    font-family: 'DejaVu Sans', Arial, sans-serif;
}

/* ═══════════════════════════════════════
   CARD BASE  — fixed 85mm × 135mm
   (portrait, scaled 2× from 54×85.6mm)
   ═══════════════════════════════════════ */
.id-card {
    width: 85mm;
    height: 135mm;
    border-radius: 3mm;
    overflow: hidden;
    position: relative;
    background: #ffffff;
    border: 0.4mm solid #d1d5db;
    font-family: 'DejaVu Sans', Arial, sans-serif;
}

/* Crop marks — tiny lines at corners */
.crop-tl, .crop-tr, .crop-bl, .crop-br {
    position: absolute;
    width: 3mm; height: 3mm;
    z-index: 99;
}
.crop-tl { top:-0.5mm; left:-0.5mm; border-top:0.3mm solid #9ca3af; border-left:0.3mm solid #9ca3af; }
.crop-tr { top:-0.5mm; right:-0.5mm; border-top:0.3mm solid #9ca3af; border-right:0.3mm solid #9ca3af; }
.crop-bl { bottom:-0.5mm; left:-0.5mm; border-bottom:0.3mm solid #9ca3af; border-left:0.3mm solid #9ca3af; }
.crop-br { bottom:-0.5mm; right:-0.5mm; border-bottom:0.3mm solid #9ca3af; border-right:0.3mm solid #9ca3af; }

/* Accent bars */
.accent-bar {
    height: 2.2mm;
    background: #1e3a5f;
    font-size: 0;
    line-height: 0;
}

/* ── FRONT CARD ── */
.front-header {
    background: #1e3a5f;
    text-align: center;
    padding: 3mm 2mm 13mm;
    position: relative;
    overflow: hidden;
}
.front-header-deco1 {
    position: absolute; top: -5mm; right: -5mm;
    width: 20mm; height: 20mm;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
}
.front-header-deco2 {
    position: absolute; bottom: -8mm; left: -3mm;
    width: 24mm; height: 24mm;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
}
.logo-wrap {
    width: 17mm; height: 17mm;
    border-radius: 50%;
    overflow: hidden;
    border: 0.6mm solid rgba(255,255,255,0.5);
    background: rgba(255,255,255,0.14);
    margin: 0 auto 2mm;
    display: block;
}
.logo-wrap img { width: 100%; height: 100%; object-fit: contain; }
.logo-placeholder {
    width: 17mm; height: 17mm;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    border: 0.6mm solid rgba(255,255,255,0.4);
    margin: 0 auto 2mm;
    text-align: center;
    line-height: 17mm;
    font-size: 14pt;
    color: #fff;
}
.school-name {
    color: #ffffff;
    font-size: 7pt;
    font-weight: bold;
    line-height: 1.3;
    position: relative;
}
.school-motto {
    color: rgba(255,255,255,0.72);
    font-size: 5pt;
    font-style: italic;
    margin-top: 0.8mm;
    position: relative;
}
.id-badge {
    display: inline-block;
    background: rgba(255,255,255,0.18);
    border: 0.3mm solid rgba(255,255,255,0.38);
    color: #fff;
    font-size: 4.5pt;
    font-weight: bold;
    letter-spacing: 1.5pt;
    padding: 0.8mm 3mm;
    border-radius: 5mm;
    margin-top: 1.5mm;
    position: relative;
}

/* Photo — overlaps header */
.photo-overlap {
    text-align: center;
    margin-top: -11mm;
    margin-bottom: 1.5mm;
    position: relative;
    z-index: 5;
}
.photo-ring {
    width: 22mm; height: 22mm;
    border-radius: 50%;
    border: 1.2mm solid #ffffff;
    overflow: hidden;
    display: inline-block;
    vertical-align: top;
    background: #dbeafe;
    /* outline trick via box-shadow (DomPDF supports box-shadow) */
    box-shadow: 0 0 0 0.8mm #2169ad, 0 3px 10px rgba(33,105,173,0.4);
}
.photo-ring img { width: 100%; height: 100%; object-fit: cover; display: block; }
.photo-initials {
    width: 22mm; height: 22mm;
    border-radius: 50%;
    background: #dbeafe;
    border: 1.2mm solid #fff;
    box-shadow: 0 0 0 0.8mm #2169ad;
    display: inline-block;
    text-align: center;
    vertical-align: top;
    font-size: 14pt;
    font-weight: bold;
    color: #2169ad;
    line-height: 19.4mm;
}

/* Watermark — DomPDF supports opacity on img */
.watermark {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    text-align: center;
    z-index: 1;
}
.watermark img {
    width: 52mm;
    height: 52mm;
    object-fit: contain;
    opacity: 0.06;
    margin-top: 30mm;
}

/* Name / Adm */
.student-name {
    text-align: center;
    font-size: 7.5pt;
    font-weight: bold;
    color: #1e2937;
    padding: 0 2.5mm;
    line-height: 1.3;
    position: relative;
    z-index: 2;
}
.adm-wrap {
    text-align: center;
    margin: 1.2mm 0;
    position: relative;
    z-index: 2;
}
.adm-label {
    background: #1e3a5f;
    color: #fff;
    font-size: 4.5pt;
    font-weight: bold;
    padding: 0.7mm 1.8mm;
    border-radius: 1mm 0 0 1mm;
}
.adm-value {
    background: #eff6ff;
    color: #2169ad;
    font-size: 5pt;
    font-weight: bold;
    padding: 0.7mm 1.8mm;
    border: 0.3mm solid #bfdbfe;
    border-left: none;
    border-radius: 0 1mm 1mm 0;
}

/* Info table — float-based for DomPDF */
.info-table {
    margin: 1mm 2.5mm 0;
    border: 0.3mm solid #e2e8f0;
    border-radius: 1.5mm;
    overflow: hidden;
    position: relative;
    z-index: 2;
}
.info-row { overflow: hidden; border-bottom: 0.3mm solid #e2e8f0; }
.info-row:last-child { border-bottom: none; }
.info-lbl {
    float: left;
    width: 20mm;
    background: #eef2ff;
    padding: 1mm 1.5mm;
    font-size: 4.3pt;
    font-weight: bold;
    color: #4338ca;
    text-transform: uppercase;
    letter-spacing: 0.2pt;
}
.info-val {
    margin-left: 20mm;
    padding: 1mm 1.5mm;
    font-size: 5pt;
    font-weight: bold;
    color: #1e2937;
}
.info-clear { clear: both; }

/* QR */
.qr-section {
    text-align: center;
    padding: 1.5mm 0 0.5mm;
    position: relative;
    z-index: 2;
}
.qr-section img { width: 14mm; height: 14mm; }
.qr-label {
    font-size: 4pt;
    color: #94a3b8;
    letter-spacing: 0.6pt;
    margin-top: 0.5mm;
}

/* ── BACK CARD ── */
.back-header {
    background: #1e3a5f;
    text-align: center;
    padding: 3mm 2mm;
    position: relative;
    overflow: hidden;
}
.back-header-sub {
    color: rgba(255,255,255,0.75);
    font-size: 4.5pt;
    font-weight: bold;
    letter-spacing: 2pt;
}
.back-header-name {
    color: #fff;
    font-size: 7pt;
    font-weight: bold;
    margin-top: 1mm;
    line-height: 1.3;
}
.back-header-addr {
    color: rgba(255,255,255,0.62);
    font-size: 4.3pt;
    margin-top: 0.8mm;
}
.mag-strip {
    height: 5mm;
    background: #1a1a2e;
    font-size: 0;
}
.student-chip {
    margin: 2mm 2.5mm 0;
    background: #eef2ff;
    border-left: 1mm solid #2169ad;
    border-radius: 1.5mm;
    padding: 1.5mm 2mm;
    position: relative;
    z-index: 2;
}
.chip-name { font-size: 6pt; font-weight: bold; color: #1e3a5f; }
.chip-sub  { font-size: 4.5pt; font-weight: bold; color: #4338ca; margin-top: 0.5mm; }

.terms-section { padding: 1.5mm 2.5mm 0; position: relative; z-index: 2; }
.terms-head {
    font-size: 5pt; font-weight: bold; color: #1e3a5f;
    text-transform: uppercase; letter-spacing: 0.4pt; margin-bottom: 0.8mm;
}
.terms-list { padding-left: 3.5mm; font-size: 4.3pt; color: #4b5563; line-height: 1.65; }

.contact-box {
    margin: 1.5mm 2.5mm 0;
    background: #f0f4ff;
    border-left: 1mm solid #2169ad;
    border-radius: 1.5mm;
    padding: 1.2mm 2mm;
    position: relative;
    z-index: 2;
}
.contact-head {
    font-size: 4pt; font-weight: bold; color: #2169ad;
    text-transform: uppercase; letter-spacing: 0.6pt; margin-bottom: 0.8mm;
}
.contact-line { font-size: 4.5pt; color: #374151; margin-bottom: 0.5mm; }

/* Signatures */
.sig-section {
    margin: 2mm 2.5mm 0;
    overflow: hidden;
    position: relative;
    z-index: 2;
}
.sig-left  { float: left;  width: 47%; text-align: center; }
.sig-right { float: right; width: 47%; text-align: center; }
.sig-line  { height: 6mm; border-bottom: 0.5mm solid #1e3a5f; margin: 0 1.5mm; }
.sig-label { font-size: 4pt; color: #6b7280; margin-top: 0.8mm; }
.sig-clear { clear: both; }

.issued-section {
    text-align: center;
    margin-top: 1.5mm;
    position: relative;
    z-index: 2;
}
.issued-label  { font-size: 4pt; color: #9ca3af; letter-spacing: 0.5pt; text-transform: uppercase; }
.issued-school { font-size: 5.5pt; font-weight: bold; color: #1e3a5f; }
.issued-role   { font-size: 4pt; color: #6b7280; }

/* Barcode */
.barcode-section {
    text-align: center;
    padding: 1.5mm 0 0;
    position: relative;
    z-index: 2;
}
.barcode-section img { height: 11mm; max-width: 70mm; }
.barcode-adm  { font-size: 5pt; font-weight: bold; color: #1e3a5f; letter-spacing: 1.5pt; margin-top: 0.5mm; }
.barcode-sub  { font-size: 3.8pt; color: #94a3b8; margin-top: 0.3mm; }
.barcode-text { font-family: monospace; font-size: 14pt; color: #1e3a5f; letter-spacing: -1px; }

</style>
</head>
<body>
@php
    $schoolName = $schoolInfo?->school_name ?? 'School Name';
    $logoUrl    = ($schoolInfo && $schoolInfo->school_logo)
                    ? $schoolInfo->getLogoUrlAttribute() : null;
    $expiry     = now()->addYear()->format('F Y');
    $barcodeGen = null;
    if (class_exists(\Picqer\Barcode\BarcodeGeneratorPNG::class)) {
        $barcodeGen = new \Picqer\Barcode\BarcodeGeneratorPNG();
    }
@endphp

<div class="page-wrap">
@foreach($students as $index => $student)
@php
    $firstname  = $student->firstname  ?? '';
    $lastname   = $student->lastname   ?? '';
    $othername  = $student->othername  ?? '';
    $fullname   = trim("$firstname $othername $lastname");
    $initials   = strtoupper(substr($firstname,0,1).substr($lastname,0,1));
    $classArm   = trim(($student->schoolclass ?? '') . ' ' . ($student->arm ?? ''));
    $admNo      = $student->admissionNo ?? '';
    $dob        = !empty($student->dateofbirth)
                    ? \Carbon\Carbon::parse($student->dateofbirth)->format('d M Y') : 'N/A';
    $admDate    = !empty($student->admission_date)
                    ? \Carbon\Carbon::parse($student->admission_date)->format('d M Y') : 'N/A';
    $photoUrl   = $student->picture
                    ? asset('storage/images/student_avatars/'.$student->picture) : null;
    $phone      = $schoolInfo?->school_phone   ?? '';
    $email      = $schoolInfo?->school_email   ?? '';
    $website    = $schoolInfo?->school_website ?? '';
    $address    = $schoolInfo?->school_address ?? '';

    $rows = array_filter([
        ['Class',        $classArm],
        ['Gender',       $student->gender           ?? ''],
        ['Date of Birth',$dob !== 'N/A' ? $dob : ''],
        ['Blood Group',  $student->blood_group       ?? ''],
        ['Nationality',  $student->nationality       ?? ''],
        ['State',        $student->state             ?? ''],
        ['L.G.A',        $student->local             ?? ''],
        ['Session',      $student->session           ?? ''],
        ['Adm. Date',    $admDate !== 'N/A' ? $admDate : ''],
        ['Category',     $student->student_category  ?? ''],
        ['Status',       $student->student_status    ?? ''],
    ], fn($r) => !empty(trim($r[1])));

    $payload = base64_encode(json_encode(['id'=>$student->id,'adm'=>$admNo,'ts'=>now()->timestamp]));
    $qrB64   = base64_encode(
        \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->format('png')
            ->generate(route('student-id-cards.verify', ['token' => $payload]))
    );
    $barcodeB64 = $barcodeGen
        ? base64_encode($barcodeGen->getBarcode($admNo, $barcodeGen::TYPE_CODE_128, 2, 42))
        : null;
@endphp

<div class="card-pair">
  <div class="card-pair-inner">

    {{-- ═══ FRONT ═══ --}}
    <div class="card-cell">
      <div class="card-label">▶ FRONT</div>
      <div class="id-card">
        <div class="crop-tl"></div><div class="crop-tr"></div>
        <div class="crop-bl"></div><div class="crop-br"></div>

        {{-- Watermark --}}
        @if($logoUrl)
        <div class="watermark">
            <img src="{{ $logoUrl }}" alt="">
        </div>
        @endif

        {{-- Accent top --}}
        <div class="accent-bar"></div>

        {{-- Header --}}
        <div class="front-header">
            <div class="front-header-deco1"></div>
            <div class="front-header-deco2"></div>
            @if($logoUrl)
                <div class="logo-wrap"><img src="{{ $logoUrl }}" alt="logo"></div>
            @else
                <div class="logo-placeholder">&#127979;</div>
            @endif
            <div class="school-name">{{ $schoolName }}</div>
            @if(!empty($schoolInfo?->school_motto))
            <div class="school-motto">{{ $schoolInfo->school_motto }}</div>
            @endif
            <div class="id-badge">STUDENT ID CARD</div>
        </div>

        {{-- Photo --}}
        <div class="photo-overlap">
            @if($photoUrl)
            <div class="photo-ring">
                <img src="{{ $photoUrl }}" alt="{{ $fullname }}">
            </div>
            @else
            <div class="photo-initials">{{ $initials }}</div>
            @endif
        </div>

        {{-- Name --}}
        <div class="student-name">{{ $fullname }}</div>
        <div class="adm-wrap">
            <span class="adm-label">ADM NO</span><span class="adm-value">{{ $admNo }}</span>
        </div>

        {{-- Info rows --}}
        <div class="info-table">
            @foreach(array_values($rows) as $i => $row)
            <div class="info-row" style="{{ $i === count($rows)-1 ? 'border-bottom:none;' : '' }}">
                <div class="info-lbl">{{ $row[0] }}</div>
                <div class="info-val">{{ $row[1] }}</div>
                <div class="info-clear"></div>
            </div>
            @endforeach
        </div>

        {{-- QR --}}
        <div class="qr-section">
            <img src="data:image/png;base64,{{ $qrB64 }}" alt="QR">
            <div class="qr-label">SCAN TO VERIFY</div>
        </div>

        {{-- Accent bottom --}}
        <div class="accent-bar" style="position:absolute;bottom:0;left:0;right:0;background:#2169ad;"></div>
      </div>
    </div>

    {{-- ═══ BACK ═══ --}}
    <div class="card-cell">
      <div class="card-label">◀ BACK</div>
      <div class="id-card">
        <div class="crop-tl"></div><div class="crop-tr"></div>
        <div class="crop-bl"></div><div class="crop-br"></div>

        {{-- Watermark --}}
        @if($logoUrl)
        <div class="watermark">
            <img src="{{ $logoUrl }}" alt="">
        </div>
        @endif

        {{-- Accent top --}}
        <div class="accent-bar"></div>

        {{-- Header --}}
        <div class="back-header">
            <div class="back-header-sub">STUDENT IDENTITY CARD</div>
            <div class="back-header-name">{{ $schoolName }}</div>
            @if($address)
            <div class="back-header-addr">{{ $address }}</div>
            @endif
        </div>

        {{-- Magnetic strip --}}
        <div class="mag-strip"></div>

        {{-- Student chip --}}
        <div class="student-chip">
            <div class="chip-name">{{ $fullname }}</div>
            <div class="chip-sub">{{ $classArm ?: 'N/A' }} &nbsp;|&nbsp; {{ $admNo }}</div>
        </div>

        {{-- Terms --}}
        <div class="terms-section">
            <div class="terms-head">Terms &amp; Conditions</div>
            <ul class="terms-list">
                <li>Property of {{ $schoolName }}. Must be carried on premises at all times.</li>
                <li>Report loss immediately. Replacement fee applies.</li>
                <li>Not transferable. Misuse attracts disciplinary action.</li>
                <li>Return upon completion or withdrawal of enrolment.</li>
            </ul>
        </div>

        {{-- Contact --}}
        @if($phone || $email || $website)
        <div class="contact-box">
            <div class="contact-head">Contact</div>
            @if($phone)<div class="contact-line">Tel: {{ $phone }}</div>@endif
            @if($email)<div class="contact-line">Email: {{ $email }}</div>@endif
            @if($website)<div class="contact-line">Web: {{ $website }}</div>@endif
        </div>
        @endif

        {{-- Signatures --}}
        <div class="sig-section">
            <div class="sig-left">
                <div class="sig-line"></div>
                <div class="sig-label">Cardholder's Signature</div>
            </div>
            <div class="sig-right">
                <div class="sig-line"></div>
                <div class="sig-label">Authorised Signature</div>
            </div>
            <div class="sig-clear"></div>
        </div>

        {{-- Issued by --}}
        <div class="issued-section">
            <div class="issued-label">Issued By</div>
            <div class="issued-school">{{ $schoolName }}</div>
            <div class="issued-role">Admin Officer</div>
        </div>

        {{-- Barcode --}}
        <div class="barcode-section">
            @if($barcodeB64)
                <img src="data:image/png;base64,{{ $barcodeB64 }}" alt="barcode">
            @else
                <div class="barcode-text">||| {{ $admNo }} |||</div>
            @endif
            <div class="barcode-adm">{{ $admNo }}</div>
            <div class="barcode-sub">Valid Until: {{ $expiry }} &bull; {{ now()->year }}</div>
        </div>

        {{-- Accent bottom --}}
        <div class="accent-bar" style="position:absolute;bottom:0;left:0;right:0;background:#2169ad;"></div>
      </div>
    </div>

  </div>
</div>{{-- /.card-pair --}}

{{-- Cut guide between pairs --}}
@if(!$loop->last)
<div class="cut-guide">
    <hr class="cut-line">
    <div class="cut-text">&#9986; &nbsp; CUT HERE &nbsp; &#9986;</div>
</div>
@endif

@endforeach
</div>{{-- /.page-wrap --}}
</body>
</html>
