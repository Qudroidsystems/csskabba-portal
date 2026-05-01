{{--
    ID Card FRONT — Portrait  (85.6 × 54 mm @ 96 dpi → 323 × 204 px, scale ×2 for PVC = 646×408)
    Usage: @include('student.idcard.card-front', ['student'=>$student, 'schoolInfo'=>$schoolInfo])
--}}
@php
    $fullname  = trim(($student->firstname ?? '') . ' ' . ($student->lastname ?? ''));
    $initials  = strtoupper(substr($student->firstname ?? ' ', 0, 1) . substr($student->lastname ?? ' ', 0, 1));
    $className = optional($student->currentTerm?->schoolClass)->schoolclass ?? 'N/A';
    $arm       = optional(optional($student->currentTerm?->schoolClass)->armRelation)->arm ?? '';
    $classArm  = trim($className . ' ' . $arm);
    $hasPic    = $student->picture && $student->picture->picture;
    $photoUrl  = $hasPic ? asset('storage/images/student_avatars/' . $student->picture->picture) : null;
    $logoUrl   = ($schoolInfo && $schoolInfo->school_logo) ? $schoolInfo->getLogoUrlAttribute() : null;

    $qrData    = $student->admissionNo . '|' . $fullname . '|' . $classArm;
    $qrB64     = base64_encode(
        \SimpleSoftwareIO\QrCode\Facades\QrCode::size(80)->format('png')->generate($qrData)
    );
@endphp

{{-- PVC card: 85.6 mm × 54 mm. At 96dpi that's ~323×204px, scale ×2 = 646×408 --}}
<div class="id-card-wrap" style="
    width: 323px;
    height: 510px;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
    background: #ffffff;
    box-shadow: 0 8px 32px rgba(0,0,0,.18);
    font-family: 'Nunito', 'Segoe UI', sans-serif;
    flex-shrink: 0;
">

    {{-- ── TOP ACCENT BAND ── --}}
    <div style="
        position: absolute; top: 0; left: 0; right: 0; height: 8px;
        background: linear-gradient(90deg, #1e3a5f 0%, #2169ad 50%, #4f46e5 100%);
    "></div>

    {{-- ── HEADER ── --}}
    <div style="
        background: linear-gradient(150deg, #1e3a5f 0%, #2169ad 60%, #1e3a5f 100%);
        padding: 18px 14px 14px;
        text-align: center;
        position: relative;
        overflow: hidden;
    ">
        {{-- decorative circles --}}
        <div style="position:absolute;top:-28px;right:-28px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.07);"></div>
        <div style="position:absolute;bottom:-40px;left:-20px;width:110px;height:110px;border-radius:50%;background:rgba(255,255,255,.04);"></div>

        @if($logoUrl)
            <img src="{{ $logoUrl }}" style="height:44px;width:44px;object-fit:contain;border-radius:50%;border:2px solid rgba(255,255,255,.4);display:block;margin:0 auto 6px;" alt="logo">
        @else
            {{-- placeholder shield --}}
            <div style="width:44px;height:44px;border-radius:50%;border:2px solid rgba(255,255,255,.4);background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 6px;font-size:20px;color:#fff;">&#127979;</div>
        @endif

        <div style="color:#fff;font-weight:800;font-size:12.5px;letter-spacing:.3px;line-height:1.3;position:relative;">
            {{ $schoolInfo?->school_name ?? 'School Name' }}
        </div>
        @if($schoolInfo?->school_motto)
        <div style="color:rgba(255,255,255,.7);font-size:9px;margin-top:2px;font-style:italic;position:relative;">
            {{ $schoolInfo->school_motto }}
        </div>
        @endif

        {{-- STUDENT ID label strip --}}
        <div style="
            display:inline-block;
            background:rgba(255,255,255,.18);
            border:1px solid rgba(255,255,255,.3);
            color:#fff;
            font-size:8px;
            font-weight:700;
            letter-spacing:2px;
            padding:3px 12px;
            border-radius:20px;
            margin-top:8px;
            position:relative;
        ">STUDENT ID CARD</div>
    </div>

    {{-- ── PHOTO SECTION ── --}}
    <div style="text-align:center;margin-top:-30px;position:relative;z-index:2;">
        <div style="
            width:76px;height:76px;border-radius:50%;
            border:3px solid #2169ad;
            box-shadow:0 4px 12px rgba(33,105,173,.35);
            margin:0 auto;
            overflow:hidden;
            background:#e2e8f0;
            display:flex;align-items:center;justify-content:center;
        ">
            @if($photoUrl)
                <img src="{{ $photoUrl }}" style="width:100%;height:100%;object-fit:cover;"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" alt="{{ $fullname }}">
                <div style="display:none;width:100%;height:100%;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:#2169ad;background:#dbeafe;">{{ $initials }}</div>
            @else
                <div style="font-size:22px;font-weight:700;color:#2169ad;">{{ $initials }}</div>
            @endif
        </div>
    </div>

    {{-- ── STUDENT INFO ── --}}
    <div style="text-align:center;padding:8px 18px 0;">
        <div style="font-size:14.5px;font-weight:800;color:#1e2937;line-height:1.2;letter-spacing:.2px;">
            {{ $fullname }}
        </div>
        <div style="
            display:inline-block;
            background:#eff6ff;
            color:#2169ad;
            font-size:10px;
            font-weight:700;
            padding:3px 10px;
            border-radius:20px;
            margin-top:5px;
            letter-spacing:.5px;
        ">{{ $student->admissionNo }}</div>
    </div>

    {{-- ── DETAIL ROWS ── --}}
    <div style="margin:10px 16px 0;background:#f8fafc;border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;">
        <div style="display:flex;border-bottom:1px solid #e2e8f0;">
            <div style="width:95px;background:#eef2ff;padding:6px 10px;font-size:9px;font-weight:700;color:#4338ca;text-transform:uppercase;letter-spacing:.5px;">Class</div>
            <div style="flex:1;padding:6px 10px;font-size:10.5px;font-weight:600;color:#1e2937;">{{ $classArm }}</div>
        </div>
        <div style="display:flex;border-bottom:1px solid #e2e8f0;">
            <div style="width:95px;background:#eef2ff;padding:6px 10px;font-size:9px;font-weight:700;color:#4338ca;text-transform:uppercase;letter-spacing:.5px;">Gender</div>
            <div style="flex:1;padding:6px 10px;font-size:10.5px;font-weight:600;color:#1e2937;">{{ $student->gender ?? 'N/A' }}</div>
        </div>
        <div style="display:flex;">
            <div style="width:95px;background:#eef2ff;padding:6px 10px;font-size:9px;font-weight:700;color:#4338ca;text-transform:uppercase;letter-spacing:.5px;">Valid Until</div>
            <div style="flex:1;padding:6px 10px;font-size:10.5px;font-weight:600;color:#1e2937;">{{ now()->addYear()->format('M Y') }}</div>
        </div>
    </div>

    {{-- ── QR CODE ── --}}
    <div style="text-align:center;padding:10px 0 6px;">
        <img src="data:image/png;base64,{{ $qrB64 }}" style="width:64px;height:64px;" alt="QR">
        <div style="font-size:8px;color:#94a3b8;margin-top:2px;letter-spacing:.5px;">SCAN TO VERIFY</div>
    </div>

    {{-- ── BOTTOM ACCENT ── --}}
    <div style="
        position:absolute;bottom:0;left:0;right:0;height:6px;
        background:linear-gradient(90deg,#4f46e5 0%,#2169ad 50%,#1e3a5f 100%);
    "></div>

</div>
