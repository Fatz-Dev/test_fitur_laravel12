<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\GelombangController;
use App\Http\Controllers\Admin\MahasiswaManagementController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistration;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\GeocodeController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PasswordController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('mahasiswa.dashboard');
    }
    return redirect()->route('login');
});

// ---------- Guest routes ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ---------- Email verification (no auth required — link comes from email) ----------
Route::get('/verify-email', [EmailVerificationController::class, 'notice'])->name('email.notice');
Route::get('/email/verify/{id}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('email.verify');
Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->name('email.resend');

// ---------- Auth routes ----------
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')->name('logout');

Route::middleware('auth')->get('/api/geocode', [GeocodeController::class, 'search'])->name('geocode');

// ---------- Account (authenticated, any role) ----------
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
});

// ---------- Mahasiswa ----------
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [MahasiswaController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile/create', [MahasiswaController::class, 'createProfile'])->name('profile.create');
    Route::post('/profile', [MahasiswaController::class, 'storeProfile'])->name('profile.store');
    Route::post('/profile/location', [MahasiswaController::class, 'updateLocation'])->name('profile.location');
    Route::delete('/registrations/{registration}', [MahasiswaController::class, 'cancelRegistration'])->name('registrations.cancel');
});

// ---------- Admin ----------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    Route::get('/mahasiswa', [MahasiswaManagementController::class, 'index'])->name('mahasiswa.index');
    Route::get('/mahasiswa/{mahasiswa}', [MahasiswaManagementController::class, 'show'])->name('mahasiswa.show');
    Route::post('/mahasiswa/{mahasiswa}/approve', [MahasiswaManagementController::class, 'approve'])->name('mahasiswa.approve');
    Route::post('/mahasiswa/{mahasiswa}/reject', [MahasiswaManagementController::class, 'reject'])->name('mahasiswa.reject');
    Route::delete('/mahasiswa/{mahasiswa}', [MahasiswaManagementController::class, 'destroy'])->name('mahasiswa.destroy');

    Route::resource('schools', SchoolController::class)->except(['show']);

    Route::get('/registrations', [AdminRegistration::class, 'index'])->name('registrations.index');
    Route::post('/registrations/{registration}/approve', [AdminRegistration::class, 'approve'])->name('registrations.approve');
    Route::post('/registrations/{registration}/reject', [AdminRegistration::class, 'reject'])->name('registrations.reject');
    Route::delete('/registrations/{registration}', [AdminRegistration::class, 'destroy'])->name('registrations.destroy');

    Route::resource('gelombang', GelombangController::class)->except(['show']);
    Route::post('/gelombang/{gelombang}/activate', [GelombangController::class, 'activate'])->name('gelombang.activate');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
