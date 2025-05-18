<?php

namespace App\Models\Tenants;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use UUID,SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'venue',
        'date',
        'price',
        'max_attendees',
        'cover_image',
    ];
}
