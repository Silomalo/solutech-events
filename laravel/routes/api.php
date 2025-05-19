<?php

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Events\APIController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::POST('/subscribe/{event_id}', [APIController::class, 'toggleEventSubscription'])->name('event.subscribe')->middleware('auth:sanctum');
// Route::GET('/subscription/{eventId}', [APIController::class, 'checkEventSubscription'])->name('event.subscription.status')->middleware('auth:sanctum');
Route::GET('/subscribed-events', [APIController::class, 'subscriptionEventIds'])->name('event.subscription.id')->middleware('auth:sanctum');

Route::POST('/login', [APIController::class, 'login'])->name('login-api');
Route::POST('/register', [APIController::class, 'register'])->name('register-api');
Route::get('/organizations/{domain?}', [APIController::class, 'getOrganizations'])->name('organizations');

Route::get('/users', function (Request $request) {
    return User::all();
});
