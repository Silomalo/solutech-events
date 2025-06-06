<?php

namespace App\Livewire\Tenants;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class UsersView extends Component
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

    protected $queryString = [
        'search' => ['except' => ''],
        'userType' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

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
                Log::error('Error deleting user: ' . $e->getMessage());
                session()->flash('error', 'An error occurred while deleting the user.');
            }

            $this->userToDelete = null;
        }
    }

    public function render()
    {
        $query = User::query();

        // Apply search if provided
        if (!empty($this->search)) {
            $query->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Filter by user type if selected
        if (!empty($this->userType)) {
            $query->where('user_system_category', $this->userType);
        }

        // Sort the results
        $query->orderBy($this->sortField, $this->sortDirection);

        // Paginate the results
        $users = $query->paginate($this->pagination);

        return view('livewire.tenants.users-view', compact('users'));
    }
}