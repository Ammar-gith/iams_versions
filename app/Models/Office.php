<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Office extends Model
{
    use HasFactory, LogsActivity;

    public const office_status = [
        1 => 'Active',
        0 => 'In Active'
    ];

    protected $fillable = ['ddo_name', 'ddo_code', 'department_id', 'district_id', 'office_category_id', 'status', 'opening_dues'];


    public function advertisements()
    {
        return $this->hasMany(Advertisement::class, 'office_id');
    }

    public function officeCategory()
    {
        return $this->belongsTo(OfficeCategory::class, 'office_category_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
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
