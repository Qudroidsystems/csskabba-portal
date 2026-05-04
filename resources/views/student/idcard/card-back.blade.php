{{--
    ID Card BACK — Portrait
    Barcode: uses BarcodeGeneratorSVG (same picqer package, inline SVG — no PNG needed)
    Falls back to text-only if package not installed.
--}}
@php
    $firstname  = $student->firstname  ?? '';
    $lastname   = $student->lastname   ?? '';
    $othername  = $student->othername  ?? '';
    // Reordered: Last Name, First Name Othername (matching front card)
    $fullname   = trim("$lastname $firstname $othername");
    $classArm   = trim(($student->schoolclass ?? '') . ' ' . ($student->arm ?? ''));
    $expiry     = now()->addYear()->format('F Y');
    $logoUrl    = ($schoolInfo && $schoolInfo->school_logo)
                    ? $schoolInfo->getLogoUrlAttribute() : null;

    // Barcode — SVG inline (no PNG/GD dependency)
    $admNo      = $student->admissionNo ?? '000000';
    $barcodeSvg = null;
    if (class_exists(\Picqer\Barcode\BarcodeGeneratorSVG::class)) {
        $gen        = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $barcodeSvg = $gen->getBarcode($admNo, $gen::TYPE_CODE_128, 1.8, 50);
    }
@endphp

<div style="
    width:323px; height:560px; border-radius:12px; overflow:hidden;
    position:relative; background:#ffffff;
    box-shadow:0 8px 32px rgba(0,0,0,.18);
    font-family:'Nunito','Segoe UI',sans-serif; flex-shrink:0;
