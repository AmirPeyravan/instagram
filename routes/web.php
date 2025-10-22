<?php

use Illuminate\Support\Facades\Route;
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

    // نمایش مشخصات یک کاربر
    Route::get('/user/{pid}', [UserController::class, 'show'])->name('users.show');

});
