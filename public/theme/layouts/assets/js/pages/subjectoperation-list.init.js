console.log("subjectoperation.init.js is loaded and executing at", new Date().toISOString());

var perPage = 10,
    checkAll = document.getElementById("checkAll");

function ensureAxios() {
    if (typeof axios === 'undefined') {
        console.error("Axios is not defined. Please include Axios library.");
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Configuration error",
            text: "Axios library is missing",
            showConfirmButton: true
        });
        return false;
    }
    return true;
}

function ischeckboxcheck() {
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    checkboxes.forEach((checkbox) => {
        checkbox.removeEventListener("change", handleCheckboxChange);
        checkbox.addEventListener("change", handleCheckboxChange);
    });
}

function handleCheckboxChange(e) {
    const row = e.target.closest("tr");
    if (e.target.checked) {
        row.classList.add("table-active");
    } else {
        row.classList.remove("table-active");
    }
    const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
    const registerButton = document.getElementById("register-selected-btn");
    const unregisterButton = document.getElementById("unregister-selected-btn");
    if (registerButton) {
        registerButton.classList.toggle("d-none", checkedCount === 0);
    }
    if (unregisterButton) {
        unregisterButton.classList.toggle("d-none", checkedCount === 0);
    }
    const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    document.getElementById("checkAll").checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCount;
}

function refreshCallbacks() {
    console.log("refreshCallbacks executed at", new Date().toISOString());
    ischeckboxcheck();
}

function updateAdmissionNoOptions(students) {
    const select = document.getElementById("idadmission");
    if (!select) return;

    select.innerHTML = '<option value="ALL">Select Admission No</option>';

    const uniqueAdmissionNos = [...new Set(students.map(s => s.admissionno).filter(Boolean))].sort();

    uniqueAdmissionNos.forEach(admissionNo => {
        const option = document.createElement("option");
        option.value = admissionNo;
        option.text = admissionNo;
        select.appendChild(option);
    });
}

function selectAllSubjects() {
    const subjectCheckboxes = document.querySelectorAll('.subject-checkbox');
    subjectCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAllSubjects() {
    const subjectCheckboxes = document.querySelectorAll('.subject-checkbox');
    subjectCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
}

document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM loaded, initializing...");
    refreshCallbacks();

    if (typeof Choices !== 'undefined') {
        const choicesElements = ['idclass', 'idsession', 'idgender', 'idadmission'];
        choicesElements.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                new Choices(element, { searchEnabled: true });
            }
        });
    } else {
        console.warn("Choices.js not available, using native select");
    }

    if (checkAll) {
        checkAll.onclick = function () {
            console.log("checkAll clicked");
            var checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
            checkboxes.forEach((checkbox) => {
                checkbox.checked = this.checked;
                const row = checkbox.closest("tr");
                if (checkbox.checked) {
                    row.classList.add("table-active");
                } else {
                    row.classList.remove("table-active");
                }
            });
            const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
            const registerButton = document.getElementById("register-selected-btn");
            const unregisterButton = document.getElementById("unregister-selected-btn");
            if (registerButton) {
                registerButton.classList.toggle("d-none", checkedCount === 0);
            }
            if (unregisterButton) {
                unregisterButton.classList.toggle("d-none", checkedCount === 0);
            }
        };
    }
});

