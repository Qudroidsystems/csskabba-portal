                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="mb-0">Previous School Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Last School Attended</label>
                                                <input type="text" id="last_school" name="last_school" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Last Class Attended</label>
                                                <input type="text" id="last_class" name="last_class" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Reason for Leaving</label>
                                                <textarea id="reason_for_leaving" name="reason_for_leaving" class="form-control" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-primary-gradient" id="add-btn">Register Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Student Modal -->
        <div id="editStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient">
                        <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editStudentForm" enctype="multipart/form-data" method="POST" action="{{ route('student.update', ':id') }}" data-base-action="{{ route('student.update', ':id') }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-body p-4">
                            <input type="hidden" id="editStudentId" name="id">
                            <div class="progress-steps mb-4">
                                <div class="step">1</div><div class="step">2</div><div class="step">3</div><div class="step">4</div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0">Academic Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Admission Number Mode</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="admissionMode" id="editAdmissionAuto" value="auto" onchange="toggleAdmissionInput('edit')">
                                                        <label class="form-check-label" for="editAdmissionAuto">Auto Generate</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="admissionMode" id="editAdmissionManual" value="manual" onchange="toggleAdmissionInput('edit')">
                                                        <label class="form-check-label" for="editAdmissionManual">Manual Entry</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Admission Number</label>
                                                <div class="input-group">
                                                    <select class="form-control" id="editAdmissionYear" name="admissionYear" onchange="updateAdmissionNumber('edit')">
                                                        @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                    <input type="text" id="editAdmissionNo" name="admissionNo" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Admission Date</label>
                                                <input type="date" id="editAdmissionDate" name="admissionDate" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Class</label>
                                                <select id="editSchoolclassid" name="schoolclassid" class="form-control" required>
                                                    <option value="">Select Class</option>
                                                    @foreach($schoolclasses as $class)
                                                        <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Term</label>
                                                        <select id="editTermid" name="termid" class="form-control" required>
                                                            <option value="">Select Term</option>
                                                            @foreach($schoolterms as $term)
                                                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Session</label>
                                                        <select id="editSessionid" name="sessionid" class="form-control" required>
                                                            <option value="">Select Session</option>
                                                            @foreach($schoolsessions as $session)
                                                                <option value="{{ $session->id }}">{{ $session->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Student Status</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="statusId" id="editStatusOld" value="1"><label for="editStatusOld">Old Student</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="statusId" id="editStatusNew" value="2"><label for="editStatusNew">New Student</label></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Activity Status</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="student_status" id="editStatusActive" value="Active"><label for="editStatusActive">Active</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="student_status" id="editStatusInactive" value="Inactive"><label for="editStatusInactive">Inactive</label></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Student Category</label>
                                                <select id="editStudentCategory" name="student_category" class="form-control" required>
                                                    <option value="">Select Category</option>
                                                    <option value="Day">Day Student</option>
                                                    <option value="Boarding">Boarding Student</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">Personal Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3 text-center">
                                                <img id="editStudentAvatar" src="{{ asset('theme/layouts/assets/media/avatars/blank.png') }}" alt="Avatar" class="rounded-circle mb-2" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #0dcaf0; cursor: pointer;" onclick="document.getElementById('editAvatar').click()">
                                                <div><label for="editAvatar" class="btn btn-outline-info btn-sm">Choose Photo</label><input type="file" id="editAvatar" name="avatar" class="d-none" accept="image/*" onchange="previewImage(this, 'editStudentAvatar')"></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Title</label>
                                                        <select id="editTitle" name="title" class="form-control">
                                                            <option value="">Select</option>
                                                            <option value="Master">Master</option>
                                                            <option value="Miss">Miss</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label class="form-label">Last Name</label>
                                                        <input type="text" id="editLastname" name="lastname" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">First Name</label>
                                                        <input type="text" id="editFirstname" name="firstname" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Other Names</label>
                                                        <input type="text" id="editOthername" name="othername" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Gender</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="gender" id="editGenderMale" value="Male"><label for="editGenderMale">Male</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="gender" id="editGenderFemale" value="Female"><label for="editGenderFemale">Female</label></div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Date of Birth</label>
                                                        <input type="date" id="editDOB" name="dateofbirth" class="form-control" required onchange="calculateAge(this.value, 'editAgeInput')">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Age</label>
                                                        <input type="number" id="editAgeInput" name="age" class="form-control" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Place of Birth</label>
                                                <input type="text" id="editPlaceofbirth" name="placeofbirth" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Phone Number</label>
                                                <input type="text" id="editPhoneNumber" name="phone_number" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" id="editEmail" name="email" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Future Ambition</label>
                                                <textarea id="editFutureAmbition" name="future_ambition" class="form-control" rows="2" required></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Permanent Address</label>
                                                <textarea id="editPermanentAddress" name="permanent_address" class="form-control" rows="2" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">Additional Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Nationality</label>
                                                <input type="text" id="editNationality" name="nationality" class="form-control" required>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">State of Origin</label>
                                                        <select id="editState" name="state" class="form-control" required>
                                                            <option value="">Select State</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">LGA</label>
                                                        <select id="editLocal" name="local" class="form-control" required disabled>
                                                            <option value="">Select LGA</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">City</label>
                                                        <input type="text" id="editCity" name="city" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Religion</label>
                                                        <select id="editReligion" name="religion" class="form-control" required>
                                                            <option value="">Select</option>
                                                            <option value="Christianity">Christianity</option>
                                                            <option value="Islam">Islam</option>
                                                            <option value="Others">Others</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Blood Group</label>
                                                        <select id="editBloodGroup" name="blood_group" class="form-control">
                                                            <option value="">Select</option>
                                                            <option>A+</option><option>A-</option><option>B+</option><option>B-</option>
                                                            <option>AB+</option><option>AB-</option><option>O+</option><option>O-</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Mother Tongue</label>
                                                        <input type="text" id="editMotherTongue" name="mother_tongue" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">NIN Number</label>
                                                        <input type="text" id="editNinNumber" name="nin_number" class="form-control" maxlength="11">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">School House</label>
                                                        <select id="editSchoolHouse" name="schoolhouseid" class="form-control" required>
                                                            <option value="">Select House</option>
                                                            @foreach($schoolhouses as $h)
                                                                <option value="{{ $h->id }}">{{ $h->house }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">Parent / Guardian Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Father's Name</label>
                                                <input type="text" id="editFatherName" name="father_name" class="form-control">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Father's Phone</label>
                                                        <input type="text" id="editFatherPhone" name="father_phone" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Father's Occupation</label>
                                                        <input type="text" id="editFatherOccupation" name="father_occupation" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Father's City</label>
                                                <input type="text" id="editFatherCity" name="father_city" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Mother's Name</label>
                                                <input type="text" id="editMotherName" name="mother_name" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Mother's Phone</label>
                                                <input type="text" id="editMotherPhone" name="mother_phone" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Parent Email</label>
                                                <input type="email" id="editParentEmail" name="parent_email" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Parent Address</label>
                                                <textarea id="editParentAddress" name="parent_address" class="form-control" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="mb-0">Previous School Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Last School Attended</label>
                                                <input type="text" id="editLastSchool" name="last_school" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Last Class Attended</label>
                                                <input type="text" id="editLastClass" name="last_class" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Reason for Leaving</label>
                                                <textarea id="editReasonForLeaving" name="reason_for_leaving" class="form-control" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-primary-gradient" id="edit-btn">Update Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Student Modal -->
        <div id="viewStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fas fa-graduation-cap fa-2x"></i>
                            <div>
                                <h5 class="modal-title mb-0">Student Profile</h5>
                                <small style="color: rgba(255,255,255,0.7)">Complete student information</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="bg-light p-4 border-bottom">
                            <div class="d-flex align-items-center gap-4">
                                <div class="position-relative">
                                    <img id="viewStudentPhoto" src="{{ asset('theme/layouts/assets/media/avatars/blank.png') }}" alt="Student Photo" class="rounded-circle border border-3 shadow" style="width: 110px; height: 110px; object-fit: cover;">
                                    <span id="studentStatusIndicator" class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white" style="width: 18px; height: 18px;"></span>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-2" id="viewFullName" style="color: var(--sm-primary)">—</h3>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="badge bg-primary px-3 py-2"><i class="fas fa-id-card me-1"></i><span id="viewAdmissionNumber">—</span></span>
                                        <span class="badge bg-info px-3 py-2"><i class="fas fa-school me-1"></i><span id="viewClassDisplay">—</span></span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-4 text-muted">
                                        <div><i class="fas fa-calendar-alt me-1"></i>Admitted: <span id="viewAdmittedDate">—</span></div>
                                        <div><i class="fas fa-venus-mars me-1"></i><span id="viewGenderText">—</span></div>
                                        <div><i class="fas fa-birthday-cake me-1"></i>Age: <span id="viewAge">—</span> yrs</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 pt-3">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vPersonal"><i class="fas fa-user-circle me-1"></i>Personal</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#vAcademic"><i class="fas fa-graduation-cap me-1"></i>Academic</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#vFamily"><i class="fas fa-users me-1"></i>Family</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#vAdditional"><i class="fas fa-info-circle me-1"></i>Additional</a></li>
                            </ul>
                        </div>
                        <div class="tab-content p-4">
                            <!-- Personal Tab -->
                            <div class="tab-pane fade show active" id="vPersonal">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border mb-3">
                                            <div class="card-header bg-light"><h6 class="mb-0">Basic Information</h6></div>
                                            <div class="card-body">
                                                <table class="table table-sm table-borderless">
                                                    <tr><th width="40%">Full Name:</th><td id="viewFullNameDetail">—</td></tr>
                                                    <tr><th>Title:</th><td id="viewTitle">—</td></tr>
                                                    <tr><th>Date of Birth:</th><td><span id="viewDOB">—</span> (<span id="viewAgeDetail">—</span> yrs)</td></tr>
                                                    <tr><th>Place of Birth:</th><td id="viewPlaceOfBirth">—</td></tr>
                                                    <tr><th>Gender:</th><td id="viewGenderDetail">—</td></tr>
                                                    <tr><th>Blood Group:</th><td id="viewBloodGroupDetail">—</td></tr>
                                                    <tr><th>Religion:</th><td id="viewReligionDetail">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border mb-3">
                                            <div class="card-header bg-light"><h6 class="mb-0">Contact</h6></div>
                                            <div class="card-body">
                                                <table class="table table-sm table-borderless">
                                                    <tr><th width="40%">Phone:</th><td id="viewPhoneNumber">—</td></tr>
                                                    <tr><th>Email:</th><td id="viewEmailAddress">—</td></tr>
                                                    <tr><th>Address:</th><td id="viewPermanentAddress">—</td></tr>
                                                    <tr><th>City:</th><td id="viewCity">—</td></tr>
                                                    <tr><th>State:</th><td id="viewStateOrigin">—</td></tr>
                                                    <tr><th>LGA:</th><td id="viewLGA">—</td></tr>
                                                    <tr><th>Nationality:</th><td id="viewNationality">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card border">
                                            <div class="card-header bg-light"><h6 class="mb-0">Future Ambition</h6></div>
                                            <div class="card-body"><p class="mb-0 fst-italic" id="viewFutureAmbition">—</p></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Academic Tab -->
                            <div class="tab-pane fade" id="vAcademic">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border mb-3">
                                            <div class="card-header bg-light"><h6 class="mb-0">Current Academic Status</h6></div>
                                            <div class="card-body">
                                                <table class="table table-sm table-borderless">
                                                    <tr><th width="40%">Admission No:</th><td class="fw-bold text-primary" id="viewAdmissionNo">—</td></tr>
                                                    <tr><th>Admission Date:</th><td id="viewAdmissionDate">—</td></tr>
                                                    <tr><th>Class:</th><td><span class="badge bg-info" id="viewCurrentClass">—</span></td></tr>
                                                    <tr><th>Arm:</th><td id="viewArm">—</td></tr>
                                                    <tr><th>Category:</th><td><span class="badge bg-secondary" id="viewStudentCategory">—</span></td></tr>
                                                    <tr><th>Status:</th><td id="viewStudentStatus">—</td></tr>
                                                    <tr><th>School House:</th><td id="viewSchoolHouse">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border mb-3">
                                            <div class="card-header bg-light"><h6 class="mb-0">Current Term</h6></div>
                                            <div class="card-body">
                                                <div id="currentTermAlert" class="mb-3"></div>
                                                <table class="table table-sm table-borderless">
                                                    <tr><th width="40%">Term:</th><td id="viewCurrentTerm">—</td></tr>
                                                    <tr><th>Session:</th><td id="viewCurrentSession">—</td></tr>
                                                    <tr><th>Term Status:</th><td id="viewCurrentTermStatus">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="card border">
                                            <div class="card-header bg-light"><h6 class="mb-0">Previous School</h6></div>
                                            <div class="card-body">
                                                <table class="table table-sm table-borderless">
                                                    <tr><th width="40%">Last School:</th><td id="viewLastSchool">—</td></tr>
                                                    <tr><th>Last Class:</th><td id="viewLastClass">—</td></tr>
                                                    <tr><th>Reason:</th><td id="viewReasonForLeaving">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Family Tab -->
                            <div class="tab-pane fade" id="vFamily">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border mb-3">
                                            <div class="card-header bg-light"><h6 class="mb-0">Father's Information</h6></div>
                                            <div class="card-body">
                                                <table class="table table-sm table-borderless">
                                                    <tr><th width="40%">Name:</th><td class="fw-semibold" id="viewFatherFullName">—</td></tr>
                                                    <tr><th>Phone:</th><td><span id="viewFatherPhone">—</span></td></tr>
                                                    <tr><th>Occupation:</th><td id="viewFatherOccupation">—</td></tr>
                                                    <tr><th>City:</th><td id="viewFatherCityState">—</td></tr>
                                                    <tr><th>Email:</th><td id="viewFatherEmail">—</td></tr>
                                                    <tr><th>Address:</th><td id="viewFatherAddress">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border mb-3">
                                            <div class="card-header bg-light"><h6 class="mb-0">Mother's Information</h6></div>
                                            <div class="card-body">
                                                <table class="table table-sm table-borderless">
                                                    <tr><th width="40%">Name:</th><td class="fw-semibold" id="viewMotherFullName">—</td></tr>
                                                    <tr><th>Phone:</th><td><span id="viewMotherPhone">—</span></td></tr>
                                                    <tr><th>Occupation:</th><td id="viewMotherOccupation">—</td></tr>
                                                    <tr><th>Email:</th><td id="viewMotherEmail">—</td></tr>
                                                    <tr><th>Address:</th><td id="viewMotherAddress">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Additional Tab -->
                            <div class="tab-pane fade" id="vAdditional">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border">
                                            <div class="card-header bg-light"><h6 class="mb-0">Medical & Personal</h6></div>
                                            <div class="card-body">
                                                <table class="table table-sm table-borderless">
                                                    <tr><th width="40%">Blood Group:</th><td id="viewBloodGroupAdditional">—</td></tr>
                                                    <tr><th>NIN Number:</th><td id="viewNIN">—</td></tr>
                                                    <tr><th>Mother Tongue:</th><td id="viewMotherTongue">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="editStudentFromView()">Edit Student</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
// Nigerian States and LGAs
const NIGERIAN_STATES = [
    { name: "Abia", lgas: ["Aba North", "Aba South", "Arochukwu", "Bende", "Ikwuano", "Isiala Ngwa North", "Isiala Ngwa South", "Isuikwuato", "Obi Ngwa", "Ohafia", "Osisioma", "Ugwunagbo", "Ukwa East", "Ukwa West", "Umuahia North", "Umuahia South", "Umu Nneochi"] },
    { name: "Adamawa", lgas: ["Demsa", "Fufure", "Ganye", "Gayuk", "Gombi", "Grie", "Hong", "Jada", "Lamurde", "Madagali", "Maiha", "Mayo Belwa", "Michika", "Mubi North", "Mubi South", "Numan", "Shelleng", "Song", "Toungo", "Yola North", "Yola South"] },
    { name: "Lagos", lgas: ["Agege", "Ajeromi-Ifelodun", "Alimosho", "Amuwo-Odofin", "Apapa", "Badagry", "Epe", "Eti Osa", "Ibeju-Lekki", "Ifako-Ijaiye", "Ikeja", "Ikorodu", "Kosofe", "Lagos Island", "Lagos Mainland", "Mushin", "Ojo", "Oshodi-Isolo", "Shomolu", "Surulere"] },
    { name: "FCT", lgas: ["Abaji", "Bwari", "Gwagwalada", "Kuje", "Kwali", "Municipal Area Council"] }
];

// Add more states as needed - add all 37 states for completeness

// Global Variables
let currentPage = 1;
let perPage = 25;
let totalPages = 1;
let totalStudents = 0;
let currentView = 'table';
let selectedStudents = new Set();

// Populate State Dropdowns
function populateStateDropdown(selectId, lgaId) {
    const stateSelect = document.getElementById(selectId);
    if (!stateSelect) return;

    stateSelect.innerHTML = '<option value="">Select State</option>';
    NIGERIAN_STATES.forEach(state => {
        const option = document.createElement('option');
        option.value = state.name;
        option.textContent = state.name;
        stateSelect.appendChild(option);
    });

    const lgaSelect = document.getElementById(lgaId);
    if (lgaSelect) {
        lgaSelect.innerHTML = '<option value="">Select LGA</option>';
        lgaSelect.disabled = true;

        stateSelect.onchange = function() {
            lgaSelect.innerHTML = '<option value="">Select LGA</option>';
            const selectedState = NIGERIAN_STATES.find(s => s.name === this.value);
            if (selectedState) {
                lgaSelect.disabled = false;
                selectedState.lgas.forEach(lga => {
                    const opt = document.createElement('option');
                    opt.value = lga;
                    opt.textContent = lga;
                    lgaSelect.appendChild(opt);
                });
            } else {
                lgaSelect.disabled = true;
            }
        };
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    populateStateDropdown('addState', 'addLocal');
    populateStateDropdown('editState', 'editLocal');

    // Set default admission number
    updateAdmissionNumber();
    updateAdmissionNumber('edit');

    // Load initial data
    fetchStudents();

    // Setup event listeners
    setupEventListeners();
});

// Fetch Students from Server
async function fetchStudents() {
    showLoading();

    const search = document.getElementById('search-input')?.value || '';
    const classId = document.getElementById('schoolclass-filter')?.value || 'all';
    const termId = document.getElementById('term-filter')?.value || 'all';
    const sessionId = document.getElementById('session-filter')?.value || 'all';

    try {
        const params = new URLSearchParams({
            page: currentPage,
            per_page: perPage,
            search: search,
            class_id: classId,
            term_id: termId,
            session_id: sessionId
        });

        const response = await axios.get(`/students/optimized?${params}`);

        if (response.data.success) {
            const data = response.data.data;
            currentPage = data.current_page;
            totalPages = data.last_page;
            totalStudents = data.total;

            updatePaginationUI(data);

            if (currentView === 'table') {
                renderTableView(data.data);
            } else {
                renderCardView(data.data);
            }

            document.getElementById('totalStudents').textContent = totalStudents;
        } else {
            throw new Error(response.data.message || 'Failed to fetch students');
        }
    } catch (error) {
        console.error('Error fetching students:', error);
        Swal.fire('Error', 'Failed to load students. Please try again.', 'error');
        document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Error loading students</td></tr>';
    } finally {
        hideLoading();
    }
}

// Render Table View
function renderTableView(students) {
    const tbody = document.getElementById('studentTableBody');
    if (!tbody) return;

    if (!students || students.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No students found</td></tr>';
        return;
    }

    tbody.innerHTML = students.map(student => `
        <tr>
            <td><input type="checkbox" class="student-checkbox" value="${student.id}"></td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-initials">${getInitials(student.firstname, student.lastname)}</div>
                    <div>
                        <div class="fw-semibold">${escapeHtml(student.lastname || '')} ${escapeHtml(student.firstname || '')}</div>
                        <small class="text-muted">${escapeHtml(student.admissionNo || 'N/A')}</small>
                    </div>
                </div>
            </td>
            <td>${escapeHtml(student.schoolclass || '')} ${escapeHtml(student.arm || '')}</td>
            <td><span class="badge ${student.student_status === 'Active' ? 'bg-success' : 'bg-secondary'}">${escapeHtml(student.student_status || 'Inactive')}</span></td>
            <td>${escapeHtml(student.gender || 'N/A')}</td>
            <td>${formatDate(student.created_at)}</td>
            <td>
                <button class="btn btn-sm btn-info view-student-btn" data-id="${student.id}"><i class="fas fa-eye"></i></button>
                <button class="btn btn-sm btn-warning edit-student-btn" data-id="${student.id}"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-danger delete-student-btn" data-id="${student.id}"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
    `).join('');

    attachTableEventListeners();
    updateCheckAllState();
}

// Render Card View
function renderCardView(students) {
    const container = document.getElementById('studentsCardsContainer');
    if (!container) return;

    if (!students || students.length === 0) {
        container.innerHTML = '<div class="col-12 text-center py-4">No students found</div>';
        return;
    }

    container.innerHTML = students.map(student => `
        <div class="col-md-4 col-lg-3 mb-4">
            <div class="student-profile-card">
                <div class="checkbox-container">
                    <input type="checkbox" class="student-checkbox" value="${student.id}">
                </div>
                <div class="card-header">
                    <div class="student-name">${escapeHtml(student.lastname || '')} ${escapeHtml(student.firstname || '')}</div>
                    <span class="student-admission">${escapeHtml(student.admissionNo || 'N/A')}</span>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge ${student.student_status === 'Active' ? 'bg-success' : 'bg-secondary'}">${escapeHtml(student.student_status || 'Inactive')}</span>
                    </div>
                    <div><strong>Class:</strong> ${escapeHtml(student.schoolclass || '')} ${escapeHtml(student.arm || '')}</div>
                    <div><strong>Gender:</strong> ${escapeHtml(student.gender || 'N/A')}</div>
                    <div class="action-buttons mt-3">
                        <button class="action-btn view-btn view-student-btn" data-id="${student.id}"><i class="fas fa-eye"></i> View</button>
                        <button class="action-btn edit-btn edit-student-btn" data-id="${student.id}"><i class="fas fa-edit"></i> Edit</button>
                        <button class="action-btn delete-btn delete-student-btn" data-id="${student.id}"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    attachCardEventListeners();
    updateCheckAllState();
}

// Helper Functions
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    } catch {
        return 'N/A';
    }
}

function getInitials(first, last) {
    return ((first?.[0] || '') + (last?.[0] || '')).toUpperCase() || 'ST';
}

function updateAdmissionNumber(prefix = '') {
    const yearSelect = document.getElementById(`${prefix}admissionYear`);
    const admissionInput = document.getElementById(`${prefix}admissionNo`);
    if (yearSelect && admissionInput) {
        const year = yearSelect.value;
        admissionInput.value = `TCC/${year}/0871`;
    }
}

function toggleAdmissionInput(prefix = '') {
    const autoRadio = document.getElementById(`${prefix}admissionAuto`);
    const manualRadio = document.getElementById(`${prefix}admissionManual`);
    const admissionInput = document.getElementById(`${prefix}admissionNo`);

    if (autoRadio && manualRadio && admissionInput) {
        admissionInput.readOnly = autoRadio.checked;
    }
}

function previewImage(input, targetId = 'addStudentAvatar') {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(targetId);
            if (img) img.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function calculateAge(dob, ageInputId) {
    if (!dob) return;
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    const ageInput = document.getElementById(ageInputId);
    if (ageInput) ageInput.value = age;
}

// Pagination UI
function updatePaginationUI(pagination) {
    document.getElementById('showingCount').textContent = pagination.from || 0;
    document.getElementById('toCount').textContent = pagination.to || 0;
    document.getElementById('totalCount').textContent = pagination.total || 0;

    const paginationUl = document.getElementById('pagination');
    if (!paginationUl) return;

    // Remove existing page items except prev/next
    const existingItems = paginationUl.querySelectorAll('.page-item:not(#prevPageLi):not(#nextPageLi)');
    existingItems.forEach(item => item.remove());

    if (totalPages <= 1) return;

    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    const nextLi = document.getElementById('nextPageLi');

    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = 'javascript:void(0);';
        a.textContent = i;
        a.onclick = () => {
            currentPage = i;
            fetchStudents();
        };
        li.appendChild(a);
        paginationUl.insertBefore(li, nextLi);
    }

    // Update prev/next buttons
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    if (prevBtn) {
        if (currentPage <= 1) {
            prevBtn.parentElement.classList.add('disabled');
        } else {
            prevBtn.parentElement.classList.remove('disabled');
            prevBtn.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    fetchStudents();
                }
            };
        }
    }
    if (nextBtn) {
        if (currentPage >= totalPages) {
            nextBtn.parentElement.classList.add('disabled');
        } else {
            nextBtn.parentElement.classList.remove('disabled');
            nextBtn.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    fetchStudents();
                }
            };
        }
    }
}

// Loading States
function showLoading() {
    document.getElementById('loadingState')?.classList.remove('d-none');
    document.getElementById('tableView')?.classList.add('d-none');
    document.getElementById('cardView')?.classList.add('d-none');
    document.getElementById('emptyState')?.classList.add('d-none');
}

function hideLoading() {
    document.getElementById('loadingState')?.classList.add('d-none');
    if (totalStudents > 0) {
        if (currentView === 'table') {
            document.getElementById('tableView')?.classList.remove('d-none');
        } else {
            document.getElementById('cardView')?.classList.remove('d-none');
        }
    } else {
        document.getElementById('emptyState')?.classList.remove('d-none');
    }
}

// Checkbox Selection
function updateCheckAllState() {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    const checked = document.querySelectorAll('.student-checkbox:checked');
    const checkAll = document.getElementById('checkAll');
    const checkAllTable = document.getElementById('checkAllTable');

    const allChecked = checkboxes.length > 0 && checkboxes.length === checked.length;
    if (checkAll) checkAll.checked = allChecked;
    if (checkAllTable) checkAllTable.checked = allChecked;

    const bulkDropdown = document.getElementById('bulkActionsDropdown');
    if (bulkDropdown) {
        bulkDropdown.disabled = checked.length === 0;
        bulkDropdown.innerHTML = checked.length > 0
            ? `<i class="fas fa-cog me-2"></i>Actions (${checked.length})`
            : `<i class="fas fa-cog me-2"></i>Actions`;
    }
}

// Event Listeners Setup
function setupEventListeners() {
    // Filter buttons
    document.getElementById('filterBtn')?.addEventListener('click', () => {
        currentPage = 1;
        fetchStudents();
    });

    document.getElementById('resetFiltersBtn')?.addEventListener('click', () => {
        document.getElementById('search-input').value = '';
        document.getElementById('schoolclass-filter').value = 'all';
        document.getElementById('term-filter').value = 'all';
        document.getElementById('session-filter').value = 'all';
        document.getElementById('clear-search').style.display = 'none';
        currentPage = 1;
        fetchStudents();
    });

    document.getElementById('resetFromEmptyBtn')?.addEventListener('click', () => {
        document.getElementById('search-input').value = '';
        document.getElementById('schoolclass-filter').value = 'all';
        document.getElementById('term-filter').value = 'all';
        document.getElementById('session-filter').value = 'all';
        currentPage = 1;
        fetchStudents();
    });

    // Search input
    const searchInput = document.getElementById('search-input');
    const clearSearch = document.getElementById('clear-search');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            if (clearSearch) clearSearch.style.display = searchInput.value ? 'block' : 'none';
        });
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                currentPage = 1;
                fetchStudents();
            }
        });
    }
    if (clearSearch) {
        clearSearch.addEventListener('click', () => {
            if (searchInput) {
                searchInput.value = '';
                clearSearch.style.display = 'none';
                currentPage = 1;
                fetchStudents();
            }
        });
    }

    // View toggle
    document.getElementById('tableViewBtn')?.addEventListener('click', () => {
        currentView = 'table';
        document.getElementById('tableViewBtn').classList.add('active');
        document.getElementById('cardViewBtn').classList.remove('active');
        fetchStudents();
    });

    document.getElementById('cardViewBtn')?.addEventListener('click', () => {
        currentView = 'card';
        document.getElementById('cardViewBtn').classList.add('active');
        document.getElementById('tableViewBtn').classList.remove('active');
        fetchStudents();
    });

    // Select all checkboxes
    document.getElementById('checkAll')?.addEventListener('change', (e) => {
        document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = e.target.checked);
        updateCheckAllState();
    });

    document.getElementById('checkAllTable')?.addEventListener('change', (e) => {
        document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = e.target.checked);
        updateCheckAllState();
    });

    // Delete multiple
    document.getElementById('deleteMultipleBtn')?.addEventListener('click', async () => {
        const selected = Array.from(document.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            Swal.fire('No Selection', 'Please select at least one student', 'warning');
            return;
        }

        const result = await Swal.fire({
            title: 'Delete Students',
            text: `Are you sure you want to delete ${selected.length} student(s)?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        });

        if (result.isConfirmed) {
            try {
                await axios.post('/students/destroy-multiple', { ids: selected });
                Swal.fire('Deleted!', `${selected.length} student(s) have been deleted.`, 'success');
                fetchStudents();
            } catch (error) {
                Swal.fire('Error', 'Failed to delete students', 'error');
            }
        }
    });

    // Update current term
    document.getElementById('updateCurrentTermBtn')?.addEventListener('click', () => {
        const selected = Array.from(document.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            Swal.fire('No Selection', 'Please select at least one student', 'warning');
            return;
        }
        document.getElementById('selectedStudentsCount').textContent = selected.length;
        new bootstrap.Modal(document.getElementById('updateCurrentTermModal')).show();
    });

    document.getElementById('confirmUpdateCurrentTerm')?.addEventListener('click', async () => {
        const form = document.getElementById('updateCurrentTermForm');
        const schoolclassId = form.querySelector('[name="schoolclassId"]').value;
        const termId = form.querySelector('[name="termId"]').value;
        const sessionId = form.querySelector('[name="sessionId"]').value;
        const isCurrent = form.querySelector('[name="is_current"]').checked;

        if (!schoolclassId || !termId || !sessionId) {
            Swal.fire('Error', 'Please fill all fields', 'error');
            return;
        }

        const selected = Array.from(document.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);

        try {
            await axios.post('/student-current-term/bulk-update', {
                student_ids: selected,
                schoolclassId: schoolclassId,
                termId: termId,
                sessionId: sessionId,
                is_current: isCurrent
            });

            bootstrap.Modal.getInstance(document.getElementById('updateCurrentTermModal')).hide();
            Swal.fire('Success', 'Term updated successfully', 'success');
            fetchStudents();
        } catch (error) {
            Swal.fire('Error', 'Failed to update term', 'error');
        }
    });

    // Generate report
    document.getElementById('generateReportBtn')?.addEventListener('click', async () => {
        const form = document.getElementById('printReportForm');
        const format = form.querySelector('input[name="format"]:checked')?.value || 'pdf';
        const classId = form.querySelector('[name="class_id"]')?.value || '';
        const status = form.querySelector('[name="status"]')?.value || '';
        const termId = form.querySelector('[name="term_id"]')?.value || '';
        const sessionId = form.querySelector('[name="session_id"]')?.value || '';

        try {
            const response = await axios({
                method: 'GET',
                url: '/students/report',
                params: { format, class_id: classId, status, term_id: termId, session_id: sessionId },
                responseType: 'blob'
            });

            const url = window.URL.createObjectURL(new Blob([response.data]));
            const a = document.createElement('a');
            a.href = url;
            a.download = `student-report.${format === 'pdf' ? 'pdf' : 'xlsx'}`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);

            bootstrap.Modal.getInstance(document.getElementById('printStudentReportModal')).hide();
            Swal.fire('Success', 'Report generated successfully', 'success');
        } catch (error) {
            Swal.fire('Error', 'Failed to generate report', 'error');
        }
    });
}

