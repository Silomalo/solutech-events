<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityLog extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'user_id',
        'event_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'deleted_at',
        'created_at',
    ];

    public static function createLog($userId, $eventId, $action, $description = null)
    {
        return self::create([
            'user_id' => $userId,
            'event_id' => $eventId,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
