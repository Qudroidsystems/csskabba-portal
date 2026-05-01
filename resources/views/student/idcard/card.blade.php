@php
    $fullname = trim(($student->firstname ?? '') . ' ' . ($student->lastname ?? ''));
    $className = optional($student->currentTerm?->schoolClass)->schoolclass ?? 'N/A';
    $arm = optional(optional($student->currentTerm?->schoolClass)->armRelation)->arm ?? '';

    // QR Code
    $qr = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(110)
            ->format('png')
            ->generate($student->admissionNo . '|' . $fullname));

    // Barcode
    $barcodeGenerator = new Picqer\Barcode\BarcodeGeneratorPNG();
    $barcode = base64_encode($barcodeGenerator->getBarcode($student->admissionNo, $barcodeGenerator::TYPE_CODE_128));
@endphp

<div class="id-card {{$orientation}}" style="width: {{ $orientation === 'landscape' ? '420px' : '295px' }}; height: {{ $orientation === 'landscape' ? '260px' : '420px' }}; border: 2px solid #1e3a5f; background: white; position: relative; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.15); margin: 10px;">

    <!-- Holographic Security Overlay -->
    <div class="holo"></div>

    <!-- Header -->
    <div style="background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%); color: white; padding: 12px; text-align: center;">
        @if($schoolInfo && $schoolInfo->school_logo)
            <img src="{{ $schoolInfo->getLogoUrlAttribute() }}" style="height: 48px;" alt="Logo">
        @endif
        <h4 style="margin: 6px 0 2px; font-size: 15px;">{{ $schoolInfo?->school_name }}</h4>
        <small style="opacity: 0.9;">{{ $schoolInfo?->school_motto }}</small>
    </div>

    <div style="display: flex; padding: 15px; gap: 15px;">
        <!-- Photo -->
        <div>
            @if($student->picture && $student->picture->picture)
                <img src="{{ asset('storage/images/student_avatars/'.$student->picture->picture) }}"
                     style="width: 110px; height: 130px; object-fit: cover; border: 3px solid #2563eb;" alt="">
            @else
                <div style="width:110px;height:130px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-size:32px;color:#64748b;border:3px solid #cbd5e1;">
                    {{ strtoupper(substr($student->firstname??'',0,1).substr($student->lastname??'',0,1)) }}
                </div>
            @endif
        </div>

        <!-- Info -->
        <div style="flex: 1;">
            <h5 style="margin:0 0 6px; color:#1e2937;">{{ $fullname }}</h5>
            <p style="margin:4px 0; font-size:13px;"><strong>{{ $student->admissionNo }}</strong></p>
            <p style="margin:6px 0 4px; font-size:13px;">{{ $className }} {{ $arm }}</p>
            <p style="font-size:12.5px; color:#475569;">{{ $student->gender }}</p>
        </div>
    </div>

    <!-- QR & Barcode -->
    <div style="text-align:center; padding: 8px 0;">
        <img src="data:image/png;base64,{{ $qr }}" style="height: 85px;">
        <div style="margin-top: 6px;">
            <img src="data:image/png;base64,{{ $barcode }}" style="height: 38px;">
        </div>
    </div>

    <!-- Footer -->
    <div style="position:absolute; bottom:12px; left:0; right:0; text-align:center; font-size:10px; color:#64748b;">
        Valid Until: <strong>{{ now()->addYear()->format('F Y') }}</strong><br>
        <small>Student ID Card • {{ now()->year }}</small>
    </div>

    <div style="position:absolute; bottom:12px; right:12px; font-size:9px; color:#94a3b8;">{{ $student->id }}</div>
</div>

<style>
.holo {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(
        135deg,
        rgba(255,255,255,0.15) 0%,
        rgba(255,255,255,0.3) 50%,
        rgba(255,255,255,0.15) 100%
    );
    pointer-events: none;
    z-index: 2;
    animation: holo-shimmer 8s infinite linear;
}
@keyframes holo-shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(300%); }
}
.id-card { box-sizing: border-box; }
</style>
