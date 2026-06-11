<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Promotion List — {{ ($schoolclass->schoolclass ?? '') . ' ' . ($schoolclass->arm_name ?? '') }}</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Segoe UI', 'Arial', sans-serif;
    font-size: 13px;
    background: #f1f5f9;
    color: #0f2342;
    line-height: 1.5;
}

@media print {
    body { background: #fff !important; font-size: 11px; }
    .no-print { display: none !important; }
    .page-wrap { max-width: none !important; padding: 0 !important; background: #fff !important; }
    .school-header { page-break-after: avoid; }
    .group-section { page-break-inside: avoid; }
    @page { margin: 1.4cm 1.2cm; }
}

.page-wrap {
    max-width: 960px;
    margin: 0 auto;
    padding: 24px 20px;
}

.school-header {
    background: linear-gradient(135deg, #0f2342 0%, #1e3a5f 55%, #0d9488 100%);
    border-radius: 12px;
    padding: 24px 28px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 20px;
    color: white;
}

.school-logo {
    width: 80px; height: 80px;
    border-radius: 50%;
    object-fit: contain;
    border: 3px solid rgba(255,255,255,.35);
    background: white;
    flex-shrink: 0;
}

.school-logo-placeholder {
    width: 80px; height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,.15);
    border: 3px solid rgba(255,255,255,.35);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; font-weight: 800; color: white; flex-shrink: 0;
}

.school-info { flex: 1; text-align: center; }
.school-name { font-size: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; line-height: 1.2; }
.school-address { font-size: 11.5px; opacity: .8; margin-top: 4px; }
.school-motto { font-size: 11px; font-style: italic; opacity: .7; margin-top: 3px; }

.list-title-bar {
    background: #0f2342;
    color: white;
    text-align: center;
    padding: 10px 20px;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 1.5px;
    border-radius: 8px;
    margin-bottom: 16px;
}

.meta-strip {
    display: flex;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
    margin-bottom: 18px;
    overflow: hidden;
}

.meta-cell { flex: 1; padding: 10px 14px; border-right: 1px solid #e2e8f0; text-align: center; }
.meta-cell:last-child { border-right: none; }
.meta-label { font-size: 9.5px; color: #64748b; text-transform: uppercase; letter-spacing: .4px; display: block; }
.meta-value { font-size: 13px; font-weight: 700; color: #0f2342; display: block; margin-top: 2px; }

.group-section { margin-bottom: 28px; }

.group-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    border-radius: 8px 8px 0 0;
    font-weight: 700;
    font-size: 14px;
    border-bottom: 2px solid rgba(0,0,0,.08);
}

.group-header .count-badge {
    margin-left: auto;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    background: rgba(255,255,255,.3);
}

.status-promoted { background: linear-gradient(90deg, #d1fae5, #ecfdf5); color: #065f46; border-left: 5px solid #10b981; }
.status-trial { background: linear-gradient(90deg, #fef3c7, #fffbeb); color: #92400e; border-left: 5px solid #f59e0b; }
.status-see_principal { background: linear-gradient(90deg, #dbeafe, #eff6ff); color: #1e40af; border-left: 5px solid #3b82f6; }
.status-repeated { background: linear-gradient(90deg, #fee2e2, #fff1f2); color: #991b1b; border-left: 5px solid #ef4444; }
.status-awaiting { background: linear-gradient(90deg, #f1f5f9, #f8fafc); color: #475569; border-left: 5px solid #94a3b8; }
.status-other { background: linear-gradient(90deg, #f5f3ff, #ede9fe); color: #5b21b6; border-left: 5px solid #7c3aed; }

.student-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border: 1px solid #e2e8f0;
    border-top: none;
    border-radius: 0 0 8px 8px;
    overflow: hidden;
    font-size: 12px;
}

.student-table thead th {
    background: #1e3a5f;
    color: #a8d4ef;
    padding: 8px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .4px;
    border-right: 1px solid rgba(255,255,255,.07);
    white-space: nowrap;
}

.student-table thead th:last-child { border-right: none; }

.student-table tbody tr:nth-child(odd) { background: #ffffff; }
.student-table tbody tr:nth-child(even) { background: #f8fafc; }
.student-table tbody tr:hover { background: #f0f9ff !important; }

.student-table tbody td {
    padding: 9px 12px;
    border-bottom: 1px solid #e2e8f0;
    border-right: 1px solid #f1f5f9;
    vertical-align: middle;
}

.student-table tbody td:last-child { border-right: none; }
.student-table tbody tr:last-child td { border-bottom: none; }

td.sn-cell { width: 36px; text-align: center; font-size: 11px; color: #64748b; font-weight: 600; }

.student-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
    flex-shrink: 0;
}

.avatar-initials {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d9488, #0ea5e9);
    color: white;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.name-cell { font-weight: 600; color: #0f2342; }
.adm-cell { font-family: 'Courier New', monospace; font-size: 11px; color: #475569; }
.gender-cell { text-align: center; font-size: 11px; }

.summary-footer {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    margin-top: 24px;
}
.summary-footer h4 { font-size: 14px; font-weight: 700; color: #0f2342; margin-bottom: 14px; }
.summary-grid { display: flex; gap: 12px; flex-wrap: wrap; }
.summary-item { flex: 1; min-width: 120px; text-align: center; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; }
.summary-count { font-size: 26px; font-weight: 800; display: block; }
.summary-lbl { font-size: 11px; font-weight: 600; display: block; margin-top: 4px; }

.toolbar {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
}

.toolbar-title { font-size: 15px; font-weight: 700; color: #0f2342; display: flex; align-items: center; gap: 8px; }
.toolbar-actions { display: flex; gap: 8px; }

.btn-print {
    background: linear-gradient(135deg, #0f2342, #1e3a5f);
    color: white; border: none; border-radius: 8px;
    padding: 9px 20px; font-size: 13px; font-weight: 600;
    cursor: pointer; display: flex; align-items: center; gap: 8px;
    transition: all .2s ease;
}
.btn-print:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(15,35,66,.3); }

.btn-close-tab {
    background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
    border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 600;
    cursor: pointer; display: flex; align-items: center; gap: 7px;
    text-decoration: none; transition: all .2s ease;
}
.btn-close-tab:hover { background: #e2e8f0; }

.generated-line {
    text-align: center;
    font-size: 10.5px;
    color: #94a3b8;
    margin-top: 20px;
    padding-top: 14px;
    border-top: 1px dashed #e2e8f0;
}
</style>
</head>
<body>
<div class="page-wrap">

    <div class="toolbar no-print">
        <div class="toolbar-title">
            <span style="font-size:20px;">📋</span>
            Promotion Student List
            <span style="font-size:12px;font-weight:400;color:#64748b;">
                — {{ ($schoolclass->schoolclass ?? '') . ' ' . ($schoolclass->arm_name ?? '') }}
                &nbsp;·&nbsp; {{ $schoolsession->session ?? '' }}
                &nbsp;·&nbsp; {{ $schoolterm->term ?? '' }}
            </span>
        </div>
        <div class="toolbar-actions">
            <button class="btn-print" onclick="window.print()">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print / Save PDF
            </button>
            <a href="javascript:window.close()" class="btn-close-tab">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                Close
            </a>
        </div>
    </div>

    <div class="school-header">
        @if(!empty($school_logo_base64))
            <img src="{{ $school_logo_base64 }}" class="school-logo" alt="Logo">
        @else
            <div class="school-logo-placeholder">
                {{ strtoupper(substr($schoolInfo->school_name ?? 'S', 0, 2)) }}
            </div>
        @endif
        <div class="school-info">
            <div class="school-name">{{ $schoolInfo->school_name ?? 'SCHOOL NAME' }}</div>
            @if(!empty($schoolInfo->school_address))
                <div class="school-address">{{ $schoolInfo->school_address }}</div>
            @endif
            @if(!empty($schoolInfo->school_motto))
                <div class="school-motto">"{{ $schoolInfo->school_motto }}"</div>
            @endif
        </div>
        <div style="width:80px;flex-shrink:0;"></div>
    </div>

    <div class="list-title-bar">STUDENT PROMOTION RECOMMENDATION LIST</div>

    <div class="meta-strip">
        <div class="meta-cell">
            <span class="meta-label">Class</span>
            <span class="meta-value">{{ ($schoolclass->schoolclass ?? '-') . ' ' . ($schoolclass->arm_name ?? '') }}</span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Session</span>
            <span class="meta-value">{{ $schoolsession->session ?? '-' }}</span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Term</span>
            <span class="meta-value">{{ $schoolterm->term ?? '-' }}</span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Total Students</span>
            <span class="meta-value">{{ $totalStudents }}</span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Generated</span>
            <span class="meta-value" style="font-size:11px;">{{ $generatedAt }}</span>
        </div>
    </div>

    @php
    $allFields = [
        'admissionno'  => 'Admission No',
        'firstname'    => 'First Name',
        'lastname'     => 'Last Name',
        'gender'       => 'Gender',
        'dateofbirth'  => 'Date of Birth',
        'arm'          => 'Arm',
        'total_cum'    => 'Cum Total',
        'total_term'   => 'Term Total',
        'position_cum' => 'Overall Pos (Cum)',
        'position_term'=> 'Overall Pos (Term)',
        'gpa'          => 'GPA',
    ];

    $statusMeta = [
        'promoted'       => ['label' => 'Promoted', 'icon' => '✅', 'class' => 'status-promoted', 'badge' => 'badge-promoted'],
        'trial'          => ['label' => 'Promoted on Trial', 'icon' => '⚠️', 'class' => 'status-trial', 'badge' => 'badge-trial'],
        'see_principal'  => ['label' => 'See Principal', 'icon' => '👤', 'class' => 'status-see_principal','badge' => 'badge-see_principal'],
        'repeated'       => ['label' => 'Repeat', 'icon' => '🔁', 'class' => 'status-repeated', 'badge' => 'badge-repeated'],
        'awaiting'       => ['label' => 'Awaiting Decision', 'icon' => '⏳', 'class' => 'status-awaiting', 'badge' => 'badge-awaiting'],
        '__other'        => ['label' => 'Other', 'icon' => '📌', 'class' => 'status-other', 'badge' => 'badge-other'],
    ];

    function listOrdinal($n) {
        if (!$n) return '—';
        $n = (int)$n;
        $s = ['th','st','nd','rd'];
        $v = $n % 100;
        return $n . ($s[($v-20)%10] ?? $s[$v] ?? $s[0]);
    }

    $globalSn = 0;
    @endphp

    @foreach($grouped_students as $statusKey => $students)
        @php
            $meta = $statusMeta[$statusKey] ?? $statusMeta['__other'];
            $groupLabel = $students[0]['promotion_label'] ?? $meta['label'];
            $groupCount = count($students);
        @endphp
        <div class="group-section">
            <div class="group-header {{ $meta['class'] }}">
                <span style="font-size:18px;">{{ $meta['icon'] }}</span>
                <span>{{ $groupLabel }}</span>
                <span class="count-badge">{{ $groupCount }} Student{{ $groupCount === 1 ? '' : 's' }}</span>
            </div>

            <table class="student-table">
                <thead>
                    <tr>
                        @if($show_sn)
                            <th style="width:40px;text-align:center;">#</th>
                        @endif
                        @if($show_photos)
                            <th style="width:44px;"></th>
                        @endif
                        @foreach($list_fields as $field)
                            @if($field === 'firstname' || $field === 'lastname') @continue @endif
                            @if(isset($allFields[$field]))
                                <th>{{ $allFields[$field] }}</th>
                            @endif
                        @endforeach
                        @if(in_array('firstname', $list_fields) && in_array('lastname', $list_fields))
                            <th>Student Name</th>
                        @elseif(in_array('firstname', $list_fields))
                            <th>First Name</th>
                        @elseif(in_array('lastname', $list_fields))
                            <th>Last Name</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $idx => $stu)
                        @php
                            $globalSn++;
                            $hasPic = !empty($stu['picture']) && $stu['picture'] !== 'unnamed.jpg';
                            $imgSrc = $hasPic ? asset('storage/student_avatars/' . basename($stu['picture'])) : null;
                            $initials = strtoupper(substr($stu['lastname']??'',0,1) . substr($stu['firstname']??'',0,1)) ?: 'ST';
                        @endphp
                        <tr>
                            @if($show_sn)
                                <td class="sn-cell">{{ $globalSn }}</td>
                            @endif

                            @if($show_photos)
                                <td style="padding:6px 10px;width:44px;">
                                    @if($imgSrc)
                                        <img src="{{ $imgSrc }}" class="student-avatar" alt="{{ $stu['firstname'] }}"
                                             onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'">
                                        <span class="avatar-initials" style="display:none;">{{ $initials }}</span>
                                    @else
                                        <span class="avatar-initials">{{ $initials }}</span>
                                    @endif
                                </td>
                            @endif

                            @if(in_array('firstname', $list_fields) && in_array('lastname', $list_fields))
                                <td class="name-cell">{{ strtoupper($stu['lastname'] ?? '') }}, {{ $stu['firstname'] ?? '' }}</td>
                            @elseif(in_array('firstname', $list_fields))
                                <td class="name-cell">{{ $stu['firstname'] ?? '' }}</td>
                            @elseif(in_array('lastname', $list_fields))
                                <td class="name-cell">{{ strtoupper($stu['lastname'] ?? '') }}</td>
                            @endif

                            @foreach($list_fields as $field)
                                @if(in_array($field, ['firstname','lastname','name'])) @continue @endif
                                @if($field === 'admissionno')
                                    <td class="adm-cell">{{ $stu['admissionno'] ?? '—' }}</td>
                                @elseif($field === 'gender')
                                    <td class="gender-cell">{{ $stu['gender'] ?? '—' }}</td>
                                @elseif($field === 'dateofbirth')
                                    <td>{{ $stu['dateofbirth'] ? \Carbon\Carbon::parse($stu['dateofbirth'])->format('d M Y') : '—' }}</td>
                                @elseif($field === 'arm')
                                    <td>{{ $stu['arm'] ?: '—' }}</td>
                                @elseif($field === 'total_cum')
                                    <td style="text-align:center;font-weight:700;">{{ $stu['total_cum'] ?? '—' }}</td>
                                @elseif($field === 'total_term')
                                    <td style="text-align:center;font-weight:700;">{{ $stu['total_term'] ?? '—' }}</td>
                                @elseif($field === 'position_cum')
                                    <td style="text-align:center;font-weight:700;color:#1e40af;">{{ listOrdinal($stu['position_cum']) }}</td>
                                @elseif($field === 'position_term')
                                    <td style="text-align:center;font-weight:700;color:#92400e;">{{ listOrdinal($stu['position_term']) }}</td>
                                @elseif($field === 'gpa')
                                    <td style="text-align:center;">{{ number_format($stu['gpa'] ?? 0, 2) }}</td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    @php
        $summaryGroups = [];
        foreach($grouped_students as $statusKey => $students) {
            $meta = $statusMeta[$statusKey] ?? $statusMeta['__other'];
            $label = !empty($students[0]['promotion_label']) ? $students[0]['promotion_label'] : $meta['label'];
            $summaryGroups[] = [
                'label' => $label,
                'count' => count($students),
                'bgColor' => match($statusKey) {
                    'promoted' => '#d1fae5',
                    'trial' => '#fef3c7',
                    'see_principal' => '#dbeafe',
                    'repeated' => '#fee2e2',
                    default => '#f1f5f9',
                },
                'textColor' => match($statusKey) {
                    'promoted' => '#065f46',
                    'trial' => '#92400e',
                    'see_principal' => '#1e40af',
                    'repeated' => '#991b1b',
                    default => '#475569',
                },
            ];
        }
    @endphp
    <div class="summary-footer">
        <h4>📊 Summary by Recommendation</h4>
        <div class="summary-grid">
            @foreach($summaryGroups as $sg)
                <div class="summary-item" style="background:{{ $sg['bgColor'] }};border-color:{{ $sg['bgColor'] }};">
                    <span class="summary-count" style="color:{{ $sg['textColor'] }};">{{ $sg['count'] }}</span>
                    <span class="summary-lbl" style="color:{{ $sg['textColor'] }};">{{ $sg['label'] }}</span>
                </div>
            @endforeach
            <div class="summary-item" style="background:#0f2342;border-color:#0f2342;">
                <span class="summary-count" style="color:white;">{{ $totalStudents }}</span>
                <span class="summary-lbl" style="color:rgba(255,255,255,.75);">Total Students</span>
            </div>
        </div>
    </div>

    <div class="generated-line">
        Generated: {{ $generatedAt }} &nbsp;·&nbsp; {{ ($schoolclass->schoolclass ?? '') . ' ' . ($schoolclass->arm_name ?? '') }}
        &nbsp;·&nbsp; {{ $schoolsession->session ?? '' }} &nbsp;·&nbsp; {{ $schoolterm->term ?? '' }}
    </div>

</div>
</body>
</html>
