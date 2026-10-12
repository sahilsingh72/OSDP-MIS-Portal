<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BatchReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FieldVerificationController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\TrainingBatchController;
use App\Http\Controllers\TrainingUploadController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckActiveUser;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Application Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', CheckActiveUser::class])->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile & Password Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Master Data & Cascading AJAX Endpoints
    Route::get('/master-data', [MasterDataController::class, 'index'])->name('master-data.index');
    Route::get('/api/districts/{district}/blocks', [MasterDataController::class, 'getBlocks'])->name('api.districts.blocks');
    Route::get('/api/blocks/{block}/gram-panchayats', [MasterDataController::class, 'getGramPanchayats'])->name('api.blocks.gps');

    // Training Batches
    Route::resource('batches', TrainingBatchController::class);
    Route::post('/batches/{batch}/submit', [TrainingBatchController::class, 'submitForReview'])->name('batches.submit');

    // Form 2 Participant Attendance Transcription
    Route::get('/batches/{batch}/attendance', [AttendanceController::class, 'show'])->name('batches.attendance.show');
    Route::post('/batches/{batch}/attendance', [AttendanceController::class, 'update'])->name('batches.attendance.update');
    Route::post('/batches/{batch}/participants', [AttendanceController::class, 'addParticipant'])->name('batches.participants.store');

    // Evidence Upload Center & Private File Streaming
    Route::get('/batches/{batch}/evidence', [TrainingUploadController::class, 'index'])->name('batches.evidence.index');
    Route::post('/batches/{batch}/evidence', [TrainingUploadController::class, 'store'])->name('batches.evidence.store');
    Route::delete('/evidence/{upload}', [TrainingUploadController::class, 'destroy'])->name('evidence.destroy');
    Route::get('/evidence/{upload}/download', [TrainingUploadController::class, 'download'])->name('evidence.download');

    // Form 3 Part C Summary & Form 1 Feedback Metrics
    Route::post('/batches/{batch}/summary', [TrainingUploadController::class, 'saveSummary'])->name('batches.summary.store');
    Route::post('/batches/{batch}/feedback', [TrainingUploadController::class, 'saveFeedback'])->name('batches.feedback.store');

    // Review & Verification Queue
    Route::get('/reviews', [BatchReviewController::class, 'index'])->name('reviews.index');
    Route::post('/batches/{batch}/reviews', [BatchReviewController::class, 'store'])->name('batches.reviews.store');

    // Independent Field Monitoring
    Route::get('/verifications', [FieldVerificationController::class, 'index'])->name('verifications.index');
    Route::get('/verifications/create', [FieldVerificationController::class, 'create'])->name('verifications.create');
    Route::post('/verifications', [FieldVerificationController::class, 'store'])->name('verifications.store');

    // Reports & Exports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');

    // User Management
    Route::resource('users', UserController::class)->except(['show']);
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // System Settings
    Route::get('/settings', [SystemSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SystemSettingController::class, 'update'])->name('settings.update');
});
