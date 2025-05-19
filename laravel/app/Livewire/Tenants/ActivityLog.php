<?php

namespace App\Livewire\Tenants;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenants\ActivityLog as ActivityLogModel;

class ActivityLog extends Component
{
    use WithPagination;

    public $search = '';
    public $action = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $event_id = null;

    protected $queryString = ['search', 'action', 'sortField', 'sortDirection'];

    public function mount($event_id = null)
    {
        $this->event_id = $event_id;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingAction()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = ActivityLogModel::query();

        // Filter by event ID if provided
        if ($this->event_id) {
            $query->where('event_id', $this->event_id);
        }

        // Apply search if provided
        if (!empty($this->search)) {
            $query->where(function ($query) {
                $query->where('description', 'like', '%' . $this->search . '%')
                    ->orWhere('action', 'like', '%' . $this->search . '%');
            });
        }

        // Filter by action if selected
        if (!empty($this->action)) {
            $query->where('action', $this->action);
        }

        // Apply sorting
        $activityLogs = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.tenants.activity-log', [
            'activityLogs' => $activityLogs
        ]);
    }
}
