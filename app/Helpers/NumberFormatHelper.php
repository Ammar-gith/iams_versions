<?php


if (!function_exists('numberToWords')) {
    function numberToWords($number)
    {
        $fmt = new \NumberFormatter("en", NumberFormatter::SPELLOUT);
        return ucfirst($fmt->format($number)) . ' only';
    }
}
