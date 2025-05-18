<?php

namespace App\Livewire\Tenants;

use Livewire\Component;
use App\Models\Tenants\Event;
use Livewire\WithPagination;

class EventsView extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $sortField = 'date';
    public $sortDirection = 'asc';

    protected $queryString = ['search', 'status', 'sortField', 'sortDirection'];

    public function updatingSearch()
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

    public function render()
    {
        $events = Event::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($subQuery) {
                    $subQuery->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhere('venue', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                return $query->where('status', $this->status);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.tenants.events-view', [
            'events' => $events,
        ]);
    }
}
