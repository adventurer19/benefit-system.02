<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Api\CardValidationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

// Dashboard (only for logged-in users)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Dashboard Routes
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Dashboard API endpoints
    Route::get('/api/stats', [DashboardController::class, 'getStats']);
    Route::get('/api/successful-checkins', [DashboardController::class, 'getSuccessfulCheckins']);
    Route::get('/api/denied-access', [DashboardController::class, 'getDeniedAccess']);
    Route::get('/api/activity-log', [DashboardController::class, 'getActivityLog']);
    Route::get('/api/updates', [DashboardController::class, 'getUpdates']);
});

// Breeze's Profile routes (edit profile, etc.)
Route::middleware('auth')->group(function () {
    // Profile (admin role)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Member (client role)
    Route::get('/member/register', [MemberController::class, 'create'])->name('members.create');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/member/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
    Route::put('/member/{member}', [MemberController::class, 'update'])->name('members.update');
    Route::post('/memberships/{id}/renew', [MemberController::class, 'renew'])->name('memberships.renew');
    Route::delete('/memberships/{memberships}', [MemberController::class, 'destroy'])->name('memberships.destroy');
    Route::get('/memberships', [MemberController::class, 'index'])->name('memberships.index');
});


Route::post('api/gym/validate-card', [CardValidationController::class, 'validateCard'])
    ->name('api.gym.validate-card');

// File 5: Add to routes/web.php

// Load Breeze authentication routes (login, register, etc.)
require __DIR__ . '/auth.php';
