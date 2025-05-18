<?php

use App\Livewire\TenantsView;
use App\Livewire\TenantManage;
use App\Livewire\TenantsStaff;
use App\Livewire\TenantDashboard;
use App\Livewire\TenantsStaffManage;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware([
    // 'auth:central_logins',
    // 'global_variables',
])->group(function () {

    Route::get('/', TenantDashboard::class)->name('central.dashboard');
    // Route::get('/dashboard', WelcomeAdmin::class)->name('central.dashboard');
    Route::get('/tenants', TenantsView::class)->name('central.tenants');
    Route::get('/tenant/{serial_number?}', TenantManage::class)->name('central.manage-tenant');
    Route::get('/{tenant_domain}/staff', TenantsStaff::class)->name('central.view-tenant-staffs');
    Route::get('/{tenant_domain}/staff/{serial_number?}', TenantsStaffManage::class)->name('central.manage-tenant-staff');

    //settings
    // Route::get('/jobs', ViewJobs::class)->name('central.jobs');
    // Route::get('/settings/central', SystemSettings::class)->name('settings.main');
});
