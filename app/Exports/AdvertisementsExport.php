<?php

// namespace App\Exports;

// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\WithMapping;

// class AdvertisementsExport implements FromCollection, WithHeadings, WithMapping
// {
//     protected $advertisements;

//     public function __construct($advertisements)
//     {
//         $this->advertisements = $advertisements;
//     }

//     public function collection()
//     {
//         return $this->advertisements;
//     }

//     public function headings(): array
//     {
//         $user = auth()->user();
//         $headings = ['S.No'];

//         if ($user->hasRole(['Superintendent', 'Diary Dispatch', 'Super Admin', 'Deputy Director', 'Director General', 'Secretary'])) {
//             $headings[] = 'INF No.';
//         }

//         $headings[] = 'Department / Office';
//         $headings[] = 'Submission Date';
//         $headings[] = 'Publication Date';
//         $headings[] = 'Status';

//         return $headings;
//     }

//     public function map($advertisement): array
//     {
//         static $rowNumber = 0;
//         $rowNumber++;

//         $user = auth()->user();
//         $row = [$rowNumber];

//         if ($user->hasRole(['Superintendent', 'Diary Dispatch', 'Super Admin', 'Deputy Director', 'Director General', 'Secretary'])) {
//             $row[] = $advertisement->inf_number;
//         }

//         $deptOffice = $advertisement->office->ddo_name ?? $advertisement->department->name ?? '-';
//         $row[] = $deptOffice;
//         $row[] = optional($advertisement->created_at)->toFormattedDateString();
//         $row[] = optional($advertisement->publish_on_or_before)->toFormattedDateString();
//         $row[] = $advertisement->status->title ?? '-';

//         return $row;
//     }
// }


namespace App\Exports;

use App\Models\Advertisement;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AdvertisementsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /** @var \Illuminate\Support\Collection<int, \App\Models\Advertisement> */
    protected Collection $advertisements;

    /** @var int|null */
    protected $statusId = null;
    /** @var string|null */
    protected $search = null;
    /** @var mixed */
    protected $from = null;
    /** @var mixed */
    protected $to = null;

    public function __construct($statusId, $search = null, $from = null, $to = null)
    {
        // Backwards-compatible:
        // - Newer controllers pass a ready collection of ads
        // - ReportsController passes (statusId, search, from, to)
        if ($statusId instanceof Collection) {
            $this->advertisements = $statusId;
            return;
        }

        // Also accept plain arrays/iterables
        if (is_array($statusId)) {
            $this->advertisements = collect($statusId);
            return;
        }

        $this->statusId = is_numeric($statusId) ? (int) $statusId : null;
        $this->search = $search;
        $this->from = $from;
        $this->to = $to;

        $this->advertisements = collect();
    }

    public function collection()
    {
        // If a ready dataset was provided, export that exactly.
        if ($this->advertisements->isNotEmpty()) {
            return $this->advertisements;
        }

        // Otherwise build a query from provided filters (used by reports).
        $query = Advertisement::query();

        if ($this->statusId !== null) {
            if ($this->statusId === 8) {
                // Published: exists in pivot table
                $query->whereHas('newspapers', function ($q) {
                    $q->whereNotNull('advertisement_newspaper.id');
                });
            } else {
                $query->where('status_id', $this->statusId);
            }
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('client_name', 'like', "%{$this->search}%");
            });
        }

        if ($this->from && $this->to) {
            $query->whereBetween('created_at', [$this->from, $this->to]);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'S. No.',
            'INF Number',
            'Memo Number',
            'Memo Date',
            'urdu_space',
            'urdu_size',
            'english_space',
            'english_size',
            'Title',
            'Client Name',
            'Office/Department',
            'Status',
            'Created At',
        ];
    }

    public function map($ad): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        // Determine office/department display
        $officeDept = '-';
        if ($ad->office_id) {
            $officeDept = $ad->office->ddo_name ?? '-';
        } elseif ($ad->department_id) {
            $officeDept = $ad->department->name ?? '-';
        }

        // Status mapping
        $statusMap = [
            3  => 'New',
            4  => 'In progress',
            10 => 'Approved',
            8  => 'Published',
            7  => 'Rejected',
        ];
        $status = $statusMap[$ad->status_id] ?? 'Unknown';

        return [
            $rowNumber,
            $ad->inf_number,
            $ad->memo_number,
            $ad->memo_date,
            $ad->urdu_space,
            $ad->urdu_size,
            $ad->english_space,
            $ad->english_size,
            $ad->title,
            $ad->client_name,
            $officeDept,
            $status,
            $ad->created_at->format('d M Y'),
        ];
    }
}
