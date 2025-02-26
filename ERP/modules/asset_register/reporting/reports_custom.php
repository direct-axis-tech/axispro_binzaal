<?php

global $reports, $dim;

$reports->addReport(RC_INVENTORY, "_assets_list", trans('Assets List'),
       array( trans('Comments') => 'TEXTBOX', trans('Destination') => 'DESTINATION'));
?>