function filterData() {
    if (!ensureAxios()) return;

    const classSelect = document.getElementById("idclass");
    const sessionSelect = document.getElementById("idsession");
    const searchInput = document.querySelector(".search-box input.search");
    const genderSelect = document.getElementById("idgender");
    const admissionNoSelect = document.getElementById("idadmission");

    if (!classSelect || !sessionSelect) {
        console.error("Required filter elements not found");
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Filter elements not found. Please refresh the page.",
            showConfirmButton: true
        });
        return;
    }

    const classValue = classSelect.value;
    const sessionValue = sessionSelect.value;
    const searchValue = searchInput ? searchInput.value.toLowerCase() : '';
    const genderValue = genderSelect ? genderSelect.value : 'ALL';
    const admissionNoValue = admissionNoSelect ? admissionNoSelect.value : 'ALL';

    if (classValue === 'ALL' || sessionValue === 'ALL') {
        Swal.fire({
            icon: "warning",
            title: "Missing filters",
            text: "Please select a class and session",
            showConfirmButton: true
        });
        return;
    }

    console.log("Filtering with:", {
        search: searchValue,
        class_id: classValue,
        session_id: sessionValue,
        gender: genderValue,
        admissionno: admissionNoValue
    });

    const tableBody = document.getElementById('studentTableBody');
    const subjectTeachersContainer = document.getElementById('subjectTeachersContainer');
    const subjectTeachersCard = document.getElementById('subjectTeachersCard');
    const subjectTeacherCount = document.getElementById('subjectTeacherCount');

    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Loading...</td></tr>';
    }
    if (subjectTeachersContainer) {
        subjectTeachersContainer.innerHTML = '<div class="col-12 text-center">Loading subject teachers...</div>';
    }

    axios.get('/subjects', {
        params: {
            search: searchValue,
            class_id: classValue,
            session_id: sessionValue,
            gender: genderValue,
            admissionno: admissionNoValue
        },
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(function (response) {
        console.log("AJAX response received");

        const parser = new DOMParser();
        const doc = parser.parseFromString(response.data, 'text/html');

        const newTableBody = doc.querySelector('#studentTableBody');
        const currentTableBody = document.getElementById('studentTableBody');
        if (newTableBody && currentTableBody) {
            currentTableBody.innerHTML = newTableBody.innerHTML;
        }

        const newPagination = doc.querySelector('#pagination-container');
        const currentPagination = document.getElementById('pagination-container');
        if (newPagination && currentPagination) {
            currentPagination.innerHTML = newPagination.innerHTML;
        }

        const newStudentCount = doc.querySelector('#studentcount');
        const currentStudentCount = document.getElementById('studentcount');
        if (newStudentCount && currentStudentCount) {
            currentStudentCount.innerText = newStudentCount.innerText;
        }

        const newSubjectTeachersContainer = doc.querySelector('#subjectTeachersContainer');
        if (newSubjectTeachersContainer && subjectTeachersContainer) {
            subjectTeachersContainer.innerHTML = newSubjectTeachersContainer.innerHTML;
            const subjectCount = subjectTeachersContainer.querySelectorAll('.subject-checkbox').length;
            if (subjectTeacherCount) {
                subjectTeacherCount.innerText = subjectCount;
            }
            if (subjectTeachersCard) {
                subjectTeachersCard.style.display = subjectCount > 0 ? 'block' : 'none';
            }
        }

        const studentRows = doc.querySelectorAll('#studentTableBody tr');
        const students = [];
        studentRows.forEach(row => {
            const admissionCell = row.querySelector('.admissionno');
            if (admissionCell) {
                const admissionNo = admissionCell.dataset.admissionno || admissionCell.textContent.trim();
                if (admissionNo && admissionNo !== 'Select class and session to view students.') {
                    students.push({ admissionno: admissionNo });
                }
            }
        });

        updateAdmissionNoOptions(students);
        refreshCallbacks();
        setupPaginationLinks();

        if (students.length === 0 && currentTableBody.innerHTML.includes('Select class and session')) {
            // Initial empty state
        } else if (students.length === 0) {
            Swal.fire({
                icon: "info",
                title: "No Results",
                text: "No students found matching your criteria",
                showConfirmButton: true
            });
        }
    }).catch(function (error) {
        console.error("Error filtering data:", error);

        if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
        }
        if (subjectTeachersContainer) {
            subjectTeachersContainer.innerHTML = '<div class="col-12 text-center text-danger">Error loading subject teachers.</div>';
        }

        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.response?.data?.message || "Failed to fetch filtered data. Please try again.",
            showConfirmButton: true
        });
    });
}

