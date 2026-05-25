{{-- resources/views/subjectscoresheet/index.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
/* ── Design tokens ───────────────────────────────────────────── */
:root{
    --p:#1e3a5f;--p2:#2563eb;--p3:#1d4ed8;
    --ok:#16a34a;--warn:#d97706;--err:#dc2626;
    --muted:#64748b;--border:#e2e8f0;--bg:#f8fafc;--card:#fff;
    --r:10px;--r-lg:14px;
    --shadow:0 1px 3px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04);
}

/* ── Page shell ─────────────────────────────────────────────── */
.ss-wrap{padding:0 0 40px;}
.ss-section{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);overflow:hidden;margin-bottom:20px;box-shadow:var(--shadow);}
.ss-section-header{padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;}
.ss-section-header h6{margin:0;font-size:13px;font-weight:700;color:var(--p);letter-spacing:.01em;}

/* ── Hero strip ─────────────────────────────────────────────── */
.ss-hero{background:var(--p);border-radius:var(--r-lg);padding:22px 28px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;}
.ss-hero-left h4{color:#fff;font-size:17px;font-weight:700;margin:0 0 4px;}
.ss-hero-left p{color:rgba(255,255,255,.65);font-size:13px;margin:0;}
.ss-hero-pills{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;}
.ss-pill{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);color:#fff;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.ss-hero-actions{display:flex;gap:8px;flex-wrap:wrap;}

/* ── Stat cards ─────────────────────────────────────────────── */
.ss-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;margin-bottom:20px;}
.ss-stat{background:var(--card);border:1px solid var(--border);border-radius:var(--r);padding:14px 16px;box-shadow:var(--shadow);transition:transform .15s,box-shadow .15s;}
.ss-stat:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.08);}
.ss-stat-icon{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;font-size:16px;}
.ss-stat-val{font-size:24px;font-weight:800;line-height:1;margin-bottom:3px;color:var(--p);}
.ss-stat-lbl{font-size:11px;color:var(--muted);font-weight:500;letter-spacing:.03em;}
.ss-stat-bar{height:3px;border-radius:2px;background:#e2e8f0;margin-top:8px;overflow:hidden;}
.ss-stat-bar-fill{height:100%;border-radius:2px;transition:width .4s;}

/* ── 3-panel summary row ───────────────────────────────────── */
.ss-panels{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;}
@media(max-width:900px){.ss-panels{grid-template-columns:1fr;}}
.ss-panel{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--shadow);}
.ss-panel-head{padding:12px 16px;border-bottom:1px solid var(--border);font-size:12px;font-weight:700;color:var(--p);display:flex;align-items:center;gap:7px;}
.ss-panel-body{padding:14px 16px;}

/* Score summary 2x2 */
.ss-score-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.ss-score-cell{border-radius:8px;padding:10px 12px;text-align:center;}
.ss-score-cell .val{font-size:20px;font-weight:800;line-height:1;margin-bottom:2px;}
.ss-score-cell .lbl{font-size:10px;font-weight:600;letter-spacing:.04em;opacity:.75;}

/* Grade pills */
.grade-strip{display:flex;gap:6px;flex-wrap:wrap;}
.grade-pill2{flex:1;min-width:56px;text-align:center;border-radius:8px;padding:7px 4px;font-weight:800;font-size:12px;border:1px solid transparent;}
.grade-pill2 .g-count{font-size:15px;line-height:1;margin-bottom:1px;}

