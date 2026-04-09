{{-- resources/views/timetable/index.blade.php --}}
@extends('layouts.master')


<style>
.tt-cell { cursor:pointer; transition:background 0.15s; vertical-align:middle; }
.tt-cell:hover { background:rgba(102,126,234,0.07) !important; }
.tt-cell-inner { display:flex; flex-direction:column; align-items:center; gap:3px; padding:6px 4px; min-height:68px; justify-content:center; }
.tt-subj { font-weight:600; font-size:12px; color:#1a1a2e; text-align:center; line-height:1.2; }
.tt-tchr { font-size:11px; color:#666; text-align:center; }
.tt-room { font-size:10px; color:#aaa; }
.tt-avatar { width:30px; height:30px; border-radius:50%; object-fit:cover; border:2px solid #667eea; margin-bottom:2px; }
.tt-free { color:#ddd; }
.tt-head th { background:#1a1a2e; color:#fff; font-size:12px; font-weight:500; padding:10px 8px; white-space:nowrap; }
.tt-period-col { background:#f8f9fa; font-weight:600; font-size:11px; min-width:88px; vertical-align:middle; }
.tt-break-row td { background:#fffbeb !important; }
.conflict-card { display:flex; align-items:center; gap:12px; padding:12px; border:1px solid #fdd8cc; border-radius:8px; background:#fff9f7; margin-bottom:8px; }
.nav-tabs-custom .nav-link { color:#555; font-size:13px; }
.nav-tabs-custom .nav-link.active { color:#667eea; border-bottom:2px solid #667eea; font-weight:500; }
</style>


@section('content')
<div class="main-content">
 <div class="page-content">
  <div class="container-fluid">

   <div class="row">
    <div class="col-12">
     <div class="page-title-box d-sm-flex align-items-center justify-content-between">
      <h4 class="mb-sm-0"><i class="ri-calendar-todo-line me-2"></i>Timetable Management</h4>
      <ol class="breadcrumb m-0">
       <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
       <li class="breadcrumb-item active">Timetable</li>
      </ol>
     </div>
    </div>
   </div>

   @if($errors->any())
   <div class="alert alert-danger alert-dismissible fade show">
    <strong>Error!</strong> {{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
   @endif

   {{-- Selection --}}
   <div class="card">
    <div class="card-body py-3">
     <div class="row g-3 align-items-end">
      <div class="col-xxl-3 col-sm-6">
       <label class="form-label fw-semibold">Class</label>
       <select class="form-select" id="classSelect">
        <option value="">— Select Class —</option>
        @foreach ($schoolclasses as $class)
        <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
        @endforeach
       </select>
      </div>
      <div class="col-xxl-3 col-sm-6">
       <label class="form-label fw-semibold">Session</label>
       <select class="form-select" id="sessionSelect">
        <option value="">— Select Session —</option>
        @foreach ($schoolsessions as $s)
        <option value="{{ $s->id }}">{{ $s->session }}</option>
        @endforeach
       </select>
      </div>
      <div class="col-xxl-3 col-sm-6">
       <label class="form-label fw-semibold">Term <small class="text-muted fw-normal">(optional)</small></label>
       <select class="form-select" id="termSelect">
        <option value="">All Terms</option>
        @foreach ($schoolterms as $t)
        <option value="{{ $t->id }}">{{ $t->term }}</option>
        @endforeach
       </select>
      </div>
      <div class="col-xxl-3 col-sm-6">
       <button class="btn btn-primary w-100" onclick="loadOrCreateSetting()">
        <i class="ri-settings-4-line me-2"></i>Load / Create
       </button>
      </div>
     </div>
    </div>
   </div>

   {{-- Existing timetables --}}
   @if($settings->count())
   <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
     <h5 class="card-title mb-0"><i class="ri-history-line me-2"></i>Existing Timetables</h5>
     <span class="badge bg-primary-subtle text-primary">{{ $settings->count() }} active</span>
    </div>
    <div class="card-body p-0">
     <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
       <thead class="table-light">
        <tr><th>Class</th><th>Session</th><th>Term</th><th>Updated</th><th>Status</th><th style="width:200px">Actions</th></tr>
       </thead>
       <tbody>
        @foreach($settings as $setting)
        <tr>
         <td class="fw-semibold"><i class="ri-school-line text-primary me-2"></i>{{ $setting->schoolclass->schoolclass ?? 'N/A' }}</td>
         <td>{{ $setting->session->session ?? 'N/A' }}</td>
         <td>{{ $setting->term->term ?? 'All Terms' }}</td>
         <td class="text-muted small">{{ $setting->updated_at->format('d M Y, H:i') }}</td>
         <td><span class="badge bg-success-subtle text-success">Active</span></td>
         <td>
          <button class="btn btn-sm btn-outline-primary me-1" onclick="loadSetting({{ $setting->id }})" title="Edit"><i class="ri-edit-line"></i></button>
          <button class="btn btn-sm btn-outline-info me-1" onclick="cloneSetting({{ $setting->id }})" title="Clone"><i class="ri-file-copy-line"></i></button>
          <button class="btn btn-sm btn-outline-secondary me-1" onclick="exportTimetable({{ $setting->id }},'pdf')" title="PDF"><i class="ri-file-pdf-line"></i></button>
          <button class="btn btn-sm btn-outline-success me-1" onclick="exportTimetable({{ $setting->id }},'csv')" title="CSV"><i class="ri-file-excel-line"></i></button>
          <button class="btn btn-sm btn-outline-danger" onclick="deleteSetting({{ $setting->id }})" title="Delete"><i class="ri-delete-bin-line"></i></button>
         </td>
        </tr>
        @endforeach
       </tbody>
      </table>
     </div>
    </div>
   </div>
   @endif

   {{-- Editor --}}
   <div id="timetableEditor" style="display:none;">
    <div class="card">
     <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
       <div id="editorTitle" class="fw-semibold text-primary fs-6"></div>
       <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('timetableEditor').style.display='none'">
        <i class="ri-close-line me-1"></i>Close
       </button>
      </div>

      <ul class="nav nav-tabs nav-tabs-custom mb-4" role="tablist">
       <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#periodsTab"><i class="ri-time-line me-1"></i>Periods</a></li>
       <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#constraintsTab"><i class="ri-bar-chart-2-line me-1"></i>Constraints</a></li>
       <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#timetableGridTab"><i class="ri-table-line me-1"></i>Grid</a></li>
       <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#conflictsTab"><i class="ri-alert-line me-1"></i>Conflicts</a></li>
      </ul>

      <div class="tab-content">

       {{-- PERIODS --}}
       <div class="tab-pane active" id="periodsTab">
        <div class="row g-4">
         <div class="col-md-6">
          <div class="card border">
           <div class="card-header bg-light"><h6 class="mb-0">School Day Settings</h6></div>
           <div class="card-body">
            <div class="row g-3">
             <div class="col-6"><label class="form-label">Start</label><input type="time" class="form-control" id="schoolDayStart"></div>
             <div class="col-6"><label class="form-label">End</label><input type="time" class="form-control" id="schoolDayEnd"></div>
             <div class="col-4"><label class="form-label">Period (min)</label><input type="number" class="form-control" id="periodDuration" min="20" max="90"></div>
             <div class="col-4"><label class="form-label">Short break</label><input type="number" class="form-control" id="shortBreakDuration" min="5" max="60"></div>
             <div class="col-4"><label class="form-label">Long break</label><input type="number" class="form-control" id="longBreakDuration" min="10" max="90"></div>
             <div class="col-12">
              <label class="form-label">Active Days</label>
              <div class="d-flex flex-wrap gap-3">
               @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day)
               <div class="form-check">
                <input class="form-check-input active-day-checkbox" type="checkbox" value="{{ $day }}" id="day_{{ $day }}">
                <label class="form-check-label" for="day_{{ $day }}">{{ $day }}</label>
               </div>
               @endforeach
              </div>
             </div>
            </div>
           </div>
          </div>
         </div>
         <div class="col-md-6">
          <div class="card border">
           <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Periods</h6>
            <button class="btn btn-sm btn-primary" onclick="addPeriodRow()"><i class="ri-add-line me-1"></i>Add</button>
           </div>
           <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
             <thead class="table-light"><tr><th style="width:32px">#</th><th>Name</th><th>Type</th><th style="width:44px"></th></tr></thead>
             <tbody id="periodsBody"></tbody>
            </table>
           </div>
           <div class="card-footer"><button class="btn btn-success btn-sm" onclick="saveSettings()"><i class="ri-save-line me-1"></i>Save Settings</button></div>
          </div>
         </div>
        </div>
       </div>

       {{-- CONSTRAINTS --}}
       <div class="tab-pane" id="constraintsTab">
        <div class="d-flex justify-content-between align-items-center mb-3">
         <h6 class="mb-0">Subject Constraints</h6>
         <div class="d-flex gap-2">
          <button class="btn btn-sm btn-success" onclick="saveConstraints()"><i class="ri-save-line me-1"></i>Save</button>
          <button class="btn btn-sm btn-primary" onclick="generateTimetable()"><i class="ri-magic-line me-1"></i>Auto-Generate</button>
         </div>
        </div>
        <div class="table-responsive">
         <table class="table table-bordered table-sm align-middle">
          <thead class="table-light">
           <tr>
            <th>Subject</th><th>Teacher</th><th style="width:80px">Periods/wk</th>
            <th style="width:70px">Double</th><th style="width:80px">Max Dbl</th>
            <th>Prefer Days</th><th>Avoid Days</th><th style="width:80px">Required</th>
           </tr>
          </thead>
          <tbody id="constraintsBody"></tbody>
         </table>
        </div>
       </div>

       {{-- GRID --}}
       <div class="tab-pane" id="timetableGridTab">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
         <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-sm btn-outline-primary" onclick="loadTimetableGrid()"><i class="ri-refresh-line me-1"></i>Reload</button>
          <button class="btn btn-sm btn-outline-secondary" onclick="exportTimetable(currentSettingId,'pdf')"><i class="ri-file-pdf-line me-1"></i>PDF</button>
          <button class="btn btn-sm btn-outline-success" onclick="exportTimetable(currentSettingId,'csv')"><i class="ri-file-excel-line me-1"></i>CSV</button>
          <button class="btn btn-sm btn-outline-warning" onclick="sendNotifications()"><i class="ri-mail-send-line me-1"></i>Notify</button>
         </div>
         <small class="text-muted"><i class="ri-information-line me-1"></i>Click any cell to edit</small>
        </div>
        <div id="timetableGridContainer">
         <div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Loading…</p></div>
        </div>
       </div>

       {{-- CONFLICTS --}}
       <div class="tab-pane" id="conflictsTab">
        <div class="d-flex justify-content-between align-items-center mb-3">
         <button class="btn btn-primary btn-sm" onclick="checkConflicts()"><i class="ri-refresh-line me-1"></i>Check Conflicts</button>
         <span id="conflictBadge"></span>
        </div>
        <div id="conflictsList">
         <div class="text-center text-muted py-5">
          <i class="ri-shield-check-line ri-3x d-block mb-2 text-success"></i>
          Click "Check Conflicts" to verify the schedule.
         </div>
        </div>
       </div>

      </div>{{-- /tab-content --}}
     </div>
    </div>
   </div>{{-- /timetableEditor --}}

  </div>
 </div>
</div>

{{-- Edit Slot Modal --}}
<div class="modal fade" id="editSlotModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered">
  <div class="modal-content">
   <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2);">
    <h5 class="modal-title text-white"><i class="ri-edit-box-line me-2"></i>Edit Slot</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
   </div>
   <div class="modal-body">
    <input type="hidden" id="editSlotSettingId">
    <input type="hidden" id="editSlotPeriodId">
    <input type="hidden" id="editSlotDay">
    <div class="row g-3 mb-3">
     <div class="col-6"><label class="form-label">Period</label><input type="text" class="form-control" id="editSlotPeriodName" readonly></div>
     <div class="col-6"><label class="form-label">Day</label><input type="text" class="form-control" id="editSlotDayName" readonly></div>
    </div>
    <div class="mb-3"><label class="form-label">Subject</label><select class="form-select" id="editSlotSubject"><option value="">— Free Period —</option></select></div>
    <div class="mb-3"><label class="form-label">Teacher</label><select class="form-select" id="editSlotTeacher"><option value="">— No Teacher —</option></select></div>
    <div class="row g-3">
     <div class="col-7"><label class="form-label">Room</label><input type="text" class="form-control" id="editSlotRoom" placeholder="e.g. Room 101"></div>
     <div class="col-5 d-flex align-items-end pb-1">
      <div class="form-check"><input class="form-check-input" type="checkbox" id="editSlotIsDouble"><label class="form-check-label" for="editSlotIsDouble">Double Period</label></div>
     </div>
    </div>
    <div class="mt-3"><label class="form-label">Notes</label><textarea class="form-control" id="editSlotNotes" rows="2" placeholder="Optional…"></textarea></div>
   </div>
   <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary" onclick="saveSlot()"><i class="ri-save-line me-1"></i>Save</button>
   </div>
  </div>
 </div>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
let currentSettingId=null,currentSetting=null,currentPeriods=[],currentGrid={},currentDays=[],availableSubjects=[],allTeachers=[];

const ROUTES={
 setup:'{{ route("timetable.setup") }}',saveSettings:'{{ route("timetable.save-settings") }}',
 saveConstraints:'{{ route("timetable.save-constraints") }}',autoGenerate:'{{ route("timetable.auto-generate") }}',
 saveSlot:'{{ route("timetable.save-slot") }}',sendNotifications:'{{ route("timetable.send-notifications") }}',
 cloneSetting:'{{ route("timetable.clone-setting") }}',getSetting:'{{ url("/timetable/get-setting") }}',
 getGrid:'{{ url("/timetable/get-grid") }}',checkConflicts:'{{ url("/timetable/check-conflicts") }}',
 export:'{{ url("/timetable/export") }}',deleteSetting:'{{ url("/timetable/delete-setting") }}',
};
const CSRF='{{ csrf_token() }}';
const esc=str=>!str?'':String(str).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
const buildUrl=(base,id)=>base.replace(/\/$/,'')+'/'+id;
const post=(url,body)=>fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},body:JSON.stringify(body)});
const get=(url)=>fetch(url,{headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}});
const showLoading=()=>Swal.fire({title:'Please wait…',allowOutsideClick:false,allowEscapeKey:false,didOpen:()=>Swal.showLoading()});
const hideLoading=()=>Swal.close();

async function loadOrCreateSetting(){
 const classId=document.getElementById('classSelect').value;
 const sessionId=document.getElementById('sessionSelect').value;
 const termId=document.getElementById('termSelect').value;
 if(!classId||!sessionId)return Swal.fire({title:'Required',text:'Select a class and session.',icon:'warning',confirmButtonColor:'#667eea'});
 showLoading();
 try{
  const r=await post(ROUTES.setup,{schoolclass_id:classId,session_id:sessionId,term_id:termId||null});
  const d=await r.json();
  if(d.success){currentSettingId=d.setting_id;await loadSetting(currentSettingId);}
  else throw new Error(d.message);
 }catch(e){Swal.fire('Error',e.message,'error');}finally{hideLoading();}
}

async function loadSetting(settingId){
 showLoading();
 try{
  const r=await get(buildUrl(ROUTES.getSetting,settingId));
  if(!r.ok)throw new Error(`HTTP ${r.status}`);
  const d=await r.json();
  if(d.success){
   currentSetting=d.setting;currentSettingId=settingId;availableSubjects=d.available_subjects||[];
   const cls=d.setting.schoolclass?.schoolclass||'',ses=d.setting.session?.session||'',trm=d.setting.term?.term?'/'+d.setting.term.term:'';
   document.getElementById('editorTitle').textContent=`${cls} — ${ses}${trm}`;
   document.getElementById('schoolDayStart').value=(currentSetting.school_day_start||'08:00').slice(0,5);
   document.getElementById('schoolDayEnd').value=(currentSetting.school_day_end||'14:30').slice(0,5);
   document.getElementById('periodDuration').value=currentSetting.period_duration_minutes||40;
   document.getElementById('shortBreakDuration').value=currentSetting.short_break_duration_minutes||20;
   document.getElementById('longBreakDuration').value=currentSetting.long_break_duration_minutes||40;
   const days=currentSetting.active_days||['Monday','Tuesday','Wednesday','Thursday','Friday'];
   document.querySelectorAll('.active-day-checkbox').forEach(cb=>{cb.checked=days.includes(cb.value);});
   loadPeriodsIntoTable(currentSetting.periods?.length?currentSetting.periods:[
    {name:'Period 1',type:'lesson'},{name:'Period 2',type:'lesson'},{name:'Short Break',type:'short_break'},
    {name:'Period 3',type:'lesson'},{name:'Period 4',type:'lesson'},{name:'Long Break',type:'long_break'},
    {name:'Period 5',type:'lesson'},{name:'Period 6',type:'lesson'},
   ]);
   loadConstraintsIntoTable(currentSetting.constraints||[]);
   await loadTimetableGrid();
   document.getElementById('timetableEditor').style.display='block';
   document.getElementById('timetableEditor').scrollIntoView({behavior:'smooth'});
  }else throw new Error(d.message);
 }catch(e){Swal.fire('Error','Failed: '+e.message,'error');}finally{hideLoading();}
}

function loadPeriodsIntoTable(periods){document.getElementById('periodsBody').innerHTML='';periods.forEach((p,i)=>addPeriodRow(p.name,p.type,i+1));}

function addPeriodRow(name='',type='lesson',order=null){
 const tbody=document.getElementById('periodsBody');
 const n=order||(tbody.querySelectorAll('tr').length+1);
 const tr=document.createElement('tr');
 tr.innerHTML=`<td class="period-order text-center">${n}</td>
  <td><input type="text" class="form-control form-control-sm period-name" placeholder="Period name" value="${esc(name)}"></td>
  <td><select class="form-select form-select-sm period-type">
   <option value="lesson" ${type==='lesson'?'selected':''}>Lesson</option>
   <option value="short_break" ${type==='short_break'?'selected':''}>Short Break</option>
   <option value="long_break" ${type==='long_break'?'selected':''}>Long Break</option>
   <option value="assembly" ${type==='assembly'?'selected':''}>Assembly</option>
   <option value="free" ${type==='free'?'selected':''}>Free</option>
  </select></td>
  <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger px-2" onclick="removePeriodRow(this)"><i class="ri-delete-bin-line"></i></button></td>`;
 tbody.appendChild(tr);
}

function removePeriodRow(btn){btn.closest('tr').remove();reorderPeriods();}
function reorderPeriods(){document.querySelectorAll('#periodsBody tr').forEach((r,i)=>{const c=r.querySelector('.period-order');if(c)c.textContent=i+1;});}
function getPeriodsFromTable(){return[...document.querySelectorAll('#periodsBody tr')].map(r=>({name:r.querySelector('.period-name')?.value||'',type:r.querySelector('.period-type')?.value||'lesson'})).filter(p=>p.name);}

async function saveSettings(){
 const periods=getPeriodsFromTable();
 const activeDays=[...document.querySelectorAll('.active-day-checkbox:checked')].map(cb=>cb.value);
 if(!periods.length)return Swal.fire('Error','Add at least one period.','error');
 if(!activeDays.length)return Swal.fire('Error','Select at least one day.','error');
 showLoading();
 try{
  const r=await post(ROUTES.saveSettings,{setting_id:currentSettingId,school_day_start:document.getElementById('schoolDayStart').value,school_day_end:document.getElementById('schoolDayEnd').value,period_duration_minutes:parseInt(document.getElementById('periodDuration').value),short_break_duration_minutes:parseInt(document.getElementById('shortBreakDuration').value),long_break_duration_minutes:parseInt(document.getElementById('longBreakDuration').value),active_days:activeDays,periods});
  const result=await r.json();
  if(result.success){Swal.fire({title:'Saved',icon:'success',timer:1500,showConfirmButton:false});await loadSetting(currentSettingId);}
  else throw new Error(result.message);
 }catch(e){Swal.fire('Error',e.message,'error');}finally{hideLoading();}
}

function loadConstraintsIntoTable(constraints){
 const tbody=document.getElementById('constraintsBody');tbody.innerHTML='';
 if(!availableSubjects.length){tbody.innerHTML='<tr><td colspan="8" class="text-center text-muted py-4">No subjects assigned to this class.</td></tr>';return;}
 const cMap=new Map(constraints.map(c=>[c.subject_id,c]));
 availableSubjects.forEach(subj=>{
  const c=cMap.get(subj.subject_id);
  const tr=document.createElement('tr');
  tr.innerHTML=`<td>${esc(subj.subject_name)}<input type="hidden" class="constraint-subject-id" value="${subj.subject_id}"></td>
   <td class="text-muted small">${esc(subj.teacher_name)}</td>
   <td><input type="number" class="form-control form-control-sm periods-per-week" value="${c?.periods_per_week||2}" min="1" max="10"></td>
   <td class="text-center"><input type="checkbox" class="form-check-input allow-double" ${c?.allow_double_period?'checked':''}></td>
   <td><input type="number" class="form-control form-control-sm max-double" value="${c?.max_double_periods_per_week||1}" min="0" max="5" ${!c?.allow_double_period?'disabled':''}></td>
   <td><select class="form-select form-select-sm preferred-days" multiple size="3">${genDays(c?.preferred_days||[])}</select></td>
   <td><select class="form-select form-select-sm avoid-days" multiple size="3">${genDays(c?.avoid_days||[])}</select></td>
   <td class="text-center"><input type="checkbox" class="form-check-input is-compulsory" ${c?.is_compulsory!==false?'checked':''}></td>`;
  tr.querySelector('.allow-double').addEventListener('change',function(){tr.querySelector('.max-double').disabled=!this.checked;});
  tbody.appendChild(tr);
 });
}

function genDays(sel=[]){return['Monday','Tuesday','Wednesday','Thursday','Friday'].map(d=>`<option value="${d}" ${sel.includes(d)?'selected':''}>${d}</option>`).join('');}

function getConstraintsFromTable(){
 return[...document.querySelectorAll('#constraintsBody tr')].map(row=>{
  const sid=row.querySelector('.constraint-subject-id')?.value;if(!sid)return null;
  return{subject_id:parseInt(sid),periods_per_week:parseInt(row.querySelector('.periods-per-week').value),allow_double:row.querySelector('.allow-double').checked,max_double:parseInt(row.querySelector('.max-double').value),preferred_days:[...row.querySelector('.preferred-days').selectedOptions].map(o=>o.value),avoid_days:[...row.querySelector('.avoid-days').selectedOptions].map(o=>o.value),is_compulsory:row.querySelector('.is-compulsory').checked};
 }).filter(Boolean);
}

async function saveConstraints(){
 const constraints=getConstraintsFromTable();
 if(!constraints.length)return Swal.fire('Error','No constraints to save.','error');
 showLoading();
 try{
  const r=await post(ROUTES.saveConstraints,{setting_id:currentSettingId,constraints});
  const result=await r.json();
  if(result.success)Swal.fire({title:'Saved',icon:'success',timer:1500,showConfirmButton:false});
  else throw new Error(result.message);
 }catch(e){Swal.fire('Error',e.message,'error');}finally{hideLoading();}
}

async function loadTimetableGrid(){
 if(!currentSettingId)return;
 const c=document.getElementById('timetableGridContainer');
 c.innerHTML='<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Loading…</p></div>';
 try{
  const r=await get(buildUrl(ROUTES.getGrid,currentSettingId));
  if(!r.ok)throw new Error(`HTTP ${r.status}`);
  const d=await r.json();
  if(d.success){currentPeriods=d.periods;currentGrid=d.grid;currentDays=d.days;allTeachers=d.teachers||[];renderTimetableGrid();}
  else throw new Error(d.message);
 }catch(e){c.innerHTML=`<div class="alert alert-danger">Failed: ${esc(e.message)}</div>`;}
}

function renderTimetableGrid(){
 const c=document.getElementById('timetableGridContainer');
 if(!currentPeriods.length||!currentDays.length){c.innerHTML='<div class="alert alert-warning">Save settings first to configure periods.</div>';return;}
 let html=`<div class="table-responsive"><table class="table table-bordered table-sm mb-0" style="table-layout:fixed;">
  <thead><tr class="tt-head"><th style="width:96px;">Period/Time</th>${currentDays.map(d=>`<th class="text-center">${esc(d)}</th>`).join('')}</tr></thead><tbody>`;
 currentPeriods.forEach(period=>{
  const isBreak=period.is_break;
  html+=`<tr ${isBreak?'class="tt-break-row"':''} data-period-id="${period.id}">
   <td class="tt-period-col"><div style="font-size:11px;font-weight:600;">${esc(period.name)}</div><div style="font-size:10px;color:#888;">${period.start_time}–${period.end_time}</div>${isBreak?'<span class="badge bg-warning-subtle text-warning" style="font-size:9px;">break</span>':''}</td>`;
  currentDays.forEach(day=>{
   const slot=currentGrid[period.id]?.[day]||null;
   const isFree=!slot?.subject_id;
   if(isBreak){html+=`<td class="text-center"><small class="text-muted">—</small></td>`;return;}
   html+=`<td class="tt-cell ${isFree?'tt-free':''}" data-period-id="${period.id}" data-day="${esc(day)}" onclick="openEditSlotModal(${period.id},'${esc(day)}')" title="${isFree?'Click to assign':'Click to edit'}">
    <div class="tt-cell-inner">`;
   if(slot&&!isFree){
    if(slot.teacher_picture)html+=`<img src="${slot.teacher_picture}" class="tt-avatar" alt="">`;
    html+=`<span class="tt-subj">${esc(slot.subject_code||slot.subject||'—')}</span>
           <span class="tt-tchr">${esc((slot.teacher||'').split(' ')[0])}</span>`;
    if(slot.room)html+=`<span class="tt-room"><i class="ri-door-line"></i> ${esc(slot.room)}</span>`;
    if(slot.is_double)html+=`<span class="badge bg-primary-subtle text-primary" style="font-size:9px;">×2</span>`;
   }else{html+=`<span style="color:#ddd;font-size:20px;line-height:1;">+</span>`;}
   html+=`</div></td>`;
  });
  html+=`</tr>`;
 });
 html+=`</tbody></table></div>`;
 c.innerHTML=html;
}

function openEditSlotModal(periodId,day){
 const period=currentPeriods.find(p=>p.id==periodId);if(!period)return;
 const slot=currentGrid[periodId]?.[day]||{};
 document.getElementById('editSlotSettingId').value=currentSettingId;
 document.getElementById('editSlotPeriodId').value=periodId;
 document.getElementById('editSlotDay').value=day;
 document.getElementById('editSlotPeriodName').value=period.name;
 document.getElementById('editSlotDayName').value=day;
 document.getElementById('editSlotRoom').value=slot.room||'';
 document.getElementById('editSlotNotes').value=slot.notes||'';
 document.getElementById('editSlotIsDouble').checked=slot.is_double||false;
 const ss=document.getElementById('editSlotSubject');
 ss.innerHTML='<option value="">— Free Period —</option>';
 availableSubjects.forEach(subj=>{const opt=document.createElement('option');opt.value=subj.subject_id;opt.dataset.teacherId=subj.teacher_id;opt.textContent=`${subj.subject_name} (${subj.teacher_name})`;if(slot.subject_id==subj.subject_id)opt.selected=true;ss.appendChild(opt);});
 const ts=document.getElementById('editSlotTeacher');
 ts.innerHTML='<option value="">— No Teacher —</option>';
 const seen=new Set();
 availableSubjects.forEach(subj=>{if(subj.teacher_id&&!seen.has(subj.teacher_id)){seen.add(subj.teacher_id);const opt=document.createElement('option');opt.value=subj.teacher_id;opt.textContent=subj.teacher_name;if(slot.teacher_id==subj.teacher_id)opt.selected=true;ts.appendChild(opt);}});
 ss.onchange=function(){const sel=ss.options[ss.selectedIndex];if(sel?.dataset.teacherId)ts.value=sel.dataset.teacherId;};
 new bootstrap.Modal(document.getElementById('editSlotModal')).show();
}

async function saveSlot(){
 const payload={setting_id:currentSettingId,period_id:document.getElementById('editSlotPeriodId').value,day:document.getElementById('editSlotDay').value,subject_id:document.getElementById('editSlotSubject').value||null,teacher_id:document.getElementById('editSlotTeacher').value||null,room:document.getElementById('editSlotRoom').value,notes:document.getElementById('editSlotNotes').value,is_double:document.getElementById('editSlotIsDouble').checked,is_free:!document.getElementById('editSlotSubject').value};
 showLoading();
 try{
  const r=await post(ROUTES.saveSlot,payload);const result=await r.json();
  if(result.success){bootstrap.Modal.getInstance(document.getElementById('editSlotModal')).hide();await loadTimetableGrid();Swal.fire({title:'Saved',icon:'success',timer:1000,showConfirmButton:false});}
  else if(result.conflict)Swal.fire('Conflict',result.message,'warning');
  else throw new Error(result.message);
 }catch(e){Swal.fire('Error',e.message,'error');}finally{hideLoading();}
}

async function generateTimetable(){
 const{isConfirmed}=await Swal.fire({title:'Auto-Generate?',html:'Clears and rebuilds from constraints.<br><strong>Cannot be undone.</strong>',icon:'warning',showCancelButton:true,confirmButtonColor:'#667eea',cancelButtonColor:'#d33',confirmButtonText:'Generate'});
 if(!isConfirmed)return;
 showLoading();
 try{
  const r=await post(ROUTES.autoGenerate,{setting_id:currentSettingId});const d=await r.json();
  if(d.success){await loadTimetableGrid();document.querySelector('[href="#timetableGridTab"]').click();Swal.fire({title:'Generated!',icon:'success',timer:1500,showConfirmButton:false});}
  else throw new Error(d.message);
 }catch(e){Swal.fire('Error',e.message,'error');}finally{hideLoading();}
}

async function checkConflicts(){
 if(!currentSettingId)return Swal.fire('Error','No timetable loaded.','error');
 showLoading();
 try{
  const r=await get(buildUrl(ROUTES.checkConflicts,currentSettingId));if(!r.ok)throw new Error(`HTTP ${r.status}`);
  const d=await r.json();const ctr=document.getElementById('conflictsList');const badge=document.getElementById('conflictBadge');
  if(d.conflict_count===0){badge.innerHTML='<span class="badge bg-success">✓ No conflicts</span>';ctr.innerHTML='<div class="alert alert-success"><i class="ri-checkbox-circle-line me-2"></i>No conflicts found!</div>';}
  else{
   badge.innerHTML=`<span class="badge bg-danger">⚠ ${d.conflict_count} conflict(s)</span>`;
   let html=`<div class="alert alert-warning mb-3">${d.conflict_count} conflict(s) require attention.</div>`;
   d.conflicts.forEach(c=>{
    const av=c.teacher_picture?`<img src="${c.teacher_picture}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;">`:`<div style="width:44px;height:44px;border-radius:50%;background:#eee;display:flex;align-items:center;justify-content:center;"><i class="ri-user-line"></i></div>`;
    html+=`<div class="conflict-card">${av}<div><div class="fw-semibold">${esc(c.teacher)}</div><div class="small text-muted">${esc(c.day)} · ${esc(c.period)} (${esc(c.period_time)})</div><div class="small mt-1"><span class="badge bg-primary-subtle text-primary me-1">${esc(c.class_a)}</span>vs<span class="badge bg-primary-subtle text-primary ms-1">${esc(c.class_b)}</span></div></div></div>`;
   });
   ctr.innerHTML=html;
  }
 }catch(e){Swal.fire('Error',e.message,'error');}finally{hideLoading();}
}

function exportTimetable(settingId,format){if(!settingId)return Swal.fire('Error','No timetable selected.','error');window.open(buildUrl(ROUTES.export,settingId)+'?format='+format,'_blank');}

async function sendNotifications(){
 const{isConfirmed}=await Swal.fire({title:'Send Notifications',text:'Email timetable to all assigned teachers?',icon:'question',showCancelButton:true,confirmButtonColor:'#667eea',confirmButtonText:'Send'});
 if(!isConfirmed)return;
 showLoading();
 try{
  const r=await post(ROUTES.sendNotifications,{setting_id:currentSettingId,type:'weekly_preview'});const d=await r.json();
  if(d.success)Swal.fire('Sent!',d.message,'success');else throw new Error(d.message);
 }catch(e){Swal.fire('Error',e.message,'error');}finally{hideLoading();}
}

async function deleteSetting(settingId){
 const{isConfirmed}=await Swal.fire({title:'Delete Timetable?',text:'Cannot be undone.',icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',confirmButtonText:'Delete'});
 if(!isConfirmed)return;
 showLoading();
 try{
  const r=await fetch(buildUrl(ROUTES.deleteSetting,settingId),{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}});
  const d=await r.json();
  if(d.success)Swal.fire('Deleted!','','success').then(()=>location.reload());else throw new Error(d.message);
 }catch(e){Swal.fire('Error',e.message,'error');}finally{hideLoading();}
}

async function cloneSetting(settingId){
 const{isConfirmed,value}=await Swal.fire({
  title:'Clone Timetable',
  html:`<div class="text-start"><div class="mb-3"><label class="form-label">New Session</label>
   <select id="sCloneSession" class="form-select form-select-sm"><option value="">Same Session</option>
   @foreach($schoolsessions as $s)<option value="{{ $s->id }}">{{ $s->session }}</option>@endforeach</select></div>
   <div><label class="form-label">New Term</label>
   <select id="sCloneTerm" class="form-select form-select-sm"><option value="">Same Term</option>
   @foreach($schoolterms as $t)<option value="{{ $t->id }}">{{ $t->term }}</option>@endforeach</select></div></div>`,
  showCancelButton:true,confirmButtonColor:'#667eea',confirmButtonText:'Clone',
  preConfirm:()=>({new_session_id:document.getElementById('sCloneSession').value||null,new_term_id:document.getElementById('sCloneTerm').value||null})
 });
 if(!isConfirmed)return;
 showLoading();
 try{
  const r=await post(ROUTES.cloneSetting,{setting_id:settingId,...value});const d=await r.json();
  if(d.success)Swal.fire('Cloned!','','success').then(()=>location.reload());else throw new Error(d.message);
 }catch(e){Swal.fire('Error',e.message,'error');}finally{hideLoading();}
}
</script>

