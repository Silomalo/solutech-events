<?php

namespace App\Livewire\Tenants;

use App\Models\User;
use Livewire\Component;
use App\Models\Tenants\Event;

class EventsView extends Component
{
    public function render()
    {
        // $events = Event::all();
        $events = User::all();
        dd($events);
        return view('livewire.tenants.events-view', compact('events'));
    }
}