function setupPaginationLinks() {
    const paginationLinks = document.querySelectorAll('#pagination-container a');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.href;
            if (url && !this.classList.contains('disabled')) {
                loadPage(url);
            }
        });
    });
}

function loadPage(url) {
    if (!ensureAxios()) return;

    const tableBody = document.getElementById('studentTableBody');
    const subjectTeachersContainer = document.getElementById('subjectTeachersContainer');
    const subjectTeachersCard = document.getElementById('subjectTeachersCard');
    const subjectTeacherCount = document.getElementById('subjectTeacherCount');

    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Loading...</td></tr>';
    }
    if (subjectTeachersContainer) {
        subjectTeachersContainer.innerHTML = '<div class="col-12 text-center">Loading subject teachers...</div>';
    }

    axios.get(url, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(function (response) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(response.data, 'text/html');

        const newTableBody = doc.querySelector('#studentTableBody');
        const currentTableBody = document.getElementById('studentTableBody');
        if (newTableBody && currentTableBody) {
            currentTableBody.innerHTML = newTableBody.innerHTML;
        }

        const newPagination = doc.querySelector('#pagination-container');
        const currentPagination = document.getElementById('pagination-container');
        if (newPagination && currentPagination) {
            currentPagination.innerHTML = newPagination.innerHTML;
        }

        const newStudentCount = doc.querySelector('#studentcount');
        const currentStudentCount = document.getElementById('studentcount');
        if (newStudentCount && currentStudentCount) {
            currentStudentCount.innerText = newStudentCount.innerText;
        }

        const newSubjectTeachersContainer = doc.querySelector('#subjectTeachersContainer');
        if (newSubjectTeachersContainer && subjectTeachersContainer) {
            subjectTeachersContainer.innerHTML = newSubjectTeachersContainer.innerHTML;
            const subjectCount = subjectTeachersContainer.querySelectorAll('.subject-checkbox').length;
            if (subjectTeacherCount) {
                subjectTeacherCount.innerText = subjectCount;
            }
            if (subjectTeachersCard) {
                subjectTeachersCard.style.display = subjectCount > 0 ? 'block' : 'none';
            }
        }

        refreshCallbacks();
        setupPaginationLinks();
    }).catch(function (error) {
        console.error("Error loading page:", error);
        if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
        }
        if (subjectTeachersContainer) {
            subjectTeachersContainer.innerHTML = '<div class="col-12 text-center text-danger">Error loading subject teachers.</div>';
        }
    });
}

function registerSelectedStudentsBatch() {
    if (!ensureAxios()) return;

    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');
    const classSelect = document.getElementById("idclass");
    const sessionSelect = document.getElementById("idsession");
    const subjectCheckboxes = document.querySelectorAll('.subject-checkbox:checked');
    const registerButton = document.getElementById("register-selected-btn");
    const loadingSpinner = document.getElementById("register-loading-spinner");

    if (!classSelect || !sessionSelect) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Required filter elements not found. Please refresh the page.",
            showConfirmButton: true
        });
        return;
    }

    const classValue = classSelect.value;
    const sessionValue = sessionSelect.value;

    if (classValue === 'ALL' || sessionValue === 'ALL') {
        Swal.fire({
            icon: "warning",
            title: "Missing filters",
            text: "Please select a class and session before registering students.",
            showConfirmButton: true
        });
        return;
    }

    if (checkboxes.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No students selected",
            text: "Please select at least one student to register.",
            showConfirmButton: true
        });
        return;
    }

    if (subjectCheckboxes.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No subjects selected",
            text: "Please select at least one subject to register.",
            showConfirmButton: true
        });
        return;
    }

    if (loadingSpinner) loadingSpinner.classList.remove("d-none");
    if (registerButton) {
        registerButton.disabled = true;
        registerButton.setAttribute("aria-disabled", "true");
    }

    const studentIds = Array.from(checkboxes).map(checkbox => checkbox.closest('tr').querySelector('.id').dataset.id);
    const subjectClasses = Array.from(subjectCheckboxes).map(checkbox => ({
        subjectclassid: checkbox.dataset.subjectclassid,
        staffid: checkbox.dataset.staffid,
        termid: checkbox.dataset.termid
    }));

    axios.post('/subjectregistration/batch', {
        studentids: studentIds,
        subjectclasses: subjectClasses,
        sessionid: sessionValue
    }, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).then(function (response) {
        if (loadingSpinner) loadingSpinner.classList.add("d-none");
        if (registerButton) {
            registerButton.disabled = false;
            registerButton.setAttribute("aria-disabled", "false");
        }

        if (response.data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: `Successfully registered ${studentIds.length} student(s) for ${subjectClasses.length} subject(s).`,
                showConfirmButton: true
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Partial/Failed',
                html: `Some or all registrations failed.<br>${(response.data.error_details || []).map(e => e.message).join('<br>')}`,
                showConfirmButton: true
            });
        }
        filterData();
    }).catch(function (error) {
        if (loadingSpinner) loadingSpinner.classList.add("d-none");
        if (registerButton) {
            registerButton.disabled = false;
            registerButton.setAttribute("aria-disabled", "false");
        }
        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.response?.data?.message || "Failed to register subjects. Please try again.",
            showConfirmButton: true
        });
    });
}

