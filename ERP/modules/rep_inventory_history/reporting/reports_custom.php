<?php

global $reports, $dim;

$reports->addReport(RC_INVENTORY,"_inventory_history",trans('Inventory History'),
	array(	trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Inventory Category') => 'CATEGORIES',
			trans('Location') => 'LOCATIONS',
			trans('Summary Only') => 'YES_NO',
			trans('Comments') => 'TEXTBOX',
			trans('Destination') => 'DESTINATION'));
?>
