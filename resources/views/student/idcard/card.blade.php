@php
    $fullname = trim(($student->firstname ?? '') . ' ' . ($student->lastname ?? '') . ' ' . ($student->othername ?? ''));

    $className = $student->schoolclass ?? 'N/A';
    $arm       = $student->arm ?? '';

    // Optimized QR Code
    $qrData = $student->admissionNo . ' | ' . strtoupper($fullname);

    $qr = base64_encode(
        \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
            ->size(128)
            ->errorCorrection('H')
            ->margin(2)
            ->generate($qrData)
    );

    // Optimized Barcode
    $barcodeGenerator = new Picqer\Barcode\BarcodeGeneratorPNG();
    $barcode = base64_encode(
        $barcodeGenerator->getBarcode($student->admissionNo, $barcodeGenerator::TYPE_CODE_128, 2.2, 48)
    );
@endphp

<div class="id-card {{ $orientation ?? 'portrait' }}"
     style="width: {{ $orientation === 'landscape' ? '440px' : '305px' }};
            height: {{ $orientation === 'landscape' ? '270px' : '440px' }};
            background: #ffffff;
            border: 3px solid #1e3a5f;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.20);
            margin: 15px auto;">

    <!-- Security Pattern -->
    <div style="position: absolute; inset: 0;
                background: repeating-linear-gradient(135deg, transparent, transparent 25px, rgba(37,99,235,0.035) 25px, rgba(37,99,235,0.035) 50px);
                z-index: 1;"></div>

    <!-- Header -->
    <div style="background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%); color: white;
                padding: 16px 20px; text-align: center; position: relative; z-index: 2;">
        @if($schoolInfo && $schoolInfo->school_logo)
            <img src="{{ $schoolInfo->getLogoUrlAttribute() }}"
                 style="height: 55px; max-width: 190px; object-fit: contain;" alt="School Logo">
        @endif
        <h4 style="margin: 8px 0 4px; font-size: 17px; font-weight: 700; letter-spacing: 0.6px;">
            {{ $schoolInfo?->school_name }}
        </h4>
        <small style="opacity: 0.92; font-size: 12.8px;">{{ $schoolInfo?->school_motto }}</small>
    </div>

    <div style="display: flex; padding: 20px; gap: 20px; position: relative; z-index: 2;">
        <!-- Photo -->
        <div style="flex-shrink: 0;">
            @if(!empty($student->picture))
                <img src="{{ asset('storage/images/student_avatars/' . $student->picture) }}"
                     style="width: 122px; height: 150px; object-fit: cover;
                            border: 4px solid #2563eb; border-radius: 8px;"
                     alt="{{ $fullname }}">
            @else
                <div style="width:122px; height:150px; background: linear-gradient(#e2e8f0, #cbd5e1);
                            display:flex; align-items:center; justify-content:center;
                            font-size: 42px; color:#475569; border:4px solid #94a3b8; border-radius:8px; font-weight:700;">
                    {{ strtoupper(substr($student->firstname??'',0,1) . substr($student->lastname??'',0,1)) }}
                </div>
            @endif
        </div>

        <!-- Information -->
        <div style="flex: 1;">
            <h5 style="margin: 0 0 10px; font-size: 18px; font-weight: 700; color: #0f172a; line-height: 1.25;">
                {{ $fullname }}
            </h5>

            <div style="margin-bottom: 12px;">
                <span style="font-size: 14.5px; font-weight: 600; color: #1e40af;">
                    {{ $student->admissionNo }}
                </span>
            </div>

            <p style="margin: 8px 0; font-size: 14.2px; color: #334155;">
                <strong>Class:</strong> {{ $className }} {{ $arm ? '- ' . $arm : '' }}
            </p>

            <p style="margin: 8px 0; font-size: 14px; color: #475569;">
                <strong>Gender:</strong> {{ $student->gender }}
            </p>

            @if(!empty($student->dateofbirth))
            <p style="margin: 8px 0; font-size: 13.2px; color: #64748b;">
                <strong>DOB:</strong> {{ \Carbon\Carbon::parse($student->dateofbirth)->format('d M Y') }}
            </p>
            @endif
        </div>
    </div>

    <!-- QR & Barcode -->
    <div style="text-align: center; padding: 12px 0 18px; border-top: 1px dashed #e2e8f0;
                position: relative; z-index: 2;">
        <img src="data:image/png;base64,{{ $qr }}"
             style="height: 95px; image-rendering: crisp-edges;" alt="QR Code">

        <div style="margin-top: 12px;">
            <img src="data:image/png;base64,{{ $barcode }}"
                 style="height: 45px; image-rendering: crisp-edges;" alt="Barcode">
        </div>
        <small style="display: block; margin-top: 8px; font-size: 10.8px; color: #64748b;">
            Scan for Verification • {{ now()->year }}
        </small>
    </div>

    <!-- Footer -->
    <div style="position: absolute; bottom: 16px; left: 0; right: 0; text-align: center;
                font-size: 10.5px; color: #64748b; z-index: 2;">
        Valid Until: <strong>{{ now()->addYear()->format('F Y') }}</strong>
    </div>

    <div style="position: absolute; bottom: 16px; right: 18px; font-size: 9.5px; color: #94a3b8; z-index: 2;">
        ID: {{ $student->id }}
    </div>
</div>

<style>
.id-card { box-sizing: border-box; }
</style>
