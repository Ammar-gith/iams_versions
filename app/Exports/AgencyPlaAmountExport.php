<?php

namespace App\Exports;

use App\Models\PlaAccountItem;
use Maatwebsite\Excel\Concerns\FromCollection;

class AgencyPlaAmountExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return PlaAccountItem::with(['agency', 'newspaper'])->get();
    }
}
