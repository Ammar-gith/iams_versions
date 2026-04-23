<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ChallanSeries extends Model
{
    use HasFactory;
    protected $table = 'challan_series';

    protected $fillable = ['series', 'start_no', 'issued_no'];

    public function treasuryChallans()
    {
        return $this->hasMany(TreasuryChallan::class);
    }
}
