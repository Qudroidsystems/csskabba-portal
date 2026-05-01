{{--
    ID Card BACK — Portrait
    Same flat data shape as card-front.blade.php
--}}
@php
    $fullname  = trim(($student->firstname ?? '') . ' ' . ($student->lastname ?? ''));
    $logoUrl   = ($schoolInfo && $schoolInfo->school_logo)
        ? $schoolInfo->getLogoUrlAttribute()
        : null;
    $expiry    = now()->addYear()->format('F Y');

    $barcodeGen = new Picqer\Barcode\BarcodeGeneratorPNG();
    $barcode    = base64_encode(
        $barcodeGen->getBarcode(
            $student->admissionNo ?? '000000',
            $barcodeGen::TYPE_CODE_128,
            2, 50
        )
    );
@endphp

<div class="id-card-wrap" style="
    width:323px;height:510px;border-radius:10px;overflow:hidden;
    position:relative;background:#ffffff;
    box-shadow:0 8px 32px rgba(0,0,0,.18);
    font-family:'Nunito','Segoe UI',sans-serif;flex-shrink:0;
">
    <div style="position:absolute;top:0;left:0;right:0;height:8px;
        background:linear-gradient(90deg,#1e3a5f 0%,#2169ad 50%,#4f46e5 100%);"></div>

    {{-- HEADER --}}
    <div style="background:linear-gradient(150deg,#1e3a5f 0%,#2169ad 60%,#1e3a5f 100%);
        padding:20px 16px 14px;text-align:center;position:relative;overflow:hidden;">
        <div style="position:absolute;top:-20px;left:-20px;width:80px;height:80px;
            border-radius:50%;background:rgba(255,255,255,.05);"></div>
        <div style="color:#fff;font-size:9px;font-weight:700;letter-spacing:2.5px;opacity:.8;">
            STUDENT IDENTITY CARD
        </div>
        <div style="color:#fff;font-weight:800;font-size:12px;margin-top:3px;">
            {{ $schoolInfo?->school_name ?? 'School Name' }}
        </div>
    </div>

    {{-- MAGNETIC STRIP --}}
    <div style="height:18px;background:#1a1a2e;"></div>

    {{-- TERMS --}}
    <div style="padding:10px 16px 0;font-size:8.5px;color:#374151;line-height:1.6;">
        <div style="font-weight:700;font-size:9px;color:#1e3a5f;margin-bottom:4px;
            text-transform:uppercase;letter-spacing:.5px;">Terms &amp; Conditions</div>
        <ul style="margin:0;padding-left:14px;color:#4b5563;">
            <li>This card is the property of {{ $schoolInfo?->school_name ?? 'the school' }} and must be carried at all times.</li>
            <li>Report loss immediately to the school office. A replacement fee applies.</li>
            <li>Not transferable. Misuse will result in disciplinary action.</li>
            <li>Return this card upon completion of enrolment.</li>
        </ul>
    </div>

    {{-- CONTACT BLOCK --}}
    @php
        $address = $schoolInfo?->school_address ?? '';
        $phone   = $schoolInfo?->school_phone   ?? '';
        $email   = $schoolInfo?->school_email   ?? '';
        $website = $schoolInfo?->school_website ?? '';
    @endphp
    @if($address || $phone || $email)
    <div style="margin:10px 16px 0;background:#f0f4ff;border-radius:8px;
        padding:8px 10px;border-left:3px solid #2169ad;">
        <div style="font-size:8px;font-weight:700;color:#2169ad;text-transform:uppercase;
            letter-spacing:.8px;margin-bottom:4px;">School Contact</div>
        @if($address)
        <div style="font-size:8.5px;color:#374151;margin-bottom:2px;">&#128205; {{ $address }}</div>
        @endif
        @if($phone)
        <div style="font-size:8.5px;color:#374151;margin-bottom:2px;">&#128222; {{ $phone }}</div>
        @endif
        @if($email)
        <div style="font-size:8.5px;color:#374151;margin-bottom:2px;">&#9993; {{ $email }}</div>
        @endif
        @if($website)
        <div style="font-size:8.5px;color:#2169ad;">&#127760; {{ $website }}</div>
        @endif
    </div>
    @endif

    {{-- SIGNATURES --}}
    <div style="margin:12px 16px 0;display:flex;gap:10px;">
        <div style="flex:1;text-align:center;">
            <div style="height:36px;border-bottom:1.5px solid #1e3a5f;margin:0 6px;"></div>
            <div style="font-size:8px;color:#6b7280;margin-top:3px;">Cardholder's Signature</div>
        </div>
        <div style="flex:1;text-align:center;">
            <div style="height:36px;border-bottom:1.5px solid #1e3a5f;margin:0 6px;"></div>
            <div style="font-size:8px;color:#6b7280;margin-top:3px;">Authorised Signature</div>
        </div>
    </div>

    {{-- ISSUED BY --}}
    <div style="text-align:center;margin-top:10px;padding:0 16px;">
        <div style="font-size:8px;color:#9ca3af;letter-spacing:.5px;">ISSUED BY</div>
        <div style="font-size:10px;font-weight:700;color:#1e3a5f;">
            {{ $schoolInfo?->school_name ?? 'School Administration' }}
        </div>
        <div style="font-size:8.5px;color:#6b7280;">Admin Officer</div>
    </div>

    {{-- BARCODE --}}
    <div style="text-align:center;padding:10px 0 6px;">
        <img src="data:image/png;base64,{{ $barcode }}" style="height:40px;max-width:220px;" alt="barcode">
        <div style="font-size:9px;font-weight:700;color:#1e3a5f;letter-spacing:2px;margin-top:2px;">
            {{ $student->admissionNo }}
        </div>
        <div style="font-size:7.5px;color:#94a3b8;margin-top:1px;">
            Valid Until: <strong>{{ $expiry }}</strong> &nbsp;|&nbsp; {{ now()->year }}
        </div>
    </div>

    <div style="position:absolute;bottom:0;left:0;right:0;height:6px;
        background:linear-gradient(90deg,#4f46e5 0%,#2169ad 50%,#1e3a5f 100%);"></div>
</div>
