<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use App\Mail\LeaveRequestCreated;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::prefix('profile')->group(function () {
//     Route::get('/', [UserController::class, 'getProfile'])->name('profile');
//     Route::get('/show', [UserController::class, 'getProfileInfo'])->name('profile.show');
// })->middleware(['auth', 'verified']);


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [UserController::class, 'getProfile'])->name('profile');
    Route::get('/profile/show', [UserController::class, 'getProfileInfo'])->name('profile.show');

    Route::get('/leaves/credits', [LeaveController::class, 'getLeaveCredits'])->name('leaves.credits');
    Route::get('/leaves/requests', [LeaveController::class, 'getLeaveRequests'])->name('leaves.requests');
    Route::get('/leaves/add', [LeaveController::class, 'addLeaveRequestPage'])->name('leaves.add');
    Route::post('/leaves/add', [LeaveController::class, 'postRequestLeave'])->name('leaves.create');
    Route::get('/leaves/{status}', [LeaveController::class, 'showLeavesPage'])->name('leaves.page');
    Route::post('/leaves/update-status', [LeaveController::class, 'postUpdateLeaveStatus'])->name('leaves.update-status');
    Route::post('/leaves/{id}', [LeaveController::class, 'postUpdateLeave'])->name('leaves.update');
    Route::get('/leaves/{id}/edit', [LeaveController::class, 'showEditLeaveRequestPage'])->name('leaves.edit');

    // Route::get('/members')
    Route::get('/team/members', [TeamController::class, 'showMembersPage'])->name('team.members');
    Route::get('/team/pending', [TeamController::class, 'showTeamRequestsPage'])->name('team.pending');
    Route::get('/team/requests', [TeamController::class, 'getMembersPendingRequests'])->name('team.requests');
    Route::get('/employees/list', [TeamController::class, 'getMembers'])->name('employees.lists');

    Route::get('/calendar', [CalendarController::class, 'showCalendarPage'])->name('calendar');
});

Route::get('/test', function () {

    $leaveRequest = \App\Models\LeaveRequest::find(13146);
    $leaveUser = $leaveRequest->user;

    // dump($leaveRequest->leaveStatusLog);
    dump($leaveRequest->latestLeaveStatusLog->toArray());

    // \Illuminate\Support\Facades\Mail::to($leaveUser->employee->supervisor()->user)->send(new LeaveRequestCreated($leaveRequest));
});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
