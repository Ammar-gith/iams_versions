<?php

use App\Models\Newspaper;
use App\Models\AdWorthParameter;
use App\Models\Status;

if (! function_exists('displayValue')) {
    function displayValue($field, $value)
    {
        if (!$value) return '0';

        // Handle all newspaper-related fields
        if (in_array($field, ['newspaper_id', 'suptd_NP_log', 'dd_NP_log', 'dg_NP_log', 'sec_NP_log'])) {
            $ids = is_array($value) ? $value : explode(',', $value);
            $ids = array_filter(array_map('intval', $ids)); // Remove empty values
            if (empty($ids)) return 'None';
            return Newspaper::whereIn('id', $ids)->pluck('title')->implode(', ');
        }

        if ($field === 'news_pos_rate_id') {
            return AdWorthParameter::find($value)?->range ?? $value;
        }

        return $value;
    }
}

if (! function_exists('fieldLabel')) {
    function fieldLabel($field)
    {
        $labels = [
            'newspaper_id'  => 'Newspapers',
            'suptd_NP_log'  => 'Newspapers (Superintendent)',
            'dd_NP_log'     => 'Newspapers (Deputy Director)',
            'dg_NP_log'    => 'Newspapers (Director General)',
            'sec_NP_log'   => 'Newspapers (Secretary)',
            'news_pos_rate_id'   => 'Position',
            'urdu_size'     => 'Urdu Size',
            'english_size'  => 'English Size',
            // Add more mappings if needed
        ];

        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
}

if (! function_exists('getStatusName')) {
    function getStatusName($statusTitle)
    {
        if (!$statusTitle) return 'N/A';

        // If it's already a status title, return it
        $status = Status::where('title', $statusTitle)->first();
        return $status ? $status->title : $statusTitle;
    }
}
