{{-- resources/views/timetable/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">
                            <i class="ri-calendar-todo-line me-2"></i>Timetable Management
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Timetable</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-2"></i>
                    <strong>Error!</strong> There were some problems with your input.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <ul class="mt-2 mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Class & Session Selection --}}
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-xxl-3 col-sm-6">
                                    <label class="form-label">Select Class</label>
                                    <select class="form-select" id="classSelect">
                                        <option value="">-- Select Class --</option>
                                        @foreach ($schoolclasses as $class)
                                            <option value="{{ $class->id }}">
                                                {{ $class->schoolclass }} {{ $class->arm }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xxl-3 col-sm-6">
                                    <label class="form-label">Select Session</label>
                                    <select class="form-select" id="sessionSelect">
                                        <option value="">-- Select Session --</option>
                                        @foreach ($schoolsessions as $session)
                                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xxl-3 col-sm-6">
                                    <label class="form-label">Select Term (Optional)</label>
                                    <select class="form-select" id="termSelect">
                                        <option value="">All Terms</option>
                                        @foreach ($schoolterms as $term)
                                            <option value="{{ $term->id }}">{{ $term->term }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xxl-3 col-sm-6">
                                    <button class="btn btn-primary w-100" onclick="loadOrCreateSetting()">
                                        <i class="ri-settings-4-line me-2"></i>Load / Create Timetable
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Existing Settings Summary --}}
            @if($settings->count() > 0)
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-history-line me-2"></i>Existing Timetables
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Class</th>
                                            <th>Session</th>
                                            <th>Term</th>
                                            <th>Last Updated</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($settings as $setting)
                                        <tr>
                                            <td class="fw-medium">
                                                <i class="ri-school-line text-primary me-2"></i>
                                                {{ $setting->schoolclass->schoolclass ?? 'N/A' }}
                                            </td>
                                            <td>{{ $setting->session->session ?? 'N/A' }}</td>
                                            <td>{{ $setting->term->term ?? 'All Terms' }}</td>
                                            <td>{{ $setting->updated_at->format('d M Y, H:i') }}</td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="ri-checkbox-circle-line me-1"></i>Active
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"
                                                        onclick="loadSetting({{ $setting->id }})"
                                                        title="Edit Timetable">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info"
                                                        onclick="cloneSetting({{ $setting->id }})"
                                                        title="Clone Timetable">
                                                    <i class="ri-file-copy-line"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteSetting({{ $setting->id }})"
                                                        title="Delete Timetable">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Main Timetable Editor (Hidden until setting loaded) --}}
            <div id="timetableEditor" style="display: none;">
                {{-- Tabs --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <ul class="nav nav-tabs nav-tabs-custom mb-4" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#periodsTab" role="tab">
                                            <i class="ri-time-line me-2"></i>Periods & Settings
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#constraintsTab" role="tab">
                                            <i class="ri-bar-chart-2-line me-2"></i>Subject Constraints
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#timetableGridTab" role="tab">
                                            <i class="ri-table-line me-2"></i>Timetable Grid
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#conflictsTab" role="tab">
                                            <i class="ri-alert-line me-2"></i>Conflicts
                                        </a>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    {{-- Periods & Settings Tab --}}
                                    <div class="tab-pane active" id="periodsTab" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card border">
                                                    <div class="card-header bg-light">
                                                        <h6 class="card-title mb-0">
                                                            <i class="ri-settings-4-line me-2"></i>School Day Settings
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">School Day Start</label>
                                                                <input type="time" class="form-control" id="schoolDayStart">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">School Day End</label>
                                                                <input type="time" class="form-control" id="schoolDayEnd">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Period Duration (min)</label>
                                                                <input type="number" class="form-control" id="periodDuration" min="20" max="90">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Short Break (min)</label>
                                                                <input type="number" class="form-control" id="shortBreakDuration" min="5" max="60">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Long Break (min)</label>
                                                                <input type="number" class="form-control" id="longBreakDuration" min="10" max="90">
                                                            </div>
                                                            <div class="col-md-12">
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
                                                        <h6 class="card-title mb-0">
                                                            <i class="ri-list-check-2-line me-2"></i>Periods
                                                        </h6>
                                                        <button type="button" class="btn btn-sm btn-primary" onclick="addPeriodRow()">
                                                            <i class="ri-add-line me-1"></i>Add Period
                                                        </button>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered" id="periodsTable">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th style="width: 40px">#</th>
                                                                        <th>Period Name</th>
                                                                        <th>Type</th>
                                                                        <th style="width: 80px"></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="periodsBody">
                                                                    <tr id="periodRowTemplate" style="display: none;">
                                                                        <td class="period-order"></td>
                                                                        <td><input type="text" class="form-control form-control-sm period-name" placeholder="e.g., Period 1"></td>
                                                                        <td>
                                                                            <select class="form-select form-select-sm period-type">
                                                                                <option value="lesson">Lesson</option>
                                                                                <option value="short_break">Short Break</option>
                                                                                <option value="long_break">Long Break</option>
                                                                                <option value="assembly">Assembly</option>
                                                                                <option value="free">Free Period</option>
                                                                            </select>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <button type="button" class="btn btn-sm btn-danger remove-period" onclick="removePeriodRow(this)">
                                                                                <i class="ri-delete-bin-line"></i>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="mt-3">
                                                            <button class="btn btn-success" onclick="saveSettings()">
                                                                <i class="ri-save-line me-2"></i>Save Settings
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Constraints Tab --}}
                                    <div class="tab-pane" id="constraintsTab" role="tabpanel">
                                        <div class="card border">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <h6 class="card-title mb-0">
                                                    <i class="ri-bar-chart-2-line me-2"></i>Subject Constraints
                                                </h6>
                                                <div>
                                                    <button class="btn btn-sm btn-success me-2" onclick="saveConstraints()">
                                                        <i class="ri-save-line me-1"></i>Save Constraints
                                                    </button>
                                                    <button class="btn btn-sm btn-primary" onclick="generateTimetable()">
                                                        <i class="ri-magic-line me-1"></i>Auto-Generate Timetable
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered" id="constraintsTable">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Subject</th>
                                                                <th>Teacher</th>
                                                                <th>Periods/Week</th>
                                                                <th>Allow Double</th>
                                                                <th>Max Double/Week</th>
                                                                <th>Preferred Days</th>
                                                                <th>Avoid Days</th>
                                                                <th>Compulsory</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="constraintsBody">
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Timetable Grid Tab --}}
                                    <div class="tab-pane" id="timetableGridTab" role="tabpanel">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <button class="btn btn-success" onclick="saveAllSlots()">
                                                    <i class="ri-save-line me-2"></i>Save All Changes
                                                </button>
                                                <button class="btn btn-info ms-2" onclick="exportTimetable('csv')">
                                                    <i class="ri-download-line me-2"></i>Export CSV
                                                </button>
                                                <button class="btn btn-warning ms-2" onclick="sendNotifications()">
                                                    <i class="ri-mail-send-line me-2"></i>Send Notifications
                                                </button>
                                            </div>
                                            <div class="text-muted">
                                                <small><i class="ri-information-line me-1"></i>Click on any cell to edit</small>
                                            </div>
                                        </div>
                                        <div class="table-responsive" id="timetableGridContainer">
                                            <div class="text-center py-5">
                                                <div class="spinner-border text-primary" role="status"></div>
                                                <p class="mt-2">Loading timetable...</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Conflicts Tab --}}
                                    <div class="tab-pane" id="conflictsTab" role="tabpanel">
                                        <div class="card border">
                                            <div class="card-header bg-light">
                                                <h6 class="card-title mb-0">
                                                    <i class="ri-alert-line me-2"></i>Conflict Checker
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <button class="btn btn-primary" onclick="checkConflicts()">
                                                        <i class="ri-refresh-line me-2"></i>Check Conflicts
                                                    </button>
                                                    <span id="conflictBadge"></span>
                                                </div>
                                                <div id="conflictsList" class="mt-3">
                                                    <div class="text-center text-muted py-4">
                                                        <i class="ri-check-line ri-3x text-success"></i>
                                                        <p>Click "Check Conflicts" to verify teacher availability</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Slot Modal with Teacher Picture Tooltip --}}