// Attach Table Event Listeners
function attachTableEventListeners() {
    document.querySelectorAll('.view-student-btn').forEach(btn => {
        btn.addEventListener('click', () => viewStudent(btn.dataset.id));
    });
    document.querySelectorAll('.edit-student-btn').forEach(btn => {
        btn.addEventListener('click', () => editStudent(btn.dataset.id));
    });
    document.querySelectorAll('.delete-student-btn').forEach(btn => {
        btn.addEventListener('click', () => deleteStudent(btn.dataset.id));
    });
    document.querySelectorAll('.student-checkbox').forEach(cb => {
        cb.addEventListener('change', updateCheckAllState);
    });
}

function attachCardEventListeners() {
    attachTableEventListeners();
}

// Student CRUD Operations
async function viewStudent(id) {
    try {
        const response = await axios.get(`/student/${id}/edit`);
        if (response.data.success && response.data.student) {
            const s = response.data.student;

            // Populate view modal
            document.getElementById('viewFullName').textContent = `${s.lastname || ''} ${s.firstname || ''}`;
            document.getElementById('viewFullNameDetail').textContent = `${s.lastname || ''} ${s.firstname || ''}`;
            document.getElementById('viewAdmissionNumber').textContent = s.admissionNo || '-';
            document.getElementById('viewAdmissionNo').textContent = s.admissionNo || '-';
            document.getElementById('viewTitle').textContent = s.title || '-';
            document.getElementById('viewDOB').textContent = formatDate(s.dateofbirth);
            document.getElementById('viewAge').textContent = calculateAgeForDisplay(s.dateofbirth);
            document.getElementById('viewPlaceOfBirth').textContent = s.placeofbirth || '-';
            document.getElementById('viewGenderDetail').textContent = s.gender || '-';
            document.getElementById('viewGenderText').textContent = s.gender || '-';
            document.getElementById('viewBloodGroupDetail').textContent = s.blood_group || '-';
            document.getElementById('viewReligionDetail').textContent = s.religion || '-';
            document.getElementById('viewPhoneNumber').textContent = s.phone_number || '-';
            document.getElementById('viewEmailAddress').textContent = s.email || '-';
            document.getElementById('viewPermanentAddress').textContent = s.permanent_address || '-';
            document.getElementById('viewCity').textContent = s.city || '-';
            document.getElementById('viewStateOrigin').textContent = s.state || '-';
            document.getElementById('viewLGA').textContent = s.local || '-';
            document.getElementById('viewNationality').textContent = s.nationality || '-';
            document.getElementById('viewFutureAmbition').textContent = s.future_ambition || '-';
            document.getElementById('viewAdmissionDate').textContent = formatDate(s.admission_date);
            document.getElementById('viewAdmittedDate').textContent = formatDate(s.admission_date);
            document.getElementById('viewCurrentClass').textContent = `${s.schoolclass || ''} ${s.arm || ''}`;
            document.getElementById('viewClassDisplay').textContent = `${s.schoolclass || ''} ${s.arm || ''}`;
            document.getElementById('viewArm').textContent = s.arm || '-';
            document.getElementById('viewStudentCategory').textContent = s.student_category || '-';
            document.getElementById('viewStudentType').textContent = s.statusId == 2 ? 'New Student' : 'Old Student';
            document.getElementById('viewStudentStatus').textContent = s.student_status || '-';
            document.getElementById('viewSchoolHouse').textContent = s.school_house || '-';
            document.getElementById('viewLastSchool').textContent = s.last_school || '-';
            document.getElementById('viewLastClass').textContent = s.last_class || '-';
            document.getElementById('viewReasonForLeaving').textContent = s.reason_for_leaving || '-';
            document.getElementById('viewFatherFullName').textContent = s.father_name || '-';
            document.getElementById('viewFatherPhone').textContent = s.father_phone || '-';
            document.getElementById('viewFatherOccupation').textContent = s.father_occupation || '-';
            document.getElementById('viewFatherCityState').textContent = s.father_city || '-';
            document.getElementById('viewFatherEmail').textContent = s.parent_email || '-';
            document.getElementById('viewFatherAddress').textContent = s.parent_address || '-';
            document.getElementById('viewMotherFullName').textContent = s.mother_name || '-';
            document.getElementById('viewMotherPhone').textContent = s.mother_phone || '-';
            document.getElementById('viewMotherOccupation').textContent = s.mother_occupation || '-';
            document.getElementById('viewMotherEmail').textContent = s.parent_email || '-';
            document.getElementById('viewMotherAddress').textContent = s.parent_address || '-';
            document.getElementById('viewNIN').textContent = s.nin_number || '-';
            document.getElementById('viewMotherTongue').textContent = s.mother_tongue || '-';
            document.getElementById('viewBloodGroupAdditional').textContent = s.blood_group || '-';

            // Fetch term info
            const termResponse = await axios.get(`/student-current-term/student/${id}/active`);
            if (termResponse.data.success && termResponse.data.data) {
                const t = termResponse.data.data;
                document.getElementById('viewCurrentTerm').textContent = t.term?.name || '-';
                document.getElementById('viewCurrentSession').textContent = t.session?.name || '-';
                const statusHtml = t.is_current ? '<span class="badge bg-success">Active Term</span>' : '<span class="badge bg-warning">Registered</span>';
                document.getElementById('viewCurrentTermStatus').innerHTML = statusHtml;
                document.getElementById('currentTermAlert').innerHTML = `<div class="alert alert-success">${t.schoolClass?.schoolclass || ''} ${t.schoolClass?.arm || ''} - ${t.term?.name || ''} Term, ${t.session?.name || ''} Session</div>`;
            } else {
                document.getElementById('viewCurrentTerm').textContent = '-';
                document.getElementById('viewCurrentSession').textContent = '-';
                document.getElementById('viewCurrentTermStatus').innerHTML = '<span class="badge bg-secondary">Not Registered</span>';
                document.getElementById('currentTermAlert').innerHTML = '<div class="alert alert-warning">No active term registration found.</div>';
            }

            // Set photo
            const photoEl = document.getElementById('viewStudentPhoto');
            if (s.picture && s.picture !== 'unnamed.jpg') {
                photoEl.src = `/storage/images/student_avatars/${s.picture}`;
            } else {
                photoEl.src = '{{ asset("theme/layouts/assets/media/avatars/blank.png") }}';
            }

            // Set status indicator
            const statusIndicator = document.getElementById('studentStatusIndicator');
            if (statusIndicator) {
                statusIndicator.className = `position-absolute bottom-0 end-0 ${s.student_status === 'Active' ? 'bg-success' : 'bg-secondary'} rounded-circle border border-2 border-white`;
            }

            new bootstrap.Modal(document.getElementById('viewStudentModal')).show();
        }
    } catch (error) {
        Swal.fire('Error', 'Failed to load student details', 'error');
    }
}

