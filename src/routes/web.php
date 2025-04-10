<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

// 認証関連のルート
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// 勤怠管理関連のルート
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/list', [App\Http\Controllers\AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/{id}', [App\Http\Controllers\AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance', [App\Http\Controllers\AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/break/start', [App\Http\Controllers\AttendanceController::class, 'startBreak'])->name('attendance.break.start');
    Route::post('/attendance/break/end', [App\Http\Controllers\AttendanceController::class, 'endBreak'])->name('attendance.break.end');
    Route::post('/attendance/end', [App\Http\Controllers\AttendanceController::class, 'end'])->name('attendance.end');
    Route::get('/attendance/status', [App\Http\Controllers\AttendanceController::class, 'getCurrentStatus'])->name('attendance.status');

    // 打刻修正申請のルート
    Route::post('/stamp-correction-request', [App\Http\Controllers\StampCorrectionRequestController::class, 'store'])->name('stamp_correction_request.store');
    Route::get('/stamp-correction-request/list', [App\Http\Controllers\StampCorrectionRequestController::class, 'list'])->name('stamp_correction_request.list');
});

// 管理者用のルート
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [App\Http\Controllers\Admin\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Admin\LoginController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\Admin\LoginController::class, 'logout'])->name('logout');

    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/attendance/list', [App\Http\Controllers\Admin\AttendanceController::class, 'list'])->name('attendance.list');
        Route::get('/attendance/{id}', [App\Http\Controllers\Admin\AttendanceController::class, 'show'])->name('attendance.show');
        Route::get('/staff/list', [App\Http\Controllers\Admin\StaffController::class, 'list'])->name('staff.list');
    });
});
