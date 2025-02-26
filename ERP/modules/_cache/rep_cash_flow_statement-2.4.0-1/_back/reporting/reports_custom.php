<?php

global $reports;

$reports->addReport(RC_BANKING,"_cash_flow_statement",trans('Cash Flow Statement'),
       array(  trans('Report Period') => 'DATEENDM',
                       trans('Dimension') => 'DIMENSIONS1',
                       trans('Comments') => 'TEXTBOX',
                       trans('Destination') => 'DESTINATION'));
?>