">
    {{-- TOP ACCENT --}}
    <div style="position:absolute;top:0;left:0;right:0;height:7px;z-index:10;
        background:linear-gradient(90deg,#1e3a5f,#2169ad,#4f46e5,#2169ad,#1e3a5f);"></div>

    {{-- WATERMARK - CLEARER (opacity increased to 0.12, size increased) --}}
    @if($logoUrl)
    <div style="position:absolute;inset:0;z-index:1;display:flex;
        align-items:center;justify-content:center;pointer-events:none;">
        <img src="{{ $logoUrl }}" style="width:220px;height:220px;object-fit:contain;
            opacity:0.12;filter:grayscale(100%);" alt="">
    </div>
    @endif

    {{-- HEADER --}}
    <div style="position:relative;z-index:2;
        background:linear-gradient(150deg,#1e3a5f 0%,#2169ad 55%,#1a3356 100%);
        padding:16px 16px 12px; text-align:center; overflow:hidden;">
        <div style="position:absolute;top:-18px;left:-18px;width:70px;height:70px;
            border-radius:50%;background:rgba(255,255,255,.06);"></div>
        <div style="color:rgba(255,255,255,.75);font-size:8px;font-weight:700;
            letter-spacing:2.5px;">STUDENT IDENTITY CARD</div>
        <div style="color:#fff;font-weight:800;font-size:12px;margin-top:2px;">
            {{ $schoolInfo?->school_name ?? 'School Name' }}
        </div>
        @if(!empty($schoolInfo?->school_address))
        <div style="color:rgba(255,255,255,.62);font-size:7.5px;margin-top:2px;">
            {{ $schoolInfo->school_address }}
        </div>
        @endif
    </div>

    {{-- MAGNETIC STRIP --}}
    <div style="height:16px;background:#1a1a2e;position:relative;z-index:2;"></div>

    {{-- STUDENT SUMMARY CHIP --}}
    <div style="position:relative;z-index:2;margin:10px 14px 0;background:#eef2ff;
        border-radius:7px;padding:7px 11px;border-left:3px solid #2169ad;">
        <div style="font-size:10.5px;font-weight:800;color:#1e3a5f;">{{ $fullname }}</div>
        <div style="font-size:8.5px;color:#4338ca;font-weight:600;margin-top:1px;">
            {{ $classArm ?: 'N/A' }} &nbsp;|&nbsp; {{ $admNo }}
        </div>
    </div>

    {{-- TERMS --}}
    <div style="position:relative;z-index:2;padding:9px 14px 0;font-size:7.8px;
        color:#374151;line-height:1.65;">
        <div style="font-weight:800;font-size:8.5px;color:#1e3a5f;margin-bottom:3px;
            text-transform:uppercase;letter-spacing:.6px;">Terms &amp; Conditions</div>
        <ul style="margin:0;padding-left:13px;color:#4b5563;">
            <li>This card is the property of <strong>{{ $schoolInfo?->school_name ?? 'the school' }}</strong> and must be carried at all times on school premises.</li>
            <li>Report loss immediately to the school office. A replacement fee applies.</li>
            <li>Not transferable. Misuse attracts disciplinary action.</li>
            <li>Return this card upon completion or withdrawal of enrolment.</li>
        </ul>
    </div>

    {{-- CONTACT --}}
    @php
        $phone   = $schoolInfo?->school_phone   ?? '';
        $email   = $schoolInfo?->school_email   ?? '';
        $website = $schoolInfo?->school_website ?? '';
    @endphp
    @if($phone || $email || $website)
    <div style="position:relative;z-index:2;margin:8px 14px 0;background:#f0f4ff;
        border-radius:7px;padding:6px 10px;border-left:3px solid #2169ad;">
        <div style="font-size:7px;font-weight:800;color:#2169ad;
            text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px;">Contact</div>
        @if($phone)
        <div style="font-size:7.5px;color:#374151;">&#128222; {{ $phone }}</div>
        @endif
        @if($email)
        <div style="font-size:7.5px;color:#374151;">&#9993; {{ $email }}</div>
        @endif
        @if($website)
        <div style="font-size:7.5px;color:#2169ad;">&#127760; {{ $website }}</div>
        @endif
    </div>
    @endif

    {{-- SIGNATURES --}}
    <div style="position:relative;z-index:2;margin:10px 14px 0;display:flex;gap:10px;">
        <div style="flex:1;text-align:center;">
            <div style="height:30px;border-bottom:1.5px solid #1e3a5f;margin:0 8px;"></div>
            <div style="font-size:7px;color:#6b7280;margin-top:3px;">Cardholder's Signature</div>
        </div>
        <div style="flex:1;text-align:center;">
            <div style="height:30px;border-bottom:1.5px solid #1e3a5f;margin:0 8px;"></div>
            <div style="font-size:7px;color:#6b7280;margin-top:3px;">Authorised Signature</div>
        </div>
    </div>

    {{-- ISSUED BY --}}
    <div style="position:relative;z-index:2;text-align:center;margin-top:8px;">
        <div style="font-size:7px;color:#9ca3af;letter-spacing:.6px;text-transform:uppercase;">Issued By</div>
        <div style="font-size:9px;font-weight:800;color:#1e3a5f;">
            {{ $schoolInfo?->school_name ?? 'School Administration' }}
        </div>
        <div style="font-size:7px;color:#6b7280;">Admin Officer</div>
    </div>

    {{-- BARCODE - CENTERED (using flex with justify-content:center) --}}
    <div style="position:relative;z-index:2;text-align:center;padding:8px 0 4px;">
        <div style="display:flex;justify-content:center;align-items:center;width:100%;">
            @if($barcodeSvg)
                <div style="display:flex;justify-content:center;">
                    {!! $barcodeSvg !!}
                </div>
            @else
                {{-- Fallback: styled text barcode look --}}
                <div style="font-family:monospace;font-size:28px;letter-spacing:-1px;
                    color:#1e3a5f;line-height:1;text-align:center;">
                    ||||| {{ $admNo }} |||||
                </div>
            @endif
        </div>
        <div style="font-size:8.5px;font-weight:800;color:#1e3a5f;letter-spacing:2px;margin-top:4px;">
            {{ $admNo }}
        </div>
        <div style="font-size:7px;color:#94a3b8;margin-top:2px;">
            Valid Until: <strong>{{ $expiry }}</strong> &bull; {{ now()->year }}
        </div>
    </div>

    {{-- BOTTOM ACCENT --}}
    <div style="position:absolute;bottom:0;left:0;right:0;height:7px;z-index:10;
        background:linear-gradient(90deg,#4f46e5,#2169ad,#1e3a5f,#2169ad,#4f46e5);"></div>
</div>
