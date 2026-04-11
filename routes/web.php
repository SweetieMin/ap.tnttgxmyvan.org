<?php

use App\Livewire\Admin\Management\Classroom\ClassroomIndex;
use App\Livewire\Admin\Management\Schedule\ScheduleIndex;
use App\Livewire\Admin\Management\Subject\SubjectIndex;
use App\Livewire\Admin\Personnel\Teacher\TeacherIndex;
use App\Livewire\Admin\Personnel\Youth\YouthIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');


    Route::prefix('admin')->name('admin.')->group(function () {
        Route::prefix('access')->name('access.')->group(function () {
           
        });

        Route::prefix('management')->name('management.')->group(function () {
            Route::get('classroom', ClassroomIndex::class)
                ->name('classroom.index');
            Route::get('subject', SubjectIndex::class)
                ->name('subject.index');
            Route::get('schedule', ScheduleIndex::class)
                ->name('schedule.index');
        });

        Route::prefix('attendance')->name('attendance.')->group(function () {
           
        });

        Route::prefix('arrangement')->name('arrangement.')->group(function () {

        });

        Route::prefix('personnel')->name('personnel.')->group(function () {
            Route::get('teacher', TeacherIndex::class)
                ->middleware('can:personnel.teacher.view')
                ->name('teacher.index');
            Route::get('youth', YouthIndex::class)
                ->middleware('can:personnel.youth.view')
                ->name('youth.index');
        });

    });
});

require __DIR__.'/settings.php';
