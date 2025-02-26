<?php

/**
 * Format the price into its English words
 *
 * @param float $price
 * @return string The words representing the given price
 */
function getPriceInWords($price){
    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);

    $int = (int)$price;

    if ($int == 0) {
        $result = 'Zero Dirham';
    } else {
        $result = ucwords($f->format($int)) . ' Dirham';
    }

    $frac = substr(str_replace($int, '', $price), 1, 2);
    if ($frac) {
        $result .= ' and ' . ucwords($f->format($frac)) . ' Fils';
    }

    return $result;
}