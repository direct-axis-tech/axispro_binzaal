<?php
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/prefs/sysprefs.inc");
include_once($path_to_root . "/includes/db/connect_db.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/helpers.php");
include_once($path_to_root.'/BarcodeGenerator/BarcodeGenerator.php');
include_once($path_to_root.'/BarcodeGenerator/BarcodeGeneratorPNG.php');

/**
 * 0 => ST_JOURNAL
 * 1 => ST_BANKPAYMENT
 * 2 => ST_BANKDEPOSIT
 */
if (empty($_GET['voucher_id']) || !preg_match('/^[1-9][0-9]*-(0|1|2)*$/', $_GET['voucher_id'])) {
    http_response_code(404);
    die("The requested resource could not be found.");
}

$array = explode("-", $_GET['voucher_id']);
$voucher_id = $array[0];
$type = $array[1];

if ($type != -1) {
    ob_start();
    include('content.php');
    $content = ob_get_clean();

    try {
        $mpdf = app(\Mpdf\Mpdf::class);
        $mpdf->WriteHTML($content);
        $mpdf->Output("invoice-".$myrow['reference'].".pdf", \Mpdf\Output\Destination::INLINE);
    }
    catch (ErrorException $e) {
        die("Error occurred while preparing PDF");
    }
} else {
    $getData = function($voucher_id, $type) {
        $types = [
            ST_BANKPAYMENT => 'PV',
            ST_BANKDEPOSIT => 'RV'
        ];

        $voucher = db_query(
            "SELECT
                bt.`type`,
                bt.dimension_id,
                bt.ref ref,
                bt.trans_date trans_date,
                bt.amount amount,
                bt.person_id person_id,
                bt.person_type_id person_type_id,
                c.`memo_` `memo_`,
                IF(u.real_name = ' ', u.user_id, u.real_name) entered_by
            FROM 0_bank_trans bt
                LEFT JOIN 0_comments c ON bt.type = c.type AND bt.trans_no = c.id
                LEFT JOIN 0_users u ON bt.created_by = u.id
            WHERE bt.trans_no = $voucher_id AND bt.type = $type"
        )->fetch_assoc();
        if (!$voucher) {
            http_response_code(404);
            die("The requested resource could not be found.");
        }

        switch($voucher['person_type_id']) {
            case PT_CUSTOMER:
                $voucher['_entity'] = get_customer_name($voucher['person_id']);
                break;
            case PT_SUPPLIER:
                $voucher['_entity'] = get_supplier_name($voucher['person_id']);
                break;
            default:
                $voucher['_entity'] = $voucher['person_id'];
        }

        $voucher['trans_date'] = DateTime::createFromFormat(DB_DATE_FORMAT, $voucher['trans_date'])->format(getDateFormatInNativeFormat());
        $abs_amt = abs($voucher['amount']);
        // $voucher['_amount_in_words'] = getPriceInWords($abs_amt);
        $voucher['amount'] = price_format($abs_amt);

        switch($type) {
            case ST_BANKPAYMENT:
                $title       = 'Payment Voucher';
                $ref_label   = 'PV No.';
                $label       = 'Payed to';
                $label_in_ar = 'المدفوعة لل';
                $recipient   = $voucher['_entity'];
                break;
            case ST_BANKDEPOSIT: 
                $title       = 'Receipt Voucher';
                $ref_label   = 'RV No.';
                $label       = 'Received from';
                $label_in_ar = 'وردت من';
                $recipient   = $voucher['entered_by'];
        }

        return compact(
            'title',
            'ref_label',
            'label',
            'label_in_ar',
            'recipient',
            'voucher'
        );
    };

    $getHTML = function(array $__GLOBALS__) {
        extract($__GLOBALS__);
        ob_start();
        include __DIR__ . '/voucher_content.php';
        return ob_get_clean();
    };

    $data = $getData($voucher_id, $type);
    $html = $getHTML($data);

    try {
        $mpdf = app(\Mpdf\Mpdf::class);
        $mpdf->WriteHTML($html);
        $mpdf->SetTitle("{$data['title']} - AxisPro");
        $mpdf->Output("voucher.pdf", \Mpdf\Output\Destination::INLINE);
    }
    catch (Exception $e) {
        // die("Error occurred while preparing PDF");
        throw $e;
    }
}
