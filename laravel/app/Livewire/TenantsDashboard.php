<?php

namespace App\Livewire;

use App\Models\Tenants\Event;
use Livewire\Component;

class TenantsDashboard extends Component
{
    public function render()
    {

        $events = Event::all();
        return view('livewire.tenants-dashboard', compact('events'));
        // ->layout('layouts.tenant');
    }
}