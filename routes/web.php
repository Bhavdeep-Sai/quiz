<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\AttemptController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Str;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Public Quiz Routes
Route::prefix('quizzes')->group(function () {
    Route::get('/', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/{quiz}/start', [AttemptController::class, 'start'])->name('quizzes.start');
});

// Quiz Management Routes (Admin)
Route::prefix('admin/quizzes')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [QuizController::class, 'manage'])->name('quizzes.manage');
    Route::get('/create', [QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/', [QuizController::class, 'store'])->name('quizzes.store');
    Route::get('/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::get('/{quiz}/edit', [QuizController::class, 'edit'])->name('quizzes.edit');
    Route::put('/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
    Route::delete('/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.destroy');
    
    // Question management
    Route::post('/{quiz}/questions', [QuizController::class, 'storeQuestion'])->name('questions.store');
    Route::put('/{quiz}/questions/{question}', [QuizController::class, 'updateQuestion'])->name('questions.update');
    Route::delete('/{quiz}/questions/{question}', [QuizController::class, 'destroyQuestion'])->name('questions.destroy');
    
    // Attempts/Results
    Route::get('/{quiz}/attempts', [AttemptController::class, 'listAttempts'])->name('attempts.list');
});

// Quiz Attempt Routes
Route::prefix('attempts')->group(function () {
    Route::post('/{quiz}', [AttemptController::class, 'store'])->name('attempts.store');
    Route::get('/{attempt}', [AttemptController::class, 'show'])->name('attempts.show');
    Route::match(['post', 'put'], '/{attempt}/submit', [AttemptController::class, 'submit'])->name('attempts.submit');
    Route::get('/{attempt}/result', [AttemptController::class, 'result'])->name('attempts.result');
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
