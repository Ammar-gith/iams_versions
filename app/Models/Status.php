<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Status extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['title'];

    // Department
    public function departments()
    {
        return $this->hasMany(Department::class, 'status_id');
    }

    // Office
    public function offices()
    {
        return $this->hasMany(Office::class);
    }

    // Status
    public function advertisements()
    {
        return $this->hasMany(Advertisement::class, 'status_id');
    }

    /**
     * Configure the activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'content', 'status', 'approved_at'])
            ->logOnlyDirty()          // Only log changed attributes
            ->dontSubmitEmptyLogs();   // Don't log if nothing changed
    }
}
