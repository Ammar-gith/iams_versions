<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaidAmount extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_no',
        'ledger_batch_no',
        'payee_id',
        'payee_name',
        'payee_type',   // newspaper | agency | kpra | fbr
        'media_bank_detail_id',
        'paid_amount',
        'cheque_no',
        'cheque_date',
        'amount',
        'status', // paid | reversed
    ];

    // Cast to a datetime type
    protected $casts = [
        'cheque_date' => 'datetime',

    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function mediaBankDetail()
    {
        return $this->belongsTo(MediaBankDetail::class, 'media_bank_detail_id');
    }

    public function newspaper()
    {
        return $this->belongsTo(Newspaper::class, 'payee_id');
    }


    public function newspaperPartner()
    {
        return $this->belongsTo(NewspaperPartner::class, 'payee_id');
    }

    public function agency()
    {
        return $this->belongsTo(AdvAgency::class, 'payee_id');
    }

    /**
     * Whether this payee's obligation for a given ledger batch is already settled
     * (paid on same batch or carried forward and paid on a later screen batch).
     */
    /**
     * Newspaper settlement: either classic single payee (newspaper) or all active partner lines (newspaper_partner).
     */
    public static function isNewspaperPayeeSettledForLedger(string $ledgerBatch, int $newspaperId): bool
    {
        $partnerCount = NewspaperPartner::where('newspaper_id', $newspaperId)->where('is_active', true)->count();
        if ($partnerCount === 0) {
            return static::isLedgerSettled($ledgerBatch, 'newspaper', $newspaperId);
        }

        $partners = NewspaperPartner::where('newspaper_id', $newspaperId)->where('is_active', true)->get();
        foreach ($partners as $p) {
            if (! static::isLedgerSettled($ledgerBatch, 'newspaper_partner', $p->id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether this payee is actually PAID for a given ledger batch.
     * Used by Paid Amount screen (reversed should re-appear as pending).
     */
    public static function isLedgerPaid(string $ledgerBatch, string $payeeType, ?int $payeeId): bool
    {
        return static::query()
            ->where('payee_type', $payeeType)
            ->where('payee_id', $payeeId)
            ->where(function ($q) use ($ledgerBatch) {
                $q->where('ledger_batch_no', $ledgerBatch)
                    ->orWhere(function ($q2) use ($ledgerBatch) {
                        $q2->whereNull('ledger_batch_no')->where('batch_no', $ledgerBatch);
                    });
            })
            ->where(function ($q) {
                $q->where('status', 'paid')
                    ->orWhereNull('status'); // legacy treated as paid
            })
            ->exists();
    }

    /** Newspaper paid state: either classic single payee or all partners. */
    public static function isNewspaperPayeePaidForLedger(string $ledgerBatch, int $newspaperId): bool
    {
        $partnerCount = NewspaperPartner::where('newspaper_id', $newspaperId)->where('is_active', true)->count();
        if ($partnerCount === 0) {
            return static::isLedgerPaid($ledgerBatch, 'newspaper', $newspaperId);
        }

        $partners = NewspaperPartner::where('newspaper_id', $newspaperId)->where('is_active', true)->get();
        foreach ($partners as $p) {
            if (! static::isLedgerPaid($ledgerBatch, 'newspaper_partner', $p->id)) {
                return false;
            }
        }

        return true;
    }

    // old funciton in which a reversed or deleted rows are show in ui with his same batch.
    public static function isLedgerSettled(string $ledgerBatch, string $payeeType, ?int $payeeId): bool
    {
        return static::query()
            ->where('payee_type', $payeeType)
            ->where('payee_id', $payeeId)
            ->where(function ($q) use ($ledgerBatch) {
                $q->where('ledger_batch_no', $ledgerBatch)
                    ->orWhere(function ($q2) use ($ledgerBatch) {
                        $q2->whereNull('ledger_batch_no')->where('batch_no', $ledgerBatch);
                    });
            })
            // Settled when either actually paid OR explicitly reversed (user chose to skip it).
            ->where(function ($q) {
                $q->whereIn('status', ['paid', 'reversed'])
                    ->orWhereNull('status'); // legacy rows
            })
            ->exists();
    }

    // new funciton
    // public static function isLedgerSettled(string $ledgerBatch, string $payeeType, ?int $payeeId): bool
    // {
    //     return static::query()
    //         ->where('payee_type', $payeeType)
    //         ->where('payee_id', $payeeId)
    //         ->where(function ($q) use ($ledgerBatch) {
    //             $q->where('ledger_batch_no', $ledgerBatch)
    //                 ->orWhere(function ($q2) use ($ledgerBatch) {
    //                     $q2->whereNull('ledger_batch_no')
    //                         ->where('batch_no', $ledgerBatch);
    //                 });
    //         })
    //         ->where('status', 'paid') // 🔥 ONLY PAID IS SETTLED
    //         ->exists();
    // }

    /** Resolve the actual payee model dynamically. */
    public function getPayeeAttribute()
    {
        return match ($this->payee_type) {
            'newspaper' => Newspaper::find($this->payee_id),
            'newspaper_partner' => NewspaperPartner::find($this->payee_id),
            'agency'    => AdvAgency::find($this->payee_id),
            'kpra',
            'fbr'       => TaxPayee::find($this->payee_id),
            default     => null,
        };
    }

    /** Resolve a human-readable payee name. */
    public function getPayeeNameAttribute(): string
    {
        return match ($this->payee_type) {
            'newspaper' => optional(Newspaper::find($this->payee_id))->title ?? 'Unknown Newspaper',
            'newspaper_partner' => (function () {
                $p = $this->relationLoaded('newspaperPartner') ? $this->newspaperPartner : NewspaperPartner::with('newspaper')->find($this->payee_id);
                if (! $p) {
                    return 'Newspaper partner';
                }
                $t = optional($p->newspaper)->title;

                return trim(($t ? $t . ' — ' : '') . $p->partner_name);
            })(),
            'agency'    => optional(AdvAgency::find($this->payee_id))->name  ?? 'Unknown Agency',
            'kpra'      => optional(TaxPayee::find($this->payee_id))->description ?? 'KPRA',
            'fbr'       => optional(TaxPayee::find($this->payee_id))->description ?? 'FBR',
            default     => 'Unknown',
        };
    }


    // app/Models/PaidAmount.php



    /**
     * Apply filters to PaidAmount query.
     *
     * @param Builder $query
     * @param array|Request $filters
     * @return Builder
     */
    public function scopePaidAmountFilter($query, $filters)
    {
        $filters = $filters instanceof Request ? $filters->all() : $filters;

        // 1. Global search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'LIKE', "%{$search}%")
                    // ->orWhere('ledger_batch_no', 'LIKE', "%{$search}%")
                    ->orWhere('cheque_no', 'LIKE', "%{$search}%")
                    ->orWhere('payee_name', 'LIKE', "%{$search}%")
                    ->orWhere('payee_type', 'LIKE', "%{$search}%")
                    ->orWhereHas('mediaBankDetail', fn($bq) => $bq->where('bank_name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('agency', fn($advgq) => $advgq->where('name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('newspaper', fn($nq) => $nq->where('title', 'LIKE', "%{$search}%"))
                    ->orWhereHas('newspaperPartner', fn($npq) => $npq->where('partner_name', 'LIKE', "%{$search}%"));
            });
        }

        // 2. Batch number (exact or partial)
        if (!empty($filters['batch_no'])) {
            $query->where('batch_no', 'LIKE', "%{$filters['batch_no']}%");
        }

        // 2. Cheque number (exact or partial)
        if (!empty($filters['cheque_no'])) {
            $query->where('cheque_no', 'LIKE', "%{$filters['cheque_no']}%");
        }

        // 3. Payee type (newspaper, agency, kpra, fbr, newspaper_partner)
        if (!empty($filters['payee_type'])) {
            $query->where('payee_type', $filters['payee_type']);
        }



        // for exact match
        // if (!empty($filters['bank_name'])) {
        //     $query->whereHas('mediaBankDetail', function ($q) use ($filters) {
        //         $q->where('bank_name', $filters['bank_name']);
        //     });
        // }

        // for exact match
        if (!empty($filters['newspaper_name'])) {
            $newspaperName = trim($filters['newspaper_name']);
            $query->where(function ($subQuery) use ($newspaperName) {
                // Direct newspaper payments
                $subQuery->where('payee_type', 'newspaper')
                    ->whereHas('newspaper', function ($q) use ($newspaperName) {
                        $q->where('title', $newspaperName);
                    });
                // Partner payments
                $subQuery->orWhere(function ($q) use ($newspaperName) {
                    $q->where('payee_type', 'newspaper_partner')
                        ->whereHas('newspaperPartner.newspaper', function ($q2) use ($newspaperName) {
                            $q2->where('title', $newspaperName);
                        });
                });
            });
        }

        // Bank filter – use LIKE for flexibility
        if (!empty($filters['bank_name'])) {
            $bankName = trim($filters['bank_name']);
            $query->whereHas('mediaBankDetail', function ($q) use ($bankName) {
                $q->where('bank_name', 'LIKE', "%{$bankName}%");
            });
        }

        //
        // if (!empty($filters['advAgency_name'])) {
        //     $query->whereHas('agency', function ($q) use ($filters) {
        //         $q->where('name', $filters['advAgency_name']);
        //     });
        // }

        if (!empty($filters['advAgency_name'])) {
            $query->where('payee_type', 'agency')
                ->whereHas('agency', function ($q) use ($filters) {
                    $q->where('name', $filters['advAgency_name']);
                });
        }
        // 4. Status (paid, reversed)
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // 5. Cheque date range (use same input name 'cheque_date')
        if (!empty($filters['cheque_date'])) {
            $dates = explode(' to ', $filters['cheque_date']);
            if (count($dates) == 2) {
                $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $to   = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
                $query->whereBetween('cheque_date', [$from, $to]);
            }
        }

        // 6. Creation date range (submission date)
        if (!empty($filters['submission_date'])) {
            $dates = explode(' to ', $filters['submission_date']);
            if (count($dates) == 2) {
                $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $to   = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
                $query->whereBetween('created_at', [$from, $to]);
            }
        }

        return $query;
    }
}