function unregisterSelectedStudentsBatch() {
    if (!ensureAxios()) return;

    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');
    const classSelect = document.getElementById("idclass");
    const sessionSelect = document.getElementById("idsession");
    const subjectCheckboxes = document.querySelectorAll('.subject-checkbox:checked');
    const unregisterButton = document.getElementById("unregister-selected-btn");
    const loadingSpinner = document.getElementById("register-loading-spinner");

    if (!classSelect || !sessionSelect) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Required filter elements not found. Please refresh the page.",
            showConfirmButton: true
        });
        return;
    }

    const classValue = classSelect.value;
    const sessionValue = sessionSelect.value;

    if (classValue === 'ALL' || sessionValue === 'ALL') {
        Swal.fire({
            icon: "warning",
            title: "Missing filters",
            text: "Please select a class and session before unregistering students.",
            showConfirmButton: true
        });
        return;
    }

    if (checkboxes.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No students selected",
            text: "Please select at least one student to unregister.",
            showConfirmButton: true
        });
        return;
    }

    if (subjectCheckboxes.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No subjects selected",
            text: "Please select at least one subject to unregister.",
            showConfirmButton: true
        });
        return;
    }

    if (loadingSpinner) loadingSpinner.classList.remove("d-none");
    if (unregisterButton) {
        unregisterButton.disabled = true;
        unregisterButton.setAttribute("aria-disabled", "true");
    }

    const studentIds = Array.from(checkboxes).map(checkbox => checkbox.closest('tr').querySelector('.id').dataset.id);
    const subjectClasses = Array.from(subjectCheckboxes).map(checkbox => ({
        subjectclassid: checkbox.dataset.subjectclassid,
        staffid: checkbox.dataset.staffid,
        termid: checkbox.dataset.termid
    }));

    axios.post('/subjectregistration/destroy', {
        studentids: studentIds,
        subjectclasses: subjectClasses,
        sessionid: sessionValue
    }, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).then(function (response) {
        if (loadingSpinner) loadingSpinner.classList.add("d-none");
        if (unregisterButton) {
            unregisterButton.disabled = false;
            unregisterButton.setAttribute("aria-disabled", "false");
        }

        if (response.data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: `Successfully unregistered ${response.data.success_count} student(s) from ${subjectClasses.length} subject(s).`,
                showConfirmButton: true
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Partial/Failed',
                html: `Some or all unregistrations failed.<br>${(response.data.error_details || []).map(e => e.message).join('<br>')}`,
                showConfirmButton: true
            });
        }
        filterData();
    }).catch(function (error) {
        if (loadingSpinner) loadingSpinner.classList.add("d-none");
        if (unregisterButton) {
            unregisterButton.disabled = false;
            unregisterButton.setAttribute("aria-disabled", "false");
        }
        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.response?.data?.message || "Failed to unregister subjects. Please try again.",
            showConfirmButton: true
        });
    });
}

