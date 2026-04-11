<?php

use App\Livewire\Admin\Attendance\AttendanceIndex;
use App\Livewire\Admin\Management\Classroom\ClassroomIndex;
use App\Livewire\Admin\Management\Schedule\ScheduleIndex;
use App\Livewire\Admin\Management\Subject\SubjectIndex;
use App\Livewire\Admin\Personnel\Teacher\TeacherIndex;
use App\Livewire\Admin\Personnel\Youth\YouthIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::prefix('access')->name('access.')->group(function () {});

        Route::prefix('management')->name('management.')->group(function () {
            Route::get('classroom', ClassroomIndex::class)
                ->name('classroom.index')->middleware('permission:management.classroom.view');
            Route::get('subject', SubjectIndex::class)
                ->name('subject.index')->middleware('permission:management.subject.view');
            Route::get('schedule', ScheduleIndex::class)
                ->name('schedule.index')->middleware('permission:management.schedule.view');
        });

        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', AttendanceIndex::class)
                ->name('index')->middleware('permission:attendance.view');
        });

        Route::prefix('arrangement')->name('arrangement.')->group(function () {});

        Route::prefix('personnel')->name('personnel.')->group(function () {
            Route::get('teacher', TeacherIndex::class)
                ->middleware('permission:personnel.teacher.view')
                ->name('teacher.index');
            Route::get('youth', YouthIndex::class)
                ->middleware('permission:personnel.youth.view')
                ->name('youth.index');
        });

    });
});

require __DIR__.'/settings.php';
