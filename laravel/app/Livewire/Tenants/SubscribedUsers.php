<?php

namespace App\Livewire\Tenants;

use App\Models\User;
use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenants\Attendee;

class SubscribedUsers extends Component
{

    use WithPagination;

    // Search and filtering properties
    public $search = '';
    public $userType = '';
    public $pagination = 10;

    // Sorting properties
    public $sortField = 'name';
    public $sortDirection = 'asc';

    // User deletion confirmation
    public $confirmingUserDeletion = false;
    public $userToDelete = null;
    public $event_id;

    protected $queryString = [
        'search' => ['except' => ''],
        'userType' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount($event_id)
    {
        $this->event_id = $event_id;
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingUserType()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function confirmUserDeletion($userId)
    {
        $this->userToDelete = $userId;
        $this->dispatch('confirm-user-deletion');
    }

    public function deleteUser()
    {
        if ($this->userToDelete) {
            try {
                $user = User::find($this->userToDelete);
                $userName = $user->name;

                $user->delete();

                session()->flash('message', "User '$userName' has been deleted successfully.");
            } catch (\Exception $e) {
                // Log::error('Error deleting user: ' . $e->getMessage());
                session()->flash('error', 'An error occurred while deleting the user.');
            }

            $this->userToDelete = null;
        }
    }


    public function render()
    {
        $query = User::query()
            ->join('attendees', 'users.id', '=', 'attendees.user_id')
            ->where('attendees.event_id', $this->event_id)
            ->select('users.*', 'attendees.check_in_time', 'attendees.event_id');

        // Apply search if provided
        if (!empty($this->search)) {
            $query->where(function ($query) {
                $query->where('users.name', 'like', '%' . $this->search . '%')
                    ->orWhere('users.email', 'like', '%' . $this->search . '%');
            });
        }

        // Filter by user type if selected
        if (!empty($this->userType)) {
            $query->where('users.user_system_category', $this->userType);
        }

        // Sort the results - need to specify table for ambiguous columns
        $sortField = $this->sortField;
        if (in_array($sortField, ['name', 'email', 'user_system_category'])) {
            $sortField = 'users.' . $sortField;
        }
        $query->orderBy($sortField, $this->sortDirection);

        // Paginate the results
        $users = $query->paginate($this->pagination);

        $event = Event::find($this->event_id);
        // dd($event);

        return view('livewire.tenants.subscribed-users', compact('users', 'event'));
    }
}
