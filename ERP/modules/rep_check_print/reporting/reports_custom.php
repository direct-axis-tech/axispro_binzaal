<?php

global $reports, $dim;

$reports->addReport(RC_SUPPLIER, "_check_print",trans('Printable &Check'),
    array(  trans('Payment') => 'REMITTANCE',
            trans('Destination') => 'DESTINATION'));
?>
