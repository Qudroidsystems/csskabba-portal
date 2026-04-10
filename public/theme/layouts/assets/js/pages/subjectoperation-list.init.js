// // ================================================
// // MAIN JAVASCRIPT FOR SUBJECT REGISTRATION PAGE
// // ================================================

// var perPage = 10,
//     checkAll = document.getElementById("checkAll");

// function ensureAxios() {
//     if (typeof axios === 'undefined') {
//         console.error("Axios is not defined. Please include Axios library.");
//         Swal.fire({
//             position: "center",
//             icon: "error",
//             title: "Configuration error",
//             text: "Axios library is missing",
//             showConfirmButton: true
//         });
//         return false;
//     }
//     return true;
// }

// // Checkbox handling
// function ischeckboxcheck() {
//     const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
//     checkboxes.forEach((checkbox) => {
//         checkbox.removeEventListener("change", handleCheckboxChange);
//         checkbox.addEventListener("change", handleCheckboxChange);
//     });
// }

// function handleCheckboxChange(e) {
//     const row = e.target.closest("tr");
//     if (e.target.checked) {
//         row.classList.add("table-active");
//     } else {
//         row.classList.remove("table-active");
//     }

//     const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
//     const registerButton = document.getElementById("register-selected-btn");
//     const unregisterButton = document.getElementById("unregister-selected-btn");

//     if (registerButton) registerButton.classList.toggle("d-none", checkedCount === 0);
//     if (unregisterButton) unregisterButton.classList.toggle("d-none", checkedCount === 0);

//     // Check "Select All" state
//     const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]');
//     if (checkAll) {
//         checkAll.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCount;
//     }
// }

// function refreshCallbacks() {
//     ischeckboxcheck();
// }

// // Update Admission No dropdown
// function updateAdmissionNoOptions(students) {
//     const select = document.getElementById("idadmission");
//     if (!select) return;

//     select.innerHTML = '<option value="ALL">All Admission Nos</option>';

//     const uniqueAdmissionNos = [...new Set(students.map(s => s.admissionno).filter(Boolean))].sort();

//     uniqueAdmissionNos.forEach(admissionNo => {
//         const option = document.createElement("option");
//         option.value = admissionNo;
//         option.text = admissionNo;
//         select.appendChild(option);
//     });
// }

// // Subject selection helpers
// function selectAllSubjects() {
//     document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = true);
// }

// function deselectAllSubjects() {
//     document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = false);
// }

// // Main Filter Function
// function filterData() {
//     if (!ensureAxios()) return;

//     const classValue = document.getElementById("idclass").value;
//     const sessionValue = document.getElementById("idsession").value;
//     const searchValue = document.querySelector(".search")?.value.toLowerCase() || '';
//     const genderValue = document.getElementById("idgender").value;
//     const admissionNoValue = document.getElementById("idadmission").value;

//     if (classValue === 'ALL' || sessionValue === 'ALL') {
//         Swal.fire({
//             icon: "warning",
//             title: "Missing Selection",
//             text: "Please select a class and session",
//             showConfirmButton: true
//         });
//         return;
//     }

//     const tableBody = document.getElementById('studentTableBody');
//     const subjectTeachersContainer = document.getElementById('subjectTeachersContainer');

//     if (tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

//     axios.get('/subjects', {
//         params: {
//             search: searchValue,
//             class_id: classValue,
//             session_id: sessionValue,
//             gender: genderValue,
//             admissionno: admissionNoValue
//         },
//         headers: {
//             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
//             'X-Requested-With': 'XMLHttpRequest'
//         }
//     }).then(function (response) {
//         const parser = new DOMParser();
//         const doc = parser.parseFromString(response.data, 'text/html');

//         // Update table body
//         const newTableBody = doc.querySelector('#studentTableBody');
//         if (newTableBody && tableBody) tableBody.innerHTML = newTableBody.innerHTML;

//         // Update pagination if exists
//         const newPagination = doc.querySelector('.pagination');
//         const currentPagination = document.querySelector('.pagination');
//         if (newPagination && currentPagination) currentPagination.innerHTML = newPagination.innerHTML;

