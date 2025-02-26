<?php

use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use PHPMailer\PHPMailer\Exception;

$path_to_root = "..";
$page_security = 'SA_PRINTSALESINV';

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/prefs/sysprefs.inc");
include_once($path_to_root . "/includes/db/connect_db.inc");
include_once($path_to_root.'/BarcodeGenerator/BarcodeGenerator.php');
include_once($path_to_root.'/BarcodeGenerator/BarcodeGeneratorPNG.php');
include_once __DIR__ . '/content.php';

if (!user_check_access($page_security)) {
    display_error('The security settings on the system does not allow you to access this function');
    exit();
}
if(!isset($_GET['PARAM_0'])) {
    return;
}

$is_emailing = $_GET['PARAM_3'] ?? false;
$is_watermarked = empty($_GET['PARAM_8']);
$trans_no = explode('-', $_GET['PARAM_0'])[0];

$content = '<body>'.get_contents($trans_no, $is_emailing, $is_watermarked).'</body>';

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

    $isEmailingOnly = $_GET['PARAM_3'] ?? false;
    $isTextingOnly = $_GET['PARAM_9'] ?? false;
    $shouldSendEmailAutomatically = shouldSendEmailAutomatically($trans);
    $shouldSendSMSAutomatically = shouldSendSMSAutomatically($trans);

    $shouldSendEmail = $isEmailingOnly || $shouldSendEmailAutomatically;
    $shouldSendSMS = $isTextingOnly || $shouldSendSMSAutomatically;

    if (!file_exists($dir =  join_paths(company_path(), 'pdf_files'))) {
        mkdir ($dir, 0777);
    }

    $readableFileName = clean_file_name("invoice_{$trans['reference']}").'.pdf';
    $filePath = join_paths($dir, random_id().'.pdf');

    $mpdf->Output($filePath, \Mpdf\Output\Destination::FILE);

    if ($shouldSendEmail || $shouldSendSMS) {
        $cloudPath = Storage::disk('s3')->putFile(config('filesystems.disks.s3.project_dir'), new File($filePath));
        $lifeTime = pref('axispro.invoice_link_lifetime', '0');
        $cloudUrl = $lifeTime
            ? Storage::disk('s3')->temporaryUrl($cloudPath, (new Carbon)->addMinutes($lifeTime))
            : Storage::disk('s3')->url($cloudPath);

        $shortUrl = generateShortUrl($cloudUrl);
        $context = [
            'customer_name' => $trans['display_customer'],
            'company_name' => pref('company.coy_name'),
            'type' => $trans['type'],
            'type_name' => 'invoice',
            'trans_no' => $trans['trans_no'],
            'reference' => $trans['reference'],
            'link' => $shortUrl,
            'total' => price_format($trans['Total'])
        ];

        if ($shouldSendEmail) {
            $result = sendBillingEmail($trans['customer_email'], $context, [$filePath => $readableFileName]);
    
            if ($isEmailingOnly) {
                return $result ? display_notification("Email sent successfully.") : display_error("Email didn't sent successfully."); 
            }
        }
    
        if ($shouldSendSMS) {
            $result = sendSMS($trans['customer_mobile'], $context);
    
            if ($isTextingOnly) {
                return $result ? display_notification("SMS sent successfully.") : display_error("SMS didn't sent successfully."); 
            }
        }
    }

    if (!in_ajax()) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="'.$readableFileName.'"');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        readfile($filePath);
        exit();
    }

    if (!$isEmailingOnly && !$isTextingOnly) {
        global $Ajax;

        // when embedded pdf viewer used otherwise use faster method
        user_rep_popup() ? $Ajax->popup($filePath) : $Ajax->redirect($filePath);
    }
}
catch (Exception $e) {
    return display_error("Error occurred while preparing PDF");
}
