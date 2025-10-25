<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\SubmitController;
use App\Http\Controllers\UserController;

// صفحه خوش‌آمدگویی
Route::get('/', function () {
    return view('welcome');
});

// مسیرهای محافظت‌شده (فقط کاربران لاگین‌شده)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('dashboard')->group(function () {

    // داشبورد اصلی
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // صفحه لیست و جستجوی کاربران
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{profile}', [UserController::class, 'show'])->name('users.show');

    // === SUBMIT ROUTES (دقیق!) ===
    Route::get('/submit', [SubmitController::class, 'index'])->name('dashboard.submit.index');
    Route::post('/submit', [SubmitController::class, 'store'])->name('dashboard.submit.store');
    Route::get('/submit/{profile}/progress', [SubmitController::class, 'progress'])->name('dashboard.submit.progress');

});