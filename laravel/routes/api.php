<?php

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Events\APIController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::POST('/login', [APIController::class, 'login'])->name('login');
Route::POST('/register', [APIController::class, 'register'])->name('register');
Route::get('/organizations/{domain?}', [APIController::class, 'getOrganizations'])->name('organizations');

Route::get('/users', function (Request $request) {
    return User::all();
});
