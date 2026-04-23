<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ClassifiedAdType extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['type'];

    // Ad Categories
    public function adCategory()
    {
        return $this->belongsTo(AdCategory::class, 'ad_category_id');
    }

    // Advertisements
    public function advertisements()
    {
        return $this->hasMany(Advertisement::class, 'classified_ad_type_id');
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