function calculateAgeForDisplay(dob) {
    if (!dob) return '-';
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) age--;
    return age;
}

async function editStudent(id) {
    try {
        const response = await axios.get(`/student/${id}/edit`);
        if (response.data.success && response.data.student) {
            const s = response.data.student;

            document.getElementById('editStudentId').value = s.id;

            // Update form action
            const form = document.getElementById('editStudentForm');
            const baseAction = form.dataset.baseAction;
            if (baseAction) {
                form.action = baseAction.replace(':id', s.id);
            }

            // Populate form fields
            document.getElementById('editAdmissionNo').value = s.admissionNo || '';
            document.getElementById('editAdmissionYear').value = s.admissionYear || new Date().getFullYear();
            if (s.admissionDate) document.getElementById('editAdmissionDate').value = s.admissionDate.split(' ')[0];
            if (s.schoolclassid) document.getElementById('editSchoolclassid').value = s.schoolclassid;
            if (s.termid) document.getElementById('editTermid').value = s.termid;
            if (s.sessionid) document.getElementById('editSessionid').value = s.sessionid;
            if (s.student_category) document.getElementById('editStudentCategory').value = s.student_category;
            if (s.title) document.getElementById('editTitle').value = s.title;
            document.getElementById('editLastname').value = s.lastname || '';
            document.getElementById('editFirstname').value = s.firstname || '';
            document.getElementById('editOthername').value = s.othername || '';
            document.getElementById('editPlaceofbirth').value = s.placeofbirth || '';
            document.getElementById('editPhoneNumber').value = s.phone_number || '';
            document.getElementById('editEmail').value = s.email || '';
            document.getElementById('editFutureAmbition').value = s.future_ambition || '';
            document.getElementById('editPermanentAddress').value = s.permanent_address || '';
            document.getElementById('editNationality').value = s.nationality || '';
            document.getElementById('editCity').value = s.city || '';
            if (s.religion) document.getElementById('editReligion').value = s.religion;
            if (s.blood_group) document.getElementById('editBloodGroup').value = s.blood_group;
            document.getElementById('editMotherTongue').value = s.mother_tongue || '';
            document.getElementById('editNinNumber').value = s.nin_number || '';
            if (s.schoolhouseid) document.getElementById('editSchoolHouse').value = s.schoolhouseid;
            document.getElementById('editFatherName').value = s.father_name || '';
            document.getElementById('editFatherPhone').value = s.father_phone || '';
            document.getElementById('editFatherOccupation').value = s.father_occupation || '';
            document.getElementById('editFatherCity').value = s.father_city || '';
            document.getElementById('editMotherName').value = s.mother_name || '';
            document.getElementById('editMotherPhone').value = s.mother_phone || '';
            document.getElementById('editParentEmail').value = s.parent_email || '';
            document.getElementById('editParentAddress').value = s.parent_address || '';
            document.getElementById('editLastSchool').value = s.last_school || '';
            document.getElementById('editLastClass').value = s.last_class || '';
            document.getElementById('editReasonForLeaving').value = s.reason_for_leaving || '';

            // Radio buttons
            if (s.statusId == 1) document.getElementById('editStatusOld').checked = true;
            else if (s.statusId == 2) document.getElementById('editStatusNew').checked = true;

            if (s.student_status === 'Active') document.getElementById('editStatusActive').checked = true;
            else document.getElementById('editStatusInactive').checked = true;

            if (s.gender === 'Male') document.getElementById('editGenderMale').checked = true;
            else if (s.gender === 'Female') document.getElementById('editGenderFemale').checked = true;

            // DOB and Age
            if (s.dateofbirth) {
                const dob = s.dateofbirth.split(' ')[0];
                document.getElementById('editDOB').value = dob;
                const age = calculateAgeForDisplay(dob);
                document.getElementById('editAgeInput').value = age;
            }

            // State and LGA
            if (s.state) {
                const stateSelect = document.getElementById('editState');
                if (stateSelect && stateSelect.options.length <= 1) {
                    populateStateDropdown('editState', 'editLocal');
                    setTimeout(() => {
                        stateSelect.value = s.state;
                        stateSelect.dispatchEvent(new Event('change'));
                        setTimeout(() => {
                            const lgaSelect = document.getElementById('editLocal');
                            if (lgaSelect && s.local) lgaSelect.value = s.local;
                        }, 300);
                    }, 100);
                } else {
                    stateSelect.value = s.state;
                    stateSelect.dispatchEvent(new Event('change'));
                    setTimeout(() => {
                        const lgaSelect = document.getElementById('editLocal');
                        if (lgaSelect && s.local) lgaSelect.value = s.local;
                    }, 300);
                }
            }

            // Photo
            const avatarImg = document.getElementById('editStudentAvatar');
            if (s.picture && s.picture !== 'unnamed.jpg') {
                avatarImg.src = `/storage/images/student_avatars/${s.picture}`;
            } else {
                avatarImg.src = '{{ asset("theme/layouts/assets/media/avatars/blank.png") }}';
            }

            new bootstrap.Modal(document.getElementById('editStudentModal')).show();
        }
    } catch (error) {
        Swal.fire('Error', 'Failed to load student for editing', 'error');
    }
}

