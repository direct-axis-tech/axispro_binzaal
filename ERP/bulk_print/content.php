<?php

date_default_timezone_set('Asia/Dubai');

function get_invoice_range($from, $to)
{
    global $SysPrefs;
    $ref = ($SysPrefs->print_invoice_no() == 1 ? "trans_no" : "reference");
    $sql = "SELECT trans.trans_no, trans.reference,trans.payment_flag,dim.name as dim_name,trans.dimension_id    
		FROM " . TB_PREF . "debtor_trans trans 
			LEFT JOIN " . TB_PREF . "voided voided ON trans.type=voided.type AND trans.trans_no=voided.id 
			LEFT JOIN 0_dimensions dim ON dim.id=trans.dimension_id 
		WHERE trans.type=" . ST_SALESINVOICE
        . " AND ISNULL(voided.id)"
        . " AND trans.trans_no BETWEEN " . db_escape($from) . " AND " . db_escape($to)
        . " ORDER BY trans.tran_date, trans.$ref";

    return db_query($sql, "Cant retrieve invoice range");
}

if (isset($_GET['_']) && $_GET['_'] == 'bulk_invoice') {

    $from_date = $_GET['from_date'];
    $to_date = $_GET['to_date'];
    $customer = $_GET['customer'];
    $payment_status = $_GET['payment_status'];
    $from_date_ = db_escape(date2sql($from_date));
    $to_date_ = db_escape(date2sql($to_date));
    $range = get_print_bulk_invoices($from_date_, $to_date_, $customer, $payment_status);

} else {

        $from = $_GET['PARAM_0'];
        $to = $_GET['PARAM_1'];

        if (!$from || !$to) return;

        $fno = explode("-", $from);
        $tno = explode("-", $to);
        $from = min($fno[0], $tno[0]);
        $to = max($fno[0], $tno[0]);
        $range = get_invoice_range($from, $to);
}

$total_count = db_num_rows($range);
$count = 1;

while ($row = db_fetch($range)) {

    echo get_contents($row['trans_no']);

    if($count < $total_count)
        echo "<pagebreak />";

    $count++;

}

?>

</body>
</html>
