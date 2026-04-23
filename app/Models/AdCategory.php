<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AdCategory extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['title'];

    // Advertisements
    public function advertisements()
    {
        return $this->hasMany(Advertisement::class, 'ad_category_id');
    }

    // Classifies Ad Types
    public function classifiedTypes()
    {
        return $this->hasMany(ClassifiedAdType::class, 'ad_category_id');
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
