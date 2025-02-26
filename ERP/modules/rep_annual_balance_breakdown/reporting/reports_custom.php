<?php

global $reports, $dim;

$reports->addReport(RC_GL,"_annual_balance_breakdown",trans('Annual &Balance Breakdown - Detailed'),
       array(  trans('Report Period') => 'DATEENDM',
                       trans('Dimension')." 1" => 'DIMENSIONS1',
                       trans('Dimension')." 2" => 'DIMENSIONS2',
                       trans('Comments') => 'TEXTBOX',
                       trans('Destination') => 'DESTINATION'));
?>
