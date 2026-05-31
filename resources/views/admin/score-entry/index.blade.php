                            </td>
                            <td>
                                @if($entryPercent >= 100)
                                    <span class="badge-terminal"><i class="ri-check-line"></i> Complete</span>
                                @elseif($entryPercent > 0)
                                    <span class="badge-open"><i class="ri-time-line"></i> In Progress</span>
                                @else
                                    <span class="badge-open"><i class="ri-add-line"></i> Not Started</span>
                                @endif
                            </td>
                            <td>
                                @if($teacherMockCompleted == $teacherTotal)
                                    <span class="badge-terminal">Complete</span>
                                @elseif($teacherMockCompleted > 0)
                                    <span class="badge-open">Partial</span>
                                @else
                                    <span class="badge-open">Not Started</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-bar-custom flex-grow-1" style="width: 100px;">
                                        <div class="progress-fill {{ $entryPercent >= 75 ? 'high' : ($entryPercent >= 50 ? 'medium' : 'low') }}" style="width: {{ $entryPercent }}%;"></div>
                                    </div>
                                    <small>{{ number_format($totalActual) }}/{{ number_format($totalExpected) }} ({{ $entryPercent }}%)</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-bar-custom flex-grow-1" style="width: 80px;">
                                        <div class="progress-fill {{ $entryPercent >= 75 ? 'high' : ($entryPercent >= 50 ? 'medium' : 'low') }}" style="width: {{ $entryPercent }}%;"></div>
                                    </div>
                                    <span class="fw-bold">{{ $entryPercent }}%</span>
                                </div>
                            </td>
                            <td><span class="status-badge {{ $statusClass }}">{{ $statusText }}</span></td>
                        </tr>
                        {{-- Child row for subjects with animated expandable content --}}
                        <tr class="child-row" data-parent="{{ $teacherId }}">
                            <td colspan="10" class="p-0">
                                <div class="expandable-content" style="padding: 0; background: #fafbfe;">
                                    <div class="p-3">
                                        <table class="child-table" style="width: 100%;">
                                            <thead>
                                                <tr style="background: #f1f5f9;">
                                                    <th style="padding: 10px 12px; font-size: 11px;">Subject</th>
                                                    <th style="padding: 10px 12px; font-size: 11px;">Class/Arm</th>
                                                    <th style="padding: 10px 12px; font-size: 11px;">Students</th>
                                                    <th style="padding: 10px 12px; font-size: 11px;">Entries</th>
                                                    <th style="padding: 10px 12px; font-size: 11px;">Progress</th>
                                                    <th style="padding: 10px 12px; font-size: 11px;">Mock</th>
                                                    <th style="padding: 10px 12px; font-size: 11px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($subjects as $subject)
                                                @php
                                                    $subjEntryPercent = $subject->entry_percentage;
                                                    $subjStatusClass = $subjEntryPercent >= 100 ? 'complete' : ($subjEntryPercent >= 75 ? 'good' : ($subjEntryPercent >= 50 ? 'partial' : 'low'));
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ $subject->subject_name }}</strong>
                                                        <br><small class="text-muted">{{ $subject->subject_code }}</small>
                                                    </td>
                                                    <td>{{ $subject->class_name }}</td>
                                                    <td>{{ $subject->student_count }}</td>
                                                    <td>{{ $subject->terminal_entries_count }}/{{ $subject->student_count }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="progress-bar-custom" style="width: 80px;">
                                                                <div class="progress-fill {{ $subjEntryPercent >= 75 ? 'high' : ($subjEntryPercent >= 50 ? 'medium' : 'low') }}" style="width: {{ $subjEntryPercent }}%;"></div>
                                                            </div>
                                                            <span class="small">{{ $subjEntryPercent }}%</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($subject->has_mock_scores)
                                                            <span class="badge-mock"><i class="ri-check-line"></i> Entered</span>
                                                        @else
                                                            <span class="badge-open"><i class="ri-add-line"></i> Pending</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <a href="{{ route('admin.score-entry.scoresheet', [$subject->subjectclass_id, $subject->teacher_id, $subject->termid, $subject->sessionid, 'terminal']) }}"
                                                               class="btn-score btn-terminal-score" style="padding: 4px 12px; font-size: 11px;">
                                                                <i class="ri-file-edit-line"></i> Terminal
                                                            </a>
                                                            <a href="{{ route('admin.score-entry.scoresheet', [$subject->subjectclass_id, $subject->teacher_id, $subject->termid, $subject->sessionid, 'mock']) }}"
                                                               class="btn-score btn-mock-score" style="padding: 4px 12px; font-size: 11px;">
                                                                <i class="ri-flask-line"></i> Mock
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Class Performance Table --}}
    @if(!empty($dashboardStats['class_stats']))
    <div class="section-card">
        <div class="section-card-header">
            <div>
                <div class="section-card-title"><i class="ri-group-line me-2 text-primary"></i>Class Performance Overview</div>
                <div class="section-card-sub">Scoresheet completion by class - Click any row to view class details</div>
            </div>
        </div>
        <div class="section-card-body p-0">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Class</th>
                            <th>Students</th>
                            <th>Subjects</th>
                            <th>Completed</th>
                            <th>Pending</th>
                            <th>Completion Rate</th>
                            <th>Entry Rate</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dashboardStats['class_stats'] as $index => $class)
                        @php
                            $realCompletionRate = $class['entry_completion_rate'] ?? 0;
                            $statusClass = $realCompletionRate == 100 ? 'complete' : ($realCompletionRate >= 75 ? 'good' : ($realCompletionRate >= 50 ? 'partial' : 'low'));
                        @endphp
                        <tr class="clickable-row" onclick="showClassDetails({{ $class['class_id'] }}, {{ json_encode($class) }})" style="cursor: pointer;">
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $class['class_name'] }}</strong></td>
                            <td>{{ number_format($class['student_count']) }}</td>
                            <td>{{ $class['total_subjects'] }}</td>
                            <td class="text-success">{{ $class['completed_subjects'] }} fully completed</td>
                            <td class="text-warning">{{ $class['pending_subjects'] }} need attention</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-bar-custom flex-grow-1" style="width: 100px;">
                                        <div class="progress-fill {{ $realCompletionRate >= 75 ? 'high' : ($realCompletionRate >= 50 ? 'medium' : 'low') }}" style="width: {{ $realCompletionRate }}%;"></div>
                                    </div>
                                    <span class="fw-bold">{{ $realCompletionRate }}%</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-bar-custom flex-grow-1" style="width: 80px;">
                                        <div class="progress-fill {{ ($class['entry_completion_rate'] ?? 0) >= 75 ? 'high' : (($class['entry_completion_rate'] ?? 0) >= 50 ? 'medium' : 'low') }}" style="width: {{ $class['entry_completion_rate'] ?? 0 }}%;"></div>
                                    </div>
                                    <span class="fw-bold">{{ $class['entry_completion_rate'] ?? 0 }}%</span>
                                </div>
                            </td>
                            <td><span class="status-badge {{ $statusClass }}">{{ $realCompletionRate == 100 ? 'Complete' : ($realCompletionRate >= 75 ? 'Good' : ($realCompletionRate >= 50 ? 'Partial' : 'Poor')) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Bulk Export Toolbar --}}
    <div id="bulkExportToolbar">
        <div class="d-flex align-items-center gap-3 flex-grow-1">
            <i class="ri-checkbox-circle-line fs-5"></i>
            <span class="fw-semibold"><span id="toolbarSelectedCount">0</span> scoresheets selected</span>
        </div>
        <button type="button" class="btn-toolbar" onclick="adminBulkExport.deselectAll()"><i class="ri-close-line"></i> Clear</button>
        <button type="button" class="btn-toolbar" onclick="adminBulkExport.selectOnlyWithScores()"><i class="ri-filter-line"></i> With scores only</button>
        <button type="button" class="btn-toolbar green" id="btnBulkExport" onclick="adminBulkExport.export()"><i class="ri-download-2-line"></i> Export ZIP</button>
        <button type="button" class="btn-toolbar" onclick="adminBulkExport.exportAllWithScores()" style="background:#2563eb;"><i class="ri-download-cloud-line"></i> Export All With Scores</button>
    </div>

    {{-- Search and Filters --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="search-input-wrapper">
            <i class="ri-search-line" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--c-muted);"></i>
            <input type="text" id="searchInput" class="search-input" placeholder="Search teacher, subject or class…">
        </div>
        <div class="d-flex align-items-center gap-3">
            <select id="statusFilter" class="form-select" style="width: auto;">
                <option value="all">All Status</option>
                <option value="complete">Complete (100%)</option>
                <option value="good">Good Progress (75-99%)</option>
                <option value="partial">Partial (50-74%)</option>
                <option value="low">Low (Below 50%)</option>
                <option value="no_scores">No Scores Entered (0%)</option>
            </select>
            <div class="d-flex align-items-center gap-2 px-3 py-2 bg-white rounded border">
                <input type="checkbox" id="selectAllCheckbox" onchange="adminBulkExport.toggleAll(this.checked)">
                <label for="selectAllCheckbox" class="mb-0 small">Select all visible</label>
                <span class="text-muted small ms-2" id="totalSubjectCount">{{ $teacherSubjects->count() }} scoresheets</span>
            </div>
        </div>
    </div>

    {{-- Teachers Grid (Alternative card view) --}}
    <div class="teachers-grid" id="teachersGrid">
        @foreach($teacherSubjects->groupBy('teacher_id') as $teacherId => $subjects)
            @php
                $teacherName      = $subjects->first()->teacher_name;
                $initials         = strtoupper(substr($teacherName, 0, 2));
                $teacherCompleted = $subjects->filter(function($s) { return $s->entry_percentage >= 100; })->count();
                $teacherTotal     = $subjects->count();
                $teacherPercent   = $teacherTotal > 0 ? round(($teacherCompleted / $teacherTotal) * 100) : 0;
                $teacherEntryAvg  = round($subjects->avg('entry_percentage'));
            @endphp
            <div class="teacher-card" data-status="{{ $teacherPercent == 100 ? 'complete' : ($teacherPercent >= 75 ? 'good' : ($teacherPercent >= 50 ? 'partial' : 'low')) }}">
                <div class="teacher-card-header">
                    <div class="teacher-avatar">{{ $initials }}</div>
                    <div>
                        <div class="teacher-name">{{ $teacherName }}</div>
                        <div class="teacher-stats">
                            <span><i class="ri-book-line"></i> {{ $teacherTotal }} subjects</span>
                            <span><i class="ri-check-line"></i> {{ $teacherCompleted }} fully completed ({{ $teacherPercent }}%)</span>
                            <span><i class="ri-database-line"></i> {{ $teacherEntryAvg }}% entries</span>
                        </div>
                        <div class="progress-bar-custom mt-2" style="width: 150px;">
                            <div class="progress-fill {{ $teacherEntryAvg >= 75 ? 'high' : ($teacherEntryAvg >= 50 ? 'medium' : 'low') }}" style="width: {{ $teacherEntryAvg }}%;"></div>
                        </div>
                    </div>
                </div>
                <div class="teacher-card-body">
                    @foreach($subjects as $subject)
                    @php
                        $subjectEntryPercent = $subject->entry_percentage;
                        $subjectStatusClass = $subjectEntryPercent >= 100 ? 'complete' : ($subjectEntryPercent >= 75 ? 'good' : ($subjectEntryPercent >= 50 ? 'partial' : 'low'));
                    @endphp
                    <div class="subject-item"
                         data-subjectclass-id="{{ $subject->subjectclass_id }}"
                         data-teacher-id="{{ $subject->teacher_id }}"
                         data-schoolclass-id="{{ $subject->schoolclass_id }}"
                         data-term-id="{{ $subject->termid }}"
                         data-session-id="{{ $subject->sessionid }}"
                         data-has-scores="{{ $subject->has_terminal_scores ? '1' : '0' }}"
                         onclick="adminBulkExport.toggleRow(this)">
                        <div class="d-flex gap-3">
                            <input type="checkbox" class="subject-checkbox bulk-export-check mt-1" onclick="event.stopPropagation(); adminBulkExport.onCheckboxClick(this)">
                            <div class="flex-grow-1">
                                <div class="subject-name">
                                    {{ $subject->subject_name }}
                                    <span class="subject-code">{{ $subject->subject_code }}</span>
                                    <span class="status-badge {{ $subjectStatusClass }}">{{ $subjectEntryPercent }}% Complete</span>
                                </div>
                                <div class="subject-class">
                                    <i class="ri-group-line"></i> {{ $subject->class_name }}
                                    · {{ $subject->student_count }} students
                                </div>
                                <div class="d-flex gap-2 mt-2 flex-wrap">
                                    @if($subjectEntryPercent >= 100)
                                        <span class="badge-terminal"><i class="ri-check-line"></i> Fully Entered ({{ $subject->terminal_entries_count }}/{{ $subject->student_count }})</span>
                                    @elseif($subjectEntryPercent > 0)
                                        <span class="badge-open"><i class="ri-time-line"></i> Partial Entry ({{ $subject->terminal_entries_count }}/{{ $subject->student_count }})</span>
                                    @else
                                        <span class="badge-open"><i class="ri-add-line"></i> No Scores Entered</span>
                                    @endif
                                    @if($subject->has_mock_scores)
                                        <span class="badge-mock"><i class="ri-flask-line"></i> Mock ({{ $subject->mock_entries_count }}/{{ $subject->student_count }})</span>
                                    @endif
                                </div>
                                <div class="mt-2">
                                    <div class="progress-bar-custom" style="width: 100%;">
                                        <div class="progress-fill {{ $subjectEntryPercent >= 75 ? 'high' : ($subjectEntryPercent >= 50 ? 'medium' : 'low') }}" style="width: {{ $subjectEntryPercent }}%;"></div>
                                    </div>
                                    <small class="text-muted">Entry completion: {{ $subject->terminal_entries_count }}/{{ $subject->student_count }} students ({{ $subjectEntryPercent }}%)</small>
                                </div>
                                <div class="btn-score-group" onclick="event.stopPropagation()">
                                    <a href="{{ route('admin.score-entry.scoresheet', [$subject->subjectclass_id, $subject->teacher_id, $subject->termid, $subject->sessionid, 'terminal']) }}" class="btn-score btn-terminal-score">
                                        <i class="ri-file-edit-line"></i> Terminal Scoresheet
                                    </a>
                                    <a href="{{ route('admin.score-entry.scoresheet', [$subject->subjectclass_id, $subject->teacher_id, $subject->termid, $subject->sessionid, 'mock']) }}" class="btn-score btn-mock-score">
                                        <i class="ri-flask-line"></i> Mock Scoresheet
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @elseif($selectedTermId && $selectedSessionId)
        <div class="text-center py-5 bg-white rounded-3 border">
            <i class="ri-user-unfollow-line fs-1 text-muted"></i>
            <h5 class="mt-3">No Teacher Assignments Found</h5>
            <p class="text-muted">No teachers have been assigned to subjects for the selected term and session.</p>
        </div>
    @else
        <div class="text-center py-5 bg-white rounded-3 border">
            <i class="ri-filter-line fs-1 text-muted"></i>
            <h5 class="mt-3">Select Session and Term</h5>
            <p class="text-muted">Please select an academic session and term to view teacher assignments.</p>
        </div>
    @endif

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* ===================================================
   ANIMATED EXPANDABLE ROWS FUNCTIONALITY
   =================================================== */
document.querySelectorAll('.parent-row').forEach(parentRow => {
    parentRow.addEventListener('click', function(e) {
        // Don't expand if clicking on a link or button inside the row
        if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a') || e.target.closest('button')) {
            return;
        }

        const teacherId = this.dataset.teacherId;
        const childRow = document.querySelector(`.child-row[data-parent="${teacherId}"]`);
        const expandIcon = this.querySelector('.expand-icon');
        const isExpanded = this.dataset.expanded === 'true';

        if (isExpanded) {
            // Collapse with animation
            const expandableContent = childRow.querySelector('.expandable-content');
            if (expandableContent) {
                expandableContent.style.animation = 'collapseSlideUp 0.35s cubic-bezier(0.4, 0, 0.2, 1) forwards';
                setTimeout(() => {
                    childRow.classList.remove('show');
                    expandableContent.style.animation = '';
                }, 350);
            } else {
                childRow.classList.remove('show');
            }
            expandIcon.classList.remove('rotated');
            this.dataset.expanded = 'false';
            this.classList.remove('expanded');
        } else {
            // Expand with animation
            childRow.classList.add('show');
            const expandableContent = childRow.querySelector('.expandable-content');
            if (expandableContent) {
                expandableContent.style.animation = 'expandSlideDown 0.35s cubic-bezier(0.4, 0, 0.2, 1) forwards';
                setTimeout(() => {
                    expandableContent.style.animation = '';
                }, 350);
            }
            expandIcon.classList.add('rotated');
            this.dataset.expanded = 'true';
            this.classList.add('expanded');
        }
    });
});

