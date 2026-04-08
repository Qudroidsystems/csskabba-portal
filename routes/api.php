<?php
// routes/api.php - Add these routes

use App\Http\Controllers\Api\TimetableApiController;

Route::middleware('auth:sanctum')->prefix('timetable')->group(function () {
    // Teacher endpoints
    Route::get('my-timetable', [TimetableApiController::class, 'getMyTimetable']);
    Route::get('today-schedule', [TimetableApiController::class, 'getTodaySchedule']);
    Route::get('upcoming-classes', [TimetableApiController::class, 'getUpcomingClasses']);
    Route::post('mark-attendance', [TimetableApiController::class, 'markAttendance']);

    // Student/Parent endpoints
    Route::get('class-timetable/{classId}', [TimetableApiController::class, 'getClassTimetable']);
    Route::get('child-timetable/{studentId}', [TimetableApiController::class, 'getChildTimetable']);

    // Substitute requests
    Route::post('request-substitute', [TimetableApiController::class, 'requestSubstitute']);
    Route::get('substitute-requests', [TimetableApiController::class, 'getSubstituteRequests']);

    // Notifications
    Route::get('notifications', [TimetableApiController::class, 'getNotifications']);
    Route::post('notifications/mark-read', [TimetableApiController::class, 'markNotificationsRead']);
});
