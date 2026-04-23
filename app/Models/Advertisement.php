<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use NunoMaduro\Collision\Adapters\Phpunit\State;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\BorderType;
use Spatie\Image\Enums\CropPosition;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use AhmedAliraqi\LaravelMediaUploader\Entities\Concerns\HasUploader;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;


class Advertisement extends Model implements HasMedia
{
    use HasFactory, HasRoles, InteractsWithMedia, HasUploader, Notifiable, LogsActivity;
    // 'newspaper_id', 'agency_id',
    protected $fillable = [
        'inf_number',
        'inf_series_id',
        'memo_number',
        'memo_date',
        'publish_on_or_before',
        'urdu_space',
        'urdu_size',
        'english_space',
        'english_size',
        'urdu_lines',
        'english_lines',
        'ad_rejection_reasons_id',
        'remarks',
        'user_id',
        'department_id',
        'office_id',
        'ad_category_id',
        'ad_worth_id',
        'status_id',
        'ad_type',
        'created_at',
        'news_pos_rate_id',
        'newspaper_id',
        'suptd_NP_log',
        'dd_NP_log',
        'dg_NP_log',
        'sec_NP_log',
        'adv_agency_id',
        'forwarded_by_role_id',
        'forwarded_to_role_id',
        'source_of_fund',
        'adp_code',
        'project_name',
        'bill_submitted_to_role_id',
        'updated_at',
        'classified_ad_type_id',
        'archived_at',
        'drafted_at'
    ];

    // Cast to a datetime type
    protected $casts = [
        'publish_on_or_before' => 'datetime',
        'ad_rejection_reasons_id' => 'array',
        'newspaper_id' => 'array',
        'suptd_NP_log' => 'array',
        'dd_NP_log' =>  'array',
        'dg_NP_log' => 'array',
        'sec_NP_log' => 'array',
        'archived_at' => 'datetime'
    ];

    // Register the media collections and retriving as thumbnail
    // public function registerMediaConversions(?Media $media = null): void
    // {
    //     $this
    //         ->addMediaConversion('thumb')
    //         ->performOnCollections('covering_letters', 'urdu_ads', 'english_ads')
    //         ->fit(Fit::Contain, 100, 100)
    //         ->nonQueued();

    // }


    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media && $media->mime_type === 'application/pdf') {
            $this->addMediaConversion('thumb')
                ->format('jpg')
                ->width(150)
                ->height(150)
                ->performOnCollections('covering_letters', 'urdu_ads', 'english_ads')
                ->nonQueued();
        } else {
            $this->addMediaConversion('thumb')
                ->width(150)
                ->height(150)
                ->performOnCollections('covering_letters', 'urdu_ads', 'english_ads')
                ->nonQueued();
        }
    }


    // Get the INF series this advertisement belongs to
    public function infSeries()
    {
        return $this->belongsTo(INFSeries::class, 'inf_series_id');
    }

    // User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Category
    public function category()
    {
        return $this->belongsTo(AdCategory::class, 'ad_category_id');
    }

    // Classified Ad Type
    public function classified_ad_type()
    {
        return $this->belongsTo(ClassifiedAdType::class);
    }

    // Status
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    // Estimated cost
    public function estimated_cost()
    {
        return $this->belongsTo(AdWorthParameter::class, 'status_id');
    }

    // Department
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // Office
    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    // Newspaper
    public function newspapers()
    {
        return $this->belongsToMany(Newspaper::class)->withPivot('agency_id', 'is_published', 'published_at')->withTimestamps();
    }


    // Agency
    public function advagency()
    {
        return $this->belongsTo(AdvAgency::class, 'adv_agency_id');
    }

    // Classified Bill
    public function billClassifiedAds()
    {
        return $this->hasMany(BillClassifiedAd::class);
    }


    // Ad Track
    public function changeLogs()
    {
        return $this->hasMany(AdChangeLog::class)->with('user');
    }

    // Archive
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    // Archive
    public function scopeNotArchived($query)
    {
        return $query->whereNull('archived_at');
    }

    public function rejectionReasons()
    {
        return $this->belongsToMany(AdRejectionReason::class,);
    }

    /**
     * Archive the current model.
     */
    public function archive()
    {
        $this->archived_at = now();
        $this->save();
    }

    /**
     * Restore the current model from archive.
     */
    public function unarchive()
    {
        $this->archived_at = null;
        $this->save();
    }

    /**
     * Check if the model is archived.
     */
    public function isArchived(): bool
    {
        return !is_null($this->archived_at);
    }


    // ===================


    // ======================


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'inf_number',
                'inf_series_id',
                'memo_number',
                'memo_date',
                'publish_on_or_before',
                'urdu_space',
                'urdu_size',
                'english_space',
                'english_size',
                'urdu_lines',
                'english_lines',
                'ad_rejection_reasons_id',
                'remarks',
                'user_id',
                'department_id',
                'office_id',
                'ad_category_id',
                'ad_worth_id',
                'status_id',
                'ad_type',
                'news_pos_rate_id',
                'newspaper_id',
                'suptd_NP_log',
                'dd_NP_log',
                'dg_NP_log',
                'sec_NP_log',
                'adv_agency_id',
                'forwarded_by_role_id',
                'forwarded_to_role_id',
                'source_of_fund',
                'adp_code',
                'project_name',
                'bill_submitted_to_role_id',
                'classified_ad_type_id',
                'archived_at',
                'drafted_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => 'Advertisement created',
                'updated' => 'Advertisement updated',
                'deleted' => 'Advertisement deleted',
                default   => $eventName,
            });
    }
}
