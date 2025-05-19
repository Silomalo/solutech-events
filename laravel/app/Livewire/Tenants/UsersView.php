<?php

namespace App\Livewire\Tenants;

use App\Models\User;
use Livewire\Component;

class UsersView extends Component
{
    public function render()
    {
        $users = User::where('user_system_category', 3)->get();
        return view('livewire.tenants.users-view',compact('users'));
    }
}
