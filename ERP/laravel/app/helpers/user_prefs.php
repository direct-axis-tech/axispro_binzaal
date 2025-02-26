<?php

function user_pos()
{
	return authUser()->pos;
}

function user_language()
{
	return authUser()->language;
}

function user_qty_dec()
{
	return authUser()->qty_dec;
}

function user_price_dec()
{
	return authUser()->prices_dec ?? 2;
}

function user_exrate_dec()
{
	return authUser()->rates_dec ?? 4;
}

function user_percent_dec()
{
	return authUser()->percent_dec ?? 1;
}

function user_show_gl_info()
{
	return authUser()->show_gl;
}

function user_show_codes()
{
	return authUser()->show_codes;
}

function user_date_format()
{
	return pref('date.format');
}

function user_date_sep()
{
	return pref('date.separator');
}

function user_date_display()
{
    return getDateFormatInNativeFormat(user_date_format(), user_date_sep());
}

function user_tho_sep()
{
	return 0;
}

function user_dec_sep()
{
	return 0;
}

function user_theme()
{
	return authUser()->theme ?? 'default';
}

function user_pagesize()
{
	return authUser()->page_size ?? 'A4';
}

function user_hints()
{
	return authUser()->show_hints;
}

function user_print_profile()
{
	return authUser()->print_profile;
}

function user_rep_popup()
{
	return authUser()->rep_popup;
}

function user_query_size()
{
	return authUser()->query_size;
}

function user_graphic_links()
{
	return authUser()->graphic_links;
}

function sticky_doc_date()
{
	return authUser()->sticky_doc_date ?? 0;
}

function user_startup_tab()
{
	return authUser()->startup_tab ?? 'orders';
}

function user_transaction_days()
{
    return authUser()->transaction_days;
}

function user_save_report_selections()
{
    return authUser()->save_report_selections;
}

function user_use_date_picker()
{
    return authUser()->use_date_picker;
}

function user_def_print_destination()
{
    return authUser()->def_print_destination;
}

function user_def_print_orientation()
{
    return authUser()->def_print_orientation;
}

function user_numeric($input) {
    $num = trim($input);
    $sep = pref('number.thousand_separators')[user_tho_sep()];
    if ($sep!='')
    	$num = str_replace( $sep, '', $num);

    $sep = pref('number.decimal_separators')[user_dec_sep()];
    if ($sep!='.')
    	$num = str_replace( $sep, '.', $num);

    if (!is_numeric($num))
	  	return false;
    $num = (float)$num;
    if ($num == (int)$num)
	  	return (int)$num;
    else
	  	return $num;
}
