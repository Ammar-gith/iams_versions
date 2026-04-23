<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ArrayExport implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return array_map(function ($row) {
            return [$row['label'], $row['count']];
        }, $this->data);
    }

    public function headings(): array
    {
        return ['Name', 'Total Advertisements'];
    }
}
