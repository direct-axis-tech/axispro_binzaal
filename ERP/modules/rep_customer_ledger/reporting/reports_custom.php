<?php

global $reports;


$reports->addReport(RC_CUSTOMER, "_customer_ledger", trans('Customer &Ledger'),
    array(	trans('Start Date') => 'DATEBEGIN',
        trans('End Date') => 'DATEENDM',
        trans('Customer') => 'CUSTOMERS_NO_FILTER',
        trans('Show Pending') => 'YES_NO',
        trans('Show Trans') => 'YES_NO',
//			trans('Sales Areas') => 'AREAS',
        trans('Sales Man') => 'SALESMEN',
//			trans('Show Balance') => 'YES_NO',
//			trans('Currency Filter') => 'CURRENCY',
//			trans('Suppress Zeros') => 'YES_NO',

//			trans('Only Recovery') => 'YES_NO',
//			trans('Comments') => 'TEXTBOX',
        trans('Orientation') => 'ORIENTATION',
        trans('Destination') => 'DESTINATION',

        			trans('Show Balance') => 'YES_NO',

    ));

?>