/* ===================================================
   BULK EXPORT MODULE
   =================================================== */
const adminBulkExport = (() => {
    const EXPORT_URL = '{{ route("admin.score-entry.bulk-export") }}';
    const CSRF = '{{ csrf_token() }}';

    function allCheckboxes() { return [...document.querySelectorAll('.bulk-export-check')]; }
    function visibleCheckboxes() { return allCheckboxes().filter(cb => { const card = cb.closest('.teacher-card'); return card && card.style.display !== 'none'; }); }
    function checkedBoxes() { return allCheckboxes().filter(cb => cb.checked); }

    function updateToolbar() {
        const checked = checkedBoxes();
        const n = checked.length;
        const toolbar = document.getElementById('bulkExportToolbar');
        const badge = document.getElementById('toolbarSelectedCount');
        const selAll = document.getElementById('selectAllCheckbox');
        if (toolbar) toolbar.classList.toggle('visible', n > 0);
        if (badge) badge.textContent = n;
        if (selAll) {
            const visible = visibleCheckboxes();
            const visChecked = visible.filter(cb => cb.checked).length;
            selAll.checked = visible.length > 0 && visChecked === visible.length;
            selAll.indeterminate = visChecked > 0 && visChecked < visible.length;
        }
    }

    function toggleRow(row) { const cb = row.querySelector('.bulk-export-check'); if (cb) { cb.checked = !cb.checked; row.classList.toggle('is-selected', cb.checked); updateToolbar(); } }
    function onCheckboxClick(cb) { cb.closest('.subject-item').classList.toggle('is-selected', cb.checked); updateToolbar(); }
    function toggleAll(checked) { visibleCheckboxes().forEach(cb => { cb.checked = checked; cb.closest('.subject-item').classList.toggle('is-selected', checked); }); updateToolbar(); }
    function deselectAll() { allCheckboxes().forEach(cb => { cb.checked = false; cb.closest('.subject-item').classList.remove('is-selected'); }); updateToolbar(); }
    function selectOnlyWithScores() { allCheckboxes().forEach(cb => { const row = cb.closest('.subject-item'); if (row && row.dataset.hasScores !== '1') { cb.checked = false; row.classList.remove('is-selected'); } }); updateToolbar(); }

    function exportAllWithScores() {
        const visible = visibleCheckboxes();
        const withScores = visible.filter(cb => { const row = cb.closest('.subject-item'); return row && row.dataset.hasScores === '1'; });
        if (withScores.length === 0) { Swal.fire({ icon: 'info', title: 'No Scoresheets', text: 'No scoresheets with scores found to export.', confirmButtonColor: '#2563eb' }); return; }
        withScores.forEach(cb => { cb.checked = true; cb.closest('.subject-item').classList.add('is-selected'); });
        updateToolbar();
        export_();
    }

    function export_() {
        const selected = checkedBoxes();
        if (selected.length === 0) { Swal.fire({ icon: 'warning', title: 'No Selection', text: 'Please select at least one scoresheet to export.', confirmButtonColor: '#2563eb' }); return; }
        const btn = document.getElementById('btnBulkExport');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Preparing…'; }
        const subjects = selected.map(cb => { const row = cb.closest('.subject-item'); return { subjectclass_id: row.dataset.subjectclassId, teacher_id: row.dataset.teacherId, schoolclass_id: row.dataset.schoolclassId, term_id: row.dataset.termId, session_id: row.dataset.sessionId }; });
        const form = document.createElement('form'); form.method = 'POST'; form.action = EXPORT_URL; form.style.display = 'none';
        const addInput = (name, value) => { const el = document.createElement('input'); el.type = 'hidden'; el.name = name; el.value = value; form.appendChild(el); };
        addInput('_token', CSRF);
        subjects.forEach((s, i) => { Object.entries(s).forEach(([key, val]) => addInput(`subjects[${i}][${key}]`, val)); });
        document.body.appendChild(form); form.submit();
        setTimeout(() => { if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ri-download-2-line"></i> Export ZIP'; } document.body.removeChild(form); }, 4000);
    }

    return { toggleRow, onCheckboxClick, toggleAll, deselectAll, selectOnlyWithScores, exportAllWithScores, export: export_ };
})();

/* ===================================================
   SEARCH AND FILTER
   =================================================== */
(function() {
    const input = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    if (!input) return;

    function filterCards() {
        const searchTerm = input.value.toLowerCase().trim();
        const statusValue = statusFilter ? statusFilter.value : 'all';
        let visible = 0;

        document.querySelectorAll('.teacher-card').forEach(card => {
            const text = (card.innerText.toLowerCase());
            const matchesSearch = !searchTerm || text.includes(searchTerm);

            let matchesStatus = statusValue === 'all';
            const cardStatus = card.dataset.status;

            if (!matchesStatus) {
                if (statusValue === 'no_scores') {
                    matchesStatus = cardStatus === 'low' && (card.innerText.match(/0% entries/) !== null);
                } else {
                    matchesStatus = cardStatus === statusValue;
                }
            }

            const show = matchesSearch && matchesStatus;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        const total = document.querySelectorAll('.teacher-card').length;
        const countSpan = document.getElementById('totalSubjectCount');
        if (countSpan) countSpan.textContent = (searchTerm || statusValue !== 'all') ? `${visible} of ${total} visible` : '{{ $teacherSubjects->count() }} scoresheets';

        const selAll = document.getElementById('selectAllCheckbox');
        if (selAll) {
            const vis = [...document.querySelectorAll('.teacher-card')].filter(c => c.style.display !== 'none');
            const visCbs = vis.flatMap(c => [...c.querySelectorAll('.bulk-export-check')]);
            const checked = visCbs.filter(cb => cb.checked).length;
            selAll.checked = visCbs.length > 0 && checked === visCbs.length;
            selAll.indeterminate = checked > 0 && checked < visCbs.length;
        }
    }

    input.addEventListener('input', filterCards);
    if (statusFilter) statusFilter.addEventListener('change', filterCards);
})();

/* ===================================================
   SHOW CLASS DETAILS MODAL
   =================================================== */
function showClassDetails(classId, classData) {
    let subjectList = '';
    if (classData.subjects && classData.subjects.length > 0) {
        subjectList = '<ul class="list-group mt-2" style="max-height: 300px; overflow-y: auto;">';
        classData.subjects.forEach(sub => {
            subjectList += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${sub}</span>
                                <span class="badge bg-primary rounded-pill">Subject</span>
                            </li>`;
        });
        subjectList += '</ul>';
    } else {
        subjectList = '<p class="text-muted">No subjects available</p>';
    }

    Swal.fire({
        title: `${classData.class_name} - Class Details`,
        html: `
            <div class="text-start">
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="border rounded p-2 text-center">
                            <div class="small text-muted">Students</div>
                            <div class="h5 mb-0">${Number(classData.student_count).toLocaleString()}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 text-center">
                            <div class="small text-muted">Subjects</div>
                            <div class="h5 mb-0">${classData.total_subjects}</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="border rounded p-2 text-center">
                            <div class="small text-muted">Completed</div>
                            <div class="h5 mb-0 text-success">${classData.completed_subjects}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 text-center">
                            <div class="small text-muted">Pending</div>
                            <div class="h5 mb-0 text-warning">${classData.pending_subjects}</div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Completion Rate</span>
                        <span class="fw-bold">${classData.completion_rate}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-${classData.completion_rate >= 75 ? 'success' : (classData.completion_rate >= 50 ? 'warning' : 'danger')}"
                             style="width: ${classData.completion_rate}%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Entry Completion Rate</span>
                        <span class="fw-bold">${classData.entry_completion_rate || 0}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: ${classData.entry_completion_rate || 0}%"></div>
                    </div>
                </div>
                <hr>
                <h6>Subjects Offered:</h6>
                ${subjectList}
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Close',
        confirmButtonColor: '#2563eb',
        width: '600px'
    });
}
</script>
@endsection
