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

    const registeredClassesModal = document.getElementById('registeredClassesModal');
    if (registeredClassesModal) {
        registeredClassesModal.addEventListener('show.bs.modal', function () {
            loadRegisteredClasses();
        });
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
    const loadingSpinner = document.getElementById("register-loading-spinner"); // Reusing same spinner

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





// Store current registered classes data for printing
let currentRegisteredClassesData = null;

// Modified loadRegisteredClasses to store data for printing
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

            // Store data for printing
            currentRegisteredClassesData = {
                data: classes,
                class_id: classId,
                session_id: sessionId,
                class_name: classes[0]?.class_name || 'N/A',
                arm_name: classes[0]?.arm_name || 'N/A',
                session_name: classes[0]?.session_name || 'N/A'
            };

            if (!classes || classes.length === 0) {
                modalContent.innerHTML = '<div class="text-center py-5"><i class="ri-information-line ri-3x text-muted"></i><p class="text-muted mt-3 mb-0">No registered classes found.</p></div>';
                return;
            }

            // Render the card-based UI (same as before)
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

                    const subjectStudentCount = totalStudents;

                    html += `
                    <div class="subject-card p-3 d-flex gap-3 align-items-start" style="border-right:0.5px solid #e2e8f0; border-bottom:0.5px solid #e2e8f0; transition:all 0.2s ease;"
                         onmouseenter="this.style.backgroundColor='#fafbff'"
                         onmouseleave="this.style.backgroundColor='transparent'">
                        <div class="subject-num" style="width:30px; height:30px; background:#EEEDFE; color:#3C3489; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:600; flex-shrink:0;">${idx + 1}</div>
                        <div class="subject-info flex-grow-1">
                            <div class="fw-semibold" style="font-size:0.9rem; color:#1e293b;">${escapeHtml(subjectName)}</div>
                            <div class="text-muted small mt-1" style="font-size:0.75rem;">
                                <i class="ri-user-star-line me-1"></i>${escapeHtml(teacherName)}
                            </div>
                            <span class="badge mt-2" style="background:#EAF3DE; color:#27500A; font-size:10px; padding:3px 10px; border-radius:20px; display:inline-flex; align-items:center; gap:4px;">
                                <i class="ri-group-line" style="font-size:10px;"></i>${subjectStudentCount} students
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

// Function to fetch school information and generate PDF/Print
async function printRegisteredClasses() {
    if (!currentRegisteredClassesData || !currentRegisteredClassesData.data) {
        Swal.fire({
            icon: 'warning',
            title: 'No Data',
            text: 'Please load registered classes first.',
            showConfirmButton: true
        });
        return;
    }

    // Show loading indicator
    Swal.fire({
        title: 'Preparing PDF...',
        text: 'Please wait while we generate your document.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        // Fetch school information
        const schoolInfoResponse = await axios.get('/api/school-information/active', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const schoolInfo = schoolInfoResponse.data.data || {};

        // Generate print HTML
        const printHtml = generatePrintHtml(currentRegisteredClassesData, schoolInfo);

        // Create a hidden iframe for printing
        const iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);

        const iframeDoc = iframe.contentWindow.document;
        iframeDoc.open();
        iframeDoc.write(printHtml);
        iframeDoc.close();

        // Wait for images to load then print
        setTimeout(() => {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();

            // Remove iframe after printing
            setTimeout(() => {
                document.body.removeChild(iframe);
            }, 1000);
        }, 500);

        Swal.close();

    } catch (error) {
        console.error('Error preparing print:', error);
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to prepare print document. Please try again.',
            showConfirmButton: true
        });
    }
}

// Generate professional print HTML
function generatePrintHtml(data, schoolInfo) {
    const classes = data.data;
    const currentDate = new Date().toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
    const currentTime = new Date().toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit'
    });

    // Get school logo URL
    const schoolLogo = schoolInfo.logo_url || (schoolInfo.school_logo ?
        (schoolInfo.school_logo.startsWith('http') ? schoolInfo.school_logo : `/storage/${schoolInfo.school_logo}`) :
        '/theme/layouts/assets/images/logo-dark.png');

    let subjectsHtml = '';

    classes.forEach((termGroup) => {
        const subjectsArray = termGroup.subjects ? termGroup.subjects.split(',').map(s => s.trim()) : [];
        const teachersArray = termGroup.teachers ? termGroup.teachers.split(',').map(t => t.trim()) : [];
        const totalStudents = termGroup.student_count || 0;

        subjectsHtml += `
            <div class="print-term-card">
                <div class="print-term-header">
                    <h3 class="print-term-title">${escapeHtml(termGroup.class_name)} ${escapeHtml(termGroup.arm_name)} — ${escapeHtml(termGroup.session_name)}</h3>
                    <p class="print-term-subtitle">${escapeHtml(termGroup.term_name)} Term</p>
                </div>
                <table class="print-subjects-table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="40%">Subject Name</th>
                            <th width="35%">Teacher</th>
                            <th width="20%">Students</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        subjectsArray.forEach((subjectName, idx) => {
            let teacherName = '— Not assigned';
            if (teachersArray.length > idx) {
                teacherName = teachersArray[idx];
            } else if (teachersArray.length > 0) {
                teacherName = teachersArray[0];
            }

            subjectsHtml += `
                <tr>
                    <td style="text-align: center;">${idx + 1}</td>
                    <td><strong>${escapeHtml(subjectName)}</strong></td>
                    <td>${escapeHtml(teacherName)}</td>
                    <td style="text-align: center;">${totalStudents}</td>
                </tr>
            `;
        });

        subjectsHtml += `
                    </tbody>
                </table>
            </div>
        `;
    });

    return `<!DOCTYPE html>
    <html>
    <head>
        <title>Registered Classes Overview - ${escapeHtml(data.class_name)} ${escapeHtml(data.arm_name)}</title>
        <meta charset="utf-8">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                background: white;
                padding: 20px;
            }

            .print-container {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
            }

            /* Header Section */
            .print-header {
                margin-bottom: 30px;
                border-bottom: 3px solid #1e3a5f;
                padding-bottom: 20px;
            }

            .print-school-info {
                display: flex;
                align-items: center;
                gap: 25px;
                margin-bottom: 20px;
            }

            .print-logo {
                max-width: 90px;
                max-height: 90px;
                object-fit: contain;
            }

            .print-school-details {
                flex: 1;
            }

            .print-school-name {
                font-size: 26px;
                font-weight: bold;
                color: #1e3a5f;
                margin: 0 0 8px 0;
                letter-spacing: 1px;
            }

            .print-school-motto {
                font-style: italic;
                color: #666;
                margin: 0 0 10px 0;
                font-size: 14px;
            }

            .print-school-address, .print-school-contact {
                font-size: 12px;
                color: #555;
                margin: 3px 0;
            }

            .print-school-address i, .print-school-contact i {
                margin-right: 5px;
            }

            /* Title Section */
            .print-title-section {
                text-align: center;
                margin: 30px 0 20px 0;
            }

            .print-title {
                font-size: 22px;
                font-weight: bold;
                color: #1e3a5f;
                margin: 0;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .print-subtitle {
                font-size: 14px;
                color: #666;
                margin: 5px 0 0 0;
            }

            .print-meta {
                text-align: center;
                font-size: 12px;
                color: #888;
                margin-bottom: 30px;
                padding-bottom: 15px;
                border-bottom: 1px dashed #ddd;
            }

            /* Term Cards */
            .print-term-card {
                margin-bottom: 35px;
                page-break-inside: avoid;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }

            .print-term-header {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                padding: 15px 20px;
                border-bottom: 2px solid #1e3a5f;
            }

            .print-term-title {
                font-size: 18px;
                font-weight: bold;
                color: #1e3a5f;
                margin: 0;
            }

            .print-term-subtitle {
                font-size: 13px;
                color: #666;
                margin: 5px 0 0 0;
            }

            /* Subjects Table */
            .print-subjects-table {
                width: 100%;
                border-collapse: collapse;
            }

            .print-subjects-table th {
                background: #f1f3f5;
                padding: 12px 15px;
                text-align: left;
                font-size: 13px;
                font-weight: bold;
                color: #495057;
                border: 1px solid #dee2e6;
            }

            .print-subjects-table td {
                padding: 10px 15px;
                font-size: 12px;
                border: 1px solid #dee2e6;
                vertical-align: top;
            }

            .print-subjects-table tbody tr:hover {
                background: #f8f9fa;
            }

            /* Footer */
            .print-footer {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #dee2e6;
                text-align: center;
                font-size: 10px;
                color: #999;
            }

            .print-footer p {
                margin: 5px 0;
            }

            /* Summary Stats */
            .print-summary {
                display: flex;
                justify-content: space-around;
                margin: 20px 0 30px 0;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 8px;
            }

            .print-summary-item {
                text-align: center;
            }

            .print-summary-label {
                font-size: 12px;
                color: #666;
                margin-bottom: 5px;
            }

            .print-summary-value {
                font-size: 20px;
                font-weight: bold;
                color: #1e3a5f;
            }

            @media print {
                body {
                    padding: 0;
                    margin: 0;
                }

                .print-container {
                    max-width: 100%;
                    padding: 20px;
                }

                .print-term-card {
                    page-break-inside: avoid;
                }
            }
        </style>
    </head>
    <body>
        <div class="print-container">
            <!-- School Header -->
            <div class="print-header">
                <div class="print-school-info">
                    ${schoolLogo ? `<img src="${schoolLogo}" alt="School Logo" class="print-logo" onerror="this.style.display='none'">` : ''}
                    <div class="print-school-details">
                        <h1 class="print-school-name">${escapeHtml(schoolInfo.school_name || 'School Name')}</h1>
                        ${schoolInfo.school_motto ? `<p class="print-school-motto">"${escapeHtml(schoolInfo.school_motto)}"</p>` : ''}
                        ${schoolInfo.school_address ? `<p class="print-school-address"><i>📍</i> ${escapeHtml(schoolInfo.school_address)}</p>` : ''}
                        <p class="print-school-contact">
                            ${schoolInfo.school_phone ? `<i>📞</i> ${escapeHtml(schoolInfo.school_phone)}` : ''}
                            ${schoolInfo.school_phone && schoolInfo.school_email ? ' | ' : ''}
                            ${schoolInfo.school_email ? `<i>✉️</i> ${escapeHtml(schoolInfo.school_email)}` : ''}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Title Section -->
            <div class="print-title-section">
                <h2 class="print-title">Registered Classes Overview</h2>
                <p class="print-subtitle">Academic Session: ${escapeHtml(data.session_name)}</p>
            </div>

            <div class="print-meta">
                <p>Generated on: ${currentDate} at ${currentTime}</p>
                <p>Class: ${escapeHtml(data.class_name)} ${escapeHtml(data.arm_name)}</p>
            </div>

            <!-- Summary Statistics -->
            <div class="print-summary">
                <div class="print-summary-item">
                    <div class="print-summary-label">Total Terms</div>
                    <div class="print-summary-value">${classes.length}</div>
                </div>
                <div class="print-summary-item">
                    <div class="print-summary-label">Total Subjects</div>
                    <div class="print-summary-value">${classes.reduce((sum, c) => sum + parseInt(c.subject_count || 0), 0)}</div>
                </div>
                <div class="print-summary-item">
                    <div class="print-summary-label">Total Students</div>
                    <div class="print-summary-value">${classes[0]?.student_count || 0}</div>
                </div>
            </div>

            <!-- Terms and Subjects -->
            ${subjectsHtml}

            <!-- Footer -->
            <div class="print-footer">
                <p>This is a computer-generated document. No signature is required.</p>
                <p>© ${new Date().getFullYear()} ${escapeHtml(schoolInfo.school_name || 'School Name')}. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>`;
}

// Make sure escapeHtml function exists
function escapeHtml(str) {
    if (!str) return str ?? '';
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


