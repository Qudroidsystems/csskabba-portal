<?php

use App\Http\Controllers\Curriculum\ClassRepController;
use App\Http\Controllers\Curriculum\TopicConfirmController;
use App\Http\Controllers\Curriculum\TopicController;
use App\Http\Controllers\Curriculum\TopicProgressController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Curriculum: topics, scheme of work, coverage, teacher board, class reps
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('curriculum')->name('curriculum.')->group(function () {

    // Topics & coverage (admin / HOD)
    Route::prefix('topics')->name('topics.')->group(function () {
        Route::get('/', [TopicController::class, 'index'])->name('index');
        Route::get('/coverage', [TopicController::class, 'coverage'])->name('coverage');
        Route::post('/', [TopicController::class, 'store'])->name('store');
        Route::post('/bulk', [TopicController::class, 'bulkAdd'])->name('bulk');
        Route::post('/reorder', [TopicController::class, 'reorder'])->name('reorder');
        Route::put('/{topic}', [TopicController::class, 'update'])->whereNumber('topic')->name('update');
        Route::delete('/{topic}', [TopicController::class, 'destroy'])->whereNumber('topic')->name('destroy');
    });

    // Teacher progress board
    Route::prefix('my-topics')->name('progress.')->group(function () {
        Route::get('/', [TopicProgressController::class, 'index'])->name('index');
        Route::get('/{subjectclass}', [TopicProgressController::class, 'board'])->whereNumber('subjectclass')->name('board');
        Route::post('/{subjectclass}/topic/{topic}/mark', [TopicProgressController::class, 'mark'])->whereNumber(['subjectclass', 'topic'])->name('mark');
        Route::post('/{subjectclass}/topic/{topic}/verify', [TopicProgressController::class, 'verify'])->whereNumber(['subjectclass', 'topic'])->name('verify');
    });

    // Class reps (admin assign + rep confirm)
    Route::prefix('class-reps')->name('reps.')->group(function () {
        Route::get('/confirm', [TopicConfirmController::class, 'index'])->name('confirm');
        Route::post('/confirm/{progress}', [TopicConfirmController::class, 'act'])->whereNumber('progress')->name('act');
        Route::get('/', [ClassRepController::class, 'index'])->name('index');
        Route::post('/', [ClassRepController::class, 'store'])->name('store');
        Route::delete('/{rep}', [ClassRepController::class, 'destroy'])->whereNumber('rep')->name('destroy');
    });
});
