{{-- resources/views/holidays/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">
                            <i class="ri-calendar-event-line me-2"></i>{{ $pagetitle }}
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Holidays</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @can('Create holidays')
            <div class="row mb-3">
                <div class="col-lg-12">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#holidayModal" onclick="resetHolidayForm()">
                        <i class="ri-add-line me-2"></i>Add Holiday
                    </button>
                </div>
            </div>
            @endcan

            {{-- Upcoming Holidays Card --}}
            @if($upcomingHolidays->count() > 0)
            <div class="row">
                <div class="col-lg-12">
                    <div class="card bg-warning-subtle">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="ri-calendar-event-line me-2"></i>Upcoming Holidays</h6>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($upcomingHolidays as $holiday)
                                <div class="card border-0 shadow-sm" style="min-width: 200px;">
                                    <div class="card-body py-2">
                                        <strong>{{ $holiday->name }}</strong><br>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($holiday->start_date)->format('d M Y') }}
                                            @if($holiday->start_date != $holiday->end_date)
                                                - {{ \Carbon\Carbon::parse($holiday->end_date)->format('d M Y') }}
                                            @endif
                                        </small>
                                        <span class="badge bg-secondary ms-2">{{ str_replace('_', ' ', $holiday->type) }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Holidays List --}}
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">All Holidays</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Holiday Name</th>
                                            <th>Type</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Duration</th>
                                            <th>Affects Timetable</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($holidays as $holiday)
                                        <tr>
                                            <td class="fw-medium">{{ $holiday->name }}</td>
                                            <td>
                                                @php
                                                    $typeColors = [
                                                        'public_holiday' => 'danger',
                                                        'school_holiday' => 'warning',
                                                        'exam_period' => 'info',
                                                        'special_event' => 'success'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $typeColors[$holiday->type] ?? 'secondary' }}-subtle text-{{ $typeColors[$holiday->type] ?? 'secondary' }}">
                                                    {{ str_replace('_', ' ', ucfirst($holiday->type)) }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($holiday->start_date)->format('d M Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($holiday->end_date)->format('d M Y') }}</td>
                                            <td>
                                                @php
                                                    $start = \Carbon\Carbon::parse($holiday->start_date);
                                                    $end = \Carbon\Carbon::parse($holiday->end_date);
                                                    $days = $start->diffInDays($end) + 1;
                                                @endphp
                                                {{ $days }} day(s)
                                            </td>
                                            <td>
                                                @if($holiday->affects_timetable)
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" onclick="viewHoliday({{ $holiday->id }})" title="View Details">
                                                    <i class="ri-eye-line"></i>
                                                </button>
                                                @can('Edit holidays')
                                                <button class="btn btn-sm btn-outline-primary" onclick="editHoliday({{ $holiday->id }})" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                @endcan
                                                @can('Delete holidays')
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteHoliday({{ $holiday->id }})" title="Delete">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                                @endcan
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                {{ $holidays->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Holiday Modal --}}
<div class="modal fade" id="holidayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title text-white" id="holidayModalTitle">Add Holiday</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="holidayForm">
                    <input type="hidden" id="holidayId">
                    <div class="mb-3">
                        <label class="form-label">Holiday Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="holidayName" required placeholder="e.g., Christmas Break">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="holidayStartDate" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" id="holidayEndDate" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Holiday Type</label>
                        <select class="form-select" id="holidayType">
                            <option value="public_holiday">Public Holiday</option>
                            <option value="school_holiday">School Holiday</option>
                            <option value="exam_period">Exam Period</option>
                            <option value="special_event">Special Event</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="holidayAffectsTimetable" checked>
                            <label class="form-check-label">Affects Timetable (auto-cancel classes)</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="holidayDescription" rows="3" placeholder="Additional information about this holiday..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveHoliday()">Save Holiday</button>
            </div>
        </div>
    </div>
</div>


<script>
    function resetHolidayForm() {
        document.getElementById('holidayId').value = '';
        document.getElementById('holidayName').value = '';
        document.getElementById('holidayStartDate').value = '';
        document.getElementById('holidayEndDate').value = '';
        document.getElementById('holidayType').value = 'public_holiday';
        document.getElementById('holidayAffectsTimetable').checked = true;
        document.getElementById('holidayDescription').value = '';
        document.getElementById('holidayModalTitle').innerText = 'Add Holiday';
    }

    function saveHoliday() {
        const id = document.getElementById('holidayId').value;
        const data = {
            name: document.getElementById('holidayName').value,
            start_date: document.getElementById('holidayStartDate').value,
            end_date: document.getElementById('holidayEndDate').value,
            type: document.getElementById('holidayType').value,
            affects_timetable: document.getElementById('holidayAffectsTimetable').checked,
            description: document.getElementById('holidayDescription').value
        };

        if (!data.name || !data.start_date || !data.end_date) {
            Swal.fire('Error', 'Please fill all required fields', 'error');
            return;
        }

        const method = id ? 'PUT' : 'POST';
        const url = id ? `/holidays/${id}` : '/holidays';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Success', data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('holidayModal')).hide();
                location.reload();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    }

    function editHoliday(id) {
        fetch(`/holidays/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const holiday = data.holiday;
                    document.getElementById('holidayId').value = holiday.id;
                    document.getElementById('holidayName').value = holiday.name;
                    document.getElementById('holidayStartDate').value = holiday.start_date;
                    document.getElementById('holidayEndDate').value = holiday.end_date;
                    document.getElementById('holidayType').value = holiday.type;
                    document.getElementById('holidayAffectsTimetable').checked = holiday.affects_timetable;
                    document.getElementById('holidayDescription').value = holiday.description || '';
                    document.getElementById('holidayModalTitle').innerText = 'Edit Holiday';
                    new bootstrap.Modal(document.getElementById('holidayModal')).show();
                }
            });
    }

    function deleteHoliday(id) {
        Swal.fire({
            title: 'Delete Holiday?',
            text: 'This will also remove timetable overrides for this holiday!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/holidays/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Deleted!', data.message, 'success');
                        location.reload();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    }

    function viewHoliday(id) {
        fetch(`/holidays/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const holiday = data.holiday;
                    Swal.fire({
                        title: holiday.name,
                        html: `
                            <div class="text-start">
                                <p><strong>Type:</strong> ${holiday.type.replace('_', ' ').toUpperCase()}</p>
                                <p><strong>Period:</strong> ${holiday.start_date} to ${holiday.end_date}</p>
                                <p><strong>Duration:</strong> ${Math.ceil((new Date(holiday.end_date) - new Date(holiday.start_date)) / (1000 * 60 * 60 * 24)) + 1} days</p>
                                <p><strong>Affects Timetable:</strong> ${holiday.affects_timetable ? 'Yes' : 'No'}</p>
                                ${holiday.description ? `<hr><p><strong>Description:</strong><br>${holiday.description}</p>` : ''}
                            </div>
                        `,
                        icon: 'info'
                    });
                }
            });
    }
</script>
@endsection
