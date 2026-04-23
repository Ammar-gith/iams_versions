<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaAccountItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'pla_acount_id',
        'inf_number',
        'newspaper_id',
        'newspaper_amount',
        'adv_agency_id',
        'agency_commission_amount',
        'net_payable',
        'inf_details'
    ];

    protected $casts = [
        'inf_details' => 'array'
    ];

    public function plaAccount()
    {
        return $this->belongsTo(PlaAcount::class, 'pla_acount_id');
    }

    public function newspaper()
    {
        return $this->belongsTo(Newspaper::class);
    }

    public function agency()
    {
        return $this->belongsTo(AdvAgency::class, 'adv_agency_id');
    }
}