//         // Update student count
//         const newCount = doc.querySelector('#studentcount');
//         const currentCount = document.getElementById('studentcount');
//         if (newCount && currentCount) currentCount.textContent = newCount.textContent;

//         // Update subject teachers
//         const newSubjectContainer = doc.querySelector('#subjectTeachersContainer');
//         if (newSubjectContainer && subjectTeachersContainer) {
//             subjectTeachersContainer.innerHTML = newSubjectContainer.innerHTML;
//         }

//         const students = [];
//         doc.querySelectorAll('#studentTableBody tr').forEach(row => {
//             const admissionCell = row.querySelector('.admissionno');
//             if (admissionCell) {
//                 const admissionNo = admissionCell.dataset.admissionno || admissionCell.textContent.trim();
//                 if (admissionNo) students.push({ admissionno: admissionNo });
//             }
//         });

//         updateAdmissionNoOptions(students);
//         refreshCallbacks();
//         setupPaginationLinks();

//     }).catch(function (error) {
//         console.error("Filter error:", error);
//         if (tableBody) {
//             tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Error loading data. Please try again.</td></tr>';
//         }
//         Swal.fire({
//             icon: "error",
//             title: "Error",
//             text: error.response?.data?.message || "Failed to load data",
//             showConfirmButton: true
//         });
//     });
// }

// function setupPaginationLinks() {
//     document.querySelectorAll('.pagination a').forEach(link => {
//         link.addEventListener('click', function(e) {
//             e.preventDefault();
//             if (!this.classList.contains('disabled')) {
//                 loadPage(this.href);
//             }
//         });
//     });
// }

// function loadPage(url) {
//     if (!ensureAxios()) return;

//     const tableBody = document.getElementById('studentTableBody');
//     if (tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

//     axios.get(url, {
//         headers: {
//             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
//             'X-Requested-With': 'XMLHttpRequest'
//         }
//     }).then(response => {
//         const parser = new DOMParser();
//         const doc = parser.parseFromString(response.data, 'text/html');

//         const newTableBody = doc.querySelector('#studentTableBody');
//         if (newTableBody && tableBody) tableBody.innerHTML = newTableBody.innerHTML;

//         refreshCallbacks();
//         setupPaginationLinks();
//     }).catch(error => {
//         console.error("Pagination error:", error);
//         if (tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Failed to load page.</td></tr>';
//     });
// }

// // Updated loadRegisteredClasses - Matches new modal (no tabs, clean UI)
// async function loadRegisteredClasses() {
//     if (!ensureAxios()) return;

//     const modalContent = document.getElementById('registeredClassesContent');
//     const classId = document.getElementById('idclass').value;
//     const sessionId = document.getElementById('idsession').value;

//     if (classId === 'ALL' || sessionId === 'ALL') {
//         modalContent.innerHTML = `
//             <div class="text-center py-5">
//                 <i class="ri-error-warning-line fs-1 text-warning d-block mb-3"></i>
//                 <h5 class="text-muted">Please select Class and Session</h5>
//                 <p class="text-muted">Choose a class and academic session above, then reopen this modal.</p>
//             </div>`;
//         return;
//     }

//     modalContent.innerHTML = `
//         <div class="text-center py-5">
//             <div class="spinner-border text-primary mb-4" style="width:3.5rem;height:3.5rem;"></div>
//             <p class="text-muted">Loading registered subjects and teachers...</p>
//         </div>`;

//     try {
//         const response = await axios.get('/subjects/registered-classes', {
//             params: { class_id: classId, session_id: sessionId },
//             headers: {
//                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
//             }
//         });

//         if (response.data.success && response.data.data.length) {
//             let html = '';
//             response.data.data.forEach(termData => {
//                 html += buildTermPane(termData);
//             });
//             modalContent.innerHTML = html;
//         } else {
//             modalContent.innerHTML = `
//                 <div class="alert alert-info text-center py-5">
//                     <i class="ri-information-line fs-3 d-block mb-3"></i>
//                     No registered subjects found for the selected class and session.
//                 </div>`;
//         }
//     } catch (error) {
//         console.error("Load registered classes error:", error);
//         modalContent.innerHTML = `
//             <div class="alert alert-danger text-center py-5">
//                 Failed to load data. Please try again.
//             </div>`;
//     }
// }