<div class="modal fade" id="editSlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title text-white">
                    <i class="ri-edit-box-line me-2"></i>Edit Timetable Slot
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editSlotSettingId">
                <input type="hidden" id="editSlotPeriodId">
                <input type="hidden" id="editSlotDay">

                <div class="mb-3">
                    <label class="form-label">Period</label>
                    <input type="text" class="form-control" id="editSlotPeriodName" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Day</label>
                    <input type="text" class="form-control" id="editSlotDayName" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <select class="form-select" id="editSlotSubject">
                        <option value="">-- Free Period --</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Teacher</label>
                    <select class="form-select" id="editSlotTeacher">
                        <option value="">-- No Teacher --</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Room / Venue</label>
                    <input type="text" class="form-control" id="editSlotRoom" placeholder="e.g., Room 101, Lab A">
                </div>

                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" id="editSlotNotes" rows="2" placeholder="Additional notes..."></textarea>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="editSlotIsDouble">
                                    <label class="form-check-label" for="editSlotIsDouble">
                                        Double Period (consecutive)
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="saveSlot()">
                                    <i class="ri-save-line me-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Confirmation Modal --}}
                <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirm Action</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="confirmModalBody">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="confirmModalBtn">Confirm</button>
                            </div>
                        </div>
                    </div>
                </div>

