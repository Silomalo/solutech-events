<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'venue',
        'date',
        'price',
        'max_attendees',
        'cover_image',
        'status',
    ];

    protected $casts = [
        'date' => 'datetime',
        'price' => 'decimal:2',
        'max_attendees' => 'integer',
    ];

    // Relationship with attendees (assuming you might have an Attendee model)
    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }

    // Helper method to check if event is full
    public function isFull()
    {
        return $this->attendees()->count() >= $this->max_attendees;
    }

    // Helper method to get remaining spots
    public function remainingSpots()
    {
        return $this->max_attendees - $this->attendees()->count();
    }

    // Helper method to check if event is upcoming
    public function isUpcoming()
    {
        return $this->date->isFuture();
    }

    // Helper method to check if event is in progress
    public function isInProgress()
    {
        return $this->date->isPast() && $this->date->addHours(5)->isFuture(); // Assuming events last about 5 hours
    }

    // Helper method to check if event is completed
    public function isCompleted()
    {
        return $this->date->addHours(5)->isPast();
    }
}