<?php
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/prefs/sysprefs.inc");
include_once($path_to_root . "/includes/db/connect_db.inc");
include_once($path_to_root.'/BarcodeGenerator/BarcodeGenerator.php');
include_once($path_to_root.'/BarcodeGenerator/BarcodeGeneratorPNG.php');
include_once($path_to_root . "/purchasing/includes/po_class.inc");
ob_start();
include('content.php');
$content = ob_get_clean();


//echo htmlspecialchars($content); die;

$path = "";

try {
    $mpdf = app(\Mpdf\Mpdf::class);
    $mpdf->WriteHTML($content);
    $mpdf->Output("grn-".$myrow->reference.".pdf", \Mpdf\Output\Destination::INLINE);
}
catch (ErrorException $e) {
    die("Error occurred while preparing PDF");
}