@endsection


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ============================================================================
    // GLOBALS
    // ============================================================================
    let currentSettingId = null;
    let currentSetting = null;
    let currentPeriods = [];
    let currentGrid = {};
    let currentDays = [];
    let availableSubjects = [];
    let allTeachers = [];

    // FIXED: Remove trailing slashes from URLs
    const ROUTES = {
        // Use route() for routes WITHOUT parameters
        setup: '{{ route("timetable.setup") }}',
        saveSettings: '{{ route("timetable.save-settings") }}',
        saveConstraints: '{{ route("timetable.save-constraints") }}',
        autoGenerate: '{{ route("timetable.auto-generate") }}',
        saveSlot: '{{ route("timetable.save-slot") }}',
        sendNotifications: '{{ route("timetable.send-notifications") }}',
        cloneSetting: '{{ route("timetable.clone-setting") }}',
        getClassSubjects: '{{ route("timetable.class-subjects") }}',

        // Use url() for routes WITH parameters (to avoid missing parameter error)
        getSetting: '{{ url("/timetable/get-setting") }}',
        getGrid: '{{ url("/timetable/get-grid") }}',
        checkConflicts: '{{ url("/timetable/check-conflicts") }}',
        export: '{{ url("/timetable/export") }}',
        deleteSetting: '{{ url("/timetable/delete-setting") }}',
    };

    const CSRF = '{{ csrf_token() }}';

    // Helper function to build URLs
    function buildUrl(baseUrl, id) {
        let cleanUrl = baseUrl.replace(/\/$/, '');
        return cleanUrl + '/' + id;
    }

    // ============================================================================
    // LOAD / CREATE SETTING
    // ============================================================================
    async function loadOrCreateSetting() {
        const classId = document.getElementById('classSelect').value;
        const sessionId = document.getElementById('sessionSelect').value;
        const termId = document.getElementById('termSelect').value;

        if (!classId || !sessionId) {
            Swal.fire({
                title: 'Selection Required',
                text: 'Please select both a class and a session.',
                icon: 'warning',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        showLoading();

        try {
            const response = await fetch(ROUTES.setup, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    schoolclass_id: classId,
                    session_id: sessionId,
                    term_id: termId || null
                })
            });

            const data = await response.json();
            if (data.success) {
                currentSettingId = data.setting_id;
                await loadSetting(currentSettingId);
            } else {
                throw new Error(data.message || 'Failed to load setting');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Failed to load timetable setting: ' + error.message, 'error');
        } finally {
            hideLoading();
        }
    }

    async function loadSetting(settingId) {
        showLoading();

        try {
            const getSettingUrl = buildUrl(ROUTES.getSetting, settingId);
            console.log('Fetching URL:', getSettingUrl);

            const response = await fetch(getSettingUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success) {
                currentSetting = data.setting;
                currentSettingId = settingId;
                availableSubjects = data.available_subjects || [];

                // Populate settings form
                document.getElementById('schoolDayStart').value = currentSetting.school_day_start?.slice(0,5) || '08:00';
                document.getElementById('schoolDayEnd').value = currentSetting.school_day_end?.slice(0,5) || '14:30';
                document.getElementById('periodDuration').value = currentSetting.period_duration_minutes || 40;
                document.getElementById('shortBreakDuration').value = currentSetting.short_break_duration_minutes || 20;
                document.getElementById('longBreakDuration').value = currentSetting.long_break_duration_minutes || 40;

                // Active days
                const activeDays = currentSetting.active_days || ['Monday','Tuesday','Wednesday','Thursday','Friday'];
                document.querySelectorAll('.active-day-checkbox').forEach(cb => {
                    cb.checked = activeDays.includes(cb.value);
                });

                // Load periods
                if (currentSetting.periods && currentSetting.periods.length > 0) {
                    loadPeriodsIntoTable(currentSetting.periods);
                } else {
                    const defaultPeriods = [
                        { name: 'Period 1', type: 'lesson' },
                        { name: 'Period 2', type: 'lesson' },
                        { name: 'Short Break', type: 'short_break' },
                        { name: 'Period 3', type: 'lesson' },
                        { name: 'Period 4', type: 'lesson' },
                        { name: 'Long Break', type: 'long_break' },
                        { name: 'Period 5', type: 'lesson' },
                        { name: 'Period 6', type: 'lesson' }
                    ];
                    loadPeriodsIntoTable(defaultPeriods);
                }

                // Load constraints
                loadConstraintsIntoTable(currentSetting.constraints || []);

                // Load timetable grid
                await loadTimetableGrid();

                // Show editor
                document.getElementById('timetableEditor').style.display = 'block';

                // Scroll to editor
                document.getElementById('timetableEditor').scrollIntoView({ behavior: 'smooth' });
            } else {
                throw new Error(data.message || 'Failed to load setting');
            }
        } catch (error) {
            console.error('Error loading setting:', error);
            Swal.fire('Error', 'Failed to load timetable: ' + error.message, 'error');
        } finally {
            hideLoading();
        }
    }

    // ============================================================================
    // PERIODS MANAGEMENT
    // ============================================================================
    function loadPeriodsIntoTable(periods) {
        const tbody = document.getElementById('periodsBody');
        tbody.innerHTML = '';

        periods.forEach((period, index) => {
            addPeriodRow(period.name, period.type, index + 1);
        });

        if (periods.length === 0) {
            addPeriodRow('Period 1', 'lesson', 1);
        }
    }

    function addPeriodRow(name = '', type = 'lesson', order = null) {
        const tbody = document.getElementById('periodsBody');
        const row = document.createElement('tr');

        const orderCell = document.createElement('td');
        orderCell.className = 'period-order';
        if (order) {
            orderCell.textContent = order;
        } else {
            const rows = document.querySelectorAll('#periodsBody tr');
            orderCell.textContent = rows.length + 1;
        }
        row.appendChild(orderCell);

        const nameCell = document.createElement('td');
        const nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.className = 'form-control form-control-sm period-name';
        nameInput.placeholder = 'e.g., Period 1';
        nameInput.value = name;
        nameCell.appendChild(nameInput);
        row.appendChild(nameCell);

        const typeCell = document.createElement('td');
        const typeSelect = document.createElement('select');
        typeSelect.className = 'form-select form-select-sm period-type';
        typeSelect.innerHTML = `
            <option value="lesson" ${type === 'lesson' ? 'selected' : ''}>Lesson</option>
            <option value="short_break" ${type === 'short_break' ? 'selected' : ''}>Short Break</option>
            <option value="long_break" ${type === 'long_break' ? 'selected' : ''}>Long Break</option>
            <option value="assembly" ${type === 'assembly' ? 'selected' : ''}>Assembly</option>
            <option value="free" ${type === 'free' ? 'selected' : ''}>Free Period</option>
        `;
        typeCell.appendChild(typeSelect);
        row.appendChild(typeCell);

        const actionCell = document.createElement('td');
        actionCell.className = 'text-center';
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-danger remove-period';
        removeBtn.innerHTML = '<i class="ri-delete-bin-line"></i>';
        removeBtn.onclick = function() { removePeriodRow(this); };
        actionCell.appendChild(removeBtn);
        row.appendChild(actionCell);

        tbody.appendChild(row);
        reorderPeriods();
    }

    function removePeriodRow(btn) {
        const row = btn.closest('tr');
        row.remove();
        reorderPeriods();
    }

    function reorderPeriods() {
        const rows = document.querySelectorAll('#periodsBody tr');
        rows.forEach((row, idx) => {
            const orderCell = row.querySelector('.period-order');
            if (orderCell) {
                orderCell.textContent = idx + 1;
            }
        });
    }

    function getPeriodsFromTable() {
        const periods = [];
        const rows = document.querySelectorAll('#periodsBody tr');
        rows.forEach(row => {
            const nameInput = row.querySelector('.period-name');
            const typeSelect = row.querySelector('.period-type');
            if (nameInput && typeSelect) {
                periods.push({
                    name: nameInput.value,
                    type: typeSelect.value
                });
            }
        });
        return periods;
    }

    async function saveSettings() {
        const periods = getPeriodsFromTable();
        if (periods.length === 0) {
            Swal.fire('Error', 'Please add at least one period.', 'error');
            return;
        }

        const activeDays = [];
        document.querySelectorAll('.active-day-checkbox:checked').forEach(cb => {
            activeDays.push(cb.value);
        });

        if (activeDays.length === 0) {
            Swal.fire('Error', 'Please select at least one active day.', 'error');
            return;
        }

        const data = {
            setting_id: currentSettingId,
            school_day_start: document.getElementById('schoolDayStart').value,
            school_day_end: document.getElementById('schoolDayEnd').value,
            period_duration_minutes: parseInt(document.getElementById('periodDuration').value),
            short_break_duration_minutes: parseInt(document.getElementById('shortBreakDuration').value),
            long_break_duration_minutes: parseInt(document.getElementById('longBreakDuration').value),
            active_days: activeDays,
            periods: periods
        };

        showLoading();

        try {
            const response = await fetch(ROUTES.saveSettings, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                Swal.fire('Success', 'Settings saved successfully!', 'success');
                await loadSetting(currentSettingId);
            } else {
                throw new Error(result.message || 'Failed to save settings');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Failed to save settings: ' + error.message, 'error');
        } finally {
            hideLoading();
        }
    }

    // ============================================================================
    // CONSTRAINTS MANAGEMENT
    // ============================================================================
    function loadConstraintsIntoTable(constraints) {
        const tbody = document.getElementById('constraintsBody');
        tbody.innerHTML = '';

        if (availableSubjects.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="ri-information-line ri-2x d-block mb-2"></i>
                        No subjects assigned to this class. Please assign subjects first.
                    </td>
                </tr>
            `;
            return;
        }

        const constraintsMap = new Map();
        constraints.forEach(c => {
            constraintsMap.set(c.subject_id, c);
        });

        availableSubjects.forEach(subj => {
            const constraint = constraintsMap.get(subj.subject_id);
            const periodsPerWeek = constraint?.periods_per_week || 2;
            const allowDouble = constraint?.allow_double_period || false;
            const maxDouble = constraint?.max_double_periods_per_week || 1;
            const preferredDays = constraint?.preferred_days || [];
            const avoidDays = constraint?.avoid_days || [];
            const isCompulsory = constraint?.is_compulsory !== false;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    ${escapeHtml(subj.subject_name)}
                    <input type="hidden" class="constraint-subject-id" value="${subj.subject_id}">
                </td>
                <td>${escapeHtml(subj.teacher_name)}</td>
                <td>
                    <input type="number" class="form-control form-control-sm periods-per-week"
                           value="${periodsPerWeek}" min="1" max="10" style="width: 80px;">
                </td>
                <td class="text-center">
                    <input type="checkbox" class="allow-double" ${allowDouble ? 'checked' : ''}>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm max-double"
                           value="${maxDouble}" min="0" max="5" style="width: 80px;" ${!allowDouble ? 'disabled' : ''}>
                </td>
                <td>
                    <select class="form-select form-select-sm preferred-days" multiple size="3" style="min-width: 120px;">
                        ${generateDayOptions(preferredDays)}
                    </select>
                    <small class="text-muted">Ctrl+Click to select multiple</small>
                </td>
                <td>
                    <select class="form-select form-select-sm avoid-days" multiple size="3" style="min-width: 120px;">
                        ${generateDayOptions(avoidDays)}
                    </select>
                </td>
                <td class="text-center">
                    <input type="checkbox" class="is-compulsory" ${isCompulsory ? 'checked' : ''}>
                </td>
            `;

            const allowDoubleCheckbox = row.querySelector('.allow-double');
            const maxDoubleInput = row.querySelector('.max-double');
            allowDoubleCheckbox.addEventListener('change', () => {
                maxDoubleInput.disabled = !allowDoubleCheckbox.checked;
            });

            tbody.appendChild(row);
        });
    }

    function generateDayOptions(selectedDays = []) {
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        return days.map(day =>
            `<option value="${day}" ${selectedDays.includes(day) ? 'selected' : ''}>${day}</option>`
        ).join('');
    }

    function getConstraintsFromTable() {
        const constraints = [];
        const rows = document.querySelectorAll('#constraintsBody tr');

        rows.forEach(row => {
            const subjectId = row.querySelector('.constraint-subject-id')?.value;
            if (!subjectId) return;

            const preferredDaysSelect = row.querySelector('.preferred-days');
            const avoidDaysSelect = row.querySelector('.avoid-days');

            constraints.push({
                subject_id: parseInt(subjectId),
                periods_per_week: parseInt(row.querySelector('.periods-per-week').value),
                allow_double: row.querySelector('.allow-double').checked,
                max_double: parseInt(row.querySelector('.max-double').value),
                preferred_days: preferredDaysSelect ? Array.from(preferredDaysSelect.selectedOptions).map(opt => opt.value) : [],
                avoid_days: avoidDaysSelect ? Array.from(avoidDaysSelect.selectedOptions).map(opt => opt.value) : [],
                is_compulsory: row.querySelector('.is-compulsory').checked
            });
        });

        return constraints;
    }

    async function saveConstraints() {
        const constraints = getConstraintsFromTable();

        if (constraints.length === 0) {
            Swal.fire('Error', 'No constraints to save.', 'error');
            return;
        }

        showLoading();

        try {
            const response = await fetch(ROUTES.saveConstraints, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    setting_id: currentSettingId,
                    constraints: constraints
                })
            });

            const result = await response.json();
            if (result.success) {
                Swal.fire('Success', 'Constraints saved successfully!', 'success');
            } else {
                throw new Error(result.message || 'Failed to save constraints');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Failed to save constraints: ' + error.message, 'error');
        } finally {
            hideLoading();
        }
    }

    // ============================================================================
    // TIMETABLE GRID
    // ============================================================================
    async function loadTimetableGrid() {
        if (!currentSettingId) return;

        const container = document.getElementById('timetableGridContainer');
        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading timetable...</p></div>';

        try {
            const getGridUrl = buildUrl(ROUTES.getGrid, currentSettingId);

            const response = await fetch(getGridUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success) {
                currentPeriods = data.periods;
                currentGrid = data.grid;
                currentDays = data.days;
                allTeachers = data.teachers || [];

                renderTimetableGrid();
            } else {
                throw new Error(data.message || 'Failed to load grid');
            }
        } catch (error) {
            console.error('Error loading grid:', error);
            container.innerHTML = `<div class="alert alert-danger">Failed to load timetable: ${error.message}</div>`;
        }
    }

    function renderTimetableGrid() {
        const container = document.getElementById('timetableGridContainer');

        if (!currentPeriods.length || !currentDays.length) {
            container.innerHTML = '<div class="alert alert-warning">No periods or days configured. Please save settings first.</div>';
            return;
        }

        let html = `
            <table class="table table-bordered timetable-grid">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 120px">Period / Time</th>
                        ${currentDays.map(day => `<th class="text-center">${escapeHtml(day)}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
        `;

        currentPeriods.forEach(period => {
            const isBreak = period.is_break;
            const rowClass = isBreak ? 'table-warning' : '';

            html += `<tr class="${rowClass}" data-period-id="${period.id}" data-period-order="${period.order}">
                <td class="fw-semibold" style="background: #f8f9fa;">
                    <div>${escapeHtml(period.name)}</div>
                    <small class="text-muted">${period.start_time} - ${period.end_time}</small>
                    ${period.is_break ? `<span class="badge bg-warning mt-1">Break</span>` : ''}
                </td>`;

            currentDays.forEach(day => {
                const slot = currentGrid[period.id]?.[day] || null;
                const isFree = slot?.is_free || (!slot?.subject_id && !slot?.teacher_id);
                const cellClass = isFree ? 'bg-light' : (slot?.is_double ? 'bg-primary-subtle' : '');

                html += `
                    <td class="timetable-cell ${cellClass}"
                        data-period-id="${period.id}"
                        data-day="${day}"
                        data-subject-id="${slot?.subject_id || ''}"
                        data-teacher-id="${slot?.teacher_id || ''}"
                        data-teacher="${escapeHtml(slot?.teacher || '')}"
                        data-teacher-picture="${slot?.teacher_picture || ''}"
                        data-subject="${escapeHtml(slot?.subject || '')}"
                        data-room="${escapeHtml(slot?.room || '')}"
                        data-notes="${escapeHtml(slot?.notes || '')}"
                        data-is-double="${slot?.is_double || false}"
                        data-is-free="${isFree}"
                        style="cursor: pointer; vertical-align: middle;"
                        onclick="openEditSlotModal(${period.id}, '${day}')">
                        <div class="d-flex flex-column align-items-center">
                `;

                if (slot && !isFree) {
                    if (slot.teacher_picture) {
                        html += `
                            <img src="${slot.teacher_picture}" class="rounded-circle mb-1"
                                 style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #667eea;">
                        `;
                    } else {
                        html += `<i class="ri-user-line ri-2x text-muted mb-1"></i>`;
                    }
                    html += `
                        <span class="fw-semibold small">${escapeHtml(slot.subject_code || slot.subject || '—')}</span>
                        <small class="text-muted">${escapeHtml(slot.teacher?.split(' ')[0] || '—')}</small>
                        ${slot.room ? `<small class="text-muted"><i class="ri-door-line"></i> ${escapeHtml(slot.room)}</small>` : ''}
                    `;
                } else {
                    html += `
                        <i class="ri-time-line ri-2x text-muted mb-1"></i>
                        <span class="text-muted">Free</span>
                    `;
                }

                html += `
                        </div>
                    </td>
                `;
            });

            html += `</tr>`;
        });

        html += `
                </tbody>
            </table>
        `;

        container.innerHTML = html;
    }

    // ============================================================================
    // EDIT SLOT MODAL
    // ============================================================================
    function openEditSlotModal(periodId, day) {
        const period = currentPeriods.find(p => p.id == periodId);
        if (!period) return;

        const slot = currentGrid[periodId]?.[day] || {};

        document.getElementById('editSlotSettingId').value = currentSettingId;
        document.getElementById('editSlotPeriodId').value = periodId;
        document.getElementById('editSlotDay').value = day;
        document.getElementById('editSlotPeriodName').value = period.name;
        document.getElementById('editSlotDayName').value = day;
        document.getElementById('editSlotRoom').value = slot.room || '';
        document.getElementById('editSlotNotes').value = slot.notes || '';
        document.getElementById('editSlotIsDouble').checked = slot.is_double || false;

        // Populate subjects dropdown
        const subjectSelect = document.getElementById('editSlotSubject');
        subjectSelect.innerHTML = '<option value="">-- Free Period --</option>';

        availableSubjects.forEach(subj => {
            const selected = slot.subject_id == subj.subject_id;
            subjectSelect.innerHTML += `
                <option value="${subj.subject_id}" data-teacher-id="${subj.teacher_id}" data-teacher-name="${subj.teacher_name}" ${selected ? 'selected' : ''}>
                    ${escapeHtml(subj.subject_name)} (${escapeHtml(subj.teacher_name)})
                </option>
            `;
        });

        // Populate teachers dropdown
        const teacherSelect = document.getElementById('editSlotTeacher');
        teacherSelect.innerHTML = '<option value="">-- No Teacher --</option>';

        const uniqueTeachers = new Map();
        availableSubjects.forEach(subj => {
            if (subj.teacher_id && !uniqueTeachers.has(subj.teacher_id)) {
                uniqueTeachers.set(subj.teacher_id, {
                    id: subj.teacher_id,
                    name: subj.teacher_name,
                    picture: subj.teacher_picture
                });
            }
        });

        uniqueTeachers.forEach(teacher => {
            const selected = slot.teacher_id == teacher.id;
            teacherSelect.innerHTML += `
                <option value="${teacher.id}" data-teacher-picture="${teacher.picture || ''}" ${selected ? 'selected' : ''}>
                    ${escapeHtml(teacher.name)}
                </option>
            `;
        });

        // When subject changes, update teacher
        subjectSelect.onchange = function() {
            const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
            const teacherId = selectedOption.dataset.teacherId;
            if (teacherId) {
                teacherSelect.value = teacherId;
            }
        };

        new bootstrap.Modal(document.getElementById('editSlotModal')).show();
    }

    async function saveSlot() {
        const periodId = document.getElementById('editSlotPeriodId').value;
        const day = document.getElementById('editSlotDay').value;
        const subjectId = document.getElementById('editSlotSubject').value;
        const teacherId = document.getElementById('editSlotTeacher').value;
        const room = document.getElementById('editSlotRoom').value;
        const notes = document.getElementById('editSlotNotes').value;
        const isDouble = document.getElementById('editSlotIsDouble').checked;

        const data = {
            setting_id: currentSettingId,
            period_id: periodId,
            day: day,
            subject_id: subjectId || null,
            teacher_id: teacherId || null,
            room: room,
            notes: notes,
            is_double: isDouble,
            is_free: !subjectId
        };

        showLoading();

        try {
            const response = await fetch(ROUTES.saveSlot, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('editSlotModal')).hide();
                await loadTimetableGrid();
                Swal.fire('Success', 'Slot saved successfully!', 'success');
            } else {
                if (result.conflict) {
                    Swal.fire('Conflict Detected', result.message, 'warning');
                } else {
                    throw new Error(result.message || 'Failed to save slot');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Failed to save slot: ' + error.message, 'error');
        } finally {
            hideLoading();
        }
    }

    async function saveAllSlots() {
        Swal.fire('Info', 'Individual slots are saved automatically when you edit them.', 'info');
    }

    // ============================================================================
    // AUTO GENERATE
    // ============================================================================
    async function generateTimetable() {
        Swal.fire({
            title: 'Auto-Generate Timetable?',
            html: 'This will clear the existing timetable and generate a new one based on your constraints.<br><strong>This action cannot be undone!</strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, generate it!'
        }).then(async (result) => {
            if (result.isConfirmed) {
                showLoading();

                try {
                    const response = await fetch(ROUTES.autoGenerate, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ setting_id: currentSettingId })
                    });

                    const data = await response.json();
                    if (data.success) {
                        await loadTimetableGrid();
                        Swal.fire('Success', 'Timetable generated successfully!', 'success');
                    } else {
                        throw new Error(data.message || 'Generation failed');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to generate timetable: ' + error.message, 'error');
                } finally {
                    hideLoading();
                }
            }
        });
    }

    // ============================================================================
    // CONFLICTS
    // ============================================================================
    async function checkConflicts() {
        if (!currentSettingId) {
            Swal.fire('Error', 'No timetable loaded', 'error');
            return;
        }

        showLoading();

        try {
            const checkConflictsUrl = buildUrl(ROUTES.checkConflicts, currentSettingId);

            const response = await fetch(checkConflictsUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success) {
                const container = document.getElementById('conflictsList');
                const badge = document.getElementById('conflictBadge');

                if (data.conflict_count === 0) {
                    badge.innerHTML = '<span class="badge bg-success">✓ No Conflicts</span>';
                    container.innerHTML = `
                        <div class="alert alert-success">
                            <i class="ri-checkbox-circle-line me-2"></i>
                            No conflicts found! All teachers are properly scheduled.
                        </div>
                    `;
                } else {
                    badge.innerHTML = `<span class="badge bg-danger">⚠ ${data.conflict_count} Conflict(s)</span>`;

                    let conflictsHtml = `
                        <div class="alert alert-warning">
                            <i class="ri-alert-line me-2"></i>
                            Found ${data.conflict_count} conflict(s) that need attention.
                        </div>
                        <div class="list-group">
                    `;

                    data.conflicts.forEach(conflict => {
                        conflictsHtml += `
                            <div class="list-group-item">
                                <div class="d-flex align-items-center gap-3">
                                    ${conflict.teacher_picture ?
                                        `<img src="${conflict.teacher_picture}" class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover;">` :
                                        `<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="ri-user-line text-white ri-xl"></i>
                                        </div>`
                                    }
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">${escapeHtml(conflict.teacher)}</h6>
                                        <p class="mb-1 text-muted">
                                            <strong>${conflict.day}</strong> • ${conflict.period} (${conflict.period_time || ''})
                                        </p>
                                        <p class="mb-0 small">
                                            Conflict between:
                                            <span class="badge bg-primary">${escapeHtml(conflict.class_a || 'Class A')}</span>
                                            and
                                            <span class="badge bg-primary">${escapeHtml(conflict.class_b || 'Class B')}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    conflictsHtml += `</div>`;
                    container.innerHTML = conflictsHtml;
                }
            } else {
                throw new Error(data.message || 'Failed to check conflicts');
            }
        } catch (error) {
            console.error('Error checking conflicts:', error);
            Swal.fire('Error', 'Failed to check conflicts: ' + error.message, 'error');
        } finally {
            hideLoading();
        }
    }

    // ============================================================================
    // NOTIFICATIONS
    // ============================================================================
    async function sendNotifications() {
        Swal.fire({
            title: 'Send Notifications',
            text: 'Send timetable notifications to all teachers?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Yes, send now!'
        }).then(async (result) => {
            if (result.isConfirmed) {
                showLoading();

                try {
                    const response = await fetch(ROUTES.sendNotifications, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            setting_id: currentSettingId,
                            type: 'weekly_preview'
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        Swal.fire('Success', data.message, 'success');
                    } else {
                        throw new Error(data.message || 'Failed to send notifications');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to send notifications: ' + error.message, 'error');
                } finally {
                    hideLoading();
                }
            }
        });
    }

    // ============================================================================
    // EXPORT
    // ============================================================================
    function exportTimetable(format) {
        if (!currentSettingId) {
            Swal.fire('Error', 'No timetable loaded', 'error');
            return;
        }
        const exportUrl = buildUrl(ROUTES.export, currentSettingId) + '?format=' + format;
        window.location.href = exportUrl;
    }

    // ============================================================================
    // DELETE / CLONE
    // ============================================================================
    async function deleteSetting(settingId) {
        Swal.fire({
            title: 'Delete Timetable?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then(async (result) => {
            if (result.isConfirmed) {
                showLoading();

                try {
                    const deleteUrl = buildUrl(ROUTES.deleteSetting, settingId);

                    const response = await fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (data.success) {
                        Swal.fire('Deleted!', 'Timetable has been deleted.', 'success');
                        location.reload();
                    } else {
                        throw new Error(data.message || 'Failed to delete');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to delete: ' + error.message, 'error');
                } finally {
                    hideLoading();
                }
            }
        });
    }

    async function cloneSetting(settingId) {
        Swal.fire({
            title: 'Clone Timetable',
            html: `
                <div class="text-start">
                    <div class="mb-3">
                        <label class="form-label">New Session (Optional)</label>
                        <select id="cloneSessionId" class="form-select">
                            <option value="">Same Session</option>
                            @foreach($schoolsessions as $session)
                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Term (Optional)</label>
                        <select id="cloneTermId" class="form-select">
                            <option value="">Same Term</option>
                            @foreach($schoolterms as $term)
                            <option value="{{ $term->id }}">{{ $term->term }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Clone',
            preConfirm: () => {
                return {
                    new_session_id: document.getElementById('cloneSessionId').value,
                    new_term_id: document.getElementById('cloneTermId').value
                };
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                showLoading();

                try {
                    const response = await fetch(ROUTES.cloneSetting, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            setting_id: settingId,
                            new_session_id: result.value.new_session_id || null,
                            new_term_id: result.value.new_term_id || null
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        Swal.fire('Cloned!', 'Timetable has been cloned successfully.', 'success');
                        location.reload();
                    } else {
                        throw new Error(data.message || 'Failed to clone');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to clone: ' + error.message, 'error');
                } finally {
                    hideLoading();
                }
            }
        });
    }

    // ============================================================================
    // UTILITIES
    // ============================================================================
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            if (m === '"') return '&quot;';
            if (m === "'") return '&#039;';
            return m;
        });
    }

    function showLoading() {
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    function hideLoading() {
        Swal.close();
    }
</script>

<style>
    .timetable-grid td {
        vertical-align: middle;
        transition: all 0.2s ease;
    }
    .timetable-cell {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .timetable-cell:hover {
        background-color: rgba(102, 126, 234, 0.1) !important;
        transform: scale(1.02);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .tooltip-timetable {
        font-size: 12px;
        line-height: 1.4;
    }
    .timetable-grid .bg-primary-subtle {
        background-color: rgba(102, 126, 234, 0.15);
    }
</style>

