// =============================================================================
// subjectoperation.js — Subject Registration Page
// =============================================================================

// ---------------------------------------------------------------------------
// GLOBALS  (populated by the Blade view via a small inline <script> block)
// ---------------------------------------------------------------------------
// window.ROUTES  = { batchRegister, unregister, getRegistered, getArchived,
//                    getSnapshot, restore, permanentDelete, index }
// window.CSRF    = '...'
// window.AVATAR_URL = '...'

let archiveCurrentPage  = 1;
let archiveMeta         = {};
let archiveSearchTimer  = null;
let currentSnapshotMeta = null;
let currentSnapshotRows = [];

// Inline SVG icon snippets reused in dynamic HTML
const SVG_PERSON = `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>`;
const SVG_GROUP  = `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`;

// =============================================================================
// HELPERS
// =============================================================================

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, m =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m])
    );
}

// Alias kept for any legacy call sites
const esc = escapeHtml;

function showSweetAlert(title, message, type, success = true) {
    Swal.fire({
        title,
        html: `<div class="d-flex align-items-center justify-content-center gap-2">
                 <span style="font-size:2rem;">${success ? '🎉' : '😞'}</span>
                 <span>${message}</span>
               </div>`,
        icon: success ? 'success' : 'error',
        confirmButtonColor: success ? '#28a745' : '#dc3545',
        confirmButtonText: success ? 'Great!' : 'Okay',
        timer: success ? 3000 : 5000,
        showConfirmButton: true,
    });
}

async function apiFetch(url, method, body) {
    const res  = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': window.CSRF,
        },
        body: JSON.stringify(body),
    });
    const data = await res.json();
    if (!res.ok && !data.success) throw new Error(data.message || `HTTP ${res.status}`);
    return data;
}

function setSpinner(on) {
    document.getElementById('register-loading-spinner')?.classList.toggle('d-none', !on);
}

// =============================================================================
// FILTER / PAGINATION
// =============================================================================

function filterData() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const search    = document.querySelector('.search')?.value ?? '';
    const gender    = document.getElementById('idgender')?.value ?? 'ALL';
    const admission = document.getElementById('idadmission')?.value ?? 'ALL';

    if (classId === 'ALL' || sessionId === 'ALL') {
        Swal.fire({ icon: 'warning', title: 'Missing Selection', text: 'Please select a class and session.' });
        return;
    }

    const params = new URLSearchParams({
        class_id   : classId,
        session_id : sessionId,
        search,
        gender,
        admissionno: admission,
    });
    window.location.href = window.ROUTES.index + '?' + params.toString();
}

function setupPaginationLinks() {
    document.querySelectorAll('.pagination a').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            if (!this.classList.contains('disabled')) loadPage(this.href);
        });
    });
}

async function loadPage(url) {
    const tableBody = document.getElementById('studentTableBody');
    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
    }

    try {
        const res  = await fetch(url, {
            headers: {
                'X-CSRF-TOKEN'     : window.CSRF,
                'X-Requested-With' : 'XMLHttpRequest',
            },
        });
        const html = await res.text();
        const doc  = new DOMParser().parseFromString(html, 'text/html');

        const newBody = doc.querySelector('#studentTableBody');
        if (newBody && tableBody) tableBody.innerHTML = newBody.innerHTML;

        refreshCheckboxCallbacks();
        setupPaginationLinks();
    } catch (err) {
        console.error('Pagination error:', err);
        if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Failed to load page.</td></tr>';
        }
    }
}

// =============================================================================
// ADMISSION NO DROPDOWN — kept for AJAX-based filter updates
// =============================================================================

function updateAdmissionNoOptions(students) {
    const select = document.getElementById('idadmission');
    if (!select) return;

    select.innerHTML = '<option value="ALL">All Admission Nos</option>';

    [...new Set(students.map(s => s.admissionno).filter(Boolean))]
        .sort()
        .forEach(no => {
            const opt = document.createElement('option');
            opt.value = no;
            opt.text  = no;
            select.appendChild(opt);
        });
}

// =============================================================================
// CHECKBOX WIRING
// =============================================================================

function handleCheckboxChange(e) {
    const row     = e.target?.closest?.('tr');
    const checked = e.target?.checked ?? false;

    if (row) row.classList.toggle('table-active', checked);

    const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;

    document.getElementById('register-selected-btn')?.classList.toggle('d-none',   checkedCount === 0);
    document.getElementById('unregister-selected-btn')?.classList.toggle('d-none', checkedCount === 0);

    const allBoxes = document.querySelectorAll('tbody input[name="chk_child"]');
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.checked = allBoxes.length > 0 && allBoxes.length === checkedCount;
    }
}

