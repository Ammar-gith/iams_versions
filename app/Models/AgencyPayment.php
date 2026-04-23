<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AgencyPayment extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = [
        'agency_id',
        'batch_no',
        'media_bank_detail_id',
        'grand_amount',
        'gross_amount_15_percent',
        'it_inf',
        'it_department',
        'kpra_inf',
        'kpra_department',
        'sbp_charges',
        'adjustment',
        'net_dues',
        'received',
        'balance',
        'remarks',
    ];

    public function agency()
    {
        return $this->belongsTo(AdvAgency::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'agency_payment_id');
    }



    public function challan()
    {
        return $this->belongsTo(TreasuryChallan::class, 'challan_id');
    }

    /**
     * Exclude agency lines already settled for their ledger batch (paid_amounts.ledger_batch_no matches batch_no,
     * or legacy rows with null ledger_batch_no and matching batch_no).
     * Used by all payment listing views so paid payees disappear automatically.
     */
    public function scopeUnpaid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('batch_no')
                ->orWhereNull('agency_id')
                ->orWhereNotExists(function ($sub) {
                    $sub->from('paid_amounts')
                        ->where(function ($q) {
                            $q->whereColumn('paid_amounts.ledger_batch_no', 'agency_payments.batch_no')
                                ->orWhere(function ($q2) {
                                    $q2->whereNull('paid_amounts.ledger_batch_no')
                                        ->whereColumn('paid_amounts.batch_no', 'agency_payments.batch_no');
                                });
                        })
                        ->whereColumn('paid_amounts.payee_id', 'agency_payments.agency_id')
                        ->where('paid_amounts.payee_type', 'agency');
                });
        });
    }


    public function mediaBankDetail()
    {
        return $this->belongsTo(MediaBankDetail::class, 'media_bank_detail_id');
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
