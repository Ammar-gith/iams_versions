<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class District extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'province_id'];

    // Province
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    // Newspaper
    public function newspaper()
    {
        return $this->belongsTo(Newspaper::class);
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
