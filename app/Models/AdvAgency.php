<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AdvAgency extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'registration_date',
        'registered_with_kpra',
        'website',
        'profile_pba',
        'status_id',
        'phone_local',
        'email_local',
        'fax_local',
        'mailing_address_local',
        'person_name_local',
        'person_cell_local',
        'phone_hq',
        'email_hq',
        'fax_hq',
        'mailing_address_hq',
        'person_name_hq',
        'person_cell_hq',

    ];

    // Advertisements
    public function advertisements()
    {
        return $this->belongsToMany(Advertisement::class);
    }

    public function mediaBankDetails()
    {
        return $this->belongsToMany(MediaBankDetail::class);
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
