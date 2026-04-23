<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MediaBankDetail extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = [
        'newspaper_id',
        'agency_id',
        'media_name',
        'bank_name',
        'account_title',
        'account_number',
    ];

    // Get media name
    public function getMediaNameAttribute()
    {
        if ($this->newspaper_id) {
            return $this->newspaper->title ?? 'Unknown Newspaper';
        } else {
            return $this->agency->name ?? 'Unknown Agency';
        }
    }

    // Relationships
    public function newspaper()
    {
        return $this->belongsTo(Newspaper::class);
    }

    public function agency()
    {
        return $this->belongsTo(AdvAgency::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function agencyPayments()
    {
        return $this->hasMany(AgencyPayment::class);
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
