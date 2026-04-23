<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class INFSeries extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'inf_series';

    protected $fillable = ['series', 'start_number', 'issued_numbers'];

    // Get the advertisements associated with this INF series
    public function advertisements()
    {
        return $this->hasMany(Advertisement::class);
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
