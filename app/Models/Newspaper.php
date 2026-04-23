<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Newspaper extends Model
{
    public const is_combined = [
        1 => 'Yes',
        0 => 'No',
    ];

    public const KPRA = [
        'Yes' => 'Yes',
        'No' => 'No',
    ];

    public function getKpraAttribute()
    {
        return self::KPRA[$this->Kpra] ?? 'No';
    }

    public const STATUS = [
        1 => 'Active',
        0 => 'Inactive',
    ];

    public function getStatusLabelAttribute()
    {
        return self::STATUS[$this->status] ?? 'N/A';
    }

    use HasFactory, LogsActivity;

    protected $fillable = ['title', 'language_id', 'district_id', 'province_id', 'is_combined', 'daily_circulation', 'rate', 'rate_efc_date', 'periodicity_id', 'category_id', 'registration_date', 'phone_no', 'email', 'fax', 'website', 'fp_name', 'cell_no', 'status', 'opening_balance', 'register_with_kapra'];

    // Advertisements
    public function advertisements()
    {
        return $this->belongsToMany(Advertisement::class)->withPivot('agency_id', 'is_published', 'published_at')->withTimestamps();
    }

    // Language
    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    // Periodicity
    public function periodicity()
    {
        return $this->belongsTo(NewspaperPeriodicity::class);
    }

    // Category
    public function category()
    {
        return $this->belongsTo(NewspaperCategory::class);
    }

    // District
    public function District()
    {
        return $this->belongsTo(District::class);
    }

    // bill classifeid Ads
    public function billClassifiedAds()
    {
        return $this->belongsTo(BillClassifiedAd::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function newspaperPartners()
    {
        return $this->hasMany(NewspaperPartner::class);
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
