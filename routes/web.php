<?php

use \App\Http\Controllers\SchoolInformationController;
use App\Http\Controllers\Admin\AdminScoreEntryController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\ScholarshipController;
use App\Http\Controllers\Admin\SiblingGroupController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceSettingController;
use App\Http\Controllers\BiodataController;
use App\Http\Controllers\BroadsheetController;
use App\Http\Controllers\CBTController;
use App\Http\Controllers\ClassBroadsheetController;
use App\Http\Controllers\ClasscategoryController;
use App\Http\Controllers\ClassOperationController;
use App\Http\Controllers\ClassTeacherController;
use App\Http\Controllers\CompulsorySubjectClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamPauseController;
use App\Http\Controllers\ExamTimetableController;
use App\Http\Controllers\Finance\PayrollController;
use App\Http\Controllers\Finance\StaffPaymentController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobStatusController;
use App\Http\Controllers\MockSubjectVettingController;
use App\Http\Controllers\MyClassController;
use App\Http\Controllers\MyMockSubjectVettingsController;
use App\Http\Controllers\MyPrincipalsCommentController;
use App\Http\Controllers\MyresultroomController;
use App\Http\Controllers\MyScoreSheetController;
use App\Http\Controllers\MySubjectController;
use App\Http\Controllers\MySubjectVettingsController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\Payment\EnhancedSchoolPaymentController;
use App\Http\Controllers\Payment\FlexibleOnlinePaymentController;
use App\Http\Controllers\Payment\OnlinePaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PrincipalsCommentController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\Reports\AnalysisReportController;
use App\Http\Controllers\Reports\FinancialReportController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SchoolArmController;
use App\Http\Controllers\SchoolBillController;
use App\Http\Controllers\SchoolBillTermSessionController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolHouseController;
use App\Http\Controllers\SchoolPaymentController;
use App\Http\Controllers\SchoolsessionController;
use App\Http\Controllers\SchooltermController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffImageUploadController;
use App\Http\Controllers\StudentAssessmentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentHouseController;
use App\Http\Controllers\StudentIdCardController;
use App\Http\Controllers\StudentImageUploadController;
use App\Http\Controllers\StudentPaymentController;
use App\Http\Controllers\StudentpersonalityprofileController;
use App\Http\Controllers\StudentResultsController;
use App\Http\Controllers\SubjectClassController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectOperationController;
use App\Http\Controllers\SubjectTeacherController;
use App\Http\Controllers\SubjectVettingController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\TimetableReportController;
use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViewStudentController;
use App\Http\Controllers\ViewStudentMockReportController;
use App\Http\Controllers\ViewStudentReportController;
use Illuminate\Support\Facades\Route;






// Redirect root to the login page
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/test-sibling-data/{id}', function($id) {
    $group = DB::table('sibling_groups')->where('id', $id)->first();
    $students = DB::table('sibling_group_students')
        ->where('sibling_group_id', $id)
        ->join('studentRegistration', 'sibling_group_students.student_id', '=', 'studentRegistration.id')
        ->select('studentRegistration.id', 'studentRegistration.firstname', 'studentRegistration.lastname', 'studentRegistration.admissionNo')
        ->get();

    return response()->json([
        'group' => $group,
        'students' => $students,
        'student_count' => $students->count()
    ]);
});

