{{--
    ID Card FRONT — Portrait  (323 × 510 px preview / scales to 85.6×54mm PVC)
    Data: flat stdClass from fetchStudentsForCards() — columns:
          id, admissionNo, firstname, lastname, othername, gender, dateofbirth,
          nationality, blood_group, student_category, student_status, admission_date,
          picture, schoolclass, arm, session, term
--}}
@php
    $firstname  = $student->firstname  ?? '';
    $lastname   = $student->lastname   ?? '';
    $othername  = $student->othername  ?? '';
    $fullname   = trim("$firstname $othername $lastname");
    $initials   = strtoupper(substr($firstname,0,1) . substr($lastname,0,1));
    $classArm   = trim(($student->schoolclass ?? '') . ' ' . ($student->arm ?? ''));
    $dob        = $student->dateofbirth
                    ? \Carbon\Carbon::parse($student->dateofbirth)->format('d M Y')
                    : 'N/A';
    $admDate    = $student->admission_date
                    ? \Carbon\Carbon::parse($student->admission_date)->format('d M Y')
                    : 'N/A';
    $bloodGroup = $student->blood_group        ?? '';
    $nationality= $student->nationality        ?? '';
    $category   = $student->student_category   ?? '';
    $status     = $student->student_status     ?? '';

    $photoUrl   = ($student->picture)
        ? asset('storage/images/student_avatars/' . $student->picture)
        : null;
    $logoUrl    = ($schoolInfo && $schoolInfo->school_logo)
        ? $schoolInfo->getLogoUrlAttribute()
        : null;

    // QR payload — verifiable via /student-id-cards/verify/{token}
    $payload    = base64_encode(json_encode([
        'id'  => $student->id,
        'adm' => $student->admissionNo,
        'ts'  => now()->timestamp,
    ]));
    $qrB64 = base64_encode(
        \SimpleSoftwareIO\QrCode\Facades\QrCode::size(72)->format('png')
            ->generate(route('student-id-cards.verify', ['token' => $payload]))
    );
@endphp

<div style="
    width:323px; height:526px; border-radius:10px; overflow:hidden;
    position:relative; background:#ffffff;
    box-shadow:0 8px 32px rgba(0,0,0,.18);
    font-family:'Nunito','Segoe UI',sans-serif; flex-shrink:0;
