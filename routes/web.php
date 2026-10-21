<?php

use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\JoinListController;
use App\Http\Controllers\LogoutController;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Lists\Index as ListsIndex;
use App\Livewire\Lists\Show as ListsShow;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('/', 'welcome')->name('home');
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

Route::view('/privacy', 'legal.privacy')->name('privacy');
Route::view('/terms', 'legal.terms')->name('terms');

Route::get('/join/{token}', [JoinListController::class, 'show'])->middleware('throttle:60,1')->name('join.show');

Route::middleware('auth')->group(function () {
    Route::post('/join/{token}', [JoinListController::class, 'store'])->middleware('throttle:20,1')->name('join.store');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware('throttle:3,1')->name('verification.send');
    Route::post('/logout', LogoutController::class)->name('logout');
    Route::get('/lists', ListsIndex::class)->name('lists.index');
    Route::get('/lists/{shoppingList}', ListsShow::class)->name('lists.show');
    Route::get('/settings/profile', Profile::class)->name('settings.profile');
});
