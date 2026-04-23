<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\Cast;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Image\Enums\BorderType;
use Spatie\Image\Enums\CropPosition;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use AhmedAliraqi\LaravelMediaUploader\Entities\Concerns\HasUploader;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class BillClassifiedAd extends Model implements HasMedia
{
    use HasFactory, HasRoles,  InteractsWithMedia, HasUploader, LogsActivity;
    protected $fillable =
    [
        'inf_number',
        'invoice_no',
        'invoice_date',
        'original_space',
        'size',
        'printed_size',
        'rate',
        'printed_rate',
        'no_of_insertion',
        'printed_no_of_insertion',
        'estimated_cost',
        'printed_bill_cost',
        'kpra_tax',
        'printed_total_bill',
        'press_cutting',
        'scanned_bill',
        'publication_date',
        'user_id',
        'advertisement_id',
        'newspaper_id',
        'placements',
        'rates_with_placement',
        'spaces',
        'total_spaces',
        'insertions',
        'total_cost_per_newspaper',
        'newspaper_share_amounts',
        'kpra_2_percent_on_85_percent_newspaper',
        'agency_share_amounts',
        'total_newspapers_tax',
        'kpra_10_percent_on_15_percent_agency',
        'total_agency_tax',
        'total_amount_with_taxes',
        'status',
        'paid_amount',
        'payment_status',
    ];

    protected $casts = [
        'newspaper_id' => 'array',
        'placements' => 'array',
        'rates_with_placement' => 'array',
        'spaces' => 'array',
        'total_spaces' => 'array',
        'insertions' => 'array',
        'total_cost_per_newspaper' => 'array',
        'newspaper_share_amounts' => 'array',
        'kpra_2_percent_on_85_percent_newspaper' => 'array',
        'agency_share_amounts' => 'array',
        'kpra_10_percent_on_15_percent_agency' => 'array',
        'total_amount_with_taxes' => 'array',
    ];


    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media && $media->mime_type === 'application/pdf') {
            $this->addMediaConversion('thumb')
                ->format('jpg')
                ->width(150)
                ->height(150)
                ->performOnCollections('scanned_bill', 'press_cutting')
                ->nonQueued();
        } else {
            $this->addMediaConversion('thumb')
                ->width(150)
                ->height(150)
                ->performOnCollections('scanned_bill', 'press_cutting')
                ->nonQueued();
        }
    }




    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getNewspaperTitlesAttribute()
    {
        $ids = $this->newspaper_id ?? [];
        return \App\Models\Newspaper::whereIn('id', $ids)->pluck('title')->toArray();
    }

    // ✅ Optional: Add an accessor to get full Newspaper models
    public function getNewspapersAttribute()
    {
        $ids = $this->newspaper_id ?? [];
        return Newspaper::whereIn('id', $ids)->get();
    }

    public function newspaper()
    {
        // If newspaper_id is a single ID (not array)
        return $this->belongsTo(Newspaper::class, 'newspaper_id');
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