// ============================================================================
// REGISTERED CLASSES MODAL - Card-based UI
// ============================================================================
function loadRegisteredClasses() {
    if (!ensureAxios()) {
        console.error('Axios not initialized.');
        return;
    }

    const modalContent = document.getElementById('registeredClassesContent');
    if (!modalContent) {
        console.error('Modal content element not found.');
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Modal container not found.',
            showConfirmButton: true
        });
        return;
    }

    const classSelect = document.getElementById('idclass');
    const sessionSelect = document.getElementById('idsession');

    if (!classSelect || !sessionSelect) {
        console.error('Required selectors missing:', { classSelect, sessionSelect });
        modalContent.innerHTML = '<p class="text-center text-red-500">Class or session selector not found.</p>';
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please ensure class and session selectors are present.',
            showConfirmButton: true
        });
        return;
    }

    const classId = classSelect.value;
    const sessionId = sessionSelect.value;

    if (!classId || classId === 'ALL' || !sessionId || sessionId === 'ALL') {
        console.warn('Invalid class or session selection.');
        modalContent.innerHTML = '<p class="text-center text-muted">Please select a valid class and session.</p>';
        Swal.fire({
            icon: 'warning',
            title: 'Missing Selection',
            text: 'Please select a valid class and session.',
            showConfirmButton: true
        });
        return;
    }

    modalContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div><p class="mt-3 text-muted">Loading registration data...</p></div>';

    axios.get('/subjects/registered-classes', {
        params: { class_id: classId, session_id: sessionId },
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        timeout: 15000
    }).then(response => {
        console.log('Registered classes response:', response.data);

        if (response.data.success) {
            const classes = response.data.data;

            if (!classes || classes.length === 0) {
                modalContent.innerHTML = '<div class="text-center py-5"><i class="ri-information-line ri-3x text-muted"></i><p class="text-muted mt-3 mb-0">No registered classes found.</p></div>';
                return;
            }

            let html = '';

            classes.forEach((termGroup, index) => {
                const subjectsArray = termGroup.subjects ? termGroup.subjects.split(',').map(s => s.trim()) : [];
                const teachersArray = termGroup.teachers ? termGroup.teachers.split(',').map(t => t.trim()) : [];
                const totalStudents = termGroup.student_count || 0;
                const totalSubjects = termGroup.subject_count || subjectsArray.length;

                html += `
                <div class="term-card mb-4" style="background:#fff; border-radius:12px; border:0.5px solid #e2e8f0; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div class="term-header p-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-bottom:0.5px solid #e2e8f0; background:#fff;">
                        <div>
                            <h5 class="fw-semibold mb-0" style="font-size:1rem;">${escapeHtml(termGroup.class_name)} ${escapeHtml(termGroup.arm_name)} — ${escapeHtml(termGroup.session_name)}</h5>
                            <span class="text-muted small">${escapeHtml(termGroup.term_name)}</span>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge" style="background:#E6F1FB; color:#0C447C; padding:4px 12px; border-radius:20px; font-weight:500;">
                                <i class="ri-user-line me-1"></i>${totalStudents} students
                            </span>
                            <span class="badge" style="background:#EEEDFE; color:#3C3489; padding:4px 12px; border-radius:20px; font-weight:500;">
                                <i class="ri-book-open-line me-1"></i>${totalSubjects} subjects
                            </span>
                        </div>
                    </div>
                    <div class="subjects-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr));">
                `;

                subjectsArray.forEach((subjectName, idx) => {
                    let teacherName = '— Not assigned';
                    if (teachersArray.length > idx) {
                        teacherName = teachersArray[idx];
                    } else if (teachersArray.length > 0) {
                        teacherName = teachersArray[0];
                    }

                    html += `
                    <div class="subject-card p-3 d-flex gap-3 align-items-start" style="border-right:0.5px solid #e2e8f0; border-bottom:0.5px solid #e2e8f0; transition:all 0.2s ease;">
                        <div class="subject-num" style="width:30px; height:30px; background:#EEEDFE; color:#3C3489; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:600; flex-shrink:0;">${idx + 1}</div>
                        <div class="subject-info flex-grow-1">
                            <div class="fw-semibold" style="font-size:0.9rem; color:#1e293b;">${escapeHtml(subjectName)}</div>
                            <div class="text-muted small mt-1" style="font-size:0.75rem;">
                                <i class="ri-user-star-line me-1"></i>${escapeHtml(teacherName)}
                            </div>
                            <span class="badge mt-2" style="background:#EAF3DE; color:#27500A; font-size:10px; padding:3px 10px; border-radius:20px; display:inline-flex; align-items:center; gap:4px;">
                                <i class="ri-group-line" style="font-size:10px;"></i>${totalStudents} students
                            </span>
                        </div>
                    </div>`;
                });

                html += `
                    </div>
                </div>`;
            });

            modalContent.innerHTML = html;

        } else {
            console.error('Failed to load:', response.data.message);
            modalContent.innerHTML = '<div class="text-center py-5 text-danger"><i class="ri-error-warning-line ri-3x"></i><p class="mt-2">Failed to load registered classes.</p></div>';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.data.message || 'Failed to load registered classes.',
                showConfirmButton: true
            });
        }
    }).catch(error => {
        console.error('Error loading registered classes:', error);
        modalContent.innerHTML = '<div class="text-center py-5 text-danger"><i class="ri-error-warning-line ri-3x"></i><p class="mt-2">Error loading registered classes. Please try again.</p></div>';
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.message || 'An error occurred while loading registered classes.',
            showConfirmButton: true
        });
    });
}

