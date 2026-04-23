<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TreasuryChallan extends Model
{
    use HasFactory, LogsActivity, HasRoles;

    protected $fillable = [
        'batch_no',
        'department_id',
        'office_id',
        'inf_number',
        'memo_number',
        'memo_date',
        'cheque_number',
        'cheque_date',
        'cheque_covering_letter_number',
        'cheque_covering_letter_date',
        'newspapers_amount',
        'total_amount',
        'bank_name',
        'bank_account_number',
        'tr_challan_image',
        'tr_challan_verification_date',
        'challan_number',
        'sbp_verification_date',
        'status_id',
        'forwarded_to_role_id',
        'verified_by',
        'approved_by',
        'verified_at',
        'approved_at',
        'rejection_reason',
        'created_by'
    ];


    protected $casts = [
        'inf_number' => 'array',
        'memo_date' => 'date',
        'cheque_date' => 'date',
        'tr_challan_verification_date' => 'date',
        'sbp_verification_date' => 'date',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',

    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'challan_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Status
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }


    // Add this method to TreasuryChallan model
    public function getOverallStatusAttribute()
    {
        $payments = $this->payments;

        if ($payments->isEmpty()) {
            return 'UNPAID';
        }

        $allPaid = $payments->every(function ($payment) {
            return $payment->status === 'PAID';
        });

        $anyUnpaid = $payments->contains(function ($payment) {
            return $payment->status === 'UNPAID';
        });

        $anyPartial = $payments->contains(function ($payment) {
            return $payment->status === 'PARTIALLY_PAID';
        });

        $anyOverPaid = $payments->contains(function ($payment) {
            return $payment->status === 'OVER_PAID';
        });

        if ($allPaid) {
            return 'PAID';
        } elseif ($anyPartial || ($anyUnpaid && $payments->where('status', 'PAID')->isNotEmpty())) {
            return 'PARTIALLY_PAID';
        } elseif ($anyOverPaid) {
            return 'OVER_PAID';
        }

        return 'UNPAID';
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