/* Assessment links */
.asmt-link{display:flex;align-items:center;justify-content:space-between;padding:9px 12px;border-radius:8px;background:#eff6ff;border:1px solid #bfdbfe;color:var(--p2);text-decoration:none;font-size:12px;font-weight:600;transition:background .15s,transform .1s;}
.asmt-link:hover{background:#dbeafe;transform:translateX(2px);color:var(--p3);}
.asmt-link .badge2{background:var(--p2);color:#fff;border-radius:20px;padding:2px 9px;font-size:11px;font-weight:700;}

/* ── Lock banners ───────────────────────────────────────────── */
.ss-banner{border-radius:var(--r);padding:13px 16px;margin-bottom:16px;display:flex;align-items:flex-start;gap:12px;border:1px solid;}
.ss-banner.warn{background:#fffbeb;border-color:#fcd34d;color:#92400e;}
.ss-banner.danger{background:#fef2f2;border-color:#fca5a5;color:#991b1b;}
.ss-banner.muted{background:#f3f4f6;border-color:#d1d5db;color:#374151;}
.ss-banner .banner-icon{font-size:20px;flex-shrink:0;margin-top:1px;}
.ss-banner strong{font-size:13px;font-weight:700;display:block;margin-bottom:2px;}
.ss-banner small{font-size:12px;line-height:1.5;}

/* ── Table card ─────────────────────────────────────────────── */
.ss-table-card{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--shadow);margin-bottom:20px;}
.ss-table-toolbar{background:var(--p);padding:14px 20px;display:flex;align-items:center;flex-wrap:wrap;gap:10px;}
.ss-table-toolbar h5{color:#fff;font-size:14px;font-weight:700;margin:0;flex-shrink:0;}
.ss-search{display:flex;align-items:center;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);border-radius:8px;padding:5px 10px;gap:6px;min-width:200px;flex:1;max-width:280px;}
.ss-search input{background:transparent;border:none;outline:none;color:#fff;font-size:13px;width:100%;}
.ss-search input::placeholder{color:rgba(255,255,255,.5);}
.ss-search i{color:rgba(255,255,255,.55);font-size:15px;flex-shrink:0;}
.ss-toolbar-btn{padding:6px 13px;border-radius:7px;font-size:12px;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all .15s;white-space:nowrap;}
.ss-toolbar-btn:hover{filter:brightness(0.92);transform:translateY(-1px);}
.ss-btn-light{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.25);color:#fff;}
.ss-btn-warn{background:#f59e0b;color:#fff;}
.ss-btn-red{background:#ef4444;color:#fff;}
.ss-btn-green{background:#10b981;color:#fff;}
.ss-btn-info{background:#38bdf8;color:#0c4a6e;}

/* ── Table itself ───────────────────────────────────────────── */
.ss-table-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;}
.ss-table-scroll::-webkit-scrollbar{height:5px;}
.ss-table-scroll::-webkit-scrollbar-track{background:#f1f5f9;}
.ss-table-scroll::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:3px;}
.ss-table-scroll::-webkit-scrollbar-thumb:hover{background:#94a3b8;}

#scoresheetTable{width:100%;border-collapse:separate;border-spacing:0;font-size:12.5px;}
#scoresheetTable thead th{
    background:var(--p);color:#fff;padding:10px 10px;
    font-weight:600;font-size:11.5px;white-space:nowrap;
    border-bottom:2px solid rgba(255,255,255,.15);
    position:sticky;top:0;z-index:2;letter-spacing:.03em;
}
#scoresheetTable thead th:first-child{border-radius:0;padding-left:14px;}
#scoresheetTable thead .th-group-label{
    background:#0f2645;color:rgba(255,255,255,.7);
    font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;
    padding:6px 10px 4px;
}
#scoresheetTable tbody tr{transition:background .1s;}
#scoresheetTable tbody td{
    padding:9px 10px;vertical-align:middle;
    border-bottom:1px solid #f1f5f9;
}
#scoresheetTable tbody td:first-child{padding-left:14px;}
#scoresheetTable tbody tr:last-child td{border-bottom:none;}

/* Row states */
.row-locked{background:#fef9f9 !important;}
.row-vetted{background:#f0fdf4 !important;}
.row-not-vetted{background:#fef9f9 !important;}
.row-pending{background:#fffef5 !important;}

/* Hover — clipped inside the card, no layout shift */
#scoresheetTable tbody tr:not(.row-locked):hover td{background:#f0f6ff !important;box-shadow:inset 3px 0 0 var(--p2);}

/* ── Row entrance ───────────────────────────────────────────── */
#scoresheetTableBody tr[data-id]{opacity:0;transform:translateY(10px);transition:opacity .32s ease,transform .32s ease;}
#scoresheetTableBody tr[data-id].row-visible{opacity:1;transform:translateY(0);}
@media(prefers-reduced-motion:reduce){#scoresheetTableBody tr[data-id]{opacity:1;transform:none;}}

/* ── Badges & inputs ────────────────────────────────────────── */
.score-input{
    width:68px;height:33px;padding:3px 5px;
    border:1.5px solid var(--border);border-radius:6px;
    font-size:12.5px;text-align:center;background:#fff;
    transition:border-color .15s,box-shadow .15s;
}
.score-input:focus{outline:none;border-color:var(--p2);box-shadow:0 0 0 3px rgba(37,99,235,.12);}
.score-input.is-invalid{border-color:var(--err)!important;background:#fef2f2;}
.score-input.is-saved{border-color:var(--ok)!important;background:#f0fdf4;}
.score-input:disabled{background:#f8f9fa;cursor:not-allowed;opacity:.6;}

.ss-badge{display:inline-flex;align-items:center;justify-content:center;border-radius:20px;padding:3px 9px;font-size:11.5px;font-weight:700;white-space:nowrap;}
.badge-total{background:#eff6ff;color:#1d4ed8;}
.badge-cum{background:#f0fdf4;color:#15803d;}
.badge-warn{background:#fffbeb;color:#b45309;}
.badge-danger{background:#fef2f2;color:#b91c1c;}
.badge-info{background:#f0f4ff;color:#4338ca;}
.badge-pos{background:var(--p);color:#fff;min-width:36px;}
.badge-pos-teal{background:#0f766e;color:#fff;min-width:36px;}
.badge-pos-cyan{background:#0891b2;color:#fff;min-width:36px;}
.badge-pos-purple{background:#7c3aed;color:#fff;min-width:36px;}
.badge-muted{background:#f1f5f9;color:var(--muted);}
.badge-avg{background:#f5f3ff;color:#6d28d9;}
.badge-gpa{background:#fef3c7;color:#92400e;}
.badge-cgpa{background:#f1f5f9;color:#334155;}

/* grade colours */
.grade-badge,.cum-grade-badge{display:inline-block;font-weight:800;font-size:13px;min-width:26px;text-align:center;transition:all .22s;}
.grade-badge.updated,.cum-grade-badge.updated{animation:gradeFlash .4s ease;}
@keyframes gradeFlash{0%,100%{transform:scale(1);}50%{transform:scale(1.2);}}
.grade-loading{display:inline-block;width:11px;height:11px;border:2px solid #e2e8f0;border-top-color:var(--p2);border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle;}
@keyframes spin{to{transform:rotate(360deg);}}

/* position flash */
@keyframes posFlash{0%,100%{transform:scale(1);}40%{transform:scale(1.22);}}
.pos-flash{animation:posFlash .45s cubic-bezier(.34,1.4,.64,1);}

/* lock badges */
.lock-chip{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap;}
.lock-chip.global{background:#fef2f2;color:#dc2626;border:1px solid #fecaca;}
.lock-chip.individual{background:#fffbeb;color:#d97706;border:1px solid #fde68a;}
.lock-chip.disabled{background:#f3f4f6;color:#6b7280;border:1px solid #e5e7eb;}
.lock-chip.open{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;}

/* student avatar */
.stu-avatar{width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;cursor:pointer;transition:transform .15s;}
.stu-avatar:hover{transform:scale(1.08);}

/* ── Table footer bar ───────────────────────────────────────── */
.ss-footer{padding:12px 20px;background:#fafbfc;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;}
.ss-footer-left,.ss-footer-right{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.ss-btn{padding:7px 16px;border-radius:8px;font-size:12.5px;font-weight:600;border:1.5px solid transparent;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all .15s;white-space:nowrap;}
.ss-btn:hover{transform:translateY(-1px);}
.ss-btn:disabled{opacity:.5;cursor:not-allowed;transform:none;}
.ss-btn-outline{background:#fff;border-color:var(--border);color:#334155;}
.ss-btn-outline:hover{border-color:#94a3b8;background:#f8fafc;}
.ss-btn-outline-danger{background:#fff;border-color:#fca5a5;color:#dc2626;}
.ss-btn-outline-danger:hover{background:#fef2f2;}
.ss-btn-save{background:var(--ok);border-color:var(--ok);color:#fff;padding:8px 22px;}
.ss-btn-save:hover{background:#15803d;border-color:#15803d;}
.ss-btn-primary{background:var(--p2);border-color:var(--p2);color:#fff;}
.ss-btn-primary:hover{background:var(--p3);}

/* ── Column group separators ────────────────────────────────── */
.col-sep{border-left:2px solid rgba(255,255,255,.15) !important;}
.col-sep-body{border-left:2px solid #f0f4f8 !important;}

/* ── Apple-style save overlay ───────────────────────────────── */
#ssSaveOverlay{display:none;position:fixed;inset:0;z-index:99999;background:rgba(15,23,42,.35);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);align-items:center;justify-content:center;}
#ssSaveOverlay.ss-visible{display:flex!important;animation:ssOvIn .2s ease;}
@keyframes ssOvIn{from{opacity:0;}to{opacity:1;}}
#ssSaveModal{background:#fff;border-radius:20px;border:1px solid rgba(0,0,0,.08);box-shadow:0 24px 64px rgba(0,0,0,.18);padding:28px 32px 22px;width:300px;text-align:center;transform:scale(.88) translateY(14px);opacity:0;transition:transform .3s cubic-bezier(.34,1.3,.64,1),opacity .2s ease;}
#ssSaveOverlay.ss-visible #ssSaveModal{transform:scale(1) translateY(0);opacity:1;}
#ssSaveOverlay.ss-closing #ssSaveModal{transform:scale(.9) translateY(8px);opacity:0;}
#ssSaveOverlay.ss-closing{animation:ssOvOut .22s ease forwards;}
@keyframes ssOvOut{to{opacity:0;}}
.ss-modal-ring{position:relative;width:56px;height:56px;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;}
.ss-arc-svg{position:absolute;inset:0;}
.ss-icon-center{width:36px;height:36px;border-radius:50%;background:rgba(30,58,95,.08);display:flex;align-items:center;justify-content:center;}
.ss-modal-title{font-size:16px;font-weight:700;color:#0f172a;margin-bottom:4px;}
.ss-modal-sub{font-size:13px;color:var(--muted);margin-bottom:14px;}
.ss-prog-track{height:4px;border-radius:2px;background:#e2e8f0;overflow:hidden;margin-bottom:8px;}
.ss-prog-fill{height:100%;border-radius:2px;transition:width .3s ease,background .3s ease;background:var(--p);}
.ss-count-row{display:flex;align-items:center;justify-content:space-between;font-size:12px;color:var(--muted);}
.ss-count-num{font-weight:700;color:#0f172a;}

/* ── Tooltip ─────────────────────────────────────────────────── */
#scoreTooltip{display:none;position:fixed;z-index:99990;background:#fff;border:1px solid var(--border);border-radius:10px;padding:10px 13px;width:220px;box-shadow:0 8px 24px rgba(0,0,0,.10);pointer-events:none;font-size:12px;opacity:0;transition:opacity .12s;}
.tip-top{display:flex;align-items:center;gap:8px;margin-bottom:7px;padding-bottom:7px;border-bottom:1px solid #f1f5f9;}
.tip-avatar{width:26px;height:26px;border-radius:50%;object-fit:cover;flex-shrink:0;}
.tip-name{font-size:12px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.tip-adm{font-size:10px;color:var(--muted);}
.tip-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px;margin-bottom:7px;}
.tip-stat-label{font-size:9px;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;font-weight:600;margin-bottom:1px;}
.tip-stat-val{font-size:14px;font-weight:800;line-height:1;}
.tip-divider{height:1px;background:#f1f5f9;margin-bottom:7px;}
.tip-prog-labels{display:flex;justify-content:space-between;font-size:10px;color:#94a3b8;margin-bottom:3px;}
.tip-prog-track{height:3px;background:#f1f5f9;border-radius:2px;overflow:hidden;}
.tip-prog-fill{height:100%;border-radius:2px;background:var(--p2);transition:width .25s,background .25s;}

/* ── Legend row ─────────────────────────────────────────────── */
.ss-legend{display:flex;align-items:center;gap:16px;flex-wrap:wrap;padding:10px 0;margin-bottom:12px;font-size:11.5px;color:var(--muted);}
.ss-legend-item{display:flex;align-items:center;gap:5px;}
.ss-legend-dot{width:8px;height:8px;border-radius:50%;}

@media(max-width:768px){
    .ss-hero{flex-direction:column;align-items:flex-start;}
    .ss-panels{grid-template-columns:1fr;}
    .ss-table-toolbar{flex-direction:column;align-items:flex-start;}
    .ss-search{min-width:100%;max-width:100%;}
    .score-input{width:60px;height:38px;font-size:.9rem;}
}
</style>

{{-- ══ SAVE OVERLAY ════════════════════════════════════════════ --}}
<div id="ssSaveOverlay">
    <div id="ssSaveModal">
        <div class="ss-modal-ring">
            <svg class="ss-arc-svg" viewBox="0 0 56 56" fill="none">
                <circle cx="28" cy="28" r="24" stroke="#e2e8f0" stroke-width="2.5"/>
                <circle id="ssArcFg" cx="28" cy="28" r="24"
                    stroke="#1e3a5f" stroke-width="2.5" stroke-linecap="round"
                    stroke-dasharray="150.8" stroke-dashoffset="150.8"
                    transform="rotate(-90 28 28)"
                    style="transition:stroke-dashoffset .35s ease,stroke .3s ease;"/>
            </svg>
            <div class="ss-icon-center" id="ssIconCenter">
                <svg id="ssIconSave" width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <rect x="2.5" y="2.5" width="13" height="13" rx="2.5" stroke="#1e3a5f" stroke-width="1.5"/>
                    <rect x="5.5" y="2.5" width="5" height="4.5" rx="1" fill="#1e3a5f" opacity=".4"/>
                    <path d="M5 10.5h8M5 13h5.5" stroke="#1e3a5f" stroke-width="1.3" stroke-linecap="round"/>
                </svg>
                <svg id="ssIconCheck" width="18" height="18" viewBox="0 0 18 18" fill="none" style="display:none">
                    <polyline id="ssCheckPath" points="3.5,9.5 7.5,13.5 14.5,5.5" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <svg id="ssIconX" width="16" height="16" viewBox="0 0 16 16" fill="none" style="display:none">
                    <line x1="3.5" y1="3.5" x2="12.5" y2="12.5" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round"/>
                    <line x1="12.5" y1="3.5" x2="3.5" y2="12.5" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
        <div class="ss-modal-title" id="ssSaveTitle">Saving scores</div>
        <div class="ss-modal-sub"  id="ssSaveSub">Please wait…</div>
        <div class="ss-prog-track"><div class="ss-prog-fill" id="ssSaveFill"></div></div>
        <div class="ss-count-row">
            <span id="ssSaveCountLabel">Saved</span>
            <span class="ss-count-num" id="ssSaveCountNum"></span>
        </div>
    </div>
</div>

{{-- ══ TOOLTIP ══════════════════════════════════════════════════ --}}
<div id="scoreTooltip">
    <div class="tip-top">
        <img id="stAvatar" class="tip-avatar" src="" alt=""
             onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
        <div style="min-width:0">
            <div class="tip-name" id="stName">—</div>
            <div class="tip-adm"  id="stMeta">—</div>
        </div>
    </div>
    <div class="tip-grid">
        <div><div class="tip-stat-label">Entering</div><div class="tip-stat-val" id="stVal" style="color:var(--p2)">—</div></div>
        <div><div class="tip-stat-label">Total</div><div class="tip-stat-val" id="stTotal" style="color:var(--p)">—</div></div>
        <div><div class="tip-stat-label">Grade</div><div class="tip-stat-val" id="stGrade" style="color:var(--muted)">—</div></div>
    </div>
    <div class="tip-divider"></div>
    <div class="tip-prog-labels">
        <span id="stProgLabel">Score progress</span>
        <span id="stProgPct">0%</span>
    </div>
    <div class="tip-prog-track"><div class="tip-prog-fill" id="stProgFill"></div></div>
</div>

<div class="main-content">
<div class="page-content">
<div class="container-fluid ss-wrap">

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Error!</strong>
        <ul class="mb-0 mt-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@foreach(['success','status','warning','error'] as $bag)
    @if(session($bag))
        <div class="alert alert-{{ $bag==='status'?'success':($bag==='error'?'danger':$bag) }} alert-dismissible fade show">
            {{ session($bag) }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
@endforeach

{{-- ══ LOCK BANNERS ════════════════════════════════════════════ --}}
@if(isset($teacherEditingEnabled) && !$teacherEditingEnabled)
    <div class="ss-banner muted">
        <i class="ri-user-settings-line banner-icon"></i>
        <div>
            <strong>Teacher editing disabled</strong>
            <small>An administrator has set this subject to read-only. Scores cannot be changed at this time.</small>
        </div>
    </div>
@elseif(isset($globalLock) && $globalLock)
    <div class="ss-banner danger">
        <i class="ri-global-line banner-icon"></i>
        <div>
            <strong>Global lock active</strong>
            <small>
                {{ $globalLock->reason ?? 'Locked by administrator' }}<br>
                Locked by {{ optional($globalLock->lockedBy)->name }} &mdash; {{ $globalLock->locked_at->format('d M Y H:i') }}
                @if($globalLock->scheduled_unlock_at)
                    &nbsp;·&nbsp; Scheduled unlock: {{ \Carbon\Carbon::parse($globalLock->scheduled_unlock_at)->format('d M Y H:i') }}
                @endif
            </small>
        </div>
    </div>
@elseif(($lockedCount ?? 0) > 0)
    <div class="ss-banner warn">
        <i class="ri-lock-line banner-icon"></i>
        <div>
            <strong>{{ $lockedCount }} of {{ $broadsheets->count() }} records locked</strong>
            <small>Locked records are read-only. Contact your administrator to unlock them.</small>
        </div>
    </div>
@endif

{{-- ══ HERO ═════════════════════════════════════════════════════ --}}
@if ($broadsheets->isNotEmpty())
@php
    $first   = $broadsheets->first();
    $total   = $broadsheets->count();
    $passed  = $broadsheets->filter(fn($b)=>($b->total??0)>=40)->count();
    $failed  = $total - $passed;
    $avg     = $total > 0 ? round($broadsheets->avg('total'),1) : 0;
    $highest = $total > 0 ? round($broadsheets->max('total'),1) : 0;
    $lowest  = $total > 0 ? round($broadsheets->min('total'),1) : 0;
    $passRate= $total > 0 ? round($passed/$total*100) : 0;
    $gradeDist= $broadsheets->groupBy('grade')->map->count();
    $gradeColors=['A'=>'#16a34a','A1'=>'#16a34a','B'=>'#2563eb','B2'=>'#2563eb','B3'=>'#3b82f6','C'=>'#7c3aed','C4'=>'#7c3aed','C5'=>'#8b5cf6','C6'=>'#a78bfa','D'=>'#d97706','D7'=>'#d97706','E8'=>'#f59e0b','F'=>'#dc2626','F9'=>'#dc2626'];
@endphp

<div class="ss-hero">
    <div class="ss-hero-left">
        <h4><i class="ri-book-2-line me-2" style="opacity:.7"></i>{{ $first->subject }} <span style="opacity:.6;font-weight:500">({{ $first->subject_code }})</span></h4>
        <p>{{ $pagetitle }}</p>
        <div class="ss-hero-pills">
            <span class="ss-pill"><i class="ri-school-line me-1"></i>{{ $first->schoolclass }} {{ $first->arm }}</span>
            <span class="ss-pill"><i class="ri-calendar-line me-1"></i>{{ $first->term }}</span>
            <span class="ss-pill"><i class="ri-time-line me-1"></i>{{ $first->session }}</span>
        </div>
    </div>
    <div class="ss-hero-actions">
        <button class="ss-toolbar-btn ss-btn-warn" id="downloadMarksSheet"><i class="ri-file-pdf-line"></i> Marks Sheet</button>
        <button class="ss-toolbar-btn ss-btn-red"  id="downloadScoresPdf"><i class="ri-file-pdf-2-line"></i> Scores PDF</button>
        <button class="ss-toolbar-btn ss-btn-green" id="downloadExcel"><i class="ri-download-line"></i> Excel</button>
        <button class="ss-toolbar-btn ss-btn-light" data-bs-toggle="modal" data-bs-target="#importModal"><i class="ri-upload-line"></i> Import</button>
        <a href="{{ route('myresultroom.index') }}" class="ss-toolbar-btn ss-btn-light"><i class="ri-arrow-left-line"></i> Back</a>
    </div>
</div>

{{-- ══ STAT STRIP ═══════════════════════════════════════════════ --}}
<div class="ss-stats">
    <div class="ss-stat">
        <div class="ss-stat-icon" style="background:#eff6ff;"><i class="ri-group-line" style="color:var(--p2)"></i></div>
        <div class="ss-stat-val">{{ $total }}</div>
        <div class="ss-stat-lbl">Students</div>
    </div>
    <div class="ss-stat">
        <div class="ss-stat-icon" style="background:#f0fdf4;"><i class="ri-check-double-line" style="color:#16a34a"></i></div>
        <div class="ss-stat-val" style="color:#16a34a">{{ $passRate }}%</div>
        <div class="ss-stat-lbl">Pass rate</div>
        <div class="ss-stat-bar"><div class="ss-stat-bar-fill" style="width:{{ $passRate }}%;background:#16a34a"></div></div>
    </div>
    <div class="ss-stat">
        <div class="ss-stat-icon" style="background:#fffbeb;"><i class="ri-bar-chart-2-line" style="color:#d97706"></i></div>
        <div class="ss-stat-val" style="color:#d97706">{{ $avg }}</div>
        <div class="ss-stat-lbl">Class average</div>
    </div>
    <div class="ss-stat">
        <div class="ss-stat-icon" style="background:#f0fdf4;"><i class="ri-arrow-up-line" style="color:#16a34a"></i></div>
        <div class="ss-stat-val" style="color:#16a34a">{{ $highest }}</div>
        <div class="ss-stat-lbl">Highest score</div>
    </div>
    <div class="ss-stat">
        <div class="ss-stat-icon" style="background:#fef2f2;"><i class="ri-arrow-down-line" style="color:#dc2626"></i></div>
        <div class="ss-stat-val" style="color:#dc2626">{{ $lowest }}</div>
        <div class="ss-stat-lbl">Lowest score</div>
    </div>
    <div class="ss-stat">
        <div class="ss-stat-icon" style="background:#fef2f2;"><i class="ri-close-circle-line" style="color:#dc2626"></i></div>
        <div class="ss-stat-val" style="color:#dc2626">{{ $failed }}</div>
        <div class="ss-stat-lbl">Failed (total)</div>
    </div>
</div>

{{-- ══ 3-PANEL ROW ══════════════════════════════════════════════ --}}
<div class="ss-panels">
    {{-- Score summary --}}
    <div class="ss-panel">
        <div class="ss-panel-head"><i class="ri-bar-chart-2-line"></i> Score summary</div>
        <div class="ss-panel-body">
            <div class="ss-score-grid">
                <div class="ss-score-cell" style="background:#f0fdf4">
                    <div class="val" style="color:#15803d">{{ $passed }}</div>
                    <div class="lbl" style="color:#166534">Passed</div>
                </div>
                <div class="ss-score-cell" style="background:#fef2f2">
                    <div class="val" style="color:#dc2626">{{ $failed }}</div>
                    <div class="lbl" style="color:#991b1b">Failed</div>
                </div>
                <div class="ss-score-cell" style="background:#eff6ff">
                    <div class="val" style="color:#1d4ed8">{{ $highest }}</div>
                    <div class="lbl" style="color:#1e40af">Highest</div>
                </div>
                <div class="ss-score-cell" style="background:#fffbeb">
                    <div class="val" style="color:#d97706">{{ $lowest }}</div>
                    <div class="lbl" style="color:#92400e">Lowest</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grade distribution --}}
    <div class="ss-panel">
        <div class="ss-panel-head"><i class="ri-pie-chart-line"></i> Grade distribution (total)</div>
        <div class="ss-panel-body">
            @if($gradeDist->isEmpty())
                <p class="text-muted small text-center mt-2">No grades yet.</p>
            @else
                <div class="grade-strip">
                    @foreach($gradeDist->sortKeysDesc() as $grade => $count)
                        @php $pct2=round($count/$total*100); $col=$gradeColors[$grade]??'#6b7280'; @endphp
                        <div class="grade-pill2" style="background:{{ $col }}18;color:{{ $col }};border-color:{{ $col }}40">
                            <div class="g-count">{{ $grade }}</div>
                            <div>{{ $count }}<span style="font-weight:500;opacity:.7"> ({{ $pct2 }}%)</span></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Assessments --}}
    <div class="ss-panel">
        <div class="ss-panel-head"><i class="ri-clipboard-line"></i> Assessments</div>
        <div class="ss-panel-body">
            @if($assessments->isNotEmpty())
                <div class="d-flex flex-column gap-2">
                    @foreach($assessments as $assessment)
                        <a href="{{ route('assessment.scoresheet', ['schoolclassid'=>session('schoolclass_id'),'subjectclassid'=>session('subjectclass_id'),'staffid'=>session('staff_id'),'termid'=>session('term_id'),'sessionid'=>session('session_id'),'assessmentid'=>$assessment->id]) }}"
                           class="asmt-link">
                            <span><i class="ri-edit-line me-1"></i>{{ $assessment->name }}</span>
                            <span class="badge2">{{ $assessment->max_score }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-muted small text-center mt-2"><i class="ri-information-line me-1"></i>No assessments defined.</p>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ══ LEGEND + RECALCULATE ════════════════════════════════════ --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div class="ss-legend">
        <span class="ss-legend-item"><span class="ss-legend-dot" style="background:var(--p)"></span>Class pos (cum) = all arms, by cum</span>
        <span class="ss-legend-item"><span class="ss-legend-dot" style="background:#0f766e"></span>Class pos (total) = all arms, by total</span>
        <span class="ss-legend-item"><span class="ss-legend-dot" style="background:#0891b2"></span>Arm pos (total) = this arm, by total</span>
        <span class="ss-legend-item"><span class="ss-legend-dot" style="background:#7c3aed"></span>Arm pos (cum) = this arm, by cum</span>
    </div>
    <button type="button" class="ss-btn ss-btn-primary" id="updateArmPositionsBtn" style="flex-shrink:0">
        <i class="ri-refresh-line"></i> Recalculate positions
    </button>
</div>

{{-- ══ MAIN TABLE CARD ══════════════════════════════════════════ --}}
<div class="ss-table-card">
    {{-- Toolbar --}}
    <div class="ss-table-toolbar">
        <h5><i class="ri-file-list-3-line me-2"></i>Scoresheet
            @if($broadsheets->isNotEmpty())
                <span id="scoreCount" style="background:rgba(255,255,255,.2);color:#fff;border-radius:12px;padding:1px 9px;font-size:12px;font-weight:700;margin-left:6px;">{{ $broadsheets->count() }}</span>
            @endif
        </h5>
        <div class="ss-search">
            <i class="ri-search-line"></i>
            <input type="text" id="searchInput" placeholder="Search admission / name…" {{ $broadsheets->isEmpty()?'disabled':'' }}>
            <button id="clearSearch" style="background:none;border:none;cursor:pointer;color:rgba(255,255,255,.5);font-size:16px;line-height:1;padding:0">&times;</button>
        </div>
        <div style="margin-left:auto;display:flex;gap:7px;flex-wrap:wrap;">
            @if($broadsheets->isNotEmpty())
                <button class="ss-toolbar-btn ss-btn-light" data-bs-toggle="modal" data-bs-target="#columnVisibilityModal"><i class="ri-eye-line"></i> Columns</button>
            @endif
        </div>
    </div>

    {{-- Download progress --}}
    <div id="downloadProgressContainer" style="display:none;" class="p-3">
        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#fefce8;border:1px solid #fde68a;">
            <div class="spinner-border spinner-border-sm text-warning"></div>
            <div class="flex-grow-1">
                <div style="font-size:13px;font-weight:600;margin-bottom:4px;" id="downloadProgressLabel">Downloading…</div>
                <div class="progress" style="height:4px;">
                    <div class="progress-bar progress-bar-animated bg-warning" id="downloadProgressBar" style="width:0%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Empty state --}}
    @if($broadsheets->isEmpty())
        <div class="text-center py-5 px-4">
            <i class="ri-inbox-line ri-3x text-muted d-block mb-3"></i>
            <h6 class="text-muted">No scores available</h6>
            <p class="text-muted small mb-0">Scores will appear here once they are entered for this subject.</p>
        </div>
    @else
    <div class="ss-table-scroll">
    <table id="scoresheetTable">
        <thead>
            {{-- Group label row --}}
            <tr>
                <th class="th-group-label" colspan="4"></th>
                @if($assessments->isNotEmpty())
                    <th class="th-group-label col-sep" colspan="{{ $assessments->count() }}">Assessments</th>
                @endif
                <th class="th-group-label col-sep" colspan="2">Score</th>
                <th class="th-group-label col-sep" colspan="3">Cumulative</th>
                <th class="th-group-label col-sep" colspan="3">Analytics</th>
                <th class="th-group-label col-sep" colspan="4">Positions</th>
                <th class="th-group-label col-sep" colspan="2">Meta</th>
            </tr>
            <tr>
                <th style="width:38px">
                    <input class="form-check-input" type="checkbox" id="checkAll" {{ (isset($teacherEditingEnabled)&&!$teacherEditingEnabled)||isset($globalLock)?'disabled':'' }}>
                </th>
                <th style="width:38px">SN</th>
                <th>Adm No</th>
                <th>Student</th>

                @forelse ($assessments as $assessment)
                    <th class="text-center col-assessment-{{ $assessment->id }} {{ $loop->first ? 'col-sep' : '' }}">
                        {{ $assessment->name }}<br><small style="opacity:.65;font-weight:400">({{ $assessment->max_score }})</small>
                    </th>
                @empty
                    <th class="col-sep" colspan="4">No assessments</th>
                @endforelse

                <th class="text-center col-sep col-total">Total</th>
                <th class="text-center col-total-grade">Grade</th>

                <th class="text-center col-sep col-bf">BF</th>
                <th class="text-center col-cum">Cum</th>
                <th class="text-center col-cum-grade" title="Grade on cumulative">Cum<br><small style="opacity:.65;font-weight:400">Grade</small></th>

                <th class="text-center col-sep col-avg" title="Subject class average">Avg</th>
                <th class="text-center col-gpa">GPA</th>
                <th class="text-center col-cgpa">CGPA</th>

                <th class="text-center col-sep col-position" title="All arms, ranked by cum">Class<br><small style="opacity:.65;font-weight:400">(Cum)</small></th>
                <th class="text-center col-position-total" title="All arms, ranked by total">Class<br><small style="opacity:.65;font-weight:400">(Total)</small></th>
                <th class="text-center col-arm-position" title="This arm, ranked by total">Arm<br><small style="opacity:.65;font-weight:400">(Total)</small></th>
                <th class="text-center col-arm-position-cum" title="This arm, ranked by cum">Arm<br><small style="opacity:.65;font-weight:400">(Cum)</small></th>

                <th class="text-center col-sep col-vetted">Status</th>
                <th class="text-center col-lock-status" style="width:90px"><i class="ri-lock-line"></i><br><small style="font-weight:400;opacity:.7">Lock</small></th>
            </tr>
        </thead>
        <tbody id="scoresheetTableBody">
        @php $i=0; @endphp
        @foreach ($broadsheets as $broadsheet)
            @php
                $rowTotal=0;
                foreach($assessments as $a){$so=$broadsheet->assessmentScores->where('assessment_id',$a->id)->first();$rowTotal+=$so?$so->score:0;}
                $cum=$broadsheet->cum??0;
                $totalGrade=$broadsheet->grade??'-';
                $gradeForCum='-';
                if(isset($broadsheet->classcategoryid)){$cat=\App\Models\Classcategory::find($broadsheet->classcategoryid);$gradeForCum=$cat?$cat->calculateGrade($cum):'-';}
                $isGloballyLocked=isset($globalLock)&&$globalLock;
                $isTeacherEditingDisabled=isset($teacherEditingEnabled)&&!$teacherEditingEnabled;
                $isRowLocked=$broadsheet->is_locked||$isGloballyLocked||$isTeacherEditingDisabled;
                $hasScheduledUnlock=!is_null($broadsheet->scheduled_unlock_at);
                $vClass=match(true){$isRowLocked=>'row-locked',$broadsheet->vettedstatus==='1'=>'row-vetted',$broadsheet->vettedstatus==='0'=>'row-not-vetted',default=>'row-pending'};
                $avatarUrl=$broadsheet->picture?asset('storage/student_avatars/'.basename($broadsheet->picture)):asset('storage/student_avatars/unnamed.jpg');
                $tcol=$rowTotal>=70?'#166534':($rowTotal>=50?'#1d4ed8':($rowTotal>=40?'#92400e':'#b91c1c'));
                $tbg=$rowTotal>=70?'#f0fdf4':($rowTotal>=50?'#eff6ff':($rowTotal>=40?'#fffbeb':'#fef2f2'));
                $ccol=$cum>=70?'#166534':($cum>=50?'#1d4ed8':($cum>=40?'#92400e':'#b91c1c'));
                $cbg=$cum>=70?'#f0fdf4':($cum>=50?'#eff6ff':($cum>=40?'#fffbeb':'#fef2f2'));
                $tgcol=$gradeColors[$totalGrade]??'#64748b';
                $cgcol=$gradeColors[$gradeForCum]??'#64748b';
            @endphp
            <tr class="{{ $vClass }}"
                data-id="{{ $broadsheet->id }}"
                data-bf="{{ $broadsheet->bf??0 }}"
                data-termid="{{ session('term_id') }}"
                data-schoolclassid="{{ $broadsheet->schoolclass_id??session('schoolclass_id') }}"
                data-categoryid="{{ $broadsheet->classcategoryid??'' }}"
                data-name="{{ $broadsheet->lname??'' }}, {{ $broadsheet->fname??'' }}{{ $broadsheet->mname?' '.$broadsheet->mname:'' }}"
                data-admissionno="{{ $broadsheet->admissionno??'' }}"
                data-avatar="{{ $avatarUrl }}"
                data-is-locked="{{ $isRowLocked?'true':'false' }}">

                <td><input class="form-check-input score-checkbox" type="checkbox" data-id="{{ $broadsheet->id }}" {{ $isRowLocked?'disabled':'' }}></td>
                <td style="color:var(--muted);font-size:11.5px;font-weight:600">{{ ++$i }}</td>
                <td><span style="font-size:11.5px;color:var(--muted)">{{ $broadsheet->admissionno??'-' }}</span></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $avatarUrl }}" class="stu-avatar"
                             data-bs-toggle="modal" data-bs-target="#imageViewModal"
                             data-image="{{ $avatarUrl }}"
                             onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                        <div>
                            <div style="font-size:12.5px;font-weight:700;color:#0f172a;line-height:1.2">{{ $broadsheet->lname??'' }}, {{ $broadsheet->fname??'' }}</div>
                            @if($broadsheet->mname)<div style="font-size:11px;color:var(--muted)">{{ $broadsheet->mname }}</div>@endif
                        </div>
                    </div>
                </td>

                @forelse ($assessments as $assessment)
                    @php $scoreObj=$broadsheet->assessmentScores->where('assessment_id',$assessment->id)->first();$scoreValue=$scoreObj?$scoreObj->score:0; @endphp
                    <td class="text-center col-assessment-{{ $assessment->id }} assessment-col {{ $loop->first ? 'col-sep-body' : '' }}">
                        <input type="number" class="score-input"
                               data-field="{{ $assessment->id }}" data-max="{{ $assessment->max_score }}"
                               data-id="{{ $broadsheet->id }}" data-original="{{ $scoreValue }}"
                               data-assessment-name="{{ $assessment->name }}"
                               value="{{ $scoreValue }}" min="0" max="{{ $assessment->max_score }}" step="0.1"
                               {{ $isRowLocked?'disabled':'' }}>
                    </td>
                @empty
                    <td class="col-sep-body text-center text-muted" colspan="4">—</td>
                @endforelse

                <td class="text-center col-sep-body col-total">
                    <span class="ss-badge total-badge" style="background:{{ $tbg }};color:{{ $tcol }}">{{ number_format($rowTotal,1) }}</span>
                </td>
                <td class="text-center col-total-grade">
                    <span class="grade-badge" style="color:{{ $tgcol }}">{{ $totalGrade }}</span>
                </td>

                <td class="text-center col-sep-body col-bf">
                    <span class="ss-badge badge-muted bf-badge">{{ number_format($broadsheet->bf??0,1) }}</span>
                </td>
                <td class="text-center col-cum">
                    <span class="ss-badge cum-badge" style="background:{{ $cbg }};color:{{ $ccol }}">{{ number_format($cum,1) }}</span>
                </td>
                <td class="text-center col-cum-grade">
                    <span class="cum-grade-badge" style="color:{{ $cgcol }}">{{ $gradeForCum }}</span>
                </td>

                <td class="text-center col-sep-body col-avg">
                    <span class="ss-badge badge-avg avg-badge">{{ number_format($broadsheet->avg??0,1) }}</span>
                </td>
                <td class="text-center col-gpa">
                    <span class="ss-badge badge-gpa gpa-badge">{{ number_format($broadsheet->gpa??0,2) }}</span>
                </td>
                <td class="text-center col-cgpa">
                    <span class="ss-badge badge-cgpa cgpa-badge">{{ number_format($broadsheet->cgpa??0,2) }}</span>
                </td>

                <td class="text-center col-sep-body col-position">
                    <span class="ss-badge badge-pos position-badge">{{ $broadsheet->position ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->position) : '—' }}</span>
                </td>
                <td class="text-center col-position-total">
                    <span class="ss-badge badge-pos-teal position-total-badge">{{ $broadsheet->position_total ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->position_total) : '—' }}</span>
                </td>
                <td class="text-center col-arm-position">
                    <span class="ss-badge badge-pos-cyan arm-position-badge">{{ $broadsheet->arm_position ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->arm_position) : '—' }}</span>
                </td>
                <td class="text-center col-arm-position-cum">
                    <span class="ss-badge badge-pos-purple arm-position-cum-badge">{{ $broadsheet->arm_position_cum ? \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->arm_position_cum) : '—' }}</span>
                </td>

                <td class="text-center col-sep-body col-vetted">
                    @if($broadsheet->vettedstatus==='1')
                        <span class="ss-badge" style="background:#f0fdf4;color:#15803d"><i class="ri-check-line me-1"></i>Vetted</span>
                    @elseif($broadsheet->vettedstatus==='0')
                        <span class="ss-badge" style="background:#fef2f2;color:#dc2626"><i class="ri-close-line me-1"></i>Not vetted</span>
                    @else
                        <span class="ss-badge" style="background:#fffbeb;color:#d97706"><i class="ri-time-line me-1"></i>Pending</span>
                    @endif
                </td>
                <td class="text-center col-lock-status">
                    @if($isGloballyLocked)
                        <span class="lock-chip global" title="{{ $globalLock->reason??'Global lock active' }}"><i class="ri-global-line"></i> Global</span>
                    @elseif($isTeacherEditingDisabled)
                        <span class="lock-chip disabled"><i class="ri-user-settings-line"></i> Read only</span>
                    @elseif($broadsheet->is_locked)
                        <span class="lock-chip individual" title="{{ $broadsheet->lock_reason??'Locked by admin' }}"><i class="ri-lock-line"></i> Locked</span>
                    @else
                        <span class="lock-chip open"><i class="ri-lock-unlock-line"></i> Editable</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>

    {{-- Footer action bar --}}
    <div class="ss-footer">
        <div class="ss-footer-left">
            <button class="ss-btn ss-btn-outline" id="selectAllScores" {{ (isset($teacherEditingEnabled)&&!$teacherEditingEnabled)||isset($globalLock)?'disabled':'' }}>
                <i class="ri-check-double-line"></i> Select all
            </button>
            <button class="ss-btn ss-btn-outline" id="clearAllScores">
                <i class="ri-close-line"></i> Clear
            </button>
            <button class="ss-btn ss-btn-outline-danger" id="deleteSelectedScoresBtn" {{ (isset($teacherEditingEnabled)&&!$teacherEditingEnabled)||isset($globalLock)?'disabled':'' }}>
                <i class="ri-delete-bin-line"></i> Delete selected
            </button>
            <a href="{{ route('myresultroom.index') }}" class="ss-btn ss-btn-outline">
                <i class="ri-arrow-left-line"></i> Back
            </a>
        </div>
        <div class="ss-footer-right">
            <small style="color:var(--muted);font-size:11.5px"><i class="ri-keyboard-line me-1"></i>Ctrl+S to save</small>
            <button class="ss-btn ss-btn-save" id="bulkUpdateScores" {{ (isset($teacherEditingEnabled)&&!$teacherEditingEnabled)||isset($globalLock)?'disabled':'' }}>
                <i class="ri-save-line"></i> Save all scores
            </button>
        </div>
    </div>
    @endif
</div>

{{-- ══ MODALS ═══════════════════════════════════════════════════ --}}

@if($broadsheets->isNotEmpty())
<div class="modal fade" id="columnVisibilityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background:var(--p)">
                <h5 class="modal-title text-white"><i class="ri-eye-line me-2"></i>Column visibility</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4"><h6 class="fw-bold mb-2" style="color:var(--p);font-size:12px;text-transform:uppercase;letter-spacing:.05em">Student info</h6>
                        @foreach([['col-checkbox','Select'],['col-sn','SN'],['col-admissionno','Adm. No'],['col-name','Name']] as [$c,$l])
                        <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" id="chk-{{ $c }}" data-col="{{ $c }}" checked><label class="form-check-label small" for="chk-{{ $c }}">{{ $l }}</label></div>
                        @endforeach
                    </div>
                    @if($assessments->isNotEmpty())
                    <div class="col-md-4"><h6 class="fw-bold mb-2" style="color:var(--p);font-size:12px;text-transform:uppercase;letter-spacing:.05em">Assessments</h6>
                        @foreach($assessments as $a)
                        <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" id="chk-col-assessment-{{ $a->id }}" data-col="col-assessment-{{ $a->id }}" checked><label class="form-check-label small" for="chk-col-assessment-{{ $a->id }}">{{ $a->name }}</label></div>
                        @endforeach
                    </div>
                    @endif
                    <div class="col-md-4"><h6 class="fw-bold mb-2" style="color:var(--p);font-size:12px;text-transform:uppercase;letter-spacing:.05em">Scores & metrics</h6>
                        @foreach([['col-total','Total'],['col-total-grade','Total grade'],['col-bf','BF'],['col-cum','Cum'],['col-cum-grade','Cum grade'],['col-avg','Class avg'],['col-gpa','GPA'],['col-cgpa','CGPA'],['col-position','Class pos (cum)'],['col-position-total','Class pos (total)'],['col-arm-position','Arm pos (total)'],['col-arm-position-cum','Arm pos (cum)'],['col-vetted','Status'],['col-lock-status','Lock']] as [$c,$l])
                        <div class="form-check"><input class="form-check-input col-toggle" type="checkbox" id="chk-{{ $c }}" data-col="{{ $c }}" checked><label class="form-check-label small" for="chk-{{ $c }}">{{ $l }}</label></div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>
@endif

<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background:var(--p)"><h5 class="modal-title text-white"><i class="ri-upload-line me-2"></i>Import scores</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="alert alert-info small"><i class="ri-information-line me-2"></i>Upload the Excel file exported from this scoresheet.</div>
                <form method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <input type="hidden" name="schoolclass_id"  value="{{ session('schoolclass_id') }}">
                    <input type="hidden" name="subjectclass_id" value="{{ session('subjectclass_id') }}">
                    <input type="hidden" name="staff_id"        value="{{ session('staff_id') }}">
                    <input type="hidden" name="term_id"         value="{{ session('term_id') }}">
                    <input type="hidden" name="session_id"      value="{{ session('session_id') }}">
                    <div class="mb-3"><label class="form-label fw-semibold small">Excel file (.xlsx)</label><input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold small">File password (if protected)</label><input type="password" name="password" class="form-control form-control-sm" placeholder="Enter password"></div>
                    <div id="importLoader" style="display:none" class="mb-3">
                        <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background:#f0fdf4">
                            <div class="spinner-border spinner-border-sm text-success"></div>
                            <div class="flex-grow-1"><div style="font-size:12px;margin-bottom:3px">Uploading…</div>
                                <div class="progress" style="height:4px"><div class="progress-bar progress-bar-animated bg-success" id="uploadProgressBar" style="width:0%"></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary" id="importSubmit"><i class="ri-upload-line me-1"></i>Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="imageViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow-lg">
        <div class="modal-header"><h5 class="modal-title">Student photo</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body text-center p-4"><img id="enlargedImage" src="" alt="Student" class="img-fluid rounded-3" style="max-height:380px"></div>
    </div></div>
</div>

</div>
</div>
</div>

<script>
if (!document.querySelector('meta[name="csrf-token"]')) {
    const m=document.createElement('meta');m.name='csrf-token';m.content='{{ csrf_token() }}';document.head.appendChild(m);
}
const CSRF=document.querySelector('meta[name="csrf-token"]').content;

window.routes={
    singleUpdate      :'{{ route("subjectscoresheet.single-update") }}',
    bulkUpdate        :'{{ route("subjectscoresheet.bulk-update") }}',
    destroy           :'{{ route("subjectscoresheet.destroy",["id"=>"__ID__"]) }}',
    results           :'{{ route("subjectscoresheet.results") }}',
    export            :'{{ route("subjectscoresheet.export") }}',
    import            :'{{ route("subjectscoresheet.import") }}',
    downloadMarksSheet:'{{ route("scoresheet.download-marks-sheet") }}',
    downloadScoresPdf :'{{ route("scoresheet.download-scores-pdf") }}',
    gradeForScore     :'{{ route("subjectscoresheet.grade-for-score") }}',
    updateArmPositions:'{{ route("update.arm.positions.all") }}',
};
window.term_id         ={{ session('term_id')         ??'null' }};
window.session_id      ={{ session('session_id')      ??'null' }};
window.subjectclass_id ={{ session('subjectclass_id') ??'null' }};
window.schoolclass_id  ={{ session('schoolclass_id')  ??'null' }};
window.staff_id        ={{ session('staff_id')        ??'null' }};
window.is_senior       ={{ ($is_senior??false)?'true':'false' }};
window.isEditingDisabled={{ ((isset($teacherEditingEnabled)&&!$teacherEditingEnabled)||isset($globalLock))?'true':'false' }};

const GRADE_COLORS={'A':'#16a34a','A1':'#16a34a','B':'#2563eb','B2':'#2563eb','B3':'#3b82f6','C':'#7c3aed','C4':'#7c3aed','C5':'#8b5cf6','C6':'#a78bfa','D':'#d97706','D7':'#d97706','E8':'#f59e0b','F':'#dc2626','F9':'#dc2626'};
const fmtN=(n,d=1)=>parseFloat(n||0).toFixed(d);
const ord=n=>{if(!n||isNaN(n))return'—';n=+n;const s=n%100;return n+(s>=11&&s<=13?'th':(['th','st','nd','rd'][n%10]||'th'));};

function showToast(msg,type='info'){
    const colors={success:'#16a34a',warning:'#d97706',danger:'#dc2626',info:'#2563eb'};
    const id='toast_'+Date.now();
    document.body.insertAdjacentHTML('beforeend',
        `<div id="${id}" class="toast align-items-center border-0 text-white show" role="alert"
          style="position:fixed;bottom:20px;right:20px;z-index:99999;background:${colors[type]||colors.info};min-width:260px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.15)">
          <div class="d-flex p-3"><div class="me-auto" style="font-size:13px">${msg}</div>
          <button class="btn-close btn-close-white ms-2" onclick="this.closest('.toast').remove()"></button></div></div>`);
    setTimeout(()=>document.getElementById(id)?.remove(),4500);
}

function clientGrade(score){
    score=parseFloat(score)||0;
    if(window.is_senior){if(score>=75)return'A1';if(score>=70)return'B2';if(score>=65)return'B3';if(score>=60)return'C4';if(score>=55)return'C5';if(score>=50)return'C6';if(score>=45)return'D7';if(score>=40)return'E8';return'F9';}
    if(score>=70)return'A';if(score>=60)return'B';if(score>=50)return'C';if(score>=40)return'D';return'F';
}

function applyGrade(badge,grade){
    if(!badge)return;badge.textContent=grade||'—';badge.style.color=GRADE_COLORS[grade]||'#64748b';
    badge.classList.remove('updating');badge.classList.add('updated');
    setTimeout(()=>badge.classList.remove('updated'),500);
}

function validateInput(inp){const max=parseFloat(inp.dataset.max)||0,val=parseFloat(inp.value)||0;inp.classList.toggle('is-invalid',val>max);return val<=max;}

function applyAllPositions(broadsheets){
    if(!Array.isArray(broadsheets)||!broadsheets.length)return;
    const map={};broadsheets.forEach(bs=>{map[String(bs.id)]=bs;});
    document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row=>{
        const bs=map[row.dataset.id];if(!bs)return;
        [[row.querySelector('.position-badge'),bs.position],[row.querySelector('.position-total-badge'),bs.position_total],[row.querySelector('.arm-position-badge'),bs.arm_position],[row.querySelector('.arm-position-cum-badge'),bs.arm_position_cum]].forEach(([badge,val])=>{
            if(!badge)return;const newText=ord(val);
            if(badge.textContent.trim()!==newText){badge.textContent=newText;badge.classList.remove('pos-flash');void badge.offsetWidth;badge.classList.add('pos-flash');setTimeout(()=>badge.classList.remove('pos-flash'),480);}
        });
    });
}

const gradeTimers={};
function updateRowGrades(row){
    const bid=row.dataset.id,bf=parseFloat(row.dataset.bf)||0,termId=parseInt(row.dataset.termid)||window.term_id,sclsId=parseInt(row.dataset.schoolclassid)||window.schoolclass_id;
    let totalRaw=0;row.querySelectorAll('.score-input').forEach(inp=>{totalRaw+=parseFloat(inp.value)||0;});
    const cum=(termId==1||bf===0)?totalRaw:(totalRaw+bf)/2;
    const tb=row.querySelector('.total-badge');
    if(tb){tb.textContent=fmtN(totalRaw);const tc=totalRaw>=70?'#166534':totalRaw>=50?'#1d4ed8':totalRaw>=40?'#92400e':'#b91c1c';const tbg=totalRaw>=70?'#f0fdf4':totalRaw>=50?'#eff6ff':totalRaw>=40?'#fffbeb':'#fef2f2';tb.style.color=tc;tb.style.background=tbg;}
    const cb=row.querySelector('.cum-badge');
    if(cb){cb.textContent=fmtN(cum);const cc=cum>=70?'#166534':cum>=50?'#1d4ed8':cum>=40?'#92400e':'#b91c1c';const cbg=cum>=70?'#f0fdf4':cum>=50?'#eff6ff':cum>=40?'#fffbeb':'#fef2f2';cb.style.color=cc;cb.style.background=cbg;}
    applyGrade(row.querySelector('.grade-badge'),clientGrade(totalRaw));
    applyGrade(row.querySelector('.cum-grade-badge'),clientGrade(cum));
    clearTimeout(gradeTimers[bid]);
    gradeTimers[bid]=setTimeout(async()=>{
        const tgb=row.querySelector('.grade-badge'),cgb=row.querySelector('.cum-grade-badge');
        try{
            if(tgb){tgb.classList.add('updating');tgb.innerHTML='<span class="grade-loading"></span>';}
            if(cgb){cgb.classList.add('updating');cgb.innerHTML='<span class="grade-loading"></span>';}
            const res=await fetch(window.routes.gradeForScore,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify({schoolclass_id:sclsId,total:totalRaw,cum})});
            const data=await res.json();
            if(data.success){applyGrade(tgb,data.total_grade);applyGrade(cgb,data.cum_grade);}
            else{applyGrade(tgb,clientGrade(totalRaw));applyGrade(cgb,clientGrade(cum));}
        }catch{applyGrade(tgb,clientGrade(totalRaw));applyGrade(cgb,clientGrade(cum));}
    },400);
}

function saveIndividualScore(input){
    if(window.isEditingDisabled){showToast('Editing is currently disabled for this subject.','warning');return;}
    const row=input.closest('tr');
    if(row.dataset.isLocked==='true'){showToast('This scoresheet is locked and cannot be edited.','warning');return;}
    fetch(window.routes.singleUpdate,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify({broadsheet_id:input.dataset.id,assessment_id:parseInt(input.dataset.field),score:parseFloat(input.value)||0,is_sub:false,term_id:window.term_id,session_id:window.session_id,subjectclass_id:window.subjectclass_id,schoolclass_id:window.schoolclass_id,staff_id:window.staff_id})})
    .then(r=>r.json()).then(data=>{
        if(!data.success){if(data.locked){showToast(data.message||'Scoresheet is locked.','warning');location.reload();}else{showToast(data.message||'Could not save.','warning');}return;}
        const d=data.data,bfB=row.querySelector('.bf-badge'),cumB=row.querySelector('.cum-badge'),tgB=row.querySelector('.grade-badge'),cgB=row.querySelector('.cum-grade-badge'),gpB=row.querySelector('.gpa-badge'),cgpB=row.querySelector('.cgpa-badge');
        if(bfB&&d.bf!=null)bfB.textContent=fmtN(d.bf);
        if(cumB&&d.cum!=null){const cum=parseFloat(d.cum);cumB.textContent=fmtN(cum);const cc=cum>=70?'#166534':cum>=50?'#1d4ed8':cum>=40?'#92400e':'#b91c1c';const cbg=cum>=70?'#f0fdf4':cum>=50?'#eff6ff':cum>=40?'#fffbeb':'#fef2f2';cumB.style.color=cc;cumB.style.background=cbg;}
        if(tgB&&d.grade!=null)applyGrade(tgB,d.grade);
        if(cgB&&d.cum!=null)applyGrade(cgB,clientGrade(parseFloat(d.cum)));
        if(gpB&&d.gpa!=null)gpB.textContent=fmtN(d.gpa,2);if(cgpB&&d.cgpa!=null)cgpB.textContent=fmtN(d.cgpa,2);
        input.classList.add('is-saved');setTimeout(()=>input.classList.remove('is-saved'),2000);
        refreshAllPositions();
    }).catch(err=>{console.warn(err);showToast('Network issue — score may not have saved.','danger');});
}

let posRefTimer=null;
function refreshAllPositions(){
    clearTimeout(posRefTimer);posRefTimer=setTimeout(()=>{
        fetch(window.routes.results,{headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':CSRF}}).then(r=>r.json()).then(d=>{if(d.success&&Array.isArray(d.scores))applyAllPositions(d.scores);}).catch(()=>{});
    },120);
}

/* ── Save modal helpers ─ */
const SS_ARC=150.8;
const ssEl=id=>document.getElementById(id);
function ssResetIcons(){ssEl('ssIconSave').style.display='';ssEl('ssIconCheck').style.display='none';ssEl('ssIconX').style.display='none';ssEl('ssIconCenter').style.background='rgba(30,58,95,.08)';ssEl('ssArcFg').style.stroke='#1e3a5f';}
function ssSetArc(pct){ssEl('ssArcFg').style.strokeDashoffset=(SS_ARC*(1-pct/100)).toFixed(2);}
function ssOpen(total){ssResetIcons();ssSetArc(0);ssEl('ssSaveFill').style.width='0%';ssEl('ssSaveFill').style.background='var(--p)';ssEl('ssSaveTitle').textContent='Saving scores';ssEl('ssSaveSub').textContent='Preparing…';ssEl('ssSaveCountLabel').textContent='Saved';ssEl('ssSaveCountNum').textContent=`0 / ${total}`;const o=ssEl('ssSaveOverlay');o.classList.remove('ss-closing');o.classList.add('ss-visible');}
function ssUpdate(saved,total,pct){ssEl('ssSaveFill').style.width=pct.toFixed(1)+'%';ssEl('ssSaveCountNum').textContent=`${saved} / ${total}`;ssSetArc(pct);ssEl('ssSaveSub').textContent=pct<30?'Uploading data…':pct<65?'Processing records…':pct<88?'Recalculating positions…':'Finalising…';}
function ssSuccess(total){ssEl('ssSaveFill').style.width='100%';ssEl('ssSaveFill').style.background='#16a34a';ssEl('ssArcFg').style.strokeDashoffset='0';ssEl('ssArcFg').style.stroke='#16a34a';ssEl('ssIconCenter').style.background='#dcfce7';ssEl('ssIconSave').style.display='none';ssEl('ssIconCheck').style.display='';ssEl('ssSaveTitle').textContent='All saved';ssEl('ssSaveSub').textContent=`${total} score${total!==1?'s':''} saved`;ssEl('ssSaveCountNum').textContent=`${total} / ${total}`;setTimeout(ssClose,1800);}
function ssError(msg){ssEl('ssSaveFill').style.background='#dc2626';ssEl('ssArcFg').style.stroke='#dc2626';ssEl('ssIconCenter').style.background='#fee2e2';ssEl('ssIconSave').style.display='none';ssEl('ssIconX').style.display='';ssEl('ssSaveTitle').textContent='Save failed';ssEl('ssSaveSub').textContent=msg||'Something went wrong.';setTimeout(ssClose,2400);}
function ssClose(){const o=ssEl('ssSaveOverlay');o.classList.add('ss-closing');setTimeout(()=>o.classList.remove('ss-visible','ss-closing'),240);}

function bulkSave(){
    if(window.isEditingDisabled){showToast('Editing is disabled for this subject.','warning');return;}
    const invalid=document.querySelectorAll('.score-input.is-invalid').length;
    if(invalid){if(typeof Swal!=='undefined')Swal.fire({icon:'warning',title:'Invalid scores',text:`${invalid} score(s) exceed their maximum.`});return;}
    const scores=[];
    document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row=>{
        if(row.dataset.isLocked==='true')return;
        const assessments={};row.querySelectorAll('.score-input').forEach(inp=>{assessments[inp.dataset.field]=parseFloat(inp.value)||0;});
        if(Object.keys(assessments).length)scores.push({id:row.dataset.id,assessments});
    });
    if(!scores.length)return;
    const total=scores.length;ssOpen(total);
    let fp=0;const fiv=setInterval(()=>{fp=Math.min(fp+Math.random()*4+2,88);ssUpdate(Math.round(fp/100*total),total,fp);},130);
    const btn=document.getElementById('bulkUpdateScores'),origHtml=btn?.innerHTML;
    if(btn){btn.disabled=true;btn.innerHTML='<i class="ri-loader-4-line"></i> Saving…';}
    fetch(window.routes.bulkUpdate,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify({scores,term_id:window.term_id,session_id:window.session_id,subjectclass_id:window.subjectclass_id,staff_id:window.staff_id,schoolclass_id:window.schoolclass_id,is_sub:false})})
    .then(r=>r.json()).then(data=>{
        clearInterval(fiv);
        if(!data.success){if(data.locked){ssError('Some scoresheets are locked. Refreshing…');setTimeout(()=>location.reload(),2000);}else ssError(data.message||'Server error.');return;}
        ssUpdate(total,total,100);setTimeout(()=>ssSuccess(total),220);
        (data.data?.broadsheets??[]).forEach(bs=>{
            const row=document.querySelector(`#scoresheetTableBody tr[data-id="${bs.id}"]`);if(!row)return;
            const ts=parseFloat(bs.total??0),tb=row.querySelector('.total-badge');
            if(tb){tb.textContent=fmtN(ts);const tc=ts>=70?'#166534':ts>=50?'#1d4ed8':ts>=40?'#92400e':'#b91c1c';const tbg=ts>=70?'#f0fdf4':ts>=50?'#eff6ff':ts>=40?'#fffbeb':'#fef2f2';tb.style.color=tc;tb.style.background=tbg;}
            applyGrade(row.querySelector('.grade-badge'),bs.grade??'—');
            const bfB=row.querySelector('.bf-badge');if(bfB)bfB.textContent=fmtN(bs.bf);
            const cum=parseFloat(bs.cum??0),cb=row.querySelector('.cum-badge');
            if(cb){cb.textContent=fmtN(cum);const cc=cum>=70?'#166534':cum>=50?'#1d4ed8':cum>=40?'#92400e':'#b91c1c';const cbg=cum>=70?'#f0fdf4':cum>=50?'#eff6ff':cum>=40?'#fffbeb':'#fef2f2';cb.style.color=cc;cb.style.background=cbg;}
            applyGrade(row.querySelector('.cum-grade-badge'),clientGrade(cum));
            const ab=row.querySelector('.avg-badge');if(ab&&bs.avg!=null)ab.textContent=fmtN(bs.avg);
            const gb=row.querySelector('.gpa-badge'),cgpab=row.querySelector('.cgpa-badge');if(gb)gb.textContent=fmtN(bs.gpa,2);if(cgpab)cgpab.textContent=fmtN(bs.cgpa,2);
            row.querySelectorAll('.score-input').forEach(i=>{i.classList.add('is-saved');setTimeout(()=>i.classList.remove('is-saved'),2000);});
        });
        applyAllPositions(data.data?.broadsheets??[]);
    }).catch(err=>{clearInterval(fiv);ssError('Check your connection and try again.');console.error(err);})
    .finally(()=>{if(btn){btn.disabled=false;btn.innerHTML=origHtml||'<i class="ri-save-line"></i> Save all scores';}});
}

function startPdfDownload(url,filename,label){
    const cont=document.getElementById('downloadProgressContainer'),bar=document.getElementById('downloadProgressBar'),lbl=document.getElementById('downloadProgressLabel');
    if(cont)cont.style.display='block';if(bar)bar.style.width='10%';if(lbl)lbl.textContent=label||'Downloading…';
    fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':CSRF}})
    .then(async r=>{if(!r.ok){const e=await r.json().catch(()=>({}));throw new Error(e.message||'Download failed.');}if(bar)bar.style.width='90%';return r.blob();})
    .then(blob=>{if(bar)bar.style.width='100%';const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download=filename;document.body.appendChild(a);a.click();document.body.removeChild(a);URL.revokeObjectURL(a.href);showToast('Downloaded!','success');})
    .catch(err=>{if(typeof Swal!=='undefined')Swal.fire({icon:'error',title:'Download failed',text:err.message});})
    .finally(()=>setTimeout(()=>{if(cont)cont.style.display='none';if(bar)bar.style.width='0%';},1200));
}

/* ── Tooltip ─ */
const tip=document.getElementById('scoreTooltip');let tipInput=null,tipHideTimer=null;
function tipPosition(inp){const r=inp.getBoundingClientRect(),tw=220,margin=8;let left=r.left+r.width/2-tw/2;left=Math.max(margin,Math.min(left,window.innerWidth-tw-margin));tip.style.left=left+'px';tip.classList.remove('tip-above','tip-below');if(r.top>145){tip.style.top=(r.top+window.scrollY-8)+'px';tip.classList.add('tip-above');}else{tip.style.top=(r.bottom+window.scrollY+8)+'px';tip.classList.add('tip-below');}}
function tipRefresh(inp){if(!inp)return;const row=inp.closest('tr'),val=parseFloat(inp.value)||0,max=parseFloat(inp.dataset.max)||100,asmtName=inp.dataset.assessmentName||'Score';let total=0,totalMax=0;row.querySelectorAll('.score-input').forEach(i=>{total+=parseFloat(i.value)||0;totalMax+=parseFloat(i.dataset.max)||0;});const grade=clientGrade(total),pct=totalMax>0?Math.min(total/totalMax*100,100):0,col=GRADE_COLORS[grade]||'#64748b';document.getElementById('stAvatar').src=row.dataset.avatar||'{{ asset("storage/student_avatars/unnamed.jpg") }}';document.getElementById('stName').textContent=row.dataset.name||'—';document.getElementById('stMeta').textContent=(row.dataset.admissionno||'—')+' · '+asmtName+' (max '+max+')';document.getElementById('stVal').textContent=val%1===0?String(val):val.toFixed(1);document.getElementById('stTotal').textContent=fmtN(total);const gEl=document.getElementById('stGrade');gEl.textContent=grade;gEl.style.color=col;document.getElementById('stProgLabel').textContent=fmtN(total)+' / '+totalMax+' marks';document.getElementById('stProgPct').textContent=Math.round(pct)+'%';const fill=document.getElementById('stProgFill');fill.style.width=pct.toFixed(1)+'%';fill.style.background=pct>=70?'#16a34a':pct>=50?'#2563eb':pct>=40?'#d97706':'#dc2626';tipPosition(inp);}
function tipShow(inp){clearTimeout(tipHideTimer);tipInput=inp;tip.style.display='block';tipRefresh(inp);requestAnimationFrame(()=>{tip.style.opacity='1';});}
function tipHide(){tip.style.opacity='0';tipHideTimer=setTimeout(()=>{if(tip.style.opacity==='0')tip.style.display='none';},150);tipInput=null;}

document.addEventListener('DOMContentLoaded',function(){

    document.getElementById('imageViewModal')?.addEventListener('show.bs.modal',function(e){
        const src=e.relatedTarget?.dataset?.image||e.relatedTarget?.getAttribute('data-image');
        document.getElementById('enlargedImage').src=src||'{{ asset("storage/student_avatars/unnamed.jpg") }}';
    });

    document.querySelectorAll('.col-toggle').forEach(cb=>{
        cb.addEventListener('change',function(){
            document.querySelectorAll(`th.${this.dataset.col},td.${this.dataset.col}`).forEach(el=>el.style.display=this.checked?'':'none');
        });
    });

    function applySearch(){
        const q=(document.getElementById('searchInput')?.value??'').trim().toLowerCase();let vis=0;
        document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row=>{
            const adm=(row.querySelector('[data-admissionno]')?.dataset?.admissionno??'').toLowerCase();
            const name=(row.dataset.name??'').toLowerCase();
            const show=!q||adm.includes(q)||name.includes(q);row.style.display=show?'':'none';if(show)vis++;
        });
        const sc=document.getElementById('scoreCount');if(sc)sc.textContent=vis;
    }
    document.getElementById('searchInput')?.addEventListener('input',applySearch);
    document.getElementById('clearSearch')?.addEventListener('click',()=>{const si=document.getElementById('searchInput');if(si)si.value='';applySearch();});

    document.getElementById('checkAll')?.addEventListener('change',function(){if(window.isEditingDisabled)return;document.querySelectorAll('.score-checkbox:not(:disabled)').forEach(cb=>cb.checked=this.checked);});
    document.addEventListener('change',function(e){if(!e.target.classList.contains('score-checkbox'))return;const all=document.querySelectorAll('.score-checkbox:not(:disabled)'),chk=document.querySelectorAll('.score-checkbox:checked'),ca=document.getElementById('checkAll');if(ca){ca.checked=chk.length===all.length&&all.length>0;ca.indeterminate=chk.length>0&&chk.length<all.length;}});
    document.getElementById('selectAllScores')?.addEventListener('click',()=>{if(window.isEditingDisabled)return;const ca=document.getElementById('checkAll');if(ca)ca.checked=true;document.querySelectorAll('.score-checkbox:not(:disabled)').forEach(cb=>cb.checked=true);});
    document.getElementById('clearAllScores')?.addEventListener('click',()=>{const ca=document.getElementById('checkAll');if(ca)ca.checked=false;document.querySelectorAll('.score-checkbox').forEach(cb=>cb.checked=false);});

    document.querySelectorAll('.score-input').forEach(inp=>{
        if(inp.disabled)return;
        inp.addEventListener('focus',function(){this.select();tipShow(this);});
        inp.addEventListener('input',function(){validateInput(this);const row=this.closest('tr');if(row)updateRowGrades(row);if(tipInput===this)tipRefresh(this);});
        inp.addEventListener('blur',function(){setTimeout(()=>{if(tipInput===this)tipHide();},80);if(!validateInput(this))return;if(window.isEditingDisabled)return;const orig=parseFloat(this.dataset.original)||0,curr=parseFloat(this.value)||0;if(Math.abs(curr-orig)>0.001){this.dataset.original=this.value;saveIndividualScore(this);}});
        inp.addEventListener('keydown',function(e){if(e.key==='Escape'){e.preventDefault();tipHide();this.blur();return;}if(e.key!=='Enter')return;e.preventDefault();if(window.isEditingDisabled)return;if(validateInput(this))saveIndividualScore(this);const all=Array.from(document.querySelectorAll('.score-input:not(:disabled)')),idx=all.indexOf(this);if(idx<all.length-1)all[idx+1].focus();});
    });

    document.addEventListener('keydown',e=>{if(e.key==='Escape'&&tipInput){tipHide();document.activeElement?.blur();return;}if((e.ctrlKey||e.metaKey)&&e.key==='s'){e.preventDefault();bulkSave();}});
    document.getElementById('bulkUpdateScores')?.addEventListener('click',bulkSave);

    document.getElementById('deleteSelectedScoresBtn')?.addEventListener('click',function(){
        if(window.isEditingDisabled){showToast('Editing is disabled for this subject.','warning');return;}
        const ids=Array.from(document.querySelectorAll('.score-checkbox:checked')).map(cb=>cb.dataset.id);
        if(!ids.length){if(typeof Swal!=='undefined')Swal.fire({icon:'warning',title:'No selection',text:'Select rows to delete.'});return;}
        if(typeof Swal!=='undefined')Swal.fire({title:'Delete selected scores?',text:'This cannot be undone.',icon:'warning',showCancelButton:true,confirmButtonColor:'#dc2626',confirmButtonText:'Yes, delete'}).then(r=>{
            if(!r.isConfirmed)return;
            Promise.all(ids.map(id=>fetch(window.routes.destroy.replace('__ID__',id),{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF}}).then(r=>r.json())))
            .then(results=>{let deleted=0;results.forEach((res,i)=>{if(res.success){document.querySelector(`tr[data-id="${ids[i]}"]`)?.remove();deleted++;}});showToast(`${deleted} score(s) deleted.`,'success');if(!document.querySelectorAll('#scoresheetTableBody tr[data-id]').length)location.reload();else refreshAllPositions();});
        });
    });

    document.getElementById('updateArmPositionsBtn')?.addEventListener('click',async function(){
        if(!window.schoolclass_id||!window.term_id||!window.session_id){if(typeof Swal!=='undefined')Swal.fire({icon:'warning',title:'Missing data',text:'Please refresh and try again.'});return;}
        const btn=this,origHtml=btn.innerHTML;btn.disabled=true;btn.innerHTML='<i class="ri-loader-4-line" style="animation:spin .8s linear infinite"></i> Recalculating…';
        try{
            const response=await fetch(window.routes.updateArmPositions,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},body:JSON.stringify({schoolclass_id:window.schoolclass_id,term_id:window.term_id,session_id:window.session_id})});
            const data=await response.json();
            if(data.success){await fetch(window.routes.results,{headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':CSRF}}).then(r=>r.json()).then(d=>{if(d.success&&Array.isArray(d.scores))applyAllPositions(d.scores);});if(typeof Swal!=='undefined')Swal.fire({icon:'success',title:'Positions updated',html:data.message,timer:3000,showConfirmButton:true});}
            else{if(typeof Swal!=='undefined')Swal.fire({icon:'error',title:'Update failed',text:data.message});}
        }catch(error){if(typeof Swal!=='undefined')Swal.fire({icon:'error',title:'Error',text:'Network error while updating positions.'});}
        finally{btn.disabled=false;btn.innerHTML=origHtml;}
    });

    document.getElementById('downloadMarksSheet')?.addEventListener('click',()=>startPdfDownload(window.routes.downloadMarksSheet,'marks-sheet.pdf','Generating marks sheet…'));
    document.getElementById('downloadScoresPdf')?.addEventListener('click',()=>startPdfDownload(window.routes.downloadScoresPdf,'scores-sheet.pdf','Generating scores PDF…'));

    document.getElementById('downloadExcel')?.addEventListener('click',()=>{
        const btn=document.getElementById('downloadExcel'),origHtml=btn?.innerHTML;if(btn){btn.disabled=true;btn.innerHTML='<i class="ri-loader-4-line"></i> Generating…';}
        fetch(window.routes.export,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'}})
        .then(r=>{const cd=r.headers.get('content-disposition')||'',m=cd.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/),fn=m?m[1].replace(/['"]/g,''):'scoresheet.xlsx';return r.blob().then(b=>({blob:b,filename:fn}));})
        .then(({blob,filename})=>{const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download=filename;document.body.appendChild(a);a.click();document.body.removeChild(a);showToast('Excel downloaded.','success');})
        .catch(err=>{if(typeof Swal!=='undefined')Swal.fire({icon:'error',title:'Download failed',text:err.message});})
        .finally(()=>{if(btn){btn.disabled=false;btn.innerHTML=origHtml;}});
    });

    document.getElementById('importForm')?.addEventListener('submit',function(e){
        e.preventDefault();const file=this.querySelector('input[name="file"]');
        if(!file?.files?.length){if(typeof Swal!=='undefined')Swal.fire({icon:'warning',title:'No file',text:'Please select an Excel file.'});return;}
        const btn=document.getElementById('importSubmit'),loader=document.getElementById('importLoader'),bar=document.getElementById('uploadProgressBar'),origHtml=btn?.innerHTML;
        if(btn){btn.disabled=true;btn.innerHTML='<i class="ri-loader-4-line"></i> Uploading…';}if(loader)loader.style.display='block';if(bar)bar.style.width='10%';
        fetch(window.routes.import,{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'},body:new FormData(this)})
        .then(r=>r.json()).then(data=>{if(bar)bar.style.width='100%';
            if(data.success||data.warning){if(typeof Swal!=='undefined')Swal.fire({icon:data.warning?'warning':'success',title:data.warning?'Partial success':'Imported!',text:data.message,timer:2500,showConfirmButton:false});bootstrap.Modal.getInstance(document.getElementById('importModal'))?.hide();setTimeout(()=>location.reload(),2600);}
            else{if(typeof Swal!=='undefined')Swal.fire({icon:'error',title:'Import failed',text:data.message||'Unknown error.'});}
        }).catch(err=>{if(typeof Swal!=='undefined')Swal.fire({icon:'error',title:'Upload error',text:err.message});})
        .finally(()=>{setTimeout(()=>{if(loader)loader.style.display='none';if(bar)bar.style.width='0%';},1000);if(btn){btn.disabled=false;btn.innerHTML=origHtml||'Upload';}if(file)file.value='';});
    });

    // Staggered row entrance
    (function initRowEntrance(){
        const rows=Array.from(document.querySelectorAll('#scoresheetTableBody tr[data-id]'));if(!rows.length)return;
        if(window.matchMedia('(prefers-reduced-motion:reduce)').matches){rows.forEach(r=>r.classList.add('row-visible'));return;}
        const observer=new IntersectionObserver((entries)=>{entries.forEach(entry=>{if(!entry.isIntersecting)return;const row=entry.target,index=rows.indexOf(row);setTimeout(()=>row.classList.add('row-visible'),Math.min(index*35,15*35)+50);observer.unobserve(row);});},{threshold:0.04,rootMargin:'0px 0px -10px 0px'});
        rows.forEach(row=>observer.observe(row));
    })();

});

if(typeof Swal==='undefined'){const s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/sweetalert2@11';document.head.appendChild(s);}
</script>
@endsection
