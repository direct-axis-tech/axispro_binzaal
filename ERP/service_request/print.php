<?php

$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");

check_page_security('SA_PRINTSERVICEREQ');

try {
    $contents = getContents();
    $mpdf = app(\Mpdf\Mpdf::class);
    $mpdf->WriteHTML($contents);
    $mpdf->SetTitle('Service Request - Axispro ERP');
    $mpdf->Output("service-request.pdf", \Mpdf\Output\Destination::INLINE);
}
catch (Exception $e) {
    // die("Error occurred while preparing PDF");
    throw $e;
}

/**
 * Returns the HTML content of the service request.
 *
 * @return string
 */
function getContents() {
    /** Validate id */
    if (empty($_GET['id']) || !preg_match('/^[1-9][0-9]*$/', $_GET['id'])){
        http_response_code(404);
        echo "The requested resource could not be found";
        exit;
    }
    $srv_req = db_query(
        "SELECT 
            srv_req.token_number,
            srv_req.reference,
            srv_req.created_at,
            srv_req.display_customer,
            srv_req.mobile,
            srv_req.cost_center_id as dimension_id,
            IF(user.real_name = ' ', user.user_id, user.real_name) employee,
            srv_req.barcode,
            dim.is_invoice_tax_included as tax_included
        FROM 
            0_service_requests srv_req
            LEFT JOIN 0_users user ON srv_req.created_by = user.id
            LEFT JOIN 0_dimensions dim ON dim.id=srv_req.cost_center_id
        WHERE srv_req.id = {$_GET['id']}"
    )->fetch_assoc();
    if (!$srv_req) {
        http_response_code(404);
        echo "The requested resource could not be found";
        exit;
    }

    $srv_req['created_at'] = DateTime::createFromFormat(DB_DATETIME_FORMAT, $srv_req['created_at'])->format(getDateFormatInNativeFormat() . ' h:i A');
    
    $srv_req['_items'] = [];
    $srv_req['_sub_total'] = 0.00;
    $srv_req['_discount_amt'] = 0.00;
    $srv_req['_tax_amt'] = 0.00;
    $dec = user_price_dec();
    $result = db_query(
        "SELECT item.* FROM 0_service_request_items item where item.req_id = {$_GET['id']}"
    );

    while($item = $result->fetch_assoc()){
        $refs = [];
        if ($item['transaction_id']) {
            $refs[] = $item['transaction_id'];
        }
        if ($item['application_id']) {
            $refs[] = $item['application_id'];
        }
        if ($item['ref_name']) {
            $refs[] = $item['ref_name'];
        }

        $_fee = $item['bank_service_charge'] + $item['govt_fee'];
        $item['price'] -= ($srv_req['tax_included'] ? $item['unit_tax'] : 0);
        $_total = $item['qty'] * ($_fee + $item['price'] + $item['unit_tax']);
        
        $item['_extra'] = implode('&nbsp;&nbsp;&nbsp;', $refs);
        $item['_fee']   = $_fee;
        $item['_total'] = round2($_total, $dec);

        $srv_req['_items'][] = $item;
        $srv_req['_tax_amt'] += round2($item['qty'] * $item['unit_tax'], $dec);
        $srv_req['_sub_total'] += round2($item['qty'] * ($_fee + $item['price']), $dec);
        $srv_req['_discount_amt'] += round2($item['qty'] * $item['discount'], $dec);
    }

    $srv_req['_net_amt'] = (
          $srv_req['_sub_total']
        - $srv_req['_discount_amt']
        + $srv_req['_tax_amt']
    );

    return html(compact('srv_req'));
}

/**
 * Generate the html in an isolated env
 *
 * @param array $__GLOBALS__ The array of variables which are globally available in the content.php
 * @return string
 */
function html($__GLOBALS__) {
    extract($__GLOBALS__);
    ob_start();
    include __DIR__ . '/content.php';
    return ob_get_clean();
}