<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$path_to_root = "..";
$page_security = 'SA_PRINTSALESINV';

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/prefs/sysprefs.inc");
include_once($path_to_root . "/includes/db/connect_db.inc");
include_once($path_to_root.'/BarcodeGenerator/BarcodeGenerator.php');
include_once($path_to_root.'/BarcodeGenerator/BarcodeGeneratorPNG.php');
include_once($path_to_root . "/invoice_print/content.php");

if (!user_check_access($page_security)) {
    display_error('The security settings on the system does not allow you to access this function');
    exit();
}

ob_start();
include('content.php');
$content = ob_get_clean();

if ($GLOBALS['dimension']['pos_type'] == POS_CAFETERIA) {
    echo $content;
    exit();
}

try {
    $mpdf = app(\Mpdf\Mpdf::class);
    $mpdf->SetTitle('Invoice print');
    $mpdf->showWatermarkText = true;
    $mpdf->WriteHTML($content);

    // This is getting initialised inside content.php
    $trans = $GLOBALS['trans'];

    if (!in_ajax()) {
        $ref = strtr($trans['reference'], '/', '');
        $fileName = "invoice-{$ref}.pdf";

        $mpdf->Output($fileName, \Mpdf\Output\Destination::INLINE);
        exit();
    }

    $ref = strtr($trans['reference'], '/', '');
    $fileName = "invoice-{$ref}.pdf";
    $dir =  company_path(). '/pdf_files';
    if (!file_exists($dir)) {
        mkdir ($dir,0777);
    }
    $filePath = $dir.'/'.$fileName.'.pdf';

    $mpdf->Output($filePath, \Mpdf\Output\Destination::FILE);

    if (in_ajax()) {
        global $Ajax;

        // when embeded pdf viewer used otherwise use faster method
        if (user_rep_popup())  {
            $Ajax->popup($filePath);
        } else {
            $Ajax->redirect($filePath);
        }
    }
}
catch (Exception $e) {
    return display_error("Error occurred while preparing PDF");
}