Auth::routes();
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/student-id-cards/verify/{token}',[StudentIdCardController::class, 'verify'])->name('student-id-cards.verify');
Route::group(['middleware' => ['auth']], function () {
        // These must come BEFORE the resource route
        Route::get('/users/all', [UserController::class, 'allUsers'])->name('users.all');
        Route::get('/users/paginate', [UserController::class, 'paginate'])->name('users.paginate');
        Route::get('/users/get-students', [UserController::class, 'getStudents'])->name('get.students');
        Route::get('/users/add-student', [UserController::class, 'createFromStudentForm'])->name('users.add-student-form');

        // Student creation routes
        Route::post('/users/store-student', [UserController::class, 'storeStudent'])->name('users.store-student');
        Route::post('/users/mass-create-students', [UserController::class, 'massCreateStudents'])->name('users.mass-create-students');
        Route::post('/users/create-from-student', [UserController::class, 'createFromStudent'])->name('users.createFromStudent');

        // Password management routes
        Route::post('/users/revoke-student-password', [UserController::class, 'revokeStudentPassword'])->name('users.revoke-student-password');
        Route::post('/users/reset-single-password/{id}', [UserController::class, 'resetSingleStudentPassword'])->name('users.reset-single-password');

        // Credentials routes
        Route::post('/users/get-student-credentials', [UserController::class, 'getStudentCredentials'])->name('users.get-student-credentials');
        Route::post('/users/bulk-reprint', [UserController::class, 'bulkReprintCredentials'])->name('users.bulk-reprint');

        // Delete user
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        // Resource route - MUST BE LAST
        Route::resource('users', UserController::class);


        // Student ID Cards
        // Route::prefix('student-id-cards')->name('student-id-cards.')->group(function () {
        //     Route::get('/', [StudentIdCardController::class, 'index'])->name('index');
        //     Route::get('/load-students', [StudentIdCardController::class, 'loadStudents'])->name('load-students');
        //     Route::post('/preview', [StudentIdCardController::class, 'preview'])->name('preview');
        //     Route::post('/download', [StudentIdCardController::class, 'download'])->name('download');
        // });

        Route::get('/student-id-cards',[StudentIdCardController::class, 'index'])->name('student-id-cards.index');
        Route::get('/student-id-cards/load-students',[StudentIdCardController::class, 'loadStudents'])->name('student-id-cards.load-students');
        Route::post('/student-id-cards/preview', [StudentIdCardController::class, 'preview'])->name('student-id-cards.preview');
        Route::post('/student-id-cards/download',[StudentIdCardController::class, 'download']) ->name('student-id-cards.download');

        // ── Roles ────────────────────────────────────────────────────────
        Route::post('roles/bulk-remove-users', [RoleController::class, 'bulkRemoveUsers'])->name('roles.bulkremoveusers');
        Route::get('/roles/{role}/users',      [RoleController::class, 'getRoleUsers'])->name('roles.users');
        Route::resource('roles', RoleController::class);

        // ── Other ────────────────────────────────────────────────────────
        Route::get('/user/overview/{id}',     [UserController::class, 'show'])->name('users.overview');
        Route::get('/users/roles',            [UserController::class, 'roles']);
        Route::resource('permissions', PermissionController::class);
        Route::get('/dashboard',              [DashboardController::class, 'index'])->name('dashboard');

        // Remove these — they were the duplicates/conflicts:
        // Route::get('/get-students', ...)           ← DELETE this line
        // Route::get('/users/get-students', ...)     ← keep only the one above

    // ===================================================================
    // PROFILE & BIODATA ROUTES - FULLY CORRECTED AND CLEANED
    // ===================================================================
    Route::prefix('profile')->name('profile.')->group(function () {
        // View profile settings
        Route::get('/settings/{id}', [BiodataController::class, 'show'])->name('settings');

        // Personal info update
        Route::post('/update-info', [BiodataController::class, 'updateProfile'])->name('update-info');

        // Avatar upload (AJAX - matches Blade JS)
        Route::post('/update-avatar', [BiodataController::class, 'updateAvatar'])->name('update-avatar');

        // Student updates
        Route::post('/update-student-info', [BiodataController::class, 'updateStudentInfo'])->name('update-student-info');
        Route::post('/update-parent-info', [BiodataController::class, 'updateParentInfo'])->name('update-parent-info');

        // Staff updates
        Route::post('/update-employment-info', [BiodataController::class, 'updateEmploymentInfo'])->name('update-employment-info');
        Route::post('/add-qualification', [BiodataController::class, 'storeQualification'])->name('add-qualification');
        Route::post('/update-qualification/{id}', [BiodataController::class, 'updateQualification'])->name('update-qualification');
        Route::delete('/delete-qualification/{id}', [BiodataController::class, 'deleteQualification'])->name('delete-qualification');

        // Security: Email & Password change (AJAX - matches Blade JS)
        Route::post('/update-email', [BiodataController::class, 'ajaxemailupdate'])->name('update-email');
        Route::post('/update-password', [BiodataController::class, 'ajaxpasswordupdate'])->name('update-password');
    });


    Route::get('/adduser/{id}', [RoleController::class, 'adduser'])->name('roles.adduser');
    Route::post('/updateuserrole', [RoleController::class, 'updateuserrole'])->name('roles.updateuserrole');
    Route::delete('roles/removeuserrole/{userid}/{roleid}', [RoleController::class, 'removeuserrole'])->name('roles.removeuserrole');

    Route::resource('subject', SubjectController::class);
    Route::get('/subjectid/{subjectid}', [SubjectController::class, 'deletesubject'])->name('subject.deletesubject');
    Route::post('subjectid', [SubjectController::class, 'updatesubject'])->name('subject.updatesubject');

    Route::resource('subjectclass', SubjectClassController::class);
    Route::delete('subjectclass/deletesubjectclass/{subjectclassid}', [SubjectClassController::class, 'deletesubjectclass'])->name('subjectclass.deletesubjectclass');
    Route::get('/subjectclass/assignments/{subjectteacherid}', [SubjectClassController::class, 'assignments'])->name('subjectclass.assignments');
    Route::get('/subjectclass/assignments-by-teacher/{subjectTeacherId}', [SubjectClassController::class, 'assignmentsBySubjectTeacher'])->name('subjectclass.assignmentsByTeacher');


    Route::resource('staff', StaffController::class);


    Route::resource('subjectteacher', SubjectTeacherController::class)->except(['update']);
    Route::match(['put', 'post'], 'subjectteacher/{id}', [SubjectTeacherController::class, 'update'])->name('subjectteacher.update');
    Route::get('subjectteacher/{id}/subjects', [SubjectTeacherController::class, 'getSubjects'])->name('subjectteacher.subjects');
    Route::post('subjectteacher/delete', [SubjectTeacherController::class, 'deletesubjectteacher'])->name('subjectteacher.delete');

    // // Class Teacher Management Routes
    // Route::get('classteacher/data', [ClassTeacherController::class, 'data'])->name('classteacher.data');
    // Route::get('classteacher/stats', [ClassTeacherController::class, 'stats'])->name('classteacher.stats');
    // Route::get('classteacher/assignments/{staffId}/{termId}/{sessionId}', [ClassTeacherController::class, 'assignments'])->name('classteacher.assignments');
    // Route::post('classteacher/bulk-destroy', [ClassTeacherController::class, 'deleteMultiple'])->name('classteacher.bulk-destroy');
    // Route::get('/classteacher/assignments/{staffId}/{termId}/{sessionId}', [ClassTeacherController::class, 'assignments'])->name('classteacher.assignments');
    // Route::post('/classteacher/delete', [ClassTeacherController::class, 'deleteMultiple'])->name('classteacher.deleteMultiple');
    // Route::resource('classteacher', ClassTeacherController::class);

    // Class Teacher Management Routes
    Route::prefix('classteacher')->name('classteacher.')->group(function () {
        Route::get('/', [ClassTeacherController::class, 'index'])->name('index');
        Route::get('/data', [ClassTeacherController::class, 'data'])->name('data');
        Route::get('/stats', [ClassTeacherController::class, 'stats'])->name('stats');
        Route::get('/assignments/{staffId}/{termId}/{sessionId}', [ClassTeacherController::class, 'assignments'])->name('assignments');
        Route::get('/{id}', [ClassTeacherController::class, 'show'])->name('show');
        Route::post('/', [ClassTeacherController::class, 'store'])->name('store');
        Route::put('/{id}', [ClassTeacherController::class, 'update'])->name('update');
        Route::delete('/{id}', [ClassTeacherController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-destroy', [ClassTeacherController::class, 'deleteMultiple'])->name('bulk-destroy');
    });


    Route::resource('session', SchoolsessionController::class);
    Route::get('/sessionid/{sessionid}', [SchoolsessionController::class, 'deletesession'])->name('session.deletesession');
    Route::post('updatesessionid', [SchoolsessionController::class, 'updatesession'])->name('session.updatesession');

    Route::resource('schoolhouse', SchoolHouseController::class);
    Route::post('schoolhouse/deletehouse', [SchoolHouseController::class, 'deletehouse'])->name('schoolhouse.deletehouse');
    Route::post('schoolhouse/updatehouse', [SchoolHouseController::class, 'updatehouse'])->name('schoolhouse.updatehouse');



    Route::resource('term', SchooltermController::class);
    Route::patch('term/{term}/status', [SchooltermController::class, 'updateStatus'])->name('term.status.update');
    Route::post('term/deleteterm', [SchooltermController::class, 'deleteterm'])->name('term.deleteterm');
    Route::post('term/updateterm', [SchooltermController::class, 'updateterm'])->name('term.updateterm');

    Route::resource('schoolarm', SchoolArmController::class);
    Route::post('schoolarm/deletearm', [SchoolArmController::class, 'deletearm'])->name('schoolarm.deletearm');
    Route::post('schoolarm/updatearm', [SchoolArmController::class, 'updatearm'])->name('schoolarm.updatearm');
    Route::post('/schoolclass/deletes-schoolclass', [SchoolClassController::class, 'deleteschoolclass'])->name('schoolclass.deleteschoolclass');
    Route::get('/schoolclasses/{getArms}/arms', [SchoolClassController::class, 'getArms'])->name('schoolclass.getArms');

    Route::get('schoolclass', [SchoolClassController::class, 'index'])->name('schoolclass.index');
    Route::post('schoolclass', [SchoolClassController::class, 'store'])->name('schoolclass.store');
    Route::put('schoolclass/{schoolclass}', [SchoolClassController::class, 'update'])->name('schoolclass.update');
    Route::delete('schoolclass/{schoolclass}', [SchoolClassController::class, 'destroy'])->name('schoolclass.destroy');
    Route::post('schoolclass/deleteschoolclass', [SchoolClassController::class, 'deleteschoolclass'])->name('schoolclass.deleteschoolclass');
    Route::get('schoolclass/{schoolclass}/arms', [SchoolClassController::class, 'getArms'])->name('schoolclass.getarms');
    Route::put('/schoolclass/{id}', [SchoolClassController::class, 'update'])->name('schoolclass.update');


    // ================================================
    // STUDENT MANAGEMENT ROUTES
    // ================================================
    Route::resource('student', StudentController::class)->except(['destroy']);

    // Additional student routes
    Route::prefix('students')->group(function () {
        Route::get('/data', [StudentController::class, 'data'])->name('student.data');
        Route::get('/last-admission-number', [StudentController::class, 'getLastAdmissionNumber'])->name('student.getLastAdmissionNumber');
        Route::get('/report', [StudentController::class, 'generateReport'])->name('students.report');
        Route::post('/destroy-multiple', [StudentController::class, 'destroyMultiple'])->name('student.destroyMultiple');
        Route::get('/optimized', [StudentController::class, 'getStudentsOptimized'])->name('students.optimized'); // THIS IS THE KEY ROUTE

        // Add these missing routes
    Route::post('/bulk-update-status', [StudentController::class, 'bulkUpdateStatus'])->name('students.bulk-update-status');
    Route::get('/by-class-session', [StudentController::class, 'getStudentsByClassAndSession'])->name('students.by-class-session');
    });


    // Add this separate route (not inside students prefix)
Route::get('/students-in-term', [StudentController::class, 'getStudentsInTerm'])->name('students.in-term');
Route::post('/students/remove-from-term', [StudentController::class, 'removeFromTerm'])->name('students.remove-from-term');
Route::post('/students/bulk-remove-from-term', [StudentController::class, 'bulkRemoveFromTerm'])->name('students.bulk-remove-from-term');


    // Individual student operations
    Route::prefix('student')->group(function () {
        Route::delete('/{id}/destroy', [StudentController::class, 'destroy'])->name('student.destroy');
        Route::get('/studentid/{studentid}', [StudentController::class, 'deletestudent'])->name('student.deletestudent');
        Route::get('/overview/{id}', [StudentController::class, 'overview'])->name('student.overview');
        Route::get('/settings/{id}', [StudentController::class, 'setting'])->name('student.settings');
        Route::put('/updateclass', [StudentController::class, 'updateClass'])->name('student.updateclass');
        Route::post('/generate-student-pdf', [StudentController::class, 'generateStudentPdf'])->name('student.pdf');
    });

    // Bulk operations
    Route::prefix('student')->group(function () {
        Route::get('/bulkupload', [StudentController::class, 'bulkupload'])->name('student.bulkupload');
        Route::post('/bulkuploadsave', [StudentController::class, 'bulkuploadsave'])->name('student.bulkuploadsave');
        Route::get('/batchindex', [StudentController::class, 'batchindex'])->name('studentbatchindex');
        Route::delete('/deletestudentbatch', [StudentController::class, 'deletestudentbatch'])->name('student.deletestudentbatch');
    });

    // ================================================
    // SYSTEM INFO ROUTES
    // ================================================
    Route::get('/system/active-term-session', function() {
        $activeTerm = \App\Models\Schoolterm::where('status', true)->first();
        $activeSession = \App\Models\Schoolsession::where('status', 'Current')->first();

        return response()->json([
            'success' => true,
            'term' => $activeTerm ? [
                'id' => $activeTerm->id,
                'term' => $activeTerm->term,
                'status' => $activeTerm->status
            ] : null,
            'session' => $activeSession ? [
                'id' => $activeSession->id,
                'session' => $activeSession->session,
                'status' => $activeSession->status
            ] : null
        ]);
    })->name('system.active-term-session');

    // ================================================
    // STUDENT CURRENT TERM ROUTES
    // ================================================
    Route::prefix('student-current-term')->group(function () {
        Route::get('/student/{studentId}', [StudentController::class, 'getCurrentTerm']);
        Route::get('/student/{studentId}/active', [StudentController::class, 'getActiveTerm']);
        Route::put('/student/{studentId}', [StudentController::class, 'updateCurrentTerm']);
        Route::post('/bulk-update', [StudentController::class, 'bulkUpdateCurrentTerm'])->name('student.current-term.bulk-update');
        Route::get('/students', [StudentController::class, 'getStudentsByCurrentFilters']);
    });

    // ================================================
    // STUDENT TERM HISTORY ROUTES
    // ================================================
    Route::prefix('student')->group(function () {
        Route::get('/{id}/current-info', [StudentController::class, 'getCurrentInfo'])->name('student.current-info');
        Route::get('/{id}/all-terms', [StudentController::class, 'getAllRegisteredTerms'])->name('student.all-terms');
    });

    // ================================================
    // REPORT ROUTES
    // ================================================
    Route::get('/reports/progress', [StudentResultsController::class, 'getReportProgress'])->name('reports.progress');
    Route::post('/reports/generate', [StudentResultsController::class, 'generateReport'])->name('reports.generate');



    Route::resource('classoperation', ClassOperationController::class);

    Route::resource('classcategories', ClasscategoryController::class);
    Route::get('/classcategoryid/{classcategoryid}', [ClasscategoryController::class, 'deleteclasscategory'])->name('classcategories.deleteclasscategory');
    Route::post('updateclasscategoryid', [ClasscategoryController::class, 'updateclasscategory'])->name('classcategories.updateclasscategory');


    Route::resource('parent', ParentController::class);
    Route::resource('studentImageUpload', StudentImageUploadController::class);
    Route::resource('myclass', MyClassController::class);
    Route::resource('mysubject', MySubjectController::class);

    Route::get('/myresultroom', [MyresultroomController::class, 'index'])->name('myresultroom.index');
    Route::post('/myresultroom', [MyresultroomController::class, 'index']);
    Route::post('/myresultroom/store', [MyresultroomController::class, 'store']);
    Route::delete('/subjects/registered-classes', [MyresultroomController::class, 'delete']); // Adjust as needed
    // Route::get('/subjectscoresheet/{schoolclassid}/{subjectclassid}/{userid}/{termid}/{session_id}', [MyScoreSheetController::class, 'index'])->name('subjectscoresheet.index');
    // Route::get('/subjectscoresheet-mock/{schoolclassid}/{subjectclassid}/{userid}/{termid}/{sessionid}', [MyScoreSheetController::class, 'index'])->name('subjectscoresheet-mock.index');
    Route::resource('studentresults', StudentResultsController::class);




    // Route for checking report generation progress
    Route::get('/reports/progress', [StudentResultsController::class, 'getReportProgress'])->name('reports.progress');

    // Route for generating report
    Route::post('/reports/generate', [StudentResultsController::class, 'generateReport'])->name('reports.generate');


    // // Terminal Scoresheet Routes
    // // Route::resource('subjectscoresheet', MyScoreSheetController::class);
    // Route::get('subjectscoresheet/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}', [MyScoreSheetController::class, 'subjectscoresheet'])->name('subjectscoresheet');
    // Route::get('subjectscoresheet/edit/{id}', [MyScoreSheetController::class, 'edit'])->name('subjectscoresheet.edit');
    // Route::put('subjectscoresheet/update/{id}', [MyScoreSheetController::class, 'update'])->name('subjectscoresheet.update');
    // Route::delete('subjectscoresheet/delete/{id}', [MyScoreSheetController::class, 'destroy'])->name('subjectscoresheet.destroy');
    // Route::get('subjectscoresheet/export', [MyScoreSheetController::class, 'export'])->name('subjectscoresheet.export');
    // Route::post('subjectscoresheet/import', [MyScoreSheetController::class, 'import'])->name('subjectscoresheet.import');
    // Route::get('/subjectscoresheet/results', [MyScoreSheetController::class, 'results'])->name('subjectscoresheet.results');
    // Route::post('/subjectscoresheet/grade-preview', [MyScoreSheetController::class, 'calculateGradePreview'])->name('subjectscoresheet.grade-preview');
    // Route::post('subjectscoresheet/bulk-update', [MyScoreSheetController::class, 'bulkUpdateScores'])->name('subjectscoresheet.bulk-update');
    // Route::get('/subjectscoresheet/import-progress', [MyScoreSheetController::class, 'importProgress'])->name('subjectscoresheet.import_progress');



    // // =========================================================================
    // // MARKS SHEET DOWNLOAD ROUTE - MUST BE BEFORE WILDCARD ROUTES
    // // =========================================================================
    // Route::get('scoresheet/download-marks-sheet', [MyScoreSheetController::class, 'downloadMarksSheet'])->name('scoresheet.download-marks-sheet');
    // // Add these routes
    // Route::get('/subjectscoresheet/import-progress', [MyScoreSheetController::class, 'importProgress'])->name('subjectscoresheet.import_progress');
    // Route::post('/subjectscoresheet/clear-progress', [MyScoreSheetController::class, 'clearImportProgress'])->name('subjectscoresheet.clear_progress');

    // // =========================================================================
    // // SUBJECT SCORESHEET ROUTES
    // // =========================================================================
    // Route::get('subjectscoresheet/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}', [MyScoreSheetController::class, 'subjectscoresheet'])->name('subjectscoresheet');
    // Route::get('subjectscoresheet/edit/{id}', [MyScoreSheetController::class, 'edit'])->name('subjectscoresheet.edit');
    // Route::put('subjectscoresheet/update/{id}', [MyScoreSheetController::class, 'update'])->name('subjectscoresheet.update');
    // Route::delete('subjectscoresheet/delete/{id}', [MyScoreSheetController::class, 'destroy'])->name('subjectscoresheet.destroy');
    // Route::get('subjectscoresheet/export', [MyScoreSheetController::class, 'export'])->name('subjectscoresheet.export');
    // Route::post('subjectscoresheet/import', [MyScoreSheetController::class, 'import'])->name('subjectscoresheet.import');
    // Route::get('subjectscoresheet/results', [MyScoreSheetController::class, 'results'])->name('subjectscoresheet.results');
    // Route::post('subjectscoresheet/grade-preview', [MyScoreSheetController::class, 'calculateGradePreview'])->name('subjectscoresheet.grade-preview');
    // Route::post('subjectscoresheet/bulk-update', [MyScoreSheetController::class, 'bulkUpdateScores'])->name('subjectscoresheet.bulk-update');
    // Route::post('subjectscoresheet/single-update', [MyScoreSheetController::class, 'singleUpdateScore'])->name('subjectscoresheet.single-update');
    // Route::get('scoresheet/download-scores-pdf',[MyScoreSheetController::class, 'downloadScoresPdf'])->name('scoresheet.download-scores-pdf');
    // // Add this route alongside your other subjectscoresheet routes
    // Route::post('subjectscoresheet/grade-for-score', [MyScoreSheetController::class, 'calculateGradeForScore'])->name('subjectscoresheet.grade-for-score');



    // =========================================================================
// MARKS SHEET DOWNLOAD ROUTE - MUST BE BEFORE WILDCARD ROUTES
// =========================================================================
Route::get('scoresheet/download-marks-sheet', [MyScoreSheetController::class, 'downloadMarksSheet'])->name('scoresheet.download-marks-sheet');

// Update Arm Positions Across All Arms (AJAX)
Route::post('subjectscoresheet/update-arm-positions-all', [MyScoreSheetController::class, 'updateAllArmPositions'])->name('update.arm.positions.all');

// Add these routes
Route::get('/subjectscoresheet/import-progress', [MyScoreSheetController::class, 'importProgress'])->name('subjectscoresheet.import_progress');
Route::post('/subjectscoresheet/clear-progress', [MyScoreSheetController::class, 'clearImportProgress'])->name('subjectscoresheet.clear_progress');

// =========================================================================
// SUBJECT SCORESHEET ROUTES
// =========================================================================
Route::get('subjectscoresheet/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}', [MyScoreSheetController::class, 'subjectscoresheet'])->name('subjectscoresheet');
Route::get('subjectscoresheet/edit/{id}', [MyScoreSheetController::class, 'edit'])->name('subjectscoresheet.edit');
Route::put('subjectscoresheet/update/{id}', [MyScoreSheetController::class, 'update'])->name('subjectscoresheet.update');
Route::delete('subjectscoresheet/delete/{id}', [MyScoreSheetController::class, 'destroy'])->name('subjectscoresheet.destroy');
Route::get('subjectscoresheet/export', [MyScoreSheetController::class, 'export'])->name('subjectscoresheet.export');
Route::post('subjectscoresheet/import', [MyScoreSheetController::class, 'import'])->name('subjectscoresheet.import');
Route::get('subjectscoresheet/results', [MyScoreSheetController::class, 'results'])->name('subjectscoresheet.results');
Route::post('subjectscoresheet/grade-preview', [MyScoreSheetController::class, 'calculateGradePreview'])->name('subjectscoresheet.grade-preview');
Route::post('subjectscoresheet/bulk-update', [MyScoreSheetController::class, 'bulkUpdateScores'])->name('subjectscoresheet.bulk-update');
Route::post('subjectscoresheet/single-update', [MyScoreSheetController::class, 'singleUpdateScore'])->name('subjectscoresheet.single-update');
Route::get('scoresheet/download-scores-pdf',[MyScoreSheetController::class, 'downloadScoresPdf'])->name('scoresheet.download-scores-pdf');
Route::post('subjectscoresheet/grade-for-score', [MyScoreSheetController::class, 'calculateGradeForScore'])->name('subjectscoresheet.grade-for-score');

Route::post('/studentreports/column-options', [ViewStudentReportController::class, 'getColumnOptions'])->name('studentreports.column-options');
Route::get('/studentreport/drawer-data/{studentId}/{schoolclassId}/{sessionId}/{termId}',[ViewStudentReportController::class, 'drawerData'])->name('studentreport.drawer-data');



    // Mock Scoresheet Routes - STATIC ROUTES FIRST
    Route::get('subjectscoresheet-mock', [MyScoreSheetController::class, 'mockIndex'])->name('subjectscoresheet-mock.index');
    Route::get('subjectscoresheet-mock/export', [MyScoreSheetController::class, 'mockExport'])->name('subjectscoresheet-mock.export');
    Route::get('subjectscoresheet-mock/results', [MyScoreSheetController::class, 'mockResults'])->name('subjectscoresheet-mock.results');
    Route::get('subjectscoresheet-mock/download-marksheet', [MyScoreSheetController::class, 'mockDownloadMarkSheet'])->name('subjectscoresheet-mock.download-marksheet');

    // WILDCARD ROUTE AFTER STATIC ONES
    Route::get('subjectscoresheet-mock/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}', [MyScoreSheetController::class, 'mockSubjectscoresheet'])->name('subjectscoresheet-mock.show');

    // Remaining routes
    Route::post('subjectscoresheet-mock/import', [MyScoreSheetController::class, 'mockImport'])->name('subjectscoresheet-mock.import');
    Route::get('subjectscoresheet-mock/{id}/edit', [MyScoreSheetController::class, 'mockEdit'])->name('subjectscoresheet-mock.edit');
    Route::put('subjectscoresheet-mock/{id}', [MyScoreSheetController::class, 'mockUpdate'])->name('subjectscoresheet-mock.update');
    Route::post('scoresheet-mock/destroy', [MyScoreSheetController::class, 'mockDestroy'])->name('scoresheet-mock.destroy');
    Route::post('scoresheet-mock/bulk-update', [MyScoreSheetController::class, 'mockBulkUpdateScores'])->name('scoresheet-mock.bulk-update');
    Route::post('subjectscoresheet-mock/calculate-grade', [MyScoreSheetController::class, 'calculateGradeForScore'])->name('subjectscoresheet-mock.calculate-grade');
    Route::post('scoresheet-mock/single-update', [MyScoreSheetController::class, 'mockSingleUpdateScore'])->name('scoresheet-mock.single-update');

    Route::get('/subassessment/scoresheet/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}/{subassessmentid}', [MyScoreSheetController::class, 'subassessmentScoresheet'])->name('subassessment.scoresheet');
    Route::get('/assessment/scoresheet/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}/{assessmentid}', [MyScoreSheetController::class, 'assessmentScoresheet'])->name('assessment.scoresheet');
    Route::post('/subjectscoresheet/single-update', [MyScoreSheetController::class, 'singleUpdateScore'])->name('subjectscoresheet.single-update');


    Route::get('/studentassessments', [StudentAssessmentController::class, 'index'])->name('assessments');
    Route::get('/studentassessments/print', [StudentAssessmentController::class, 'printResult'])->name('assessments.print');


    // ── Student Payment Portal ──────────────────────────────────────
    Route::get('/my-payments', [StudentPaymentController::class, 'index'])->name('student.payments');
    Route::get('/my-payments/receipt', [StudentPaymentController::class, 'printReceipt'])->name('student.payments.receipt');


        // Marks Sheet Download Routes
    // Route::get('/scoresheet/download-marks-sheet', [MyScoreSheetController::class, 'downloadMarkSheet'])->name('scoresheet.download-marks-sheet');
    // Route::post('/subjectscoresheet/bulk-update', [MyScoreSheetController::class, 'bulkUpdateScores']) ->name('subjectscoresheet.bulk-update');

   Route::prefix('school-info')->name('admin.school-info.')->group(function () {
        Route::get('/', [SchoolInformationController::class, 'index'])->name('index');
        Route::post('/', [SchoolInformationController::class, 'store'])->name('store');
        Route::match(['PUT', 'PATCH', 'POST'], '/{id}', [SchoolInformationController::class, 'update'])->name('update');
        Route::delete('/{id}', [SchoolInformationController::class, 'destroy'])->name('destroy');
        Route::get('/{id}', [SchoolInformationController::class, 'show'])->name('show');
        Route::get('/{id}/edit-json', [SchoolInformationController::class, 'editJson'])->name('edit-json');
        Route::post('/bulk-delete', [SchoolInformationController::class, 'bulkDestroy'])->name('bulk-destroy'); // Add this line
    });
    // Route::resource('schoolbill', SchoolBillController::class);
    // Route::get('/billid/{billid}', [SchoolBillController::class, 'deletebill'])->name('schoolbill.deletebill');
    // Route::post('billid', [SchoolBillController::class, 'updatebill'])->name('schoolbill.updateschoolbill');





    // ============================================
    // SCHOOL BILL MANAGEMENT ROUTES
    // ============================================
    Route::prefix('schoolbill')->name('schoolbill.')->group(function () {
        Route::get('/',               [SchoolBillController::class, 'index']      )->name('index');
        Route::post('/store',         [SchoolBillController::class, 'store']      )->name('store');
        Route::post('/bulk-destroy',  [SchoolBillController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::get('/{id}/edit-json', [SchoolBillController::class, 'edit']       )->name('edit-json');
        Route::get('/{id}',           [SchoolBillController::class, 'show']       )->name('show');
        Route::put('/{id}',           [SchoolBillController::class, 'update']     )->name('update');
        Route::delete('/{id}',        [SchoolBillController::class, 'destroy']    )->name('destroy');
    });


    Route::prefix('schoolbilltermsession')->name('schoolbilltermsession.')->group(function () {
        Route::get('/', [SchoolBillTermSessionController::class, 'index'])->name('index');
        Route::get('/data', [SchoolBillTermSessionController::class, 'data'])->name('data');         // ← ADD
        Route::get('/stats', [SchoolBillTermSessionController::class, 'stats'])->name('stats');      // ← ADD
        Route::post('/store', [SchoolBillTermSessionController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [SchoolBillTermSessionController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SchoolBillTermSessionController::class, 'update'])->name('update');
        Route::delete('/{id}', [SchoolBillTermSessionController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/related', [SchoolBillTermSessionController::class, 'getRelated'])->name('related');
        Route::post('/bulk-destroy', [SchoolBillTermSessionController::class, 'bulkDestroy'])->name('bulk-destroy');
    });


    // ============================================
    // SCHOLARSHIP MANAGEMENT ROUTES
    // ============================================
    Route::prefix('admin/scholarship')->name('admin.scholarship.')->group(function () {
        // Static routes FIRST
        Route::get('/assignments', [ScholarshipController::class, 'showAssignments'])->name('assignments');
        Route::get('/applications', [ScholarshipController::class, 'showApplications'])->name('applications');
        Route::get('/create', [ScholarshipController::class, 'create'])->name('create');
        Route::post('/store', [ScholarshipController::class, 'store'])->name('store');
        Route::post('/bulk-destroy', [ScholarshipController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/assign', [ScholarshipController::class, 'assignToStudent'])->name('assign');
        Route::delete('/assignment/{assignmentId}', [ScholarshipController::class, 'revokeAssignment'])->name('assignment.revoke');
        Route::post('/application/{applicationId}/approve', [ScholarshipController::class, 'approveApplication'])->name('application.approve');
        Route::post('/application/{applicationId}/reject', [ScholarshipController::class, 'rejectApplication'])->name('application.reject');

        // ADD THIS MISSING ROUTE
        Route::get('/eligible-students', [ScholarshipController::class, 'getEligibleStudents'])->name('eligible-students');

        // Parameter routes LAST
        Route::get('/', [ScholarshipController::class, 'index'])->name('index');
        Route::get('/{id}', [ScholarshipController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ScholarshipController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ScholarshipController::class, 'update'])->name('update');
        Route::delete('/{id}', [ScholarshipController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/approve', [ScholarshipController::class, 'approve'])->name('approve');
        Route::post('/{id}/revoke', [ScholarshipController::class, 'revoke'])->name('revoke');
    });

    // ============================================
    // DISCOUNT MANAGEMENT ROUTES
    // ============================================
    Route::prefix('admin/discount')->name('admin.discount.')->group(function () {
        // Static routes FIRST (before parameter routes)
        Route::get('/assignments', [DiscountController::class, 'showAssignments'])->name('assignments');
        Route::get('/create', [DiscountController::class, 'create'])->name('create');
        Route::post('/store', [DiscountController::class, 'store'])->name('store');
        Route::post('/bulk-destroy', [DiscountController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/assign', [DiscountController::class, 'assignToStudent'])->name('assign');
        Route::delete('/assignment/{assignmentId}', [DiscountController::class, 'removeAssignment'])->name('assignment.remove');

        // ADD THIS MISSING ROUTE
        Route::get('/eligible-students', [DiscountController::class, 'getEligibleStudents'])->name('eligible-students');

        // Parameter routes LAST
        Route::get('/', [DiscountController::class, 'index'])->name('index');
        Route::get('/{id}', [DiscountController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [DiscountController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DiscountController::class, 'update'])->name('update');
        Route::delete('/{id}', [DiscountController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/approve', [DiscountController::class, 'approve'])->name('approve');
    });




        // ============================================
        // SIBLING GROUP ROUTES - ORDER MATTERS!
        // ============================================
        Route::prefix('sibling')->name('sibling.')->group(function () {
            // SPECIFIC ROUTES FIRST (no parameters)
            Route::get('/create', [SiblingGroupController::class, 'create'])->name('create');
            Route::post('/store', [SiblingGroupController::class, 'store'])->name('store');
            Route::get('/search-students', [SiblingGroupController::class, 'searchStudents'])->name('search-students');
            Route::post('/apply-discount', [SiblingGroupController::class, 'applyDiscount'])->name('apply-discount');
            Route::get('/student/{studentId}', [SiblingGroupController::class, 'getStudentSiblings'])->name('student-siblings');

            // PARAMETER ROUTES LAST (these catch everything else)
            Route::get('/', [SiblingGroupController::class, 'index'])->name('index');
            Route::get('/{id}', [SiblingGroupController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [SiblingGroupController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SiblingGroupController::class, 'update'])->name('update');
            Route::delete('/{id}', [SiblingGroupController::class, 'destroy'])->name('destroy');
        });


        // ============================================
        // PAYMENT GATEWAY ROUTES
        // ============================================
        Route::prefix('admin/payment-gateways')->name('admin.payment-gateways.')->group(function () {
            Route::get('/', [PaymentGatewayController::class, 'index'])->name('index');
            Route::post('/{gateway}/toggle', [PaymentGatewayController::class, 'toggleGateway'])->name('toggle');
            Route::put('/{gateway}', [PaymentGatewayController::class, 'updateConfig'])->name('update');
            Route::post('/test/{gateway}', [PaymentGatewayController::class, 'testGateway'])->name('test');
        });


        // ============================================
        // ENHANCED PAYMENT ROUTES
        // ============================================
        Route::prefix('payment')->name('payment.')->group(function () {
            Route::get('/', [EnhancedSchoolPaymentController::class, 'index'])->name('index');
            Route::get('/student/{studentId}/class/{classId}/term/{termId}/session/{sessionId}', [EnhancedSchoolPaymentController::class, 'showPaymentDetails'])->name('details');
            Route::get('/flexible/{studentId}/{classId}/{termId}/{sessionId}', [EnhancedSchoolPaymentController::class, 'showFlexiblePayment'])->name('flexible');
            Route::post('/offline/process', [EnhancedSchoolPaymentController::class, 'processOfflinePayment'])->name('offline.process');
            Route::post('/invoice/generate/{paymentId}', [EnhancedSchoolPaymentController::class, 'generateInvoice'])->name('invoice.generate');
            Route::get('/invoice/{studentId}/{classId}/{termId}/{sessionId}', [EnhancedSchoolPaymentController::class, 'showInvoice'])->name('invoice');
            Route::get('/invoice/download/{studentId}/{classId}/{termId}/{sessionId}', [EnhancedSchoolPaymentController::class, 'showInvoice'])->name('invoice.download');
            Route::get('/receipt/{batchId}', [EnhancedSchoolPaymentController::class, 'showReceipt'])->name('receipt');
            Route::post('/reverse/{batchId}', [EnhancedSchoolPaymentController::class, 'reversePayment'])->name('reverse');
            Route::get('/status/{studentId}/{classId}/{termId}/{sessionId}', [EnhancedSchoolPaymentController::class, 'getPaymentStatus'])->name('status');
            Route::get('/savings/{studentId}', [EnhancedSchoolPaymentController::class, 'getSavingsSummary'])->name('savings');
            Route::get('/history', [EnhancedSchoolPaymentController::class, 'getPaymentHistory'])->name('history');

            // FIXED: Use getPaymentStatusAjax for query string parameters
            Route::get('/details/ajax', [EnhancedSchoolPaymentController::class, 'getPaymentStatusAjax'])->name('details.ajax');
        });

    // ============================================
    // FLEXIBLE ONLINE PAYMENT ROUTES
    // ============================================
    // Route::prefix('payment/flexible')->name('payment.flexible.')->group(function () {
    //     Route::get('/{studentId}/{classId}/{termId}/{sessionId}', [FlexibleOnlinePaymentController::class, 'showFlexiblePayment'])->name('show');
    //     Route::post('/initialize', [FlexibleOnlinePaymentController::class, 'initializeFlexiblePayment'])->name('initialize');
    //     Route::get('/callback', [FlexibleOnlinePaymentController::class, 'handlePaymentCallback'])->name('callback');
    //     Route::get('/success/{reference}', [FlexibleOnlinePaymentController::class, 'paymentSuccess'])->name('success');
    //     Route::get('/status/{reference}', [FlexibleOnlinePaymentController::class, 'getPaymentStatus'])->name('status');
    //     Route::post('/retry/{onlinePaymentId}', [FlexibleOnlinePaymentController::class, 'retryPayment'])->name('retry');
    //     Route::post('/webhook/{gateway}', [FlexibleOnlinePaymentController::class, 'webhook'])->name('webhook');
    // });

    // ============================================
    // SIMPLE ONLINE PAYMENT ROUTES
    // ============================================
    Route::prefix('payment/online')->name('payment.online.')->group(function () {
        Route::get('/', [OnlinePaymentController::class, 'index'])->name('index');
        Route::get('/success/{reference}', [OnlinePaymentController::class, 'success'])->name('success');
        Route::get('/bills', [OnlinePaymentController::class, 'getStudentBillsAjax'])->name('bills');
        Route::post('/initialize', [OnlinePaymentController::class, 'initialize'])->name('initialize');
        Route::get('/status/{reference}', [OnlinePaymentController::class, 'getPaymentStatus'])->name('status');
        Route::post('/retry/{onlinePaymentId}', [OnlinePaymentController::class, 'retryPayment'])->name('retry');
        Route::post('/cancel/{onlinePaymentId}', [OnlinePaymentController::class, 'cancelPayment'])->name('cancel');
        Route::get('/verify/{reference}', [OnlinePaymentController::class, 'verifyPayment'])->name('verify');
        Route::get('/analytics', [OnlinePaymentController::class, 'getPaymentAnalytics'])->name('analytics');
        Route::post('/bank-transfer/initiate', [OnlinePaymentController::class, 'initiateBankTransfer'])->name('bank-transfer.initiate');
        Route::get('/bank-transfer/status/{reference}', [OnlinePaymentController::class, 'checkBankTransferStatus'])->name('bank-transfer.status');
        Route::get('/banks', [OnlinePaymentController::class, 'getSupportedBanks'])->name('banks');
        Route::get('/receipt/{batchId}', [OnlinePaymentController::class, 'downloadReceipt'])->name('receipt');
        Route::get('/transaction/{reference}', [OnlinePaymentController::class, 'getTransactionDetails'])->name('transaction');
        Route::get('/callback', [OnlinePaymentController::class, 'callback'])->name('callback');
        Route::post('/webhook/{gateway}', [OnlinePaymentController::class, 'webhook'])->name('webhook');
    });



    // ============================================
    // LEGACY PAYMENT ROUTES (Backward Compatibility)
    // ============================================
    // Route::prefix('schoolpayment')->name('schoolpayment.')->group(function () {
    //     Route::get('/', [SchoolPaymentController::class, 'index'])->name('index');
    //     Route::get('/term-session/{id}', [SchoolPaymentController::class, 'termSession'])->name('termsession');
    //     Route::get('/termsessionpayments', [SchoolPaymentController::class, 'termsessionpayments'])->name('termsessionpayments');
    //     Route::get('/get-payment-details', [SchoolPaymentController::class, 'getPaymentDetailsAjax'])->name('getPaymentDetailsAjax');
    //     Route::post('/store', [SchoolPaymentController::class, 'store'])->name('store');
    //     Route::post('/bulk-store', [SchoolPaymentController::class, 'bulkStore'])->name('bulk-store');
    //     Route::post('/delete/{recordId}', [SchoolPaymentController::class, 'deletestudentpayment'])->name('deletestudentpayment');
    //     Route::get('/invoice/{studentId}/{schoolclassid}/{termid}/{sessionid}', [SchoolPaymentController::class, 'invoice'])->name('invoice');
    //     Route::get('/statement/{studentId}/{schoolclassid}/{termid}/{sessionid}', [SchoolPaymentController::class, 'statement'])->name('statement');
    // });


    // ============================================
    // ENHANCED PAYMENT ROUTES (Using SchoolPaymentController)
    // ============================================
    Route::prefix('payment')->name('payment.')->group(function () {
        // Main index (student list)
        Route::get('/', [SchoolPaymentController::class, 'index'])->name('index');

        // Term/Session selection
        Route::get('/term-session/{id}', [SchoolPaymentController::class, 'termSession'])->name('termsession');

        // Payment details page (called from debtors list)
        Route::get('/details/{studentId}/{classId}/{termId}/{sessionId}', [SchoolPaymentController::class, 'showPaymentDetails'])->name('details');

        // AJAX endpoints
        Route::get('/get-payment-details', [SchoolPaymentController::class, 'getPaymentDetailsAjax'])->name('getPaymentDetailsAjax');
        Route::post('/store', [SchoolPaymentController::class, 'store'])->name('store');
        Route::post('/bulk-store', [SchoolPaymentController::class, 'bulkStore'])->name('bulk-store');
        Route::post('/delete/{recordId}', [SchoolPaymentController::class, 'deletestudentpayment'])->name('delete');

        // Invoice and Statement
        Route::get('/invoice/{studentId}/{schoolclassid}/{termid}/{sessionid}', [SchoolPaymentController::class, 'invoice'])->name('invoice');
        Route::get('/statement/{studentId}/{schoolclassid}/{termid}/{sessionid}', [SchoolPaymentController::class, 'statement'])->name('statement');

        // Termsession payments (alternative entry)
        Route::get('/termsessionpayments', [SchoolPaymentController::class, 'termsessionpayments'])->name('termsessionpayments');
    });

    // ============================================
    // LEGACY PAYMENT ROUTES (Backward Compatibility)
    // ============================================
    Route::prefix('schoolpayment')->name('schoolpayment.')->group(function () {
        Route::get('/', [SchoolPaymentController::class, 'index'])->name('index');
        Route::get('/term-session/{id}', [SchoolPaymentController::class, 'termSession'])->name('termsession');
        Route::get('/termsessionpayments', [SchoolPaymentController::class, 'termsessionpayments'])->name('termsessionpayments');
        Route::get('/get-payment-details', [SchoolPaymentController::class, 'getPaymentDetailsAjax'])->name('getPaymentDetailsAjax');
        Route::post('/store', [SchoolPaymentController::class, 'store'])->name('store');
        Route::post('/bulk-store', [SchoolPaymentController::class, 'bulkStore'])->name('bulk-store');
        Route::post('/delete/{recordId}', [SchoolPaymentController::class, 'deletestudentpayment'])->name('deletestudentpayment');
        Route::get('/invoice/{studentId}/{schoolclassid}/{termid}/{sessionid}', [SchoolPaymentController::class, 'invoice'])->name('invoice');
        Route::get('/statement/{studentId}/{schoolclassid}/{termid}/{sessionid}', [SchoolPaymentController::class, 'statement'])->name('statement');
    });




// ============================================
// FINANCIAL REPORTS ROUTES (FinancialReportController)
// ============================================
Route::prefix('reports/financial')->name('reports.financial.')->group(function () {
    // Balance Sheet
    Route::get('/balance-sheet', [FinancialReportController::class, 'balanceSheet'])->name('balance-sheet');
    Route::get('/balance-sheet/export', [FinancialReportController::class, 'exportBalanceSheet'])->name('balance-sheet.export');

    // Income Statement
    Route::get('/income-statement', [FinancialReportController::class, 'incomeStatement'])->name('income-statement');
    Route::get('/income-statement/export', [FinancialReportController::class, 'exportIncomeStatement'])->name('income-statement.export');

    // Trial Balance
    Route::get('/trial-balance', [FinancialReportController::class, 'trialBalance'])->name('trial-balance');

    // Cash Flow
    Route::get('/cash-flow', [FinancialReportController::class, 'cashFlow'])->name('cash-flow');
    Route::get('/cash-flow/export', [FinancialReportController::class, 'exportCashFlow'])->name('cash-flow.export');

    // Debtors List
    Route::get('/debtors', [FinancialReportController::class, 'debtorsList'])->name('debtors');

    // Collection Summary
    Route::get('/collection-summary', [FinancialReportController::class, 'collectionSummary'])->name('collection-summary');

    // Scholarship Impact
    Route::get('/scholarship-impact', [FinancialReportController::class, 'scholarshipImpact'])->name('scholarship-impact');
    // In your financial reports group
    Route::get('/debtors/export/{report}', [FinancialReportController::class, 'exportDebtors'])->name('export');

});

// ============================================
// ANALYSIS REPORTS ROUTES (AnalysisReportController)
// ============================================
// In routes/web.php, update the analysis routes:

Route::prefix('reports/analysis')->name('reports.analysis.')->group(function () {
    // Main index page
    Route::get('/', [AnalysisReportController::class, 'index'])->name('index');

    // Class analysis - FIXED: use the correct method names
    Route::get('/class', [AnalysisReportController::class, 'getClassAnalysisData'])->name('class');
    Route::get('/class-details', [AnalysisReportController::class, 'analysisClassTermSession'])->name('class-details');
    Route::get('/class-data', [AnalysisReportController::class, 'getClassAnalysisData'])->name('class-data');
    Route::get('/export-pdf/{class_id}/{termid_id}/{session_id}/{action?}', [AnalysisReportController::class, 'exportPDF'])->name('export-pdf');
    Route::get('/export', [AnalysisReportController::class, 'exportClassAnalysis'])->name('export');

    // School wide analysis
    Route::get('/school-wide', [AnalysisReportController::class, 'schoolWideAnalysis'])->name('school-wide');
    Route::get('/school-wide/export', [AnalysisReportController::class, 'exportSchoolWideAnalysis'])->name('school-wide.export');

    // Scholarship impact
    Route::get('/scholarship-impact', [AnalysisReportController::class, 'scholarshipImpactAnalysis'])->name('scholarship-impact');

    // Student payment details
    Route::get('/student/{studentId}/{classId}/{termId}/{sessionId}', [AnalysisReportController::class, 'studentPaymentDetails'])->name('student-details');
});


    // ============================================
    // STAFF PAYMENT ROUTES
    // ============================================
    Route::prefix('staff/payments')->name('staff.payments.')->group(function () {

        // Admin views
        Route::get('/', [StaffPaymentController::class, 'index'])->name('index');
        Route::get('/create', [StaffPaymentController::class, 'create'])->name('create');
        Route::post('/store', [StaffPaymentController::class, 'store'])->name('store');
        Route::get('/{id}', [StaffPaymentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [StaffPaymentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [StaffPaymentController::class, 'update'])->name('update');
        Route::delete('/{id}', [StaffPaymentController::class, 'destroy'])->name('destroy');
        // Staff dashboard (staff view)
        Route::get('/dashboard', [StaffPaymentController::class, 'staffDashboard'])->name('dashboard');

        // AJAX endpoints
        Route::get('/history', [StaffPaymentController::class, 'getPaymentHistory'])->name('history');
        Route::post('/reverse/{paymentId}', [StaffPaymentController::class, 'reversePayment'])->name('reverse');
        Route::post('/mark-paid/{paymentId}', [StaffPaymentController::class, 'markAsPaid'])->name('mark-paid');

        // Payslip
        Route::get('/payslip/{payrollRunId}', [StaffPaymentController::class, 'viewPayslip'])->name('payslip');
        Route::get('/payslip/download/{payrollRunId}', [StaffPaymentController::class, 'downloadPayslip'])->name('payslip.download');
    });


     // ============================================
    // PAYROLL MANAGEMENT ROUTES
    // ============================================
    Route::prefix('payroll')->name('payroll.')->group(function () {
        // Periods
        Route::get('/periods', [PayrollController::class, 'periods'])->name('periods');
        Route::post('/periods', [PayrollController::class, 'createPeriod'])->name('periods.store');
        Route::post('/periods/{periodId}/process', [PayrollController::class, 'processPayroll'])->name('process');
        Route::post('/periods/{periodId}/approve', [PayrollController::class, 'approvePayroll'])->name('approve');
        Route::post('/periods/{periodId}/lock', [PayrollController::class, 'lockPeriod'])->name('lock');

        // Payroll Runs
        Route::get('/runs/{periodId}', [PayrollController::class, 'getPayrollRuns'])->name('runs');
        Route::get('/run/{payrollRunId}', [PayrollController::class, 'showPayrollRun'])->name('run.show');
        Route::post('/run/{payrollRunId}/pay', [PayrollController::class, 'processStaffPayment'])->name('run.pay');

        // Reports
        Route::get('/summary', [PayrollController::class, 'summaryReport'])->name('summary');
        Route::get('/statutory', [PayrollController::class, 'statutoryReport'])->name('statutory');

        // Salary Structures
        Route::get('/salary-structures', [PayrollController::class, 'salaryStructures'])->name('salary-structures');
        Route::post('/salary-structures', [PayrollController::class, 'storeSalaryStructure'])->name('salary-structures.store');
        Route::get('/payroll/salary-structures/{id}', [PayrollController::class, 'showSalaryStructure'])->name('payroll.salary-structures.show');
        Route::get('/salary-structures/{id}/edit', [PayrollController::class, 'editSalaryStructure'])->name('salary-structures.edit');
        Route::put('/salary-structures/{id}', [PayrollController::class, 'updateSalaryStructure'])->name('salary-structures.update');
        Route::delete('/salary-structures/{id}', [PayrollController::class, 'destroySalaryStructure'])->name('salary-structures.destroy');

        // Alias for backward compatibility
        Route::get('/structures', [PayrollController::class, 'salaryStructures'])->name('structures');
    });




// Analysis Reports - Permissions handled in controller constructor
Route::prefix('reports/analysis')->name('reports.analysis.')->group(function () {
    Route::get('/', [AnalysisReportController::class, 'index'])->name('index');
    Route::get('/class-data', [AnalysisReportController::class, 'getClassAnalysisData'])->name('class-data');
    Route::get('/export', [AnalysisReportController::class, 'exportClassAnalysis'])->name('export');
    Route::get('/student/{studentId}/{classId}/{termId}/{sessionId}', [AnalysisReportController::class, 'studentPaymentDetails'])->name('student-details');
});

// Financial Reports - Permissions handled in controller constructor
Route::prefix('reports/financial')->name('reports.financial.')->group(function () {
    Route::get('/debtors', [FinancialReportController::class, 'debtorsList'])->name('debtors');
    Route::get('/balance-sheet', [FinancialReportController::class, 'balanceSheet'])->name('balance-sheet');
    Route::get('/income-statement', [FinancialReportController::class, 'incomeStatement'])->name('income-statement');
    Route::get('/trial-balance', [FinancialReportController::class, 'trialBalance'])->name('trial-balance');
    Route::get('/cash-flow', [FinancialReportController::class, 'cashFlow'])->name('cash-flow');
    Route::get('/collection-summary', [FinancialReportController::class, 'collectionSummary'])->name('collection-summary');
    Route::get('/scholarship-impact', [FinancialReportController::class, 'scholarshipImpact'])->name('scholarship-impact');
    Route::get('/export/{report}/{format}', [FinancialReportController::class, 'export'])->name('export');
});



    // ============================================
    // WEBHOOK ROUTES (No CSRF)
    // ============================================
    // Route::prefix('webhook')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->group(function () {
    //     Route::post('/paystack', [FlexibleOnlinePaymentController::class, 'webhook'])->name('webhook.paystack');
    //     Route::post('/remita', [FlexibleOnlinePaymentController::class, 'webhook'])->name('webhook.remita');
    //     Route::post('/flutterwave', [FlexibleOnlinePaymentController::class, 'webhook'])->name('webhook.flutterwave');
    // });

    // ============================================
    // BULK PAYMENT ROUTES
    // ============================================
    Route::prefix('bulk-payment')->name('bulk-payment.')->group(function () {
        Route::get('/', [EnhancedSchoolPaymentController::class, 'bulkPaymentForm'])->name('form');
        Route::post('/upload', [EnhancedSchoolPaymentController::class, 'uploadBulkPayment'])->name('upload');
        Route::post('/process', [EnhancedSchoolPaymentController::class, 'processBulkPayment'])->name('process');
        Route::get('/template', [EnhancedSchoolPaymentController::class, 'downloadTemplate'])->name('template');
        Route::get('/status/{batchId}', [EnhancedSchoolPaymentController::class, 'getBulkStatus'])->name('status');
    });


















    Route::resource('schoolbilltermsession', SchoolBillTermSessionController::class);
    Route::get('/schoolbilltermsessionid/{schoolbilltermsessionid}', [SchoolBillTermSessionController::class, 'deleteschoolbilltermsession'])->name('schoolbilltermsession.deleteschoolbilltermsession');
    Route::post('schoolbilltermsessionbid', [SchoolBillTermSessionController::class, 'updateschoolbilltermsession'])->name('schoolbilltermsession.updateschoolbilltermsession');
    Route::get('/schoolbilltermsession/{id}/related', 'App\Http\Controllers\SchoolBillTermSessionController@getRelated')->name('schoolbilltermsession.related');


    Route::get('/schoolpayment', [SchoolPaymentController::class, 'index'])->name('schoolpayment.index');
    Route::get('/schoolpayment/term-session/{id}', [SchoolPaymentController::class, 'termSession'])->name('schoolpayment.termsession');
    Route::get('termsessionpayments', [SchoolPaymentController::class, 'termsessionpayments'])->name('schoolpayment.termsessionpayments');
    Route::get('/schoolpayment/term-session-payments', [SchoolPaymentController::class, 'termSessionPayments'])->name('schoolpayment.termsessionpayments');
    Route::post('/schoolpayment/store', [SchoolPaymentController::class, 'store'])->name('schoolpayment.store');
    Route::post('/schoolpayment/delete/{recordId}', [SchoolPaymentController::class, 'deletestudentpayment'])->name('schoolpayment.deletestudentpayment');
    Route::get('/schoolpayment/invoice/{studentId}/{schoolclassid}/{termid}/{sessionid}', [SchoolPaymentController::class, 'invoice'])->name('schoolpayment.invoice');
    Route::get('/schoolpayment/statement/{studentId}/{schoolclassid}/{termid}/{sessionid}', [SchoolPaymentController::class, 'statement'])->name('schoolpayment.statement');



    // School-wide payment analysis routes
    Route::get('/school-wide-payment-analysis/{termid_id}/{session_id}/{action?}/{format?}','App\Http\Controllers\AnalysisController@schoolWidePaymentAnalysis')->name('school.wide.payment.analysis')->where(['action' => 'view|download','format' => 'pdf|word' ]);



    Route::get('/viewstudent/{schoolclassid}/{termid}/{sessionid}', [ViewStudentController::class, 'show'])->name('viewstudent');

    Route::get('/studentreports', [ViewStudentReportController::class, 'index'])->name('studentreports.index');
    Route::get('/studentresult/{id}/{schoolclassid}/{sessionid}/{termid}', [ViewStudentReportController::class, 'studentresult'])->name('studentresult');
    Route::get('/student-reports/registered-classes', [ViewStudentReportController::class, 'registeredClasses'])->name('studentreports.registeredClasses');
    Route::get('/class-broadsheet/{schoolclassid}/{sessionid}/{termid}', [ViewStudentReportController::class, 'classBroadsheet'])->name('classbroadsheet');
    // Route::get('/studentreports/export/{id}/{schoolclassid}/{sessionid}/{termid}', [ViewStudentReportController::class, 'exportStudentResultPdf'])->name('studentreports.exportStudentResultPdf');
    Route::match(['get', 'post'], '/studentreports/export-class-results-pdf', [ViewStudentReportController::class, 'exportClassResultsPdf'])->name('studentreports.exportClassResultsPdf');



    Route::get('/studentmockreports', [ViewStudentMockReportController::class, 'index'])->name('studentmockreports.index');

    // Mock Student Report Routes
    Route::get('studentmockreports', [ViewStudentMockReportController::class, 'index'])->name('studentmockreports.index');
    Route::post('studentmockreports/column-options', [ViewStudentMockReportController::class, 'getColumnOptions'])->name('studentmockreports.column-options');
    Route::post('studentmockreports/export-class-pdf', [ViewStudentMockReportController::class, 'exportClassMockResultsPdf'])->name('studentmockreports.exportClassMockResultsPdf');
    Route::get('studentmockreports/{id}/{schoolclassid}/{sessionid}/{termid}', [ViewStudentMockReportController::class, 'studentmockresult'])->name('studentmockreports.studentmockresult');
    Route::get('studentmockreports/{id}/{schoolclassid}/{sessionid}/{termid}/pdf', [ViewStudentMockReportController::class, 'exportStudentMockResultPdf'])->name('studentmockreports.exportStudentMockResultPdf');
    Route::get('studentmockreports/registered-classes', [ViewStudentMockReportController::class, 'registeredClasses'])->name('studentmockreports.registeredClasses');









    // ============================================================================
    // ADD THIS ROUTE to your existing routes (alongside the others)
    // ============================================================================

    // GET snapshot detail — returns individual student rows + score snapshots
    // for a specific named snapshot group
    Route::get('/subjectoperation/snapshot/detail', [SubjectOperationController::class, 'getSnapshotDetail'])
        ->name('subjectoperation.snapshot.detail');
    Route::get('/subjectoperation/registered-classes', [SubjectOperationController::class, 'registeredClasses'])
        ->name('subjectoperation.registered-classes');
    Route::get('/subjectoperation/student-subject-counts', [SubjectOperationController::class, 'getStudentSubjectCounts'])
        ->name('subjectoperation.student-subject-counts');

    // ── All existing routes stay the same ──────────────────────────────────────

    Route::get('/subjects', [SubjectOperationController::class, 'index'])->name('subjects.index');

    Route::post('/subjectregistration', [SubjectOperationController::class, 'store'])->name('subjects.store');
    Route::get('/subjectoperation/subjectinfo/{id}/{schoolclassid}/{termid}/{sessionid}', [SubjectOperationController::class, 'subjectinfo'])->name('subjects.subjectinfo');

    Route::delete('/subjects/registered-classes', [SubjectOperationController::class, 'destroy'])->name('subjects.destroy');
    Route::get('/subjects/registered-classes', [SubjectOperationController::class, 'getRegisteredClasses'])->name('subjects.registered-classes');

    Route::post('/subjectregistration/destroy', [SubjectOperationController::class, 'destroy'])->name('subjectregistration.destroy');
    Route::post('/subjectregistration/batch', [SubjectOperationController::class, 'batchRegister'])->name('subjectregistration.batch');

    Route::get('/subjectoperation/archived', [SubjectOperationController::class, 'getArchivedRegistrations'])->name('subjectoperation.archived');
    Route::post('/subjectoperation/restore', [SubjectOperationController::class, 'restoreRegistration'])->name('subjectoperation.restore');

    Route::delete('/subjectoperation/archive/batch-delete', [SubjectOperationController::class, 'permanentlyDeleteArchiveBatch'])->name('subjectoperation.archive.batch-delete');
    Route::delete('/subjectoperation/archive/{archiveId}', [SubjectOperationController::class, 'permanentlyDeleteArchive'])->name('subjectoperation.archive.delete');
    Route::get('/school-information/get', [SubjectOperationController::class, 'getSchoolInformation'])->name('school.information.get');
    Route::resource('subjectoperation', SubjectOperationController::class);


    // Get active school information
    Route::get('/api/school-information/active', function () {
        $schoolInfo = App\Models\SchoolInformation::getActiveSchool();
        return response()->json([
            'success' => true,
            'data' => $schoolInfo ? [
                'school_name' => $schoolInfo->school_name,
                'school_address' => $schoolInfo->school_address,
                'school_phone' => $schoolInfo->school_phone,
                'school_email' => $schoolInfo->school_email,
                'school_motto' => $schoolInfo->school_motto,
                'school_website' => $schoolInfo->school_website,
                'logo_url' => $schoolInfo->logo_url,
            ] : null
        ]);
    })->name('api.school-information.active');

    Route::get('/viewresults/{id}/{schoolclassid}/{sessid}/{termid}', [StudentResultsController::class, 'viewresults']);


    Route::get('/studentpersonalityprofile/{id}/{schoolclassid}/{sessid}/{termid}', [StudentpersonalityprofileController::class, 'studentpersonalityprofile'])->name('myclass.studentpersonalityprofile');
    Route::post('save', [StudentpersonalityprofileController::class, 'save'])->name('studentpersonalityprofile.save');
    Route::get('/studentpersonalityprofile/data/{id}/{schoolclassid}/{sessionid}/{termid}',[StudentpersonalityprofileController::class, 'profileData'])->name('myclass.studentpersonalityprofile.data');

    Route::get('/classbroadsheet/{schoolclassid}/{sessionid}/{termid}', [ClassBroadsheetController::class, 'classBroadsheet'])->name('classbroadsheet.viewcomments');
    Route::patch('/classbroadsheet/{schoolclassid}/{sessionid}/{termid}/comments', [ClassBroadsheetController::class, 'updateComments'])->name('classbroadsheet.updateComments');
    Route::get('/classbroadsheet/past-comments/{studentId}', [ClassBroadsheetController::class, 'getPastComments']);

    // compulsory subject class
    Route::resource('compulsorysubjectclass', CompulsorySubjectClassController::class);

    //principal's comment
    Route::resource('principalscomment', PrincipalsCommentController::class);
    Route::prefix('myprincipalscomment')->name('myprincipalscomment.')->group(function () {
        Route::get('/', [MyPrincipalsCommentController::class, 'index'])->name('index');
        Route::get('/broadsheet/{schoolclassid}/{sessionid}/{termid}', [MyPrincipalsCommentController::class, 'classBroadsheet'])->name('classbroadsheet');
        Route::post('/broadsheet/{schoolclassid}/{sessionid}/{termid}', [MyPrincipalsCommentController::class, 'updateComments'])->name('updateComments');
    });

 // Add these routes to your routes/web.php file
    Route::get('/api/subject-classes/search', [SubjectVettingController::class, 'searchSubjectClasses'])->name('api.subject-classes.search');
    Route::post('/api/subject-classes/details', [SubjectVettingController::class, 'getSelectedSubjectClasses'])->name('api.subject-classes.details');

    // For Mock Subject Vetting (if not already added)
    Route::get('/api/mock-subject-classes/search', [MockSubjectVettingController::class, 'searchSubjectClasses'])->name('api.mock-subject-classes.search');
    Route::post('/api/mock-subject-classes/details', [MockSubjectVettingController::class, 'getSelectedSubjectClasses'])->name('api.mock-subject-classes.details');

    Route::resource('subjectvetting', SubjectVettingController::class);
    Route::resource('mocksubjectvetting', MockSubjectVettingController::class);

    // Bulk delete routes
    Route::post('/subjectvetting/bulk-delete', [SubjectVettingController::class, 'bulkDelete'])->name('subjectvetting.bulkDelete');
    Route::post('/mocksubjectvetting/bulk-delete', [MockSubjectVettingController::class, 'bulkDelete'])->name('mocksubjectvetting.bulkDelete');



    // my subject vettings
    Route::get('/mysubjectvettings', [MySubjectVettingsController::class, 'index'])->name('mysubjectvettings.index');
    Route::get('/mysubjectvettings/classbroadsheet/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}', [MySubjectVettingsController::class, 'classBroadsheet'])->name('mysubjectvettings.classbroadsheet');
    Route::get('/mysubjectvettings/classbroadsheetmock/{schoolclassid}/{sessionid}/{termid}', [MySubjectVettingsController::class, 'classBroadsheetMock'])->name('mysubjectvettings.classbroadsheetmock');
    Route::put('/mysubjectvettings/{id}', [MySubjectVettingsController::class, 'update'])->name('mysubjectvettings.update');
    Route::put('/mysubjectvettings/{id}', [MySubjectVettingsController::class, 'updateMock'])->name('mysubjectvettings.updatemock');


    Route::get('/mymocksubjectvettings', [MyMockSubjectVettingsController::class, 'index'])->name('mymocksubjectvettings.index');
    Route::get('/mymocksubjectvettings/classbroadsheet/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}', [MyMockSubjectVettingsController::class, 'classBroadsheet'])->name('mymocksubjectvettings.classbroadsheet');
    Route::post('/mymocksubjectvettings/update-vetted-status', [MyMockSubjectVettingsController::class, 'updateVettedStatus'])->name('mymocksubjectvettings.update-vetted-status');
    Route::get('/mymocksubjectvettings/results', [MyMockSubjectVettingsController::class, 'results'])->name('mymocksubjectvettings.results');
    Route::put('/mymocksubjectvettings/{id}', [MyMockSubjectVettingsController::class, 'update'])->name('mymocksubjectvettings.update');



    Route::post('/broadsheets/update-vetted-status', [MySubjectVettingsController::class, 'updateVettedStatus'])->name('broadsheets.update-vetted-status');

    //school information
    Route::resource('school-information', SchoolInformationController::class);







    Route::get('image-upload', [StaffImageUploadController::class, 'imageUpload'])->name('image.upload');
    Route::post('image-upload', [StaffImageUploadController::class, 'imageUploadPost'])->name('image.upload.post');



    // Main resource routes (index, create, store, show, edit, update, destroy)
    Route::resource('exams', ExamController::class)->except(['show']); // 'show' not used

    // Custom routes (override or add what resource doesn't cover)
    Route::delete('exams/bulk-destroy', [ExamController::class, 'bulkDestroy'])
        ->name('exams.bulk-destroy');

    // View students who attempted this exam + class filter support
    Route::get('exams/{exam}/students', [ExamController::class, 'showStudents'])
        ->name('exams.students');

    // Delete a student's attempt (allow retake)
    Route::delete('exams/{exam}/students/{student}/attempt', [ExamController::class, 'deleteStudentAttempt'])
        ->name('exams.student.attempt.delete');

    // View detailed answers for one student
    Route::get('exams/{exam}/students/{student}/answers', [ExamController::class, 'showStudentAnswers'])
        ->name('exams.student.answers');

    // Download question paper PDF with student's answers
    Route::get('exams/{exam}/students/{student}/question-paper', [ExamController::class, 'generateQuestionPaperPdf'])
        ->name('exams.student.question-paper');


    // Analytics dashboard for the exam
    Route::get('exams/{exam}/analytics', [ExamController::class, 'analytics'])
        ->name('exams.analytics');

    // Get filtered subjects based on term/session
    Route::get('exams/filtered-subjects', [ExamController::class, 'getFilteredSubjects'])
        ->name('exams.filtered-subjects');

    // Helper route: get classes for a subject (used in AJAX for modals)
    Route::get('exams/subject-classes/{subjectTeacherId}', [ExamController::class, 'getClassesForSubject'])
        ->name('exams.subject-classes');


         // Get exam questions for copy modal
    Route::get('/exams/{exam}/questions', [ExamController::class, 'getExamQuestions'])->name('exams.questions');

    Route::post('/exams/update-assessment-score', [ExamController::class, 'updateAssessmentScore'])->name('exams.update-assessment-score');
    Route::get('/exams/assessments/{examId}', [ExamController::class, 'getAssessments'])->name('exams.get-assessments');
    // Exam Transfer Subject Selection
    Route::get('/exams/transfer/subjects', [ExamController::class, 'showTransferSubjects'])->name('exams.transfer.subjects');
    Route::post('/exams/transfer/subjects', [ExamController::class, 'getTransferSubjects'])->name('exams.transfer.subjects.post');
    Route::get('/exams/transfer/scoresheet/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}', [ExamController::class, 'showTransferScoresheet'])->name('exams.transfer.scoresheet');

    Route::get('/exams/assessments/for-subject/{subjectclassId}/{termId}/{sessionId}', [ExamController::class, 'getAssessmentsForSubject'])->name('exams.assessments.for-subject');


    // Exam Transfer Subject Selection
    Route::get('/exams/transfer/subjects', [ExamController::class, 'showTransferSubjects'])->name('exams.transfer.subjects');
    Route::post('/exams/transfer/subjects', [ExamController::class, 'getTransferSubjects'])->name('exams.transfer.subjects.post');
    Route::get('/exams/transfer/scoresheet/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}', [ExamController::class, 'showTransferScoresheet'])->name('exams.transfer.scoresheet');

    // PDF Generation Route - THIS IS THE MISSING ONE
    Route::get('/exams/{exam}/generate-pdf/{student}', [ExamController::class, 'generateQuestionPaperPdf'])->name('exams.generate-pdf');

    Route::get('/exams/analytics/{exam}', [ExamController::class, 'analytics'])->name('exams.analytics');
    Route::get('/exams/questions/{exam}', [ExamController::class, 'getExamQuestions'])->name('exams.questions');

    // Specific routes FIRST
    Route::get('/questions/get-exams', [QuestionController::class, 'getExamsForSelection'])->name('questions.getExams');
    Route::get('/questions/all-questions', [QuestionController::class, 'index'])->name('questions.all');
    Route::post('/questions/import', [QuestionController::class, 'import'])->name('questions.import');
    Route::post('/questions/export/pdf', [QuestionController::class, 'exportPdf'])->name('questions.export.pdf');
    Route::post('/questions/export/word', [QuestionController::class, 'exportWord'])->name('questions.export.word');
    Route::post('/questions/{question}/duplicate', [QuestionController::class, 'duplicate'])->name('questions.duplicate');
    Route::post('/questions/reorder', [QuestionController::class, 'reorder'])->name('questions.reorder');
    Route::post('/questions/bulk-update', [QuestionController::class, 'bulkUpdate'])->name('questions.bulk.update');
    Route::get('/questions/reusable/list', [QuestionController::class, 'getReusableQuestions'])->name('questions.reusable.list');
    Route::delete('/questions/bulk-destroy', [QuestionController::class, 'bulkDestroy'])->name('questions.bulk.destroy');

    // Resource routes LAST
    Route::resource('questions', QuestionController::class);

    // Other question routes
    Route::get('/questions/{question}/details', [QuestionController::class, 'showDetails']);
    Route::get('/{question}/details', [QuestionController::class, 'details'])->name('questions.details');
    Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');

    Route::resource('cbt', CBTController::class);
    Route::get('/cbt/{examid}/takecbt', [CBTController::class, 'takeCBT'])->name('cbt.take');
    Route::post('/cbt/submit', [CBTController::class, 'submit'])->name('cbt.submit');

    // //Exams routes...
    // Route::resource('exams', ExamController::class);


    // //Questions routes...
    // Route::resource('questions', QuestionController::class);
    // Route::get('/questions/{question}/details', [QuestionController::class, 'showDetails']);
    // Route::post('/{exam}', [QuestionController::class, 'store'])->name('questions.store');
    // Route::get('/{question}/details', [QuestionController::class, 'details'])->name('questions.details');
    // Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
    // Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    // Route::delete('/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

    // //CBT  routes...
    // Route::resource('cbt', CBTController::class);
    // Route::get('/cbt/{examid}/takecbt', [CBTController::class, 'takeCBT'])->name('cbt.take');
    // Route::post('/cbt/submit', [CBTController::class, 'submit'])->name('cbt.submit');


    Route::post('/admin/exams/{exam}/pause', [ExamPauseController::class, 'pause'])->name('admin.exams.pause');
    Route::post('/admin/exams/{exam}/resume', [ExamPauseController::class, 'resume'])->name('admin.exams.resume');
    Route::get('/api/exams/{exam}/status', [ExamPauseController::class, 'status'])->name('api.exams.status');

    Route::get('/debug-student-scores', [ViewStudentReportController::class, 'debugStudentScores']);



// =========================================================================
// routes/web.php — UPDATED broadsheet route group
// Change Route::post('/web-view', ...) to Route::match(['GET','POST'], ...)
// so the form POST works AND direct GET navigation also works.
// =========================================================================

    Route::prefix('broadsheet')->name('broadsheet.')->group(function () {

        // Index page
        Route::get('/', [BroadsheetController::class, 'index'])->name('index');

        // AJAX: column options
        Route::post('/column-options',  [BroadsheetController::class, 'getColumnOptions'])->name('column-options');

        // AJAX: student preview
        Route::post('/student-preview', [BroadsheetController::class, 'getStudentPreview'])->name('student-preview');

        // Web View — accept both GET and POST (form submits via POST, direct link works via GET)
        Route::match(['GET', 'POST'], '/web-view', [BroadsheetController::class, 'webView'])->name('web-view');

        // PDF export
        Route::post('/export/pdf',   [BroadsheetController::class, 'exportPdf'])->name('export.pdf');

        // Excel export
        Route::post('/export/excel', [BroadsheetController::class, 'exportExcel'])->name('export.excel');
    });

    // All-classes broadsheet
    Route::post('/broadsheet/all-classes/web',  [BroadsheetController::class, 'allClassesWebView'])->name('broadsheet.all-classes.web');
    Route::post('/broadsheet/all-classes/pdf',  [BroadsheetController::class, 'allClassesExportPdf'])->name('broadsheet.all-classes.pdf');
    Route::get('/broadsheet/class-groups',      [BroadsheetController::class, 'getClassGroups'])->name('broadsheet.class-groups');

    // =========================================================================
    // TIMETABLE MANAGEMENT ROUTES
    // =========================================================================

    Route::prefix('timetable')->name('timetable.')->group(function () {

        // Views
        Route::get('/', [TimetableController::class, 'index'])->name('index');
        Route::get('/teacher', [TimetableController::class, 'teacherView'])->name('teacher');

        // AJAX — Setting management
        Route::post('/setup', [TimetableController::class, 'setup'])->name('setup');
        Route::get('/get-setting/{settingId}', [TimetableController::class, 'getSetting'])->name('get-setting');
        Route::post('/save-settings', [TimetableController::class, 'saveSettings'])->name('save-settings');
        Route::post('/save-constraints', [TimetableController::class, 'saveConstraints'])->name('save-constraints');
        Route::delete('/delete-setting/{settingId}', [TimetableController::class, 'deleteSetting'])->name('delete-setting');
        Route::post('/clone-setting', [TimetableController::class, 'cloneSetting'])->name('clone-setting');

        // AJAX — Grid & slots
        Route::post('/auto-generate', [TimetableController::class, 'autoGenerate'])->name('auto-generate');
        Route::get('/get-grid/{settingId}', [TimetableController::class, 'getGrid'])->name('get-grid');
        Route::post('/save-slot', [TimetableController::class, 'saveSlot'])->name('save-slot');
        Route::post('/bulk-update', [TimetableController::class, 'bulkUpdateSlots'])->name('bulk-update');

        // AJAX — Checks & utilities
        Route::get('/check-conflicts/{settingId}', [TimetableController::class, 'checkConflicts'])->name('check-conflicts');
        Route::post('/send-notifications', [TimetableController::class, 'sendNotifications'])->name('send-notifications');
        Route::get('/export/{settingId}', [TimetableController::class, 'export'])->name('export');

        // AJAX — Subjects & teachers
        Route::get('/class-subjects', [TimetableController::class, 'getClassSubjects'])->name('class-subjects');

        // AJAX — Teacher availability
        Route::post('/teacher-availability', [TimetableController::class, 'saveTeacherAvailability'])->name('teacher-availability');
        Route::get('/teacher-availability/{teacherId}', [TimetableController::class, 'getTeacherAvailability'])->name('get-teacher-availability');

        // AJAX — Substitutes
        Route::post('/request-substitute', [TimetableController::class, 'requestSubstitute'])->name('request-substitute');
        Route::post('/approve-substitute/{substituteId}', [TimetableController::class, 'approveSubstitute'])->name('approve-substitute');
        Route::get('/substitute-requests', [TimetableController::class, 'getSubstituteRequests'])->name('substitute-requests');

        // NEW: Available substitutes for a given slot (was missing, broke teacher view)
        Route::get('/available-substitutes', [TimetableController::class, 'getAvailableSubstitutes'])->name('available-substitutes');

        // AJAX — Dashboard & analytics
        Route::get('/workload-dashboard', [TimetableController::class, 'workloadDashboard'])->name('workload-dashboard');
        Route::post('/generate-analytics', [TimetableController::class, 'generateAnalytics'])->name('generate-analytics');


    });
    // Add this route with your other timetable routes
    Route::get('/timetable/export-whole-school', [TimetableController::class, 'exportWholeSchool'])->name('timetable.export-whole-school');
    Route::post('/timetable/check-conflict-suggestions', [TimetableController::class, 'checkConflictWithSuggestions'])->name('timetable.check-conflict-suggestions');
    // Add this route definition
    Route::post('/timetable/check-slot-conflict', [TimetableController::class, 'checkSlotConflict'])->name('timetable.check-slot-conflict');

    // Add these routes to your web.php file

    // =========================================================================
    // ROOM MANAGEMENT ROUTES
    // =========================================================================

    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [RoomController::class, 'index'])->name('index');
        Route::post('/', [RoomController::class, 'store'])->name('store');
        Route::get('/{room}', [RoomController::class, 'show'])->name('show');
        Route::put('/{room}', [RoomController::class, 'update'])->name('update');
        Route::delete('/{room}', [RoomController::class, 'destroy'])->name('destroy');
        Route::post('/{room}/book', [RoomController::class, 'book'])->name('book');
        Route::delete('/bookings/{booking}', [RoomController::class, 'cancelBooking'])->name('cancel-booking');
        Route::get('/availability/check', [RoomController::class, 'checkAvailability'])->name('check-availability');
    });

    // =========================================================================
    // EXAM TIMETABLE ROUTES
    // =========================================================================

    Route::prefix('exam-timetable')->name('exam-timetable.')->group(function () {
        Route::get('/', [ExamTimetableController::class, 'index'])->name('index');
        Route::post('/', [ExamTimetableController::class, 'store'])->name('store');
        Route::get('/{examTimetable}', [ExamTimetableController::class, 'show'])->name('show');
        Route::put('/{examTimetable}', [ExamTimetableController::class, 'update'])->name('update');
        Route::delete('/{examTimetable}', [ExamTimetableController::class, 'destroy'])->name('destroy');
        Route::post('/{examTimetable}/slots', [ExamTimetableController::class, 'addSlot'])->name('add-slot');
        Route::delete('/slots/{slot}', [ExamTimetableController::class, 'removeSlot'])->name('remove-slot');
        Route::post('/{examTimetable}/publish', [ExamTimetableController::class, 'publish'])->name('publish');
        Route::get('/{examTimetable}/export', [ExamTimetableController::class, 'export'])->name('export');
    });

    // =========================================================================
    // HOLIDAY MANAGEMENT ROUTES
    // =========================================================================

    Route::prefix('holidays')->name('holidays.')->group(function () {
        Route::get('/', [HolidayController::class, 'index'])->name('index');
        Route::post('/', [HolidayController::class, 'store'])->name('store');
        Route::get('/{holiday}', [HolidayController::class, 'show'])->name('show');    // WAS MISSING
        Route::put('/{holiday}', [HolidayController::class, 'update'])->name('update');
        Route::delete('/{holiday}', [HolidayController::class, 'destroy'])->name('destroy');
        Route::post('/{holiday}/apply', [HolidayController::class, 'applyToTimetable'])->name('apply');
    });

    // =========================================================================
    // TIMETABLE REPORTS ROUTES
    // =========================================================================

    Route::prefix('timetable-reports')->name('timetable.reports.')->group(function () {
        Route::get('/', [TimetableReportController::class, 'index'])->name('index');
        Route::post('/generate', [TimetableReportController::class, 'generate'])->name('generate');
        Route::get('/{report}', [TimetableReportController::class, 'show'])->name('show');
        Route::get('/download/{report}', [TimetableReportController::class, 'download'])->name('download');
        Route::delete('/{report}', [TimetableReportController::class, 'destroy'])->name('destroy');
        Route::post('/schedule', [TimetableReportController::class, 'schedule'])->name('schedule');
    });

    // =========================================================================
    // API ROUTES (also add to routes/api.php if using separate API prefix)
    // =========================================================================

    // The teacher view's JS calls /api/timetable/available-substitutes
    // Add this alias to api.php or define it here:
    Route::prefix('api/timetable')->name('api.timetable.')->group(function () {
        Route::get('/available-substitutes', [TimetableController::class, 'getAvailableSubstitutes'])
            ->name('available-substitutes')
            ->middleware('auth');
    });

    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
    Route::put('/promotions/{studentId}', [PromotionController::class, 'update'])->name('promotions.update');
    Route::delete('/promotions/{studentId}', [PromotionController::class, 'destroy'])->name('promotions.destroy');



    // ── Attendance Routes ──────────────────────────────────────────
    // Class teacher
    Route::get('/attendance/my-classes',              [AttendanceController::class, 'myClasses'])->name('attendance.my-classes');
    Route::get('/attendance/register/{classId}/{termId}/{sessionId}', [AttendanceController::class, 'register'])->name('attendance.register');
    Route::post('/attendance/save',                   [AttendanceController::class, 'save'])->name('attendance.save');
    Route::post('/attendance/save-single',            [AttendanceController::class, 'saveSingle'])->name('attendance.save-single');
    Route::post('/attendance/mark-all-present',       [AttendanceController::class, 'markAllPresent'])->name('attendance.mark-all-present');
    Route::get('/attendance/student/{studentId}/{classId}/{termId}/{sessionId}', [AttendanceController::class, 'studentReport'])->name('attendance.student-report');
    Route::get('/attendance/class-summary/{classId}/{termId}/{sessionId}', [AttendanceController::class, 'classSummary'])->name('attendance.class-summary');

    // Admin
    Route::get('/attendance/settings',                [AttendanceSettingController::class, 'index'])->name('attendance.settings');
    // Add this route in your routes file
    Route::put('/attendance/settings/{id}', [AttendanceSettingController::class, 'update'])->name('attendance.settings.update');
    Route::post('/attendance/settings',               [AttendanceSettingController::class, 'store'])->name('attendance.settings.store');
    Route::delete('/attendance/settings/{id}',        [AttendanceSettingController::class, 'destroy'])->name('attendance.settings.destroy');
    Route::get('/attendance/holidays',                [AttendanceSettingController::class, 'holidays'])->name('attendance.holidays');
    Route::post('/attendance/holidays',               [AttendanceSettingController::class, 'storeHoliday'])->name('attendance.holidays.store');
    Route::delete('/attendance/holidays/{id}',        [AttendanceSettingController::class, 'destroyHoliday'])->name('attendance.holidays.destroy');
    Route::get('/attendance/school-report',           [AttendanceSettingController::class, 'schoolReport'])->name('attendance.school-report');



    // Transcript Routes
    Route::prefix('transcript')->name('transcript.')->group(function () {
        Route::get('/',                [TranscriptController::class, 'index'])->name('index');
        Route::post('/search',         [TranscriptController::class, 'searchStudents'])->name('search');
        Route::post('/preview',        [TranscriptController::class, 'preview'])->name('preview');
        Route::post('/pdf',            [TranscriptController::class, 'exportPdf'])->name('pdf');
    });




    // Admin Score Entry Routes
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
        Route::prefix('score-entry')->name('score-entry.')->group(function () {
            // Main listing page
            Route::get('/', [AdminScoreEntryController::class, 'index'])->name('index');

            // Scoresheet view (terminal or mock)
            Route::get('/scoresheet/{subjectclassId}/{teacherId}/{termId}/{sessionId}/{type?}',
                [AdminScoreEntryController::class, 'showScoresheet'])
                ->name('scoresheet')
                ->where('type', 'terminal|mock');

            // Terminal scoresheet endpoints
            Route::post('/single-update', [AdminScoreEntryController::class, 'singleUpdate'])->name('single-update');
            Route::post('/bulk-update', [AdminScoreEntryController::class, 'bulkUpdate'])->name('bulk-update');
            Route::delete('/destroy', [AdminScoreEntryController::class, 'destroy'])->name('destroy');
            Route::get('/results', [AdminScoreEntryController::class, 'results'])->name('results');

            // Mock scoresheet endpoints
            Route::post('/mock-single-update', [AdminScoreEntryController::class, 'mockSingleUpdate'])->name('mock-single-update');
            Route::post('/mock-bulk-update', [AdminScoreEntryController::class, 'mockBulkUpdate'])->name('mock-bulk-update');

            // Download & export endpoints
            Route::get('/download-marks-sheet', [AdminScoreEntryController::class, 'downloadMarksSheet'])->name('download-marks-sheet');
            Route::get('/download-scores-pdf', [AdminScoreEntryController::class, 'downloadScoresPdf'])->name('download-scores-pdf');
            Route::get('/export', [AdminScoreEntryController::class, 'export'])->name('export');
            Route::post('/import', [AdminScoreEntryController::class, 'import'])->name('import');

            // Utility endpoints
            Route::post('/grade-for-score', [AdminScoreEntryController::class, 'calculateGradeForScore'])->name('grade-for-score');
            Route::post('/update-arm-positions', [AdminScoreEntryController::class, 'updateAllArmPositions'])->name('update-arm-positions');

            // Lock management routes
            Route::post('/lock-scoresheet', [AdminScoreEntryController::class, 'lockScoresheet'])->name('lock-scoresheet');
            Route::post('/unlock-scoresheet', [AdminScoreEntryController::class, 'unlockScoresheet'])->name('unlock-scoresheet');
            Route::post('/lock-with-schedule', [AdminScoreEntryController::class, 'lockScoresheetWithSchedule'])->name('lock-with-schedule');
            Route::post('/lock-batch', [AdminScoreEntryController::class, 'lockBatchScoresheets'])->name('lock-batch');
            Route::post('/lock-batch-with-schedule', [AdminScoreEntryController::class, 'lockBatchWithSchedule'])->name('lock-batch-with-schedule');
            Route::post('/unlock-batch', [AdminScoreEntryController::class, 'unlockBatchScoresheets'])->name('unlock-batch');
            Route::post('/disable-teacher-editing', [AdminScoreEntryController::class, 'disableTeacherEditing'])->name('disable-teacher-editing');
            Route::post('/enable-teacher-editing', [AdminScoreEntryController::class, 'enableTeacherEditing'])->name('enable-teacher-editing');
            Route::get('/lock-status', [AdminScoreEntryController::class, 'getLockStatus'])->name('lock-status');
        });
    });


});

// ============================================
    // WEBHOOK ROUTES (No CSRF)
    // ============================================
    Route::prefix('webhook')->group(function () {
        Route::post('/paystack', [FlexibleOnlinePaymentController::class, 'webhook'])->name('webhook.paystack');
        Route::post('/remita', [FlexibleOnlinePaymentController::class, 'webhook'])->name('webhook.remita');
        Route::post('/flutterwave', [FlexibleOnlinePaymentController::class, 'webhook'])->name('webhook.flutterwave');
    });
