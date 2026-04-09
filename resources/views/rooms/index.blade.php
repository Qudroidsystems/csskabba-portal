{{-- resources/views/rooms/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">
                            <i class="ri-door-line me-2"></i>{{ $pagetitle }}
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Room Management</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-2"></i>
                    <strong>Error!</strong> There were some problems.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <ul class="mt-2 mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @can('Create rooms')
            <div class="row mb-3">
                <div class="col-lg-12">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roomModal" onclick="resetRoomForm()">
                        <i class="ri-add-line me-2"></i>Add New Room
                    </button>
                </div>
            </div>
            @endcan

            {{-- Room Statistics Cards --}}
            <div class="row">
                <div class="col-md-3">
                    <div class="card bg-primary bg-opacity-10 border-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Total Rooms</h6>
                                    <h3 class="mb-0">{{ $rooms->total() }}</h3>
                                </div>
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-primary bg-opacity-25 rounded-circle">
                                        <i class="ri-door-line text-primary fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success bg-opacity-10 border-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Classrooms</h6>
                                    <h3 class="mb-0">{{ $rooms->where('type', 'classroom')->count() }}</h3>
                                </div>
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-success bg-opacity-25 rounded-circle">
                                        <i class="ri-school-line text-success fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info bg-opacity-10 border-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Laboratories</h6>
                                    <h3 class="mb-0">{{ $rooms->where('type', 'laboratory')->count() }}</h3>
                                </div>
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-info bg-opacity-25 rounded-circle">
                                        <i class="ri-flask-line text-info fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning bg-opacity-10 border-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Total Capacity</h6>
                                    <h3 class="mb-0">{{ $rooms->sum('capacity') }}</h3>
                                </div>
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-warning bg-opacity-25 rounded-circle">
                                        <i class="ri-group-line text-warning fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rooms List --}}
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ri-building-line me-2"></i>All Rooms
                            </h5>
                            <div class="d-flex gap-2">
                                <input type="text" id="searchRoom" class="form-control form-control-sm" placeholder="Search rooms..." style="width: 250px;">
                                <select id="filterRoomType" class="form-select form-select-sm" style="width: 150px;">
                                    <option value="">All Types</option>
                                    <option value="classroom">Classroom</option>
                                    <option value="laboratory">Laboratory</option>
                                    <option value="auditorium">Auditorium</option>
                                    <option value="library">Library</option>
                                    <option value="sports">Sports</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="roomsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Room Code</th>
                                            <th>Room Name</th>
                                            <th>Type</th>
                                            <th>Capacity</th>
                                            <th>Building/Floor</th>
                                            <th>Facilities</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rooms as $room)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ $room->room_code }}</span>
                                            </td>
                                            <td class="fw-medium">
                                                <i class="ri-door-line me-1 text-muted"></i>
                                                {{ $room->room_name }}
                                            </td>
                                            <td>
                                                @php
                                                    $typeColors = [
                                                        'classroom' => 'success',
                                                        'laboratory' => 'info',
                                                        'auditorium' => 'warning',
                                                        'library' => 'secondary',
                                                        'sports' => 'danger',
                                                        'other' => 'dark'
                                                    ];
                                                    $typeIcons = [
                                                        'classroom' => 'ri-school-line',
                                                        'laboratory' => 'ri-flask-line',
                                                        'auditorium' => 'ri-mic-line',
                                                        'library' => 'ri-book-open-line',
                                                        'sports' => 'ri-basketball-line',
                                                        'other' => 'ri-building-line'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $typeColors[$room->type] ?? 'secondary' }}-subtle text-{{ $typeColors[$room->type] ?? 'secondary' }}">
                                                    <i class="{{ $typeIcons[$room->type] ?? 'ri-building-line' }} me-1"></i>
                                                    {{ ucfirst($room->type) }}
                                                </span>
                                            </td>
                                            <td>
                                                <i class="ri-group-line me-1 text-muted"></i>
                                                {{ $room->capacity }}
                                             </td>
                                            <td>
                                                @if($room->building)
                                                    <i class="ri-building-line me-1"></i>{{ $room->building }}
                                                    @if($room->floor)
                                                        <br><small class="text-muted"><i class="ri-stairs-line me-1"></i>{{ $room->floor }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                             </td>
                                            <td>
                                                @if($room->facilities)
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach(array_slice($room->facilities, 0, 2) as $facility)
                                                            <span class="badge bg-light text-dark">
                                                                <i class="ri-checkbox-circle-line me-1 text-success"></i>
                                                                {{ $facility }}
                                                            </span>
                                                        @endforeach
                                                        @if(count($room->facilities) > 2)
                                                            <span class="badge bg-light text-dark">
                                                                +{{ count($room->facilities) - 2 }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                             </td>
                                            <td>
                                                @if($room->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                             </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewRoom({{ $room->id }})" title="View Details">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="viewRoomSchedule({{ $room->id }})" title="View Schedule">
                                                        <i class="ri-calendar-line"></i>
                                                    </button>
                                                    @can('Edit rooms')
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editRoom({{ $room->id }})" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    @endcan
                                                    @can('Delete rooms')
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteRoom({{ $room->id }})" title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    @endcan
                                                    @can('Manage room bookings')
                                                    <button class="btn btn-sm btn-outline-success" onclick="bookRoom({{ $room->id }})" title="Book Room">
                                                        <i class="ri-calendar-check-line"></i>
                                                    </button>
                                                    @endcan
                                                </div>
                                             </td>
                                        </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="ri-information-line ri-2x d-block mb-2"></i>
                                                    No rooms found. Click "Add New Room" to create one.
                                                 </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                {{ $rooms->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add/Edit Room Modal --}}
<div class="modal fade" id="roomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title text-white" id="roomModalTitle">Add New Room</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="roomForm">
                    <input type="hidden" id="roomId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Room Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="roomCode" placeholder="e.g., RM101, LAB01" required>
                            <small class="text-muted">Unique identifier for the room</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Room Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="roomName" placeholder="e.g., Room 101, Science Laboratory" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Room Type</label>
                            <select class="form-select" id="roomType">
                                <option value="classroom">📚 Classroom</option>
                                <option value="laboratory">🔬 Laboratory</option>
                                <option value="auditorium">🎤 Auditorium</option>
                                <option value="library">📖 Library</option>
                                <option value="sports">⚽ Sports Facility</option>
                                <option value="other">🏢 Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" id="roomCapacity" value="30" min="1">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Building</label>
                            <input type="text" class="form-control" id="roomBuilding" placeholder="e.g., Main Building, Science Block">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Floor</label>
                            <input type="text" class="form-control" id="roomFloor" placeholder="e.g., Ground Floor, 2nd Floor">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Facilities & Equipment</label>
                        <select class="form-select" id="roomFacilities" multiple size="4">
                            <option value="Projector">📽️ Projector</option>
                            <option value="Smartboard">📱 Smartboard / Interactive Board</option>
                            <option value="AC">❄️ Air Conditioner</option>
                            <option value="Whiteboard">📝 Whiteboard</option>
                            <option value="Computer">💻 Computer</option>
                            <option value="WiFi">📶 WiFi</option>
                            <option value="Microphone">🎤 Microphone / Sound System</option>
                            <option value="Lab Equipment">🧪 Laboratory Equipment</option>
                            <option value="Sports Equipment">🏀 Sports Equipment</option>
                            <option value="Library Books">📚 Library Books</option>
                        </select>
                        <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="roomIsActive" checked>
                                <label class="form-check-label">Active</label>
                                <div class="form-text">Inactive rooms won't appear in booking options</div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="roomNotes" rows="2" placeholder="Any special notes about this room..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveRoom()">
                    <i class="ri-save-line me-2"></i>Save Room
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Room Schedule Modal --}}
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title text-white" id="scheduleModalTitle">Room Schedule</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="scheduleTable">
                        <thead class="table-dark">
                            <tr id="scheduleHeader">
                                <th>Period / Time</th>
                            </tr>
                        </thead>
                        <tbody id="scheduleBody">
                            <tr><td colspan="6" class="text-center py-4">Loading schedule...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Book Room Modal --}}
