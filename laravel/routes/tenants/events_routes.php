<?php
use App\Livewire\Tenants\ActivityLog;
use App\Livewire\Tenants\EventManage;
use App\Livewire\Tenants\SubscribedUsers;
use App\Livewire\Tenants\UsersView;
use App\Livewire\TenantsDashboard;
use App\Livewire\Tenants\EventsView;
use Illuminate\Support\Facades\Route;

Route::prefix('account')->middleware([
    // 'auth:central_logins',
])->group(function () {
    Route::get('/', TenantsDashboard::class)->name('tenant.dashboard');
    Route::get('/events', EventsView::class)->name('tenants.events');
    Route::get('/users', UsersView::class)->name('tenants.users');
    Route::get('/event/{event_id?}', EventManage::class)->name('tenants.events-manage');
    Route::get('/logs/{event_id?}', ActivityLog::class)->name('tenants.events-logs');
    Route::get('/attendees/{event_id}', SubscribedUsers::class)->name('tenants.event-attendees');
});
