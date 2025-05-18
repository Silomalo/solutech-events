<?php
use App\Livewire\TenantsDashboard;
use App\Livewire\Tenants\EventsView;
use Illuminate\Support\Facades\Route;

Route::prefix('account')->middleware([
    // 'auth:central_logins',
])->group(function () {
    Route::get('/', TenantsDashboard::class)->name('tenant.dashboard');
    Route::get('/events', EventsView::class)->name('tenants.events');
});
