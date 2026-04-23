<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PlaAcount extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['inf_number', 'department_id', 'office_id', 'newspaper_id', 'inf_details', 'cheque_no', 'cheque_date', 'challan_no', 'newspaper_amount', 'total_cheque_amount', 'created_by'];

    protected $casts = [
        'inf_number' => 'array',
        'newspaper_id' => 'array',
        'newspaper_amount' => 'array',
        'inf_details' => 'array',
        'cheque_date' => 'date',

    ];

    public function plaAccountItems()
    {
        return $this->hasMany(PlaAccountItem::class, 'pla_acount_id',);
    }


    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function newspaperName()
    {
        return $this->belongsTo(Office::class, 'newspaper_id');
    }
    // Status
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
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


    // seach and filter logic

    // app/Models/PlaAcount.php


    /**
     * Apply filters to PlaAcount query.
     *
     * @param Builder $query
     * @param array|Request $filters
     * @return Builder
     */
    public function scopePlaFilter($query, $filters)
    {
        // Convert Request to array if needed
        $filters = $filters instanceof Request ? $filters->all() : $filters;

        // 1. Global search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('cheque_no', 'LIKE', "%{$search}%")
                    ->orwhereJsonContains('inf_number', $search)
                    ->orWhere('challan_no', 'LIKE', "%{$search}%")
                    ->orWhereHas('department', fn($dq) => $dq->where('name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('office', fn($oq) => $oq->where('ddo_name', 'LIKE', "%{$search}%"));
                // If you want to search inside inf_number JSON, add:
                // ->orWhere('inf_number', 'LIKE', '%' . $search . '%');
            });
        }

        // 2. INF number (stored as JSON array)
        if (!empty($filters['inf_number'])) {
            $inf = $filters['inf_number'];
            $query->whereJsonContains('inf_number', $inf);
            // Or if stored as simple comma-separated string:
            // $query->where('inf_number', 'LIKE', "%{$inf}%");
        }

        // 3. Department
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        // 4. Office
        if (!empty($filters['office_id'])) {
            $query->where('office_id', $filters['office_id']);
        }

        // 5. Cheque date range (maps to 'publication_date' in the Blade)

        // 6. Submission date (created_at)
        if (!empty($filters['cheque_date'])) {
            $dates = explode(' to ', $filters['cheque_date']);
            if (count($dates) == 2) {
                $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $to   = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
                $query->whereBetween('cheque_date', [$from, $to]);
            }
        }

        // 7. Exact cheque number (if needed)
        if (!empty($filters['cheque_number'])) {
            $query->where('cheque_no', $filters['cheque_number']);
        }
        if (!empty($filters['challan_number'])) {
            $query->where('challan_no', $filters['challan_number']);
        }

        return $query;
    }
}
