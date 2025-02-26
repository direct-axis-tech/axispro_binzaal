<?php

global $reports;

$reports->addReport(RC_GL,"_annual_expense_breakdown",trans('Annual &Expense Breakdown - Detailed'),
       array(  trans('Report Period') => 'DATEENDM',
                       trans('Dimension')." 1" => 'DIMENSIONS1',
                       trans('Dimension')." 2" => 'DIMENSIONS2',
                       trans('Comments') => 'TEXTBOX',
                       trans('Destination') => 'DESTINATION'));
?>
