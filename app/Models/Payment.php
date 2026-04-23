<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Payment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [

        'inf_number',
        'challan_id',
        'batch_no',
        'rt_number',
        'newspaper_id',
        'total_amount',
        'gross_amount_100_or_85_percent',
        'it_department',
        'it_inf',
        'kpra_inf',
        'kpra_department',
        'sbp_charges',
        'adjustment',
        'net_dues',
        'received',
        'balance',
        'status',
        'remarks',
        'agency_payment_id',
        'media_bank_detail_id',
        'payment_type'
    ];

    public function newspaper()
    {
        return $this->belongsTo(Newspaper::class);
    }

    public function challan()
    {
        return $this->belongsTo(TreasuryChallan::class, 'challan_id');
    }

    public function bill()
    {
        return $this->hasOne(BillClassifiedAd::class, 'inf_number', 'inf_number');
    }

    /**
     * Exclude payments already settled for their ledger batch (paid_amounts.ledger_batch_no matches payments.batch_no,
     * or legacy rows with null ledger_batch_no and matching batch_no).
     * Used by all payment listing views so paid payees disappear automatically.
     */
    public function scopeUnpaid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('batch_no')
                ->orWhereNull('newspaper_id')
                ->orWhere(function ($outer) {
                    $outer->whereNotNull('payments.batch_no')
                        ->whereNotNull('payments.newspaper_id')
                        ->where(function ($inner) {
                            // Unpaid when ledger is not fully settled (single NP row OR all partner rows).
                            $inner->where(function ($blockA) {
                                $blockA->whereNotExists(function ($sub) {
                                    $sub->from('newspaper_partners')
                                        ->whereColumn('newspaper_partners.newspaper_id', 'payments.newspaper_id')
                                        ->where('newspaper_partners.is_active', true);
                                })
                                    ->whereNotExists(function ($sub) {
                                        $sub->from('paid_amounts')
                                            ->where(function ($q) {
                                                $q->whereColumn('paid_amounts.ledger_batch_no', 'payments.batch_no')
                                                    ->orWhere(function ($q2) {
                                                        $q2->whereNull('paid_amounts.ledger_batch_no')
                                                            ->whereColumn('paid_amounts.batch_no', 'payments.batch_no');
                                                    });
                                            })
                                            ->whereColumn('paid_amounts.payee_id', 'payments.newspaper_id')
                                            ->where('paid_amounts.payee_type', 'newspaper')
                                            ->where(function ($q) {
                                                $q->whereIn('paid_amounts.status', ['paid', 'reversed'])
                                                    ->orWhereNull('paid_amounts.status');
                                            });
                                    });
                            })
                                ->orWhere(function ($blockB) {
                                    $blockB->whereExists(function ($sub) {
                                        $sub->from('newspaper_partners')
                                            ->whereColumn('newspaper_partners.newspaper_id', 'payments.newspaper_id')
                                            ->where('newspaper_partners.is_active', true);
                                    })
                                        ->whereExists(function ($sub) {
                                            $sub->from('newspaper_partners as np')
                                                ->whereColumn('np.newspaper_id', 'payments.newspaper_id')
                                                ->where('np.is_active', true)
                                                ->whereNotExists(function ($sub2) {
                                                    $sub2->from('paid_amounts as pa')
                                                        ->where('pa.payee_type', 'newspaper_partner')
                                                        ->whereColumn('pa.payee_id', 'np.id')
                                                        ->where(function ($w) {
                                                            $w->whereColumn('pa.ledger_batch_no', 'payments.batch_no')
                                                                ->orWhere(function ($w2) {
                                                                    $w2->whereNull('pa.ledger_batch_no')
                                                                        ->whereColumn('pa.batch_no', 'payments.batch_no');
                                                                });
                                                        })
                                                        ->where(function ($w) {
                                                            $w->whereIn('pa.status', ['paid', 'reversed'])
                                                                ->orWhereNull('pa.status');
                                                        });
                                                });
                                        });
                                });
                        });
                });
        });
    }
    public function agencyPayment()
    {
        return $this->belongsTo(AgencyPayment::class, 'agency_payment_id');
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


    // Inside Payment model class



    /**
     * Apply filters to Payment query.
     *
     * @param Builder $query
     * @param array|\Illuminate\Http\Request $filters
     * @return Builder
     */
    public function scopePaymentFilter($query, $filters)
    {
        // Convert Request to array if needed
        $filters = $filters instanceof \Illuminate\Http\Request ? $filters->all() : $filters;

        // 1. Global search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('payments.inf_number', 'LIKE', "%{$search}%")
                    ->orWhere('payments.rt_number', 'LIKE', "%{$search}%")
                    ->orWhere('payments.batch_no', 'LIKE', "%{$search}%")
                    ->orWhere('cheque_number', 'LIKE', "%{$search}%")

                    ->orWhereHas('newspaper', fn($nq) => $nq->where('title', 'LIKE', "%{$search}%"))
                    ->orWhereHas('mediaBankDetail', fn($bq) => $bq->where('bank_name', 'LIKE', "%{$search}%"));
            });
        }

        // 2. Exact INF number
        if (!empty($filters['inf_number'])) {
            $query->where('payments.inf_number', $filters['inf_number']);
        }

        // 3. Newspaper ID
        if (!empty($filters['newspaper_id'])) {
            $query->where('payments.newspaper_id', $filters['newspaper_id']);
        }

        // 4. Batch number (exact or partial)
        if (!empty($filters['batch_no'])) {
            $query->where('payments.batch_no', 'LIKE', "%{$filters['batch_no']}%");
        }

        // 5. Submission date range (created_at)
        // if (!empty($filters['sbp_verification_date'])) {
        //     $dates = explode(' to ', $filters['sbp_verification_date']);
        //     if (count($dates) == 2) {
        //         $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
        //         $to   = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
        //         $query->whereBetween('sbp_verfication_date', [$from, $to]);
        //     }
        // }

        // if (!empty($filters['sbp_verification_date'])) {
        //     $dates = explode(' to ', $filters['sbp_verification_date']);
        //     if (count($dates) == 2) {
        //         $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
        //         $to   = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
        //         $query->whereBetween('tr_challan_verification_date', [$from, $to]);
        //     }
        // }


        // 6. Optional: filter by department (requires join via treasury_challans)
        // Uncomment if needed:
        /*
    if (!empty($filters['department_id'])) {
        $query->whereHas('challan', fn($cq) => $cq->where('department_id', $filters['department_id']));
    }
    if (!empty($filters['office_id'])) {
        $query->whereHas('challan', fn($cq) => $cq->where('office_id', $filters['office_id']));
    }
    */

        return $query;
    }
}