">
    {{-- ══ TOP ACCENT ══ --}}
    <div style="position:absolute;top:0;left:0;right:0;height:7px;
        background:linear-gradient(90deg,#1e3a5f,#2169ad,#4f46e5,#2169ad,#1e3a5f);
        z-index:10;"></div>

    {{-- ══ WATERMARK LOGO (behind everything) ══ --}}
    @if($logoUrl)
    <div style="position:absolute;inset:0;z-index:1;display:flex;
        align-items:center;justify-content:center;pointer-events:none;">
        <img src="{{ $logoUrl }}" style="width:190px;height:190px;object-fit:contain;
            opacity:0.055;filter:grayscale(100%);" alt="">
    </div>
    @endif

    {{-- ══ HEADER BAND ══ --}}
    <div style="position:relative;z-index:2;
        background:linear-gradient(150deg,#1e3a5f 0%,#2169ad 55%,#1a3356 100%);
        padding:14px 14px 44px; text-align:center; overflow:hidden;">

        {{-- decorative bubbles --}}
        <div style="position:absolute;top:-22px;right:-22px;width:80px;height:80px;
            border-radius:50%;background:rgba(255,255,255,.08);"></div>
        <div style="position:absolute;bottom:-30px;left:-14px;width:90px;height:90px;
            border-radius:50%;background:rgba(255,255,255,.05);"></div>

        {{-- LOGO --}}
        @if($logoUrl)
            <img src="{{ $logoUrl }}" style="height:52px;width:52px;object-fit:contain;
                border-radius:50%;border:2.5px solid rgba(255,255,255,.5);
                background:rgba(255,255,255,.12);
                display:block;margin:0 auto 7px;position:relative;" alt="logo">
        @else
            <div style="width:52px;height:52px;border-radius:50%;
                border:2px solid rgba(255,255,255,.4);
                background:rgba(255,255,255,.15);display:flex;align-items:center;
                justify-content:center;margin:0 auto 7px;font-size:22px;color:#fff;
                position:relative;">&#127979;</div>
        @endif

        <div style="color:#fff;font-weight:800;font-size:13px;letter-spacing:.3px;
            line-height:1.25;position:relative;">
            {{ $schoolInfo?->school_name ?? 'School Name' }}
        </div>
        @if(!empty($schoolInfo?->school_motto))
        <div style="color:rgba(255,255,255,.72);font-size:8.5px;margin-top:2px;
            font-style:italic;position:relative;">
            {{ $schoolInfo->school_motto }}
        </div>
        @endif

        {{-- STUDENT ID CARD badge --}}
        <div style="display:inline-block;background:rgba(255,255,255,.18);
            border:1px solid rgba(255,255,255,.35);color:#fff;font-size:7.5px;
            font-weight:800;letter-spacing:2.5px;padding:3px 14px;border-radius:20px;
            margin-top:8px;position:relative;">STUDENT ID CARD</div>
    </div>

    {{-- ══ PHOTO (overlaps header bottom) ══ --}}
    <div style="position:relative;z-index:3;text-align:center;margin-top:-36px;">
        <div style="width:72px;height:72px;border-radius:50%;
            border:3px solid #2169ad;
            box-shadow:0 4px 14px rgba(33,105,173,.4);
            margin:0 auto;overflow:hidden;background:#dbeafe;
            display:flex;align-items:center;justify-content:center;">
            @if($photoUrl)
                <img src="{{ $photoUrl }}"
                     style="width:100%;height:100%;object-fit:cover;"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                     alt="{{ $fullname }}">
                <div style="display:none;width:100%;height:100%;align-items:center;
                    justify-content:center;font-size:20px;font-weight:800;
                    color:#2169ad;">{{ $initials }}</div>
            @else
                <div style="font-size:20px;font-weight:800;color:#2169ad;">{{ $initials }}</div>
            @endif
        </div>
    </div>

    {{-- ══ NAME & ADM NUMBER ══ --}}
    <div style="position:relative;z-index:2;text-align:center;padding:6px 16px 0;">
        <div style="font-size:13.5px;font-weight:800;color:#1e2937;line-height:1.25;">
            {{ $fullname }}
        </div>
        <div style="display:inline-flex;align-items:center;gap:5px;margin-top:4px;">
            <div style="background:#1e3a5f;color:#fff;font-size:8px;font-weight:700;
                padding:2px 9px;border-radius:3px 0 0 3px;letter-spacing:.5px;">ADM NO</div>
            <div style="background:#eff6ff;color:#2169ad;font-size:9.5px;font-weight:800;
                padding:2px 9px;border-radius:0 3px 3px 0;border:1px solid #bfdbfe;
                letter-spacing:1px;">{{ $student->admissionNo }}</div>
        </div>
    </div>

    {{-- ══ INFO TABLE ══ --}}
    <div style="position:relative;z-index:2;margin:8px 14px 0;
        background:#f8fafc;border-radius:8px;overflow:hidden;
        border:1px solid #e2e8f0;font-size:9px;">

        @php
        $rows = [
            ['Class',       $classArm   ?: 'N/A'],
            ['Gender',      $student->gender ?? 'N/A'],
            ['Date of Birth', $dob],
            ['Blood Group', $bloodGroup ?: 'N/A'],
            ['Nationality', $nationality ?: 'N/A'],
            ['Adm. Date',   $admDate],
        ];
        if($category) $rows[] = ['Category', $category];
        @endphp

        @foreach($rows as $i => $row)
        <div style="display:flex;{{ !$loop->last ? 'border-bottom:1px solid #e2e8f0;' : '' }}">
            <div style="width:88px;background:#eef2ff;padding:4px 9px;font-weight:700;
                color:#4338ca;text-transform:uppercase;letter-spacing:.4px;flex-shrink:0;">
                {{ $row[0] }}
            </div>
            <div style="flex:1;padding:4px 9px;font-weight:600;color:#1e2937;">
                {{ $row[1] }}
            </div>
        </div>
        @endforeach
    </div>

    {{-- ══ QR CODE ══ --}}
    <div style="position:relative;z-index:2;text-align:center;padding:8px 0 4px;">
        <img src="data:image/png;base64,{{ $qrB64 }}" style="width:58px;height:58px;" alt="QR">
        <div style="font-size:7px;color:#94a3b8;letter-spacing:.8px;margin-top:1px;">
            SCAN TO VERIFY
        </div>
    </div>

    {{-- ══ BOTTOM ACCENT ══ --}}
    <div style="position:absolute;bottom:0;left:0;right:0;height:7px;z-index:10;
        background:linear-gradient(90deg,#4f46e5,#2169ad,#1e3a5f,#2169ad,#4f46e5);"></div>
</div>