async function deleteStudent(id) {
    const result = await Swal.fire({
        title: 'Delete Student',
        text: 'Are you sure you want to delete this student?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(`/student/${id}/destroy`);
            Swal.fire('Deleted!', 'Student has been deleted.', 'success');
            fetchStudents();
        } catch (error) {
            Swal.fire('Error', 'Failed to delete student', 'error');
        }
    }
}

// Form Submissions
document.getElementById('addStudentForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);

    try {
        const response = await axios.post(e.target.action, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addStudentModal')).hide();
            Swal.fire('Success', 'Student registered successfully', 'success');
            fetchStudents();
            e.target.reset();
            document.getElementById('addStudentAvatar').src = 'https://via.placeholder.com/120x120/667eea/ffffff?text=Photo';
        }
    } catch (error) {
        Swal.fire('Error', error.response?.data?.message || 'Failed to register student', 'error');
    }
});

document.getElementById('editStudentForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('_method', 'PATCH');

    try {
        const response = await axios.post(e.target.action, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editStudentModal')).hide();
            Swal.fire('Success', 'Student updated successfully', 'success');
            fetchStudents();
        }
    } catch (error) {
        Swal.fire('Error', error.response?.data?.message || 'Failed to update student', 'error');
    }
});

// Global functions for inline handlers
window.updateAdmissionNumber = updateAdmissionNumber;
window.toggleAdmissionInput = toggleAdmissionInput;
window.previewImage = previewImage;
window.calculateAge = calculateAge;
window.editStudentFromView = () => {
    const modal = bootstrap.Modal.getInstance(document.getElementById('viewStudentModal'));
    if (modal) modal.hide();
    setTimeout(() => {
        const id = document.getElementById('viewAdmissionNumber')?.textContent;
        // Need to store the current student ID somewhere
    }, 300);
};
</script>
@endsection