function refreshCheckboxCallbacks() {
    document.querySelectorAll('tbody input[name="chk_child"]').forEach(cb => {
        cb.removeEventListener('change', handleCheckboxChange);
        cb.addEventListener('change', handleCheckboxChange);
    });
}

// Alias used by older call sites
const refreshCallbacks = refreshCheckboxCallbacks;

function toggleBatchButtons() {
    const any = document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked').length > 0;
    document.getElementById('register-selected-btn')?.classList.toggle('d-none',   !any);
    document.getElementById('unregister-selected-btn')?.classList.toggle('d-none', !any);
}

// =============================================================================
// SUBJECT CARD HELPERS
// =============================================================================

function toggleSubjectCard(card) {
    const cb = card.querySelector('input[type="checkbox"]');
    if (cb) { cb.checked = !cb.checked; updateSubjectCount(); }
}

function selectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = true);
    updateSubjectCount();
}

function deselectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = false);
    updateSubjectCount();
}

function updateSubjectCount() {
    document.getElementById('subjectTeacherCount').textContent =
        document.querySelectorAll('.subject-checkbox:checked').length;
}

// =============================================================================
// SELECTED ITEM HELPERS
// =============================================================================

function getSelectedStudentIds() {
    return [...document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked')]
        .map(cb => parseInt(cb.closest('tr').querySelector('.id').dataset.id));
}

function getSelectedSubjectClasses() {
    return [...document.querySelectorAll('.subject-checkbox:checked')].map(cb => ({
        subjectclassid : parseInt(cb.dataset.subjectclassid),
        staffid        : parseInt(cb.dataset.staffid),
        termid         : parseInt(cb.dataset.termid),
    }));
}

// =============================================================================
// REGISTER BATCH
// =============================================================================

async function registerSelectedStudentsBatch() {
    const studentIds     = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId      = document.getElementById('idsession').value;

    if (!studentIds.length)     return showSweetAlert('No Students Selected',  'Please select at least one student.',  'warning', false);
    if (!subjectClasses.length) return showSweetAlert('No Subjects Selected',  'Please select at least one subject.',  'warning', false);
    if (sessionId === 'ALL')    return showSweetAlert('Session Required',       'Please select a session.',             'warning', false);

    const ok = await Swal.fire({
        title : 'Confirm Registration',
        html  : `<div class="text-center"><span style="font-size:3rem;">📚</span>
                 <p class="mt-2">Register <strong>${studentIds.length}</strong> student(s) for <strong>${subjectClasses.length}</strong> subject(s)?</p></div>`,
        icon  : 'question',
        showCancelButton   : true,
        confirmButtonColor : '#28a745',
        cancelButtonColor  : '#6c757d',
        confirmButtonText  : 'Yes, register!',
    });
    if (!ok.isConfirmed) return;

    setSpinner(true);
    try {
        const res = await apiFetch(window.ROUTES.batchRegister, 'POST', {
            studentids    : studentIds,
            subjectclasses: subjectClasses,
            sessionid     : parseInt(sessionId),
        });
        if (res.success) {
            showSweetAlert('Registration Successful!', res.message, 'success', true);
            setTimeout(() => location.reload(), 2000);
        } else {
            showSweetAlert('Registration Failed', res.message || 'Some students could not be registered.', 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', 'Registration failed: ' + err.message, 'error', false);
    } finally {
        setSpinner(false);
    }
}

// =============================================================================
// UNREGISTER — SNAPSHOT NAMING MODAL
// =============================================================================

function openUnregisterModal() {
    const studentIds     = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId      = document.getElementById('idsession').value;

    if (!studentIds.length)     return showSweetAlert('No Students Selected', 'Please select at least one student.', 'warning', false);
    if (!subjectClasses.length) return showSweetAlert('No Subjects Selected', 'Please select at least one subject.', 'warning', false);
    if (sessionId === 'ALL')    return showSweetAlert('Session Required',      'Please select a session.',            'warning', false);

    document.getElementById('snapshotStudentCount').textContent =
        `${studentIds.length} student${studentIds.length !== 1 ? 's' : ''}`;
    document.getElementById('snapshotSubjectCount').textContent =
        `${subjectClasses.length} subject${subjectClasses.length !== 1 ? 's' : ''}`;

    const nameInput = document.getElementById('snapshotNameInput');
    nameInput.value = '';
    nameInput.classList.remove('is-invalid');
    document.getElementById('snapshotNotesInput').value   = '';
    document.getElementById('snapshotNotesCount').textContent = '0';

    const now     = new Date();
    const dateStr = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    const timeStr = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    nameInput.value = `Unregistration — ${dateStr} ${timeStr}`;

    new bootstrap.Modal(document.getElementById('snapshotNameModal')).show();
}

async function proceedUnregister() {
    const nameInput  = document.getElementById('snapshotNameInput');
    const notesInput = document.getElementById('snapshotNotesInput');
    const name       = nameInput.value.trim();

    if (!name) { nameInput.classList.add('is-invalid'); return; }
    nameInput.classList.remove('is-invalid');

    const studentIds     = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId      = document.getElementById('idsession').value;

    bootstrap.Modal.getInstance(document.getElementById('snapshotNameModal'))?.hide();
    setSpinner(true);

    try {
        const res = await apiFetch(window.ROUTES.unregister, 'DELETE', {
            studentids    : studentIds,
            subjectclasses: subjectClasses,
            sessionid     : parseInt(sessionId),
            snapshot_name : name,
            snapshot_notes: notesInput.value.trim() || null,
        });
        if (res.success || res.success_count > 0) {
            showSweetAlert(
                'Unregistration Complete',
                `${res.success_count} student(s) unregistered.<br><small class="text-muted">Snapshot saved as "<strong>${escapeHtml(name)}</strong>"</small>`,
                'success', true
            );
            setTimeout(() => location.reload(), 2500);
        } else {
            showSweetAlert('Unregistration Failed', res.message || 'No students were unregistered.', 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', 'Unregistration failed: ' + err.message, 'error', false);
    } finally {
        setSpinner(false);
    }
}

// =============================================================================
// REGISTERED CLASSES MODAL
// =============================================================================

async function loadRegisteredClasses() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const container = document.getElementById('registeredClassesContent');

    if (classId === 'ALL' || sessionId === 'ALL') {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="ri-error-warning-line ri-3x text-warning d-block mb-3"></i>
                <h5 class="text-muted">Please select a class and session first</h5>
            </div>`;
        return;
    }

    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>
            <p class="text-muted mb-0">Loading registered subjects…</p>
        </div>`;

    try {
        const res  = await fetch(
            window.ROUTES.getRegistered + '?' + new URLSearchParams({ class_id: classId, session_id: sessionId }),
            { headers: { 'X-CSRF-TOKEN': window.CSRF, 'Accept': 'application/json' } }
        );
        const data = await res.json();

        if (!data.success || !data.data.length) {
            container.innerHTML = `
                <div class="alert alert-info text-center py-5">
                    <i class="ri-information-line fs-3 d-block mb-2"></i>
                    No registered subjects found for the selected class and session.
                </div>`;
            return;
        }

        container.innerHTML = data.data.map(term => buildRegisteredTermCard(term)).join('');
    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger m-3">Failed to load data: ${escapeHtml(err.message)}</div>`;
    }
}

function buildRegisteredTermCard(term) {
    const subjects = [...(term.subjects_teachers || [])]
        .sort((a, b) => (a.name || '').localeCompare(b.name || '', undefined, { sensitivity: 'base' }));

    const cells = subjects.map((subject, i) => {
        const hasTeachers  = subject.teachers && subject.teachers.length;
        const teacherNames = hasTeachers
            ? subject.teachers.map(t => escapeHtml(t.name)).join(', ')
            : null;

        const teacherHtml = hasTeachers
            ? `<div class="reg-subject-teacher">${SVG_PERSON} ${teacherNames}</div>`
            : `<div class="reg-subject-teacher unassigned">Not assigned</div>`;

        return `<div class="reg-subject-cell">
            <div class="reg-num-circle">${i + 1}</div>
            <div class="flex-grow-1 min-w-0">
                <div class="reg-subject-name text-truncate" title="${escapeHtml(subject.name)}">${escapeHtml(subject.name)}</div>
                ${teacherHtml}
                <span class="reg-student-pill">${SVG_GROUP} ${subject.student_count || 0} students</span>
            </div>
        </div>`;
    }).join('');

    return `
        <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;overflow:hidden;">
            <div class="rc-term-header">
                <div>
                    <h6>
                        ${escapeHtml(term.class_name)} ${escapeHtml(term.arm_name)}
                        <span class="rc-session">— ${escapeHtml(term.session_name)}</span>
                    </h6>
                    <small>${escapeHtml(term.term_name)}</small>
                </div>
                <div class="rc-term-badges">
                    <span class="rc-badge-blue">${term.student_count || 0} students</span>
                    <span class="rc-badge-purple">${subjects.length} subjects</span>
                </div>
            </div>
            <div class="bg-white">
                <div class="reg-subjects-grid">
                    ${cells || '<div class="p-4 text-center text-muted w-100">No subjects found for this term.</div>'}
                </div>
            </div>
        </div>`;
}

// =============================================================================
// ARCHIVE (SNAPSHOT LIST) MODAL
// =============================================================================

function openArchivedModal() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (classId === 'ALL' || sessionId === 'ALL') {
        return showSweetAlert('Selection Required', 'Please select a class and session first.', 'warning', false);
    }

    archiveCurrentPage = 1;
    new bootstrap.Modal(document.getElementById('archivedModal')).show();
    loadArchivedPage(1);
}

async function loadArchivedPage(page) {
    archiveCurrentPage = page;

    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const termId    = document.getElementById('archiveTermFilter').value;
    const search    = document.getElementById('archiveSearch').value.trim();
    const perPage   = document.getElementById('archivePerPage').value;

    if (classId === 'ALL' || sessionId === 'ALL') return;

    const spinner   = document.getElementById('archiveSpinner');
    const container = document.getElementById('snapshotCardsContainer');

    spinner.classList.remove('d-none');
    container.innerHTML = `<div class="text-center py-4">
        <div class="spinner-border spinner-border-sm text-warning me-2"></div> Loading snapshots…
    </div>`;

    try {
        const params = new URLSearchParams({ class_id: classId, session_id: sessionId, page, per_page: perPage });
        if (termId) params.set('term_id', termId);
        if (search) params.set('search',  search);

        const res  = await fetch(window.ROUTES.getArchived + '?' + params.toString(), {
            headers: { 'X-CSRF-TOKEN': window.CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (!data.success) {
            container.innerHTML = `<div class="text-center text-danger py-4">${escapeHtml(data.message)}</div>`;
            return;
        }

        archiveMeta = data.meta;
        renderSnapshotCards(data.data);
        renderArchivePagination(data.meta);
        updateArchiveMeta(data.meta);
    } catch (err) {
        container.innerHTML = `<div class="text-center text-danger py-4">Error: ${escapeHtml(err.message)}</div>`;
    } finally {
        spinner.classList.add('d-none');
    }
}

function renderSnapshotCards(rows) {
    const container = document.getElementById('snapshotCardsContainer');

    if (!rows.length) {
        container.innerHTML = `<div class="text-center text-muted py-5">
            <i class="ri-archive-line ri-3x d-block mb-2"></i>No unregistration snapshots found.
        </div>`;
        return;
    }

    // Group by snapshot_name + subjectclassid + termid
    const groups = {};
    rows.forEach(row => {
        const key = `${row.snapshot_name}__${row.subjectclassid}__${row.termid}`;
        if (!groups[key]) groups[key] = { ...row, subjects: [] };
        groups[key].subjects.push({
            subjectname   : row.subjectname,
            subjectcode   : row.subjectcode,
            staffname     : row.staffname,
            student_count : row.student_count,
            subjectclassid: row.subjectclassid,
            termid        : row.termid,
            sessionid     : row.sessionid,
            staffid       : row.staffid,
            archive_id    : row.archive_id,
        });
    });

    let html = '<div class="row g-3">';

    Object.values(groups).forEach(group => {
        const unregDate = group.unregistered_at
            ? new Date(group.unregistered_at).toLocaleDateString('en-GB', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
              })
            : '—';

        const subjectPills = group.subjects.map(s =>
            `<span class="badge bg-primary-subtle text-primary me-1 mb-1">${escapeHtml(s.subjectname)}</span>`
        ).join('');

        const metaEncoded = encodeURIComponent(JSON.stringify({
            snapshot_name  : group.snapshot_name,
            subjectclassid : group.subjectclassid,
            termid         : group.termid,
            sessionid      : group.sessionid,
            staffid        : group.staffid,
            archive_id     : group.archive_id,
        }));

        html += `
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 snapshot-card"
                 style="cursor:pointer;transition:transform .15s,box-shadow .15s;"
                 onclick="openSnapshotDetail('${metaEncoded}')"
                 onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.12)';"
                 onmouseleave="this.style.transform='';this.style.boxShadow='';">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="fw-semibold mb-0 text-truncate" title="${escapeHtml(group.snapshot_name)}">
                                <i class="ri-camera-line text-danger me-1"></i>${escapeHtml(group.snapshot_name)}
                            </h6>
                            <small class="text-muted">${unregDate}</small>
                        </div>
                        <span class="badge bg-danger-subtle text-danger rounded-pill flex-shrink-0">
                            ${group.student_count} student${group.student_count !== 1 ? 's' : ''}
                        </span>
                    </div>
                    ${group.snapshot_notes
                        ? `<p class="text-muted small fst-italic mb-2"
                               style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                               "${escapeHtml(group.snapshot_notes)}"
                           </p>`
                        : ''}
                    <div class="mb-2">${subjectPills}</div>
                    <div class="d-flex justify-content-between align-items-center pt-1 border-top mt-auto">
                        <small class="text-muted"><i class="ri-user-star-line me-1"></i>${escapeHtml(group.staffname ?? '—')}</small>
                        <span class="badge bg-warning-subtle text-warning-emphasis">${escapeHtml(group.termname)}</span>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 d-flex gap-2 py-2">
                    <button class="btn btn-sm btn-outline-primary flex-grow-1"
                        onclick="event.stopPropagation();openSnapshotDetail('${metaEncoded}');">
                        <i class="ri-eye-line me-1"></i> View
                    </button>
                    <button class="btn btn-sm btn-outline-success flex-grow-1"
                        onclick="event.stopPropagation();restoreSingleSnapshot('${metaEncoded}');">
                        <i class="ri-refresh-line me-1"></i> Restore
                    </button>
                    <button class="btn btn-sm btn-outline-danger"
                        onclick="event.stopPropagation();deleteSnapshotGroup('${metaEncoded}');"
                        title="Delete snapshot">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
        </div>`;
    });

    html += '</div>';
    container.innerHTML = html;
}

function renderArchivePagination(meta) {
    const container = document.getElementById('archivePagination');
    if (!meta || meta.last_page <= 1) { container.innerHTML = ''; return; }

    const delta = 3;
    let html = `<button class="btn btn-sm btn-outline-secondary ${meta.current_page === 1 ? 'disabled' : ''}"
                    onclick="loadArchivedPage(${meta.current_page - 1})">‹</button>`;
    for (let p = 1; p <= meta.last_page; p++) {
        if (p === 1 || p === meta.last_page || (p >= meta.current_page - delta && p <= meta.current_page + delta)) {
            html += `<button class="btn btn-sm ${p === meta.current_page ? 'btn-warning' : 'btn-outline-secondary'}"
                         onclick="loadArchivedPage(${p})">${p}</button>`;
        } else if (p === meta.current_page - delta - 1 || p === meta.current_page + delta + 1) {
            html += `<span class="btn btn-sm btn-outline-secondary disabled">…</span>`;
        }
    }
    html += `<button class="btn btn-sm btn-outline-secondary ${meta.current_page === meta.last_page ? 'disabled' : ''}"
                 onclick="loadArchivedPage(${meta.current_page + 1})">›</button>`;
    container.innerHTML = html;
}

function updateArchiveMeta(meta) {
    const el = document.getElementById('archiveMeta');
    if (!meta || !meta.total) { if (el) el.textContent = ''; return; }
    const from = (meta.current_page - 1) * meta.per_page + 1;
    const to   = Math.min(meta.current_page * meta.per_page, meta.total);
    if (el) el.textContent = `Showing ${from}–${to} of ${meta.total} snapshots`;
}

// =============================================================================
// SNAPSHOT DETAIL MODAL
// =============================================================================

async function openSnapshotDetail(metaEncoded) {
    currentSnapshotMeta = JSON.parse(decodeURIComponent(metaEncoded));

    document.getElementById('snapshotDetailTitle').textContent    = currentSnapshotMeta.snapshot_name;
    document.getElementById('snapshotDetailSubtitle').textContent = '';
    document.getElementById('snapshotNotesBanner')?.classList.add('d-none');

    const searchInput = document.getElementById('detailSearchInput');
    if (searchInput) searchInput.value = '';

    document.getElementById('snapshotDetailBody').innerHTML =
        '<tr><td colspan="10" class="text-center py-4"><div class="spinner-border spinner-border-sm me-2"></div>Loading students…</td></tr>';
    document.getElementById('detailRestoreSelectedBtn')?.classList.add('d-none');
    document.getElementById('detailDeleteSelectedBtn')?.classList.add('d-none');

    new bootstrap.Modal(document.getElementById('snapshotDetailModal')).show();

    try {
        const params = new URLSearchParams({
            snapshot_name  : currentSnapshotMeta.snapshot_name,
            subjectclassid : currentSnapshotMeta.subjectclassid,
            termid         : currentSnapshotMeta.termid,
            sessionid      : currentSnapshotMeta.sessionid,
            staffid        : currentSnapshotMeta.staffid,
        });

        const res  = await fetch(window.ROUTES.getSnapshot + '?' + params.toString(), {
            headers: { 'X-CSRF-TOKEN': window.CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (!data.success) {
            document.getElementById('snapshotDetailBody').innerHTML =
                `<tr><td colspan="10" class="text-center text-danger py-4">${escapeHtml(data.message)}</td></tr>`;
            return;
        }

        currentSnapshotRows = data.rows;

        if (data.snapshot_notes) {
            document.getElementById('snapshotNotesBanner')?.classList.remove('d-none');
            document.getElementById('snapshotNotesText').textContent = data.snapshot_notes;
        }

        document.getElementById('detailStudentMeta').textContent =
            `${data.total_students} student${data.total_students !== 1 ? 's' : ''} in this snapshot`;

        renderSnapshotDetailTable(data.rows, data.assessment_headers);
    } catch (err) {
        document.getElementById('snapshotDetailBody').innerHTML =
            `<tr><td colspan="10" class="text-center text-danger py-4">Error: ${escapeHtml(err.message)}</td></tr>`;
    }
}

function renderSnapshotDetailTable(rows, assessmentHeaders) {
    const headerRow = document.getElementById('snapshotDetailHeaderRow');
    while (headerRow.cells.length > 4) headerRow.deleteCell(headerRow.cells.length - 1);

    (assessmentHeaders || []).forEach(a => {
        const th       = document.createElement('th');
        th.textContent = a.assessment_name || `Assessment ${a.assessment_id}`;
        headerRow.appendChild(th);
    });
    const thTotal       = document.createElement('th');
    thTotal.textContent = 'Total';
    headerRow.appendChild(thTotal);

    let html = '';
    rows.forEach(row => {
        const name    = [row.lastname, row.firstname, row.othername].filter(Boolean).join(' ');
        const picFile = row.picture ? row.picture.split('/').pop() : null;
        const pic     = picFile
            ? `${window.AVATAR_URL}/student_avatars/${picFile}`
            : `${window.AVATAR_URL}/student_avatars/unnamed.jpg`;

        const genderBadge = row.gender === 'Female'
            ? `<span class="badge text-white" style="background:#e84393;">${escapeHtml(row.gender)}</span>`
            : `<span class="badge text-white" style="background:#1a6fd4;">${escapeHtml(row.gender ?? '—')}</span>`;

        let scoresCells = '';
        let total       = 0;

        (assessmentHeaders || []).forEach(a => {
            const score = (row.assessment_scores || []).find(s => s.assessment_id == a.assessment_id);
            const val   = score ? parseFloat(score.score) : 0;
            total += val;
            scoresCells += `<td class="text-center fw-medium">${val > 0 ? val.toFixed(1) : '<span class="text-muted">—</span>'}</td>`;
        });
        scoresCells += `<td class="text-center fw-bold ${total > 0 ? 'text-success' : 'text-muted'}">${total > 0 ? total.toFixed(1) : '—'}</td>`;

        const searchKey = `${name} ${row.admissionno ?? ''}`.toLowerCase();

        html += `<tr data-archive-id="${row.archive_id}" data-search="${escapeHtml(searchKey)}">
            <td><div class="form-check mb-0">
                <input class="form-check-input detail-chk" type="checkbox" value="${row.archive_id}">
            </div></td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <img src="${pic}" class="rounded-circle" style="width:34px;height:34px;object-fit:cover;border:2px solid #e9ecef;"
                         onerror="this.src='${window.AVATAR_URL}/student_avatars/unnamed.jpg'">
                    <span class="fw-medium">${escapeHtml(name)}</span>
                </div>
            </td>
            <td class="text-muted small">${escapeHtml(row.admissionno ?? '—')}</td>
            <td>${genderBadge}</td>
            ${scoresCells}
        </tr>`;
    });

    document.getElementById('snapshotDetailBody').innerHTML =
        html || '<tr><td colspan="10" class="text-center text-muted py-4">No students found.</td></tr>';

    document.getElementById('detailCheckAll')?.addEventListener('change', function () {
        document.querySelectorAll('.detail-chk').forEach(cb => cb.checked = this.checked);
        toggleDetailButtons();
    });
    document.querySelectorAll('.detail-chk').forEach(cb =>
        cb.addEventListener('change', toggleDetailButtons)
    );
}

function toggleDetailButtons() {
    const any = document.querySelectorAll('.detail-chk:checked').length > 0;
    document.getElementById('detailRestoreSelectedBtn')?.classList.toggle('d-none', !any);
    document.getElementById('detailDeleteSelectedBtn')?.classList.toggle('d-none',  !any);
}

function filterDetailRows(query) {
    const q     = query.toLowerCase().trim();
    const rows  = document.querySelectorAll('#snapshotDetailBody tr[data-search]');
    let visible = 0;
    rows.forEach(tr => {
        const match = !q || tr.dataset.search.includes(q);
        tr.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    const total = currentSnapshotRows.length;
    const meta  = document.getElementById('detailStudentMeta');
    if (meta) {
        meta.textContent = q
            ? `${visible} of ${total} student${total !== 1 ? 's' : ''} shown`
            : `${total} student${total !== 1 ? 's' : ''} in this snapshot`;
    }
}

// =============================================================================
// RESTORE
// =============================================================================

async function restoreEntireSnapshot() {
    if (!currentSnapshotRows.length) return;
    await doRestore(currentSnapshotRows.map(r => r.archive_id), 'all students in this snapshot');
}

async function restoreDetailSelected() {
    const ids = [...document.querySelectorAll('.detail-chk:checked')].map(cb => parseInt(cb.value));
    if (!ids.length) return;
    await doRestore(ids, `${ids.length} selected student${ids.length !== 1 ? 's' : ''}`);
}

async function doRestore(archiveIds, label) {
    const ok = await Swal.fire({
        title : 'Restore Registration?',
        html  : `<p>Restore <strong>${label}</strong>? Their original scores will be recovered.</p>`,
        icon  : 'question',
        showCancelButton   : true,
        confirmButtonColor : '#28a745',
        confirmButtonText  : 'Yes, restore!',
    });
    if (!ok.isConfirmed) return;

    const spinner = document.getElementById('detailSpinner');
    spinner?.classList.remove('d-none');

    try {
        const res = await apiFetch(window.ROUTES.restore, 'POST', { archive_ids: archiveIds });
        if (res.success || res.total_restored > 0) {
            showSweetAlert('Restored!', `${res.total_restored || archiveIds.length} registration(s) restored with original scores.`, 'success', true);
            bootstrap.Modal.getInstance(document.getElementById('snapshotDetailModal'))?.hide();
            loadArchivedPage(archiveCurrentPage);
        } else {
            showSweetAlert('Restore Failed', res.message || 'Could not restore.', 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', 'Restore failed: ' + err.message, 'error', false);
    } finally {
        spinner?.classList.add('d-none');
    }
}

async function restoreSingleSnapshot(metaEncoded) {
    const meta = JSON.parse(decodeURIComponent(metaEncoded));

    const ok = await Swal.fire({
        title : 'Restore Snapshot?',
        html  : `<p>Restore all students in snapshot "<strong>${escapeHtml(meta.snapshot_name)}</strong>"?<br>Original scores will be recovered.</p>`,
        icon  : 'question',
        showCancelButton   : true,
        confirmButtonColor : '#28a745',
        confirmButtonText  : 'Yes, restore all!',
    });
    if (!ok.isConfirmed) return;

    const spinner = document.getElementById('archiveSpinner');
    spinner?.classList.remove('d-none');

    try {
        const params = new URLSearchParams({
            snapshot_name  : meta.snapshot_name,
            subjectclassid : meta.subjectclassid,
            termid         : meta.termid,
            sessionid      : meta.sessionid,
            staffid        : meta.staffid,
        });
        const detailRes  = await fetch(window.ROUTES.getSnapshot + '?' + params.toString(), {
            headers: { 'X-CSRF-TOKEN': window.CSRF, 'Accept': 'application/json' },
        });
        const detailData = await detailRes.json();

        if (!detailData.success || !detailData.rows?.length) {
            showSweetAlert('Not Found', detailData.message || 'Snapshot records not found.', 'error', false);
            return;
        }

        const ids = detailData.rows.map(r => r.archive_id);
        const res = await apiFetch(window.ROUTES.restore, 'POST', { archive_ids: ids });

        if (res.success || res.total_restored > 0) {
            showSweetAlert('Restored!', `${res.total_restored || ids.length} registration(s) restored.`, 'success', true);
            loadArchivedPage(archiveCurrentPage);
        } else {
            showSweetAlert('Restore Failed', res.message, 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', err.message, 'error', false);
    } finally {
        spinner?.classList.add('d-none');
    }
}

// =============================================================================
// DELETE
// =============================================================================

async function deleteSnapshotGroup(metaEncoded) {
    const meta = JSON.parse(decodeURIComponent(metaEncoded));

    const ok = await Swal.fire({
        title : 'Delete Snapshot?',
        html  : `<p class="text-danger">Permanently delete snapshot "<strong>${escapeHtml(meta.snapshot_name)}</strong>"?<br>This cannot be undone.</p>`,
        icon  : 'error',
        showCancelButton   : true,
        confirmButtonColor : '#dc3545',
        confirmButtonText  : 'Yes, delete permanently',
    });
    if (!ok.isConfirmed) return;

    const spinner = document.getElementById('archiveSpinner');
    spinner?.classList.remove('d-none');

    try {
        const params = new URLSearchParams({
            snapshot_name  : meta.snapshot_name,
            subjectclassid : meta.subjectclassid,
            termid         : meta.termid,
            sessionid      : meta.sessionid,
            staffid        : meta.staffid,
        });
        const detailRes  = await fetch(window.ROUTES.getSnapshot + '?' + params.toString(), {
            headers: { 'X-CSRF-TOKEN': window.CSRF, 'Accept': 'application/json' },
        });
        const detailData = await detailRes.json();

        if (!detailData.success || !detailData.rows?.length) {
            showSweetAlert('Not Found', detailData.message || 'Snapshot records not found.', 'error', false);
            return;
        }

        const ids = detailData.rows.map(r => r.archive_id);
        const res = await apiFetch(window.ROUTES.permanentDelete, 'DELETE', { archive_ids: ids });

        if (res.success) {
            showSweetAlert('Deleted', `${res.deleted || ids.length} record(s) permanently deleted.`, 'success', false);
            loadArchivedPage(archiveCurrentPage);
        } else {
            showSweetAlert('Delete Failed', res.message, 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', err.message, 'error', false);
    } finally {
        spinner?.classList.add('d-none');
    }
}

async function deleteDetailSelected() {
    const ids = [...document.querySelectorAll('.detail-chk:checked')].map(cb => parseInt(cb.value));
    if (!ids.length) return;

    const ok = await Swal.fire({
        title : 'Permanently Delete?',
        html  : `<p class="text-danger">Delete <strong>${ids.length}</strong> record(s) permanently?</p>`,
        icon  : 'error',
        showCancelButton   : true,
        confirmButtonColor : '#dc3545',
        confirmButtonText  : 'Yes, delete permanently',
    });
    if (!ok.isConfirmed) return;

    const spinner = document.getElementById('detailSpinner');
    spinner?.classList.remove('d-none');

    try {
        const res = await apiFetch(window.ROUTES.permanentDelete, 'DELETE', { archive_ids: ids });
        if (res.success) {
            showSweetAlert('Deleted', `${res.deleted || ids.length} record(s) permanently deleted.`, 'success', false);
            bootstrap.Modal.getInstance(document.getElementById('snapshotDetailModal'))?.hide();
            loadArchivedPage(archiveCurrentPage);
        } else {
            showSweetAlert('Delete Failed', res.message, 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', err.message, 'error', false);
    } finally {
        spinner?.classList.add('d-none');
    }
}

// Toolbar stubs — per-card actions handle the real work
async function restoreSelected()         { /* handled via restoreSingleSnapshot */ }
async function permanentDeleteSelected() { /* handled via deleteSnapshotGroup  */ }

// =============================================================================
// DOM READY
// =============================================================================

document.addEventListener('DOMContentLoaded', function () {

    // Image modal
    document.getElementById('imageViewModal')?.addEventListener('show.bs.modal', function (e) {
        const src = e.relatedTarget?.getAttribute('data-image');
        document.getElementById('enlargedImage').src = src || `${window.AVATAR_URL}/student_avatars/unnamed.jpg`;
    });

    // Registered classes modal — load on open
    document.getElementById('registeredClassesModal')?.addEventListener('show.bs.modal', loadRegisteredClasses);

    // Archive controls
    document.getElementById('archivePerPage')?.addEventListener('change', () => loadArchivedPage(1));
    document.getElementById('archiveTermFilter')?.addEventListener('change', () => loadArchivedPage(1));
    document.getElementById('archiveSearch')?.addEventListener('input', function () {
        clearTimeout(archiveSearchTimer);
        archiveSearchTimer = setTimeout(() => loadArchivedPage(1), 400);
    });

    // Snapshot notes character counter
    document.getElementById('snapshotNotesInput')?.addEventListener('input', function () {
        document.getElementById('snapshotNotesCount').textContent = this.value.length;
    });

    // Subject checkbox count
    document.querySelectorAll('.subject-checkbox').forEach(cb =>
        cb.addEventListener('change', updateSubjectCount)
    );
    updateSubjectCount();

    // Check-all students
    const checkAll = document.getElementById('checkAll');
    checkAll?.addEventListener('change', function () {
        document.querySelectorAll('#studentTableBody input[name="chk_child"]').forEach(cb => {
            cb.checked = this.checked;
            cb.closest('tr')?.classList.toggle('table-active', this.checked);
        });
        toggleBatchButtons();
    });

    // Individual student checkboxes (delegated)
    document.addEventListener('change', function (e) {
        if (e.target?.name === 'chk_child') {
            e.target.closest('tr')?.classList.toggle('table-active', e.target.checked);
            toggleBatchButtons();
        }
    });

    // Wire up checkbox callbacks for server-rendered rows
    refreshCheckboxCallbacks();
    setupPaginationLinks();
});
