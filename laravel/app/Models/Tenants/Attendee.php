<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;

class Attendee extends Model
{
    protected $fillable=[
        'event_id',
        'user_id',
        'confirmed',
        'ticket_number',
        'check_in_time',
    ];
}
