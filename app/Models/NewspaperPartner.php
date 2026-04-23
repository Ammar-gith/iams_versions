<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewspaperPartner extends Model
{
    protected $fillable = [
        'newspaper_id',
        'partner_name',
        'share_percentage',
        'media_bank_detail_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'share_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function newspaper(): BelongsTo
    {
        return $this->belongsTo(Newspaper::class);
    }

    public function mediaBankDetail(): BelongsTo
    {
        return $this->belongsTo(MediaBankDetail::class, 'media_bank_detail_id');
    }

    public function scopeActiveForNewspaper($query, int $newspaperId)
    {
        return $query->where('newspaper_id', $newspaperId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
