@extends('layouts.master')
@section('content')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--s:#0f172a;--sf:#1e293b;--sc:#263348;--b:rgba(148,163,184,.15);--t:#e2e8f0;--m:#94a3b8;--ac:#38bdf8;--r:12px;}
.hol{background:var(--s);min-height:100vh;padding:28px;color:var(--t);font-family:'DM Sans',sans-serif;}
.hol h1{font-size:1.55rem;font-weight:800;color:#fff;margin-bottom:6px;}
.hol h1 span{color:var(--ac);}
.back-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;background:rgba(148,163,184,.1);border:1px solid var(--b);color:var(--t);text-decoration:none;font-size:13px;font-weight:600;transition:.18s;}
.back-btn:hover{border-color:var(--ac);color:var(--ac);}
.panel{background:var(--sf);border:1px solid var(--b);border-radius:var(--r);padding:24px;margin-bottom:24px;}
.panel h2{font-size:1rem;font-weight:700;color:#fff;margin-bottom:18px;display:flex;align-items:center;gap:8px;}
.panel h2 span{background:rgba(56,189,248,.12);color:var(--ac);border-radius:6px;padding:2px 10px;font-size:13px;}
.form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;}
.fg{display:flex;flex-direction:column;gap:5px;}
.fg label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--m);}
.fg select,.fg input{background:var(--sc);border:1px solid var(--b);color:var(--t);border-radius:8px;padding:9px 12px;font-size:14px;outline:none;}
.fg select:focus,.fg input:focus{border-color:var(--ac);}
.btn-save{background:var(--ac);color:#0f172a;border:none;border-radius:8px;padding:10px 22px;font-weight:700;font-size:13px;cursor:pointer;transition:.18s;}
.btn-save:hover{background:#7dd3fc;}
.btn-del{background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.25);border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;cursor:pointer;transition:.18s;}
.btn-del:hover{background:rgba(239,68,68,.22);}
.list-table{width:100%;border-collapse:collapse;}
.list-table th{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--m);padding:10px 14px;text-align:left;border-bottom:1px solid var(--b);background:rgba(56,189,248,.04);}
.list-table td{padding:11px 14px;font-size:13px;border-bottom:1px solid var(--b);vertical-align:middle;}
.list-table tr:last-child td{border-bottom:none;}
.list-table tr:hover td{background:rgba(255,255,255,.02);}
.type-badge{padding:3px 10px;border-radius:99px;font-size:11px;font-weight:700;}
.type-public{background:rgba(56,189,248,.12);color:var(--ac);}
.type-midterm{background:rgba(167,139,250,.12);color:#a78bfa;}
.type-school_event{background:rgba(34,197,94,.12);color:#22c55e;}
.type-other{background:rgba(148,163,184,.12);color:var(--m);}
</style>

<div class="hol">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
        <div>
            <h1>Holidays <span>& Breaks</span></h1>
            <p style="color:var(--m);font-size:14px;margin:2px 0 0;">Manage public holidays, mid-term breaks and school events.</p>
        </div>
        <a href="{{ route('attendance.settings') }}" class="back-btn">← Back to Settings</a>
    </div>

    {{-- Add form --}}
    <div class="panel">
        <h2>Add Holiday / Break <span>Admin</span></h2>
        <form id="holidayForm">
            @csrf
            <div class="form-grid">
                <div class="fg"><label>Term</label>
                    <select name="term_id" required>
                        <option value="">Select Term</option>
                        @foreach($terms as $t)<option value="{{ $t->id }}">{{ $t->term }}</option>@endforeach
                    </select>
                </div>
                <div class="fg"><label>Session</label>
                    <select name="session_id" required>
                        <option value="">Select Session</option>
                        @foreach($sessions as $s)<option value="{{ $s->id }}">{{ $s->session }}</option>@endforeach
                    </select>
                </div>
                <div class="fg"><label>Start Date</label><input type="date" name="holiday_date" required></div>
                <div class="fg"><label>End Date (optional)</label><input type="date" name="holiday_end_date"></div>
                <div class="fg"><label>Name</label><input type="text" name="holiday_name" placeholder="e.g. Mid-Term Break" required></div>
                <div class="fg"><label>Type</label>
                    <select name="holiday_type" required>
                        <option value="public">Public Holiday</option>
                        <option value="midterm">Mid-Term Break</option>
                        <option value="school_event">School Event</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="fg" style="margin-top:14px;max-width:500px;"><label>Notes (optional)</label>
                <input type="text" name="notes" placeholder="Any additional notes…">
            </div>
            <div style="margin-top:18px;"><button type="submit" class="btn-save">💾 Save Holiday</button></div>
        </form>
    </div>

    {{-- List --}}
    <div class="panel">
        <h2>Existing Holidays</h2>
        <div style="overflow-x:auto;">
            <table class="list-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Term</th>
                        <th>Session</th>
                        <th>Notes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($holidays as $h)
                <tr>
                    <td><strong>{{ $h->holiday_name }}</strong></td>
                    <td><span class="type-badge type-{{ $h->holiday_type }}">{{ ucfirst(str_replace('_',' ',$h->holiday_type)) }}</span></td>
                    <td>{{ $h->holiday_date->format('d M Y') }}</td>
                    <td>{{ $h->holiday_end_date ? $h->holiday_end_date->format('d M Y') : '—' }}</td>
                    <td>{{ $h->term?->term }}</td>
                    <td>{{ $h->session?->session }}</td>
                    <td style="color:var(--m);font-size:12px;">{{ $h->notes ?? '—' }}</td>
                    <td><button class="btn-del" onclick="deleteHoliday({{ $h->id }})">Delete</button></td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;color:var(--m);padding:28px;">No holidays added yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="att-toast" style="position:fixed;bottom:24px;right:24px;z-index:999;display:flex;flex-direction:column;gap:8px;"></div>

<script>
function csrfToken(){ return document.querySelector('meta[name="csrf-token"]')?.content||''; }
function toast(msg, type='success'){
    const c=document.getElementById('att-toast');
    const t=document.createElement('div');
    t.style.cssText=`padding:12px 18px;border-radius:10px;font-size:13px;font-weight:600;color:#fff;background:${type==='success'?'rgba(34,197,94,.9)':'rgba(239,68,68,.9)'};animation:slideUp .3s ease;`;
    t.textContent=msg; c.appendChild(t); setTimeout(()=>t.remove(),3500);
}
document.getElementById('holidayForm').addEventListener('submit', async function(e){
    e.preventDefault();
    const r = await fetch('{{ route('attendance.holidays.store') }}', { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken()}, body: new FormData(this) });
    const d = await r.json();
    toast(d.message, d.success?'success':'error');
    if(d.success) setTimeout(()=>location.reload(),1000);
});
async function deleteHoliday(id){
    if(!confirm('Delete this holiday?')) return;
    const r = await fetch(`/attendance/holidays/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken()} });
    const d = await r.json(); toast(d.message, d.success?'success':'error'); if(d.success) setTimeout(()=>location.reload(),800);
}
</script>
<style>@keyframes slideUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}</style>
@endsection
