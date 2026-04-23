<?php

namespace App\Exports;

use App\Models\TreasuryChallan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TreasuryChallanExport implements FromCollection
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        $user = auth()->user();
        $headings = ['S.No'];

        if ($user->hasRole(['Superintendent', 'Diary Dispatch', 'Super Admin', 'Deputy Director', 'Director General', 'Secretary'])) {
            $headings[] = 'INF No.';
        }

        $headings[] = 'Memo No';
        $headings[] = 'INF No';
        $headings[] = 'Department / Office';
        $headings[] = 'Cheque Number';
        $headings[] = 'Cheque Date';
        $headings[] = 'Cheque Amount';
        $headings[] = 'Bank Verify Date';
        $headings[] = 'Status';

        return $headings;
    }


    public function map($data): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        $user = auth()->user();
        $row = [$rowNumber];

        if ($user->hasRole(['Superintendent', 'Diary Dispatch', 'Super Admin', 'Deputy Director', 'Director General', 'Secretary'])) {
            $row[] = $data->inf_number;
        }

        $deptOffice = $data->office->ddo_name ?? $data->department->name ?? '-';

        $row[] = $data->memo_number;
        $row[] = $data->inf_number;
        $row[] = $deptOffice;
        $row[] = $data->cheque_number;
        $row[] = optional($data->cheque_date)->toFormattedDateString();
        $row[] = $data->total_amount;
        $row[] = optional($data->sbp_verification_date)->toFormattedDateString();
        $row[] = $advertisement->status->title ?? '-';

        return $row;
    }
}
