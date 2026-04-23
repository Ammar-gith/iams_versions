<?php

use Illuminate\Support\Facades\DB;
use App\Models\ChallanSeries;


if (!function_exists('generate_diary_number')) {
    function generate_diary_number()
    {
        $diaryNumber = NUll;
        $ChallanSeriesId = Null;

        DB::transaction(function () use (&$diaryNumber, &$ChallanSeriesId) {
            $currentYear = now()->format('y');

            $ChallanSeries = ChallanSeries::where('series', 'like', '%-' . $currentYear)->lockForUpdate()->first();

            if (!$ChallanSeries) {
                $ChallanSeries = ChallanSeries::create([
                    'series' => sprintf('%003d-%s', 1, $currentYear),
                    'start_no' => 1,
                    'issued_no' => 0,
                ]);
            }

            $nextNumber = $ChallanSeries->issued_no + 1;
            $diaryNumber = sprintf('%003d-%s', $nextNumber, $currentYear);
            $ChallanSeriesId = $ChallanSeries->id;

            $ChallanSeries->update(['issued_no' => $nextNumber]);
        });

        return [
            'diary_number' => $diaryNumber,
            'challan_series_id' => $ChallanSeriesId,
        ];
    }
}

// Show challan number in create form
if (!function_exists('get_next_diary_number_preview')) {
    function get_next_diary_number_preview()
    {
        $currentYear = now()->format('y');

        $ChallanSeries = ChallanSeries::where('series', 'like', '%-' . $currentYear)->first();

        if (!$ChallanSeries) {
            $nextNumber = 1;
        } else {
            $nextNumber = $ChallanSeries->issued_no + 1;
        }

        return sprintf('%003d-%s', $nextNumber, $currentYear);
    }
}

// Generate / reuse the current active batch_no.
//
// Rules:
//   1. No challans exist yet                          → start "Apr-2026-1"
//   2. Latest batch for CURRENT month, NOT yet paid   → reuse it
//   3. Latest batch for CURRENT month, already paid   → increment sequence
//   4. No batch exists for current month yet          → start at sequence 1
//      (even if old months have unpaid batches — each month gets its own series)
if (!function_exists('generate_batch_no')) {
    function generate_batch_no(): string
    {
        $currentPrefix = now()->format('M-Y'); // e.g. "Apr-2026"

        // Find the latest batch_no for the CURRENT month only
        $latestThisMonth = \App\Models\TreasuryChallan::whereNotNull('batch_no')
            ->where('batch_no', 'like', $currentPrefix . '-%')
            ->latest('id')
            ->value('batch_no');

        // ── No batch for this month yet → start at 1 ───────────────────────
        if (!$latestThisMonth) {
            return $currentPrefix . '-1';
        }

        // ── Current month batch exists, check if it's paid ─────────────────
        $alreadyPaid = \App\Models\PaidAmount::where('batch_no', $latestThisMonth)->exists();

        if (!$alreadyPaid) {
            return $latestThisMonth;   // still open — reuse it
        }

        // ── Current month batch is paid → increment sequence ────────────────
        $parts    = explode('-', $latestThisMonth);
        $sequence = (int) array_pop($parts);
        $newSeq   = $sequence + 1;
        $candidate = $currentPrefix . '-' . $newSeq;

        // Safety guard against duplicates
        while (\App\Models\TreasuryChallan::where('batch_no', $candidate)->exists()) {
            $newSeq++;
            $candidate = $currentPrefix . '-' . $newSeq;
        }

        return $candidate;
    }
}