// // Clean Combined Subject + Teacher Display
// function buildTermPane(termData) {
//     const subjects = termData.subjects_teachers || [];
//     const sortedSubjects = [...subjects].sort((a, b) =>
//         (a.name || '').localeCompare(b.name || '', undefined, { sensitivity: 'base' })
//     );

//     let items = '';
//     sortedSubjects.forEach((subject, index) => {
//         const teachers = subject.teachers && subject.teachers.length
//             ? subject.teachers.map(t => esc(t.name)).join(', ')
//             : '<span class="text-muted">— Not assigned</span>';

//         items += `
//             <div class="subject-item d-flex align-items-start gap-3 py-3 border-bottom">
//                 <div class="subject-num">${index + 1}</div>
//                 <div class="flex-grow-1">
//                     <div class="fw-semibold">${esc(subject.name)}</div>
//                     <div class="small text-muted mt-1">
//                         <i class="ri-user-follow-line me-1"></i>${teachers}
//                     </div>
//                 </div>
//                 <div>
//                     <span class="badge bg-primary-subtle text-primary px-3 py-1">
//                         ${subject.student_count || 0} students
//                     </span>
//                 </div>
//             </div>`;
//     });

//     return `
//         <div class="card border-0 shadow-sm mb-4 term-card">
//             <div class="card-header py-3" style="background:linear-gradient(135deg,#1e3a5f,#2563eb); color:white;">
//                 <div class="d-flex justify-content-between align-items-center">
//                     <div>
//                         <h5 class="mb-1">${esc(termData.class_name)} ${esc(termData.arm_name)}</h5>
//                         <small>${esc(termData.session_name)} — ${esc(termData.term_name)}</small>
//                     </div>
//                     <div class="text-end">
//                         <span class="badge bg-white text-dark px-3 py-2">${termData.student_count || 0} Students</span>
//                         <div class="mt-1 small">${sortedSubjects.length} Subjects</div>
//                     </div>
//                 </div>
//             </div>
//             <div class="card-body p-0">
//                 <div class="subject-list">
//                     ${items || '<div class="text-center text-muted py-5">No subjects found for this term.</div>'}
//                 </div>
//             </div>
//         </div>`;
// }

// function esc(str) {
//     if (!str) return '';
//     return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
// }

// // Registration Functions
// async function registerSelectedStudentsBatch() {
//     // ... your existing logic (kept minimal for now)
//     if (!ensureAxios()) return;
//     Swal.fire('Info', 'Registration function triggered', 'info');
//     // Add your full axios post logic here if needed
// }

// function openUnregisterModal() {
//     Swal.fire('Info', 'Unregister modal opened', 'info');
// }

// function proceedUnregister() {
//     Swal.fire('Success', 'Unregistration completed', 'success');
// }

// // DOM Ready
// document.addEventListener("DOMContentLoaded", function () {
//     refreshCallbacks();

//     // Initialize Choices.js if available
//     if (typeof Choices !== 'undefined') {
//         ['idclass', 'idsession', 'idgender', 'idadmission'].forEach(id => {
//             const el = document.getElementById(id);
//             if (el) new Choices(el, { searchEnabled: true });
//         });
//     }

//     // Check All
//     if (checkAll) {
//         checkAll.addEventListener('click', function () {
//             document.querySelectorAll('tbody input[name="chk_child"]').forEach(cb => {
//                 cb.checked = this.checked;
//                 const row = cb.closest("tr");
//                 if (row) row.classList.toggle("table-active", this.checked);
//             });
//             handleCheckboxChange({ target: { checked: this.checked } }); // Trigger count update
//         });
//     }

//     // Button handlers
//     const registerBtn = document.getElementById("register-selected-btn");
//     const unregisterBtn = document.getElementById("unregister-selected-btn");

//     if (registerBtn) registerBtn.addEventListener('click', registerSelectedStudentsBatch);
//     if (unregisterBtn) unregisterBtn.addEventListener('click', openUnregisterModal);

//     // Modal listener
//     const registeredModal = document.getElementById('registeredClassesModal');
//     if (registeredModal) {
//         registeredModal.addEventListener('show.bs.modal', loadRegisteredClasses);
//     }
// });
