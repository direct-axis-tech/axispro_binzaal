<?php

global $reports, $dim;
			
$reports->addReport(RC_INVENTORY,"_dated_stock",trans('Dated Stock Sheet'),
	array(	trans('Date') => 'DATE',
			trans('Inventory Category') => 'CATEGORIES',
			trans('Location') => 'LOCATIONS',
			trans('Comments') => 'TEXTBOX',
			trans('Destination') => 'DESTINATION'));				
?>