// ============================================================================
// OPEN ARCHIVED MODAL - Unregistered History
// ============================================================================
function openArchivedModal() {
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (classId === 'ALL' || sessionId === 'ALL') {
        Swal.fire({
            icon: 'warning',
            title: 'Selection Required',
            text: 'Please select a class and session first.',
            showConfirmButton: true
        });
        return;
    }

    const archivedModal = new bootstrap.Modal(document.getElementById('archivedModal'));
    archivedModal.show();
    loadArchivedPage(1);
}

// Archive state variables
let archiveCurrentPage = 1;
let archiveMeta = {};
let archiveSearchTimer = null;

function loadArchivedPage(page) {
    if (!ensureAxios()) return;

    archiveCurrentPage = page;

    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const termId = document.getElementById('archiveTermFilter')?.value || '';
    const search = document.getElementById('archiveSearch')?.value.trim() || '';
    const perPage = document.getElementById('archivePerPage')?.value || 50;

    if (classId === 'ALL' || sessionId === 'ALL') return;

    const spinner = document.getElementById('archiveSpinner');
    const container = document.getElementById('snapshotCardsContainer');

    if (spinner) spinner.classList.remove('d-none');
    if (container) {
        container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-warning me-2"></div> Loading snapshots…</div>';
    }

    axios.get('/subjectoperation/archived', {
        params: {
            class_id: classId,
            session_id: sessionId,
            page: page,
            per_page: perPage,
            term_id: termId,
            search: search
        },
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(function(response) {
        if (response.data.success) {
            archiveMeta = response.data.meta;
            renderSnapshotCards(response.data.data);
            renderArchivePagination(response.data.meta);
            updateArchiveMeta(response.data.meta);
        } else {
            if (container) {
                container.innerHTML = '<div class="text-center text-danger py-4">' + (response.data.message || 'Failed to load snapshots') + '</div>';
            }
        }
    }).catch(function(error) {
        console.error('Error loading archived data:', error);
        if (container) {
            container.innerHTML = '<div class="text-center text-danger py-4">Error loading snapshots. Please try again.</div>';
        }
    }).finally(function() {
        if (spinner) spinner.classList.add('d-none');
    });
}

function renderSnapshotCards(rows) {
    const container = document.getElementById('snapshotCardsContainer');
    const restoreBtn = document.getElementById('restoreSelectedBtn');
    const deleteBtn = document.getElementById('deleteSelectedBtn');

    if (!rows || rows.length === 0) {
        if (container) {
            container.innerHTML = '<div class="text-center text-muted py-5"><i class="ri-archive-line ri-3x d-block mb-2"></i>No unregistration snapshots found.</div>';
        }
        if (restoreBtn) restoreBtn.classList.add('d-none');
        if (deleteBtn) deleteBtn.classList.add('d-none');
        return;
    }

    if (restoreBtn) restoreBtn.classList.add('d-none');
    if (deleteBtn) deleteBtn.classList.add('d-none');

    // Group rows by snapshot_name
    const groups = {};
    rows.forEach(row => {
        const key = row.snapshot_name + '__' + row.subjectclassid + '__' + row.termid;
        if (!groups[key]) {
            groups[key] = {
                snapshot_name: row.snapshot_name,
                snapshot_notes: row.snapshot_notes,
                unregistered_at: row.unregistered_at,
                staffname: row.staffname,
                termname: row.termname,
                student_count: row.student_count,
                subjectclassid: row.subjectclassid,
                termid: row.termid,
                sessionid: row.sessionid,
                staffid: row.staffid,
                subjects: []
            };
        }
        groups[key].subjects.push({
            subjectname: row.subjectname,
            student_count: row.student_count
        });
    });

    let html = '<div class="row g-3">';

    for (const group of Object.values(groups)) {
        const unregDate = group.unregistered_at
            ? new Date(group.unregistered_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
            : '—';

        const subjectPills = group.subjects.map(s =>
            '<span class="badge bg-primary-subtle text-primary me-1 mb-1">' + escapeHtml(s.subjectname) + '</span>'
        ).join('');

        const metaEncoded = encodeURIComponent(JSON.stringify({
            snapshot_name: group.snapshot_name,
            subjectclassid: group.subjectclassid,
            termid: group.termid,
            sessionid: group.sessionid,
            staffid: group.staffid
        }));

        html += `
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="cursor:pointer; transition:transform .15s,box-shadow .15s;">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="fw-semibold mb-0 text-truncate" title="${escapeHtml(group.snapshot_name)}">
                                <i class="ri-camera-line text-danger me-1"></i>${escapeHtml(group.snapshot_name)}
                            </h6>
                            <small class="text-muted">${unregDate}</small>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="badge bg-danger-subtle text-danger rounded-pill">
                                ${group.student_count} student${group.student_count !== 1 ? 's' : ''}
                            </span>
                        </div>
                    </div>
                    ${group.snapshot_notes ? '<p class="text-muted small fst-italic mb-2">"' + escapeHtml(group.snapshot_notes) + '"</p>' : ''}
                    <div class="mb-2">${subjectPills}</div>
                    <div class="d-flex justify-content-between align-items-center mt-auto pt-1 border-top">
                        <small class="text-muted">
                            <i class="ri-user-star-line me-1"></i>${escapeHtml(group.staffname || '—')}
                        </small>
                        <small class="text-muted">
                            <span class="badge bg-warning-subtle text-warning-emphasis">${escapeHtml(group.termname)}</span>
                        </small>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 d-flex gap-2 py-2">
                    <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="viewSnapshotDetail('${metaEncoded}')">
                        <i class="ri-eye-line me-1"></i> View
                    </button>
                    <button class="btn btn-sm btn-outline-success flex-grow-1" onclick="restoreSnapshot('${metaEncoded}')">
                        <i class="ri-refresh-line me-1"></i> Restore
                    </button>
                </div>
            </div>
        </div>`;
    }

    html += '</div>';
    if (container) container.innerHTML = html;
}

function renderArchivePagination(meta) {
    const container = document.getElementById('archivePagination');
    if (!container || !meta || meta.last_page <= 1) {
        if (container) container.innerHTML = '';
        return;
    }

    let html = '<button class="btn btn-sm btn-outline-secondary ' + (meta.current_page === 1 ? 'disabled' : '') + '" onclick="loadArchivedPage(' + (meta.current_page - 1) + ')">‹</button>';

    for (let p = 1; p <= meta.last_page; p++) {
        if (p === 1 || p === meta.last_page || (p >= meta.current_page - 2 && p <= meta.current_page + 2)) {
            html += '<button class="btn btn-sm ' + (p === meta.current_page ? 'btn-warning' : 'btn-outline-secondary') + '" onclick="loadArchivedPage(' + p + ')">' + p + '</button>';
        } else if (p === meta.current_page - 3 || p === meta.current_page + 3) {
            html += '<span class="btn btn-sm btn-outline-secondary disabled">…</span>';
        }
    }

    html += '<button class="btn btn-sm btn-outline-secondary ' + (meta.current_page === meta.last_page ? 'disabled' : '') + '" onclick="loadArchivedPage(' + (meta.current_page + 1) + ')">›</button>';
    container.innerHTML = html;
}

function updateArchiveMeta(meta) {
    const el = document.getElementById('archiveMeta');
    if (!el || !meta || !meta.total) {
        if (el) el.textContent = '';
        return;
    }
    const from = (meta.current_page - 1) * meta.per_page + 1;
    const to = Math.min(meta.current_page * meta.per_page, meta.total);
    el.textContent = 'Showing ' + from + '–' + to + ' of ' + meta.total + ' snapshots';
}

// Placeholder functions for snapshot actions
function viewSnapshotDetail(metaEncoded) {
    Swal.fire({
        icon: 'info',
        title: 'Coming Soon',
        text: 'Snapshot detail view will be implemented here.',
        showConfirmButton: true
    });
}

function restoreSnapshot(metaEncoded) {
    Swal.fire({
        icon: 'question',
        title: 'Restore Snapshot',
        text: 'Restore functionality will be implemented here.',
        showConfirmButton: true
    });
}

// Archive filter event listeners
document.addEventListener("DOMContentLoaded", function () {
    const archiveSearch = document.getElementById('archiveSearch');
    if (archiveSearch) {
        archiveSearch.addEventListener('input', function() {
            clearTimeout(archiveSearchTimer);
            archiveSearchTimer = setTimeout(function() { loadArchivedPage(1); }, 400);
        });
    }

    const archiveTermFilter = document.getElementById('archiveTermFilter');
    if (archiveTermFilter) {
        archiveTermFilter.addEventListener('change', function() { loadArchivedPage(1); });
    }

    const archivePerPage = document.getElementById('archivePerPage');
    if (archivePerPage) {
        archivePerPage.addEventListener('change', function() { loadArchivedPage(1); });
    }
});

// Helper function to escape HTML
function escapeHtml(str) {
    if (!str) return str || '';
    return String(str).replace(/[&<>"']/g, function(match) {
        if (match === '&') return '&amp;';
        if (match === '<') return '&lt;';
        if (match === '>') return '&gt;';
        if (match === '"') return '&quot;';
        if (match === "'") return '&#039;';
        return match;
    });
}

// Attach to buttons
document.addEventListener("DOMContentLoaded", function () {
    const registerBtn = document.getElementById("register-selected-btn");
    const unregisterBtn = document.getElementById("unregister-selected-btn");
    if (registerBtn) {
        registerBtn.onclick = registerSelectedStudentsBatch;
    }
    if (unregisterBtn) {
        unregisterBtn.onclick = unregisterSelectedStudentsBatch;
    }
});
