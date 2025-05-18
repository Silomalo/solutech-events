<?php

namespace App\Livewire\Tenants;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;

class EventManage extends Component
{
    use WithFileUploads;

    // Form fields
    public $event_id;
    public $title;
    public $description;
    public $venue;
    public $date;
    public $time;
    public $price;
    public $max_attendees;
    public $cover_image;
    public $temp_cover_image;
    public $status = 'active';

    // Validation rules
    protected $rules = [
        'title' => 'required|min:3',
        'description' => 'required',
        'venue' => 'required',
        'date' => 'required|date|after_or_equal:today',
        'time' => 'required',
        'price' => 'required|numeric|min:0',
        'max_attendees' => 'required|integer|min:1',
        'temp_cover_image' => 'nullable|image|max:2048', // 2MB max
        'status' => 'required|in:active,cancelled,completed,postponed',
    ];

    public function mount($event_id = null)
    {
        if ($event_id) {
            $this->event_id = $event_id;
            $event = Event::findOrFail($event_id);

            $this->title = $event->title;
            $this->description = $event->description;
            $this->venue = $event->venue;
            $this->date = $event->date ? date('Y-m-d', strtotime($event->date)) : null;
            $this->time = $event->date ? date('H:i', strtotime($event->date)) : null;
            $this->price = $event->price;
            $this->max_attendees = $event->max_attendees;
            $this->cover_image = $event->cover_image;
            $this->status = $event->status;
        }
    }

    public function save()
    {
        $this->validate();

        // Combine date and time
        $dateTime = $this->date . ' ' . $this->time . ':00';

        // Handle image upload
        $coverImagePath = $this->cover_image;
        if ($this->temp_cover_image) {
            // Store the image in the public disk
            $coverImagePath = $this->temp_cover_image->store('events', 'public');

            // Remove old image if this is an update
            if ($this->event_id && $this->cover_image) {
                Storage::disk('public')->delete($this->cover_image);
            }
        }

        // Create or update event
        $event = $this->event_id ? Event::find($this->event_id) : new Event();

        $event->title = $this->title;
        $event->description = $this->description;
        $event->venue = $this->venue;
        $event->date = $dateTime;
        $event->price = $this->price;
        $event->max_attendees = $this->max_attendees;
        $event->cover_image = $coverImagePath;
        $event->status = $this->status;

        $event->save();

        // Redirect or show success message
        session()->flash('message', $this->event_id ? 'Event updated successfully!' : 'Event created successfully!');
        return redirect()->route('tenant.events');
    }

    public function clearImage()
    {
        $this->temp_cover_image = null;
    }

    public function render()
    {
        return view('livewire.tenants.event-manage');
    }
}
