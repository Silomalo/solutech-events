<?php

use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Password;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Settings\Appearance;
use Illuminate\Support\Facades\Route;

// Auth::routes(['register' => false]);
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    require __DIR__ . '/central/central_routes.php';
    require __DIR__ . '/tenants/events_routes.php';
});

require __DIR__ . '/auth.php';
