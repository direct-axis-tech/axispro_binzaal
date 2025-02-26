<?php

/**
 * Returns the rounded value of the given number
 *
 * @param int|float $number
 * @param integer $decimals
 * @return float
 */
function round2($number, $decimals=0)
{
	$delta = ($number < 0 ? -.0000000001 : .0000000001);
	return round($number+$delta, $decimals);
}

/**
 * Returns the number formatted
 * 
 * Formats the number according to user setup and using $decimals digits after dot 
 * (defualt is 0). When $decimals is set to 'max' maximum available precision is used 
 * (decimals depend on value) and trailing zeros are trimmed.
 *
 * @param int|float $number
 * @param integer $decimals
 * @return string
 */
function number_format2($number, $decimals=0)
{
	$tsep = pref('number.thousand_separators')[user_tho_sep()];
	$dsep = pref('number.decimal_separators')[user_dec_sep()];

	if ($number == '')
		$number = 0;
	if($decimals==='max')
		$dec = 15 - floor(log10(abs($number)));
	else {
		$delta = ($number < 0 ? -.0000000001 : .0000000001);
		@$number += $delta;
		$dec = $decimals;
	}

	$num = number_format($number, intval($dec), $dsep, $tsep);

	return $decimals==='max' ? rtrim($num, '0') : $num;

}

/**
 * Compares the floating point values
 * 
 * price/float comparision helper to be used in any suspicious place for zero values?
 * 
 * usage: `if (!floatcmp($value1, $value2)) compare value is 0`
 *
 * @param int|float $a
 * @param int|float $b
 * @return -1|1|0 1 if a > b, -1 if a < b and 0 otherwise
 */
function floatcmp($a, $b)
{
    return $a - $b > FLOAT_COMP_DELTA ? 1 : ($b - $a > FLOAT_COMP_DELTA ? -1 : 0);
}

/**
 * Formats the price
 *
 * @param int|float $number
 * @return string
 */
function price_format($number)
{
    return number_format2($number, user_price_dec());
}

function price_decimal_format($number, &$dec)
{
	$dec = user_price_dec();
	$str = strval($number);
	$pos = strpos($str, '.');
	if ($pos !== false)
	{
		$len = strlen(substr($str, $pos + 1));
		if ($len > $dec && $len < ini_get('precision')-3)
			$dec = $len;
	}
	return number_format2($number, $dec);
}

if (!function_exists('money_format')) {
	function money_format($format, $number) 
	{
		return price_format($number);
	} 
}

/**
 * Maximum precision format. Strips trailing unsignificant digits.
 *
 * @param int|float $number
 * @return string
 */
function maxprec_format($number)
{
    return number_format2($number, 'max');
}

function exrate_format($number)
{
    return number_format2($number, user_exrate_dec());
}

function percent_format($number)
{
    return number_format2($number, user_percent_dec());
}