<?php

use Illuminate\Support\Facades\DB;
use App\Models\INFSeries;


if (!function_exists('generate_inf_number')) {
    function generate_inf_number()
    {
        $infNumber = NUll;
        $infSeriesId = Null;

        DB::transaction(function () use (&$infNumber, &$infSeriesId) {
            $currentYear = now()->format('y');

            $infSeries = INFSeries::where('series', 'like', '%/' . $currentYear)->lockForUpdate()->first();

            if (!$infSeries) {
                $infSeries = INFSeries::create([
                    'series' => sprintf('%02d/%s', 1, $currentYear),
                    'start_number' => 1,
                    'issued_numbers' => 0,
                ]);
            }

            $nextNumber = $infSeries->issued_numbers + 1;
            $infNumber = sprintf('%02d/%s', $nextNumber, $currentYear);
            $infSeriesId = $infSeries->id;

            $infSeries->update(['issued_numbers' => $nextNumber]);
        });

        return [
            'inf_number' => $infNumber,
            'inf_series_id' => $infSeriesId,
        ];
    }
}

// Show inf number in create form 
if (!function_exists('get_next_inf_number_preview')) {
    function get_next_inf_number_preview()
    {
        $currentYear = now()->format('y');

        $infSeries = INFSeries::where('series', 'like', '%/' . $currentYear)->first();

        if (!$infSeries) {
            $nextNumber = 1;
        } else {
            $nextNumber = $infSeries->issued_numbers + 1;
        }

        return sprintf('%02d/%s', $nextNumber, $currentYear);
    }
}
