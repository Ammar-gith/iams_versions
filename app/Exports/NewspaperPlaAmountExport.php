<?php

namespace App\Exports;

use App\Models\PlaAccountItem;
use Maatwebsite\Excel\Concerns\FromCollection;

class NewspaperPlaAmountExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return PlaAccountItem::with('newspaper')->get();
    }
}