<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title text-white">Book Room</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="bookingForm">
                    <input type="hidden" id="bookingRoomId">
                    <div class="mb-3">
                        <label class="form-label">Room</label>
                        <input type="text" class="form-control" id="bookingRoomName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" id="bookingDate" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="bookingStartTime" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-control" id="bookingEndTime" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Purpose</label>
                        <textarea class="form-control" id="bookingPurpose" rows="3" placeholder="What is this booking for?" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Recurring</label>
                        <select class="form-select" id="bookingRecurring">
                            <option value="none">One-time booking</option>
                            <option value="weekly">Weekly (every week on this day)</option>
                            <option value="biweekly">Bi-weekly (every other week)</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitBooking()">
                    <i class="ri-calendar-check-line me-2"></i>Confirm Booking
                </button>
            </div>
        </div>
    </div>
</div>


<script>
    let currentRoomId = null;
    let currentScheduleData = null;

    // Room CRUD Functions
    function resetRoomForm() {
        document.getElementById('roomId').value = '';
        document.getElementById('roomCode').value = '';
        document.getElementById('roomName').value = '';
        document.getElementById('roomType').value = 'classroom';
        document.getElementById('roomCapacity').value = '30';
        document.getElementById('roomBuilding').value = '';
        document.getElementById('roomFloor').value = '';
        document.getElementById('roomIsActive').checked = true;
        document.getElementById('roomNotes').value = '';

        // Clear facilities selection
        const facilitiesSelect = document.getElementById('roomFacilities');
        for(let i = 0; i < facilitiesSelect.options.length; i++) {
            facilitiesSelect.options[i].selected = false;
        }

        document.getElementById('roomModalTitle').innerText = 'Add New Room';
    }

    function saveRoom() {
        const id = document.getElementById('roomId').value;
        const facilities = Array.from(document.getElementById('roomFacilities').selectedOptions).map(opt => opt.value);

        const data = {
            room_code: document.getElementById('roomCode').value,
            room_name: document.getElementById('roomName').value,
            type: document.getElementById('roomType').value,
            capacity: parseInt(document.getElementById('roomCapacity').value),
            building: document.getElementById('roomBuilding').value,
            floor: document.getElementById('roomFloor').value,
            facilities: facilities,
            is_active: document.getElementById('roomIsActive').checked,
            notes: document.getElementById('roomNotes').value
        };

        if (!data.room_code || !data.room_name) {
            Swal.fire('Error', 'Please fill in all required fields', 'error');
            return;
        }

        const method = id ? 'PUT' : 'POST';
        const url = id ? `/rooms/${id}` : '/rooms';

        Swal.fire({
            title: 'Saving...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                Swal.fire('Success', data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('roomModal')).hide();
                location.reload();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire('Error', 'Failed to save room: ' + error.message, 'error');
        });
    }

    function editRoom(id) {
        currentRoomId = id;
        document.getElementById('roomModalTitle').innerText = 'Edit Room';

        fetch(`/rooms/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const room = data.room;
                    document.getElementById('roomId').value = room.id;
                    document.getElementById('roomCode').value = room.room_code;
                    document.getElementById('roomName').value = room.room_name;
                    document.getElementById('roomType').value = room.type;
                    document.getElementById('roomCapacity').value = room.capacity;
                    document.getElementById('roomBuilding').value = room.building || '';
                    document.getElementById('roomFloor').value = room.floor || '';
                    document.getElementById('roomIsActive').checked = room.is_active;
                    document.getElementById('roomNotes').value = room.notes || '';

                    // Set facilities
                    if (room.facilities) {
                        const options = document.getElementById('roomFacilities').options;
                        for (let i = 0; i < options.length; i++) {
                            options[i].selected = room.facilities.includes(options[i].value);
                        }
                    }

                    new bootstrap.Modal(document.getElementById('roomModal')).show();
                }
            });
    }

    function deleteRoom(id) {
        Swal.fire({
            title: 'Delete Room?',
            text: 'This action cannot be undone! The room must not be in use.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch(`/rooms/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    Swal.close();
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

    function viewRoom(id) {
        fetch(`/rooms/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const room = data.room;
                    Swal.fire({
                        title: `<i class="ri-door-line me-2"></i>${room.room_name}`,
                        html: `
                            <div class="text-start">
                                <div class="mb-3">
                                    <strong>Room Code:</strong> <span class="badge bg-primary">${room.room_code}</span>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <strong>Type:</strong> ${room.type}
                                    </div>
                                    <div class="col-6">
                                        <strong>Capacity:</strong> ${room.capacity} students
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <strong>Building:</strong> ${room.building || 'N/A'}
                                    </div>
                                    <div class="col-6">
                                        <strong>Floor:</strong> ${room.floor || 'N/A'}
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <strong>Facilities:</strong><br>
                                    ${room.facilities && room.facilities.length ?
                                        room.facilities.map(f => `<span class="badge bg-light text-dark me-1">✓ ${f}</span>`).join('') :
                                        '<span class="text-muted">None listed</span>'}
                                </div>
                                <div class="mb-2">
                                    <strong>Status:</strong>
                                    ${room.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'}
                                </div>
                                ${room.notes ? `<div class="mb-2"><strong>Notes:</strong><br>${room.notes}</div>` : ''}
                            </div>
                        `,
                        width: 500,
                        icon: 'info'
                    });
                }
            });
    }

    function viewRoomSchedule(id) {
        fetch(`/rooms/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const room = data.room;
                    const bookings = data.current_bookings || [];

                    document.getElementById('scheduleModalTitle').innerHTML = `<i class="ri-door-line me-2"></i>${room.room_name} - Weekly Schedule`;

                    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

                    // Build header
                    let headerHtml = '<th>Period / Time</th>';
                    days.forEach(day => {
                        headerHtml += `<th class="text-center">${day}</th>`;
                    });
                    document.getElementById('scheduleHeader').innerHTML = headerHtml;

                    // Group bookings by period and day
                    const periods = ['Period 1 (7:30-8:10)', 'Period 2 (8:10-8:50)', 'Period 3 (9:10-9:50)', 'Period 4 (9:50-10:30)', 'Period 5 (11:10-11:50)', 'Period 6 (11:50-12:30)'];

                    let bodyHtml = '';
                    periods.forEach(period => {
                        bodyHtml += `<tr><td class="fw-semibold">${period}</td>`;
                        days.forEach(day => {
                            const booking = bookings.find(b => b.day === day && b.period?.name === period);
                            if (booking) {
                                bodyHtml += `<td class="text-center">
                                    <span class="fw-semibold">${booking.subject?.subject || 'Booked'}</span><br>
                                    <small class="text-muted">${booking.teacher?.name || ''}</small>
                                    ${booking.room ? `<br><small><i class="ri-door-line"></i> ${booking.room}</small>` : ''}
                                </td>`;
                            } else {
                                bodyHtml += `<td class="text-center text-muted">—</td>`;
                            }
                        });
                        bodyHtml += `</tr>`;
                    });

                    document.getElementById('scheduleBody').innerHTML = bodyHtml;
                    new bootstrap.Modal(document.getElementById('scheduleModal')).show();
                }
            });
    }

    function bookRoom(id) {
        fetch(`/rooms/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('bookingRoomId').value = id;
                    document.getElementById('bookingRoomName').value = data.room.room_name;
                    document.getElementById('bookingDate').value = '';
                    document.getElementById('bookingStartTime').value = '';
                    document.getElementById('bookingEndTime').value = '';
                    document.getElementById('bookingPurpose').value = '';
                    document.getElementById('bookingRecurring').value = 'none';

                    new bootstrap.Modal(document.getElementById('bookingModal')).show();
                }
            });
    }

    function submitBooking() {
        const data = {
            room_id: document.getElementById('bookingRoomId').value,
            date: document.getElementById('bookingDate').value,
            start_time: document.getElementById('bookingStartTime').value,
            end_time: document.getElementById('bookingEndTime').value,
            purpose: document.getElementById('bookingPurpose').value,
            recurring_type: document.getElementById('bookingRecurring').value
        };

        if (!data.date || !data.start_time || !data.end_time || !data.purpose) {
            Swal.fire('Error', 'Please fill in all required fields', 'error');
            return;
        }

        Swal.fire({
            title: 'Checking Availability...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // First check availability
        fetch('/rooms/availability/check?' + new URLSearchParams({
            room_id: data.room_id,
            date: data.date,
            start_time: data.start_time,
            end_time: data.end_time
        }))
        .then(res => res.json())
        .then(availability => {
            if (!availability.available) {
                Swal.close();
                Swal.fire('Not Available', 'This room is already booked for the selected time slot', 'warning');
                return;
            }

            // Proceed with booking
            fetch('/rooms/book', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                Swal.close();
                if (result.success) {
                    Swal.fire('Success!', 'Room booked successfully', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            });
        });
    }

    // Search and Filter
    document.getElementById('searchRoom')?.addEventListener('keyup', function() {
        filterRooms();
    });

    document.getElementById('filterRoomType')?.addEventListener('change', function() {
        filterRooms();
    });

    function filterRooms() {
        const searchTerm = document.getElementById('searchRoom').value.toLowerCase();
        const typeFilter = document.getElementById('filterRoomType').value;
        const rows = document.querySelectorAll('#roomsTable tbody tr');

        rows.forEach(row => {
            const roomCode = row.cells[0]?.innerText.toLowerCase() || '';
            const roomName = row.cells[1]?.innerText.toLowerCase() || '';
            const roomType = row.cells[2]?.innerText.toLowerCase() || '';

            const matchesSearch = roomCode.includes(searchTerm) || roomName.includes(searchTerm);
            const matchesType = !typeFilter || roomType.includes(typeFilter);

            row.style.display = matchesSearch && matchesType ? '' : 'none';
        });
    }
</script>

<style>
    .btn-group {
        gap: 5px;
    }
    .btn-group .btn {
        border-radius: 4px !important;
    }
    #roomsTable tbody tr {
        transition: all 0.2s ease;
    }
    #roomsTable tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }
    .badge i {
        font-size: 12px;
    }
</style>

@endsection
