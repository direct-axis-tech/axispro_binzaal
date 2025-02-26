<?php

global $reports, $dim;
			
$reports->addReport(RC_INVENTORY,"_sales_summary",trans('&Sales Summary Report'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Inventory Category') => 'CATEGORIES',
			trans('Comments') => 'TEXTBOX',
			trans('Destination') => 'DESTINATION'));				
?>
