<?php

use App\Http\Controllers\Admin\ClassAssignmentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\GelombangController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\MahasiswaManagementController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistration;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SupervisorManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeocodeController;
use App\Http\Controllers\Mahasiswa\SubmissionController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupervisorController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->isAdmin()) return redirect()->route('admin.dashboard');
        if ($user->isSupervisor()) return redirect()->route('supervisor.dashboard');
        return redirect()->route('mahasiswa.dashboard');
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware('auth')->get('/api/geocode', [GeocodeController::class, 'search'])->name('geocode');

// ─── Mahasiswa ────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [MahasiswaController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile/create', [MahasiswaController::class, 'createProfile'])->name('profile.create');
    Route::post('/profile', [MahasiswaController::class, 'storeProfile'])->name('profile.store');
    Route::post('/profile/location', [MahasiswaController::class, 'updateLocation'])->name('profile.location');
    Route::delete('/registrations/{registration}', [MahasiswaController::class, 'cancelRegistration'])->name('registrations.cancel');

    // SIPEP Class
    Route::get('/class', [SubmissionController::class, 'classList'])->name('class.index');
    Route::get('/class/{registration}/assignments', [SubmissionController::class, 'assignments'])->name('class.assignments');
    Route::get('/class/{registration}/assignments/{assignment}', [SubmissionController::class, 'submissionDetail'])->name('class.submission');
    Route::post('/class/{registration}/assignments/{assignment}/submit', [SubmissionController::class, 'submit'])->name('class.submit');
});

// ─── Admin ────────────────────────────────────────────────────────────────────
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

    // SIPEP Class — Tugas
    Route::resource('class/assignments', ClassAssignmentController::class)
        ->names([
            'index'   => 'class.assignments.index',
            'create'  => 'class.assignments.create',
            'store'   => 'class.assignments.store',
            'edit'    => 'class.assignments.edit',
            'update'  => 'class.assignments.update',
            'destroy' => 'class.assignments.destroy',
        ]);

    // SIPEP Class — Nilai
    Route::get('/class/grades', [GradeController::class, 'index'])->name('class.grades');

    // Supervisor Management
    Route::get('/supervisors', [SupervisorManagementController::class, 'index'])->name('supervisors.index');
    Route::post('/supervisors', [SupervisorManagementController::class, 'store'])->name('supervisors.store');
    Route::delete('/supervisors/{user}', [SupervisorManagementController::class, 'destroy'])->name('supervisors.destroy');
    Route::post('/supervisors/assign', [SupervisorManagementController::class, 'assignSchool'])->name('supervisors.assign');
});

// ─── Supervisor ───────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorController::class, 'dashboard'])->name('dashboard');
    Route::get('/classes', [SupervisorController::class, 'classesList'])->name('classes.index');
    Route::get('/classes/{school}', [SupervisorController::class, 'classDetail'])->name('classes.show');
    Route::get('/classes/{school}/students/{profile}/assignments', [SupervisorController::class, 'studentAssignments'])->name('students.assignments');
    Route::get('/classes/{school}/students/{profile}/assignments/{assignment}', [SupervisorController::class, 'submissionDetail'])->name('submissions.show');
    Route::post('/classes/{school}/students/{profile}/assignments/{assignment}/grade', [SupervisorController::class, 'gradeSubmission'])->name('submissions.grade');
});
