<?php

namespace App\Models;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Spatie\Activitylog\Traits\LogsActivity;
// use Spatie\Activitylog\LogOptions;

class AdChangeLog extends Model
{
    use HasFactory;

    // Fillable
    protected $fillable = [
        'advertisement_id',
        'user_id',
        'role',
        'field',
        'old_value',
        'new_value',
        'changed_at',
        'action',
        'from_status',
        'to_status',
        'assigned_to_id',
        'metadata',
        'comments',
    ];

    // Cast metadata to array
    protected $casts = [
        'metadata' => 'array',
        'changed_at' => 'datetime',
    ];

    // User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Assigned To User
    public function assignedTo()
    {
        return $this->belongsTo(Role::class, 'assigned_to_id');
    }

    // Advertisement
    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }




}
