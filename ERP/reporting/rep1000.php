<?php
/**********************************************************************
 * Direct Axis Technology L.L.C.
 * Released under the terms of the GNU General Public License, GPL,
 * as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/

use App\Models\Accounting\Dimension;
use Illuminate\Support\Str;

// ----------------------------------------------------------------
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");

$canAccess = [
    "OWN" => user_check_access('SA_CSHCOLLECTREP'),
    "DEP" => user_check_access('SA_CSHCOLLECTREPDEP'),
    "ALL" => user_check_access('SA_CSHCOLLECTREPALL')
];

$page_security = in_array(true, $canAccess, true) ? 'SA_ALLOW' : 'SA_DENIED';
//------------------------------------------------------------------

print_report($canAccess);

function get_invoice_payment_inquiry(
    $from,
    $to,
    $customer_id,
    $bank,
    $user_id,
    $pay_method,
    $cost_center,
    $user_cost_center,
    $canAccess,
    $customer_type,
    $payment_invoice_date_relationship
) {
    $from = date2sql($from);
    $to = date2sql($to);

    $data_after = $from;
    $date_to = $to;

    if(!$canAccess['ALL']) {
        $user_cost_center = $_SESSION['wa_current_user']->default_cost_center;

        if(!$canAccess['DEP']) {
            $user_id = $_SESSION['wa_current_user']->loginname;
        }
    }

    $sql = get_sql_for_invoice_payment_inquiry(
        $customer_id,
        $user_id,
        $data_after,
        $date_to,
        $bank,
        $pay_method,
        $cost_center,
        $user_cost_center,
        true,
        null,
        $customer_type,
        $payment_invoice_date_relationship
    );

    return db_query($sql);
}

function format_stamp($date_time) {
    $transacted_at = DateTime::createFromFormat(DB_DATETIME_FORMAT, $date_time);
    return $transacted_at->format('d-m h:i A');
}

function trim_name($name) {
    // If Excel no need to trim
    if (!empty($_POST['PARAM_5'])) {
        return $name;
    }

    return Str::limit($name, '20');
}

function trim_user($name) {
    // If Excel no need to trim
    if (!empty($_POST['PARAM_5'])) {
        return $name;
    }

    return Str::limit($name, '10');
}

function trim_invoices($name) {
    // If Excel no need to trim
    if (!empty($_POST['PARAM_5'])) {
        return $name;
    }

    return Str::limit($name, '13');
}

//----------------------------------------------------------------------------------------------------

function print_report($canAccess)
{
    global $path_to_root, $systypes_array;

    $from = $_POST['PARAM_0'];
    $to = $_POST['PARAM_1'];
    $summaryOnly = $_POST['PARAM_2'];
    $comments = $_POST['PARAM_3'];
    $orientation = $_POST['PARAM_4'];
    $destination = $_POST['PARAM_5'];
    $customer_id = $_POST['PARAM_6'];
    $bank = $_POST['PARAM_7'];
    $user_id = $_POST['PARAM_8'];
    $pay_method = $_POST['PARAM_9'];
    $cost_center = $_POST['PARAM_10'];
    $user_cost_center = $_POST['PARAM_11'];
    $customer_type = $_POST['PARAM_12'];
    $payment_invoice_date_relationship = $_POST['PARAM_13'];

    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");

    $orientation = "L";
    $page = 'A4';
    $margins = [
        'top' => 30,
        'bottom' => 30,
        'left' => 20,
        'right' => 20,
    ];
    $dec = user_price_dec();

    $rep = new FrontReport(
        trans('Payment Collection Report'),
        "PaymentCollectionReport",
        $page,
        9,
        $orientation,
        $margins
    );
    $summary = trans('Detailed Report');

    if($customer_id == ALL_TEXT) {
        $cust = trans("All");
    }
    else {
        $cust = get_customer_name($customer_id);
    }
    if($bank == ALL_TEXT) {
        $bank_name = trans("All");
    }
    else {
        $bank_info=get_bank_account($bank);
        $bank_name = $bank_info["bank_account_name"];
    }
    if($user_id == ALL_TEXT) {
        $user = trans("All");
    }
    else {
        $user_info = get_user_by_login($user_id);
        $user = $user_info["user_id"];
    }

    $params = array(0 => $comments,
        1 => array('text' => trans('Period'), 'from' => $from, 'to' => $to),
        2 => array('text' => trans('Type'), 'from' => $summary, 'to' => ''),
        3 => array('text' => trans('Customer'), 'from' => $cust, 'to' => ''),
        4 => array('text' => trans('Bank'), 'from' => $bank_name, 'to' => ''),
        5 => array('text' => trans('User'), 'from' => $user, 'to' => '')
    );

    $columns = [];

    if (Dimension::count() > 1) {
        $columns[] = [
            "key" => 'user_dimension',
            "title" => trans('User Dep'),
            "width" => 32,
            "align" => 'left',
            "type" => 'TextCol',
            "additionalParam" => [-2]
        ];
    }

    $columns[] = [
        "key" => 'date_alloc',
        "title" => trans('Date'),
        "width" => 26,
        "align" => 'left',
        "type" => 'DateCol',
        "additionalParam" => [true, -2]
    ];
    $columns[] = [
        "key" => 'transacted_at',
        "title" => trans('Stamp'),
        "width" => 27,
        "align" => 'left',
        "type" => 'TextCol',
        "preProcess" => 'format_stamp',
        "additionalParam" => [-2]
    ];
    $columns[] = [
        "key" => 'payment_ref',
        "title" => trans('ReceiptNo'),
        "width" => 22,
        "align" => 'left',
        "type" => 'TextCol',
        "additionalParam" => [-2]
    ];
    $columns[] = [
        "key" => 'invoice_numbers',
        "title" => trans('Invoices'),
        "width" => 22,
        "align" => 'left',
        "type" => 'TextCol',
        "preProcess" => 'trim_invoices',
        "additionalParam" => [-2]
    ];
    // $columns[] = [
    //     "key" => 'bank_account_name',
    //     "title" => trans('Bank'),
    //     "width" => 60,
    //     "align" => 'left',
    //     "type" => 'TextCol',
    //     "additionalParam" => [-2]
    // ];
    // $columns[] = [
    //     "key" => 'customer',
    //     "title" => trans('Customer'),
    //     "width" => 42,
    //     "align" => 'left',
    //     "type" => 'TextCol',
    //     "preProcess" => 'trim_name',
    //     "additionalParam" => [-2]
    // ];
    $columns[] = [
        "key" => 'user_id',
        "title" => trans('User'),
        "width" => 16,
        "align" => 'left',
        "type" => 'TextCol',
        "preProcess" => 'trim_user',
        "additionalParam" => [-2]
    ];
    $columns[] = [
        "key" => 'payment_method',
        "title" => trans('Pay. Method'),
        "width" => 20,
        "align" => 'left',
        "type" => 'TextCol',
        "additionalParam" => [-2]
    ];

    if (pref('axispro.req_auth_code_4_cc_pmt', 0)) {
        $columns[] = [
            "key" => 'auth_code',
            "title" => trans('Auth Code.'),
            "width" => 28,
            "align" => 'center',
            "type" => 'TextCol',
            "additionalParam" => [-2]
        ];
    }

    $columns[] = [
        "key" => 'gross_payment',
        "title" => trans('Gross'),
        "width" => 18,
        "align" => 'right',
        "type" => 'AmountCol',
        "additionalParam" => [$dec, -2]
    ];
    $columns[] = [
        "key" => 'reward_amount',
        "title" => trans('Disc'),
        "width" => 12,
        "align" => 'right',
        "type" => 'AmountCol',
        "additionalParam" => [$dec, -2]
    ];
    $columns[] = [
        "key" => 'credit_card_charge',
        "title" => trans('Chg'),
        "width" => 12,
        "align" => 'right',
        "type" => 'AmountCol',
        "additionalParam" => [$dec, -2]
    ];
    $columns[] = [
        "key" => 'round_of_amount',
        "title" => trans('RndOf'),
        "width" => 12,
        "align" => 'right',
        "type" => 'AmountCol',
        "additionalParam" => [$dec, -2]
    ];
    $columns[] = [
        "key" => 'commission_amount',
        "title" => trans('Comm'),
        "width" => 12,
        "align" => 'right',
        "type" => 'AmountCol',
        "additionalParam" => [$dec, -2]
    ];
    $columns[] = [
        "key" => 'net_payment',
        "title" => trans('Tot'),
        "width" => 20,
        "align" => 'right',
        "type" => 'AmountCol',
        "additionalParam" => [$dec]
    ];

    $colInfo = new ColumnInfo(
        $columns,
        $page,
        $orientation,
        $margins
    );

    $rep->Font();
    $rep->Info(
        $params,
        $colInfo->cols(),
        $colInfo->headers(),
        $colInfo->aligns(),
    );

    $rep->NewPage();
    $transactions = get_invoice_payment_inquiry(
        $from,
        $to,
        $customer_id,
        $bank,
        $user_id,
        $pay_method,
        $cost_center,
        $user_cost_center,
        $canAccess,
        $customer_type,
        $payment_invoice_date_relationship
    );

    $totals = [
        'gross_payment' => 0,
        'reward_amount' => 0,
        'credit_card_charge' => 0,
        'round_of_amount' => 0,
        'net_payment' => 0
    ];
    $groupTotal = [];

    while ($trans = db_fetch($transactions)) {
        foreach ($totals as $key => $_) { $totals[$key] += $trans[$key]; }

        $payment_method = $trans['payment_method'];
        if (!isset($groupTotal[$payment_method])) {
            $groupTotal[$payment_method] = 0;
        }
        $groupTotal[$payment_method] += $trans['net_payment'];

        foreach ($columns as $col) {
            $_key = $col['key'];
            $_value = $trans[$_key] ?? '';
            if (isset($col['preProcess'])) {
                $_value = $col['preProcess']($_value);
            }
            $_type = $col['type'];
            
            isset($col['additionalParam'])
                ? $rep->$_type(
                    $colInfo->x1($_key),
                    $colInfo->x2($_key),
                    $_value,
                    ...$col['additionalParam']
                ) : $rep->$_type(
                    $colInfo->x1($_key),
                    $colInfo->x2($_key),
                    $_value
                );
        }

        $rep->NewLine();
        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }

    $rep->Font('bold');
    $rep->NewLine();
    $rep->Line($rep->row + $rep->lineHeight);
    $rep->TextCol(
        $colInfo->x1('user_dimension'),
        $colInfo->x2('date_alloc'),
        trans("Total")
    );
    foreach ($totals as $key => $total) {
        $rep->AmountCol(
            $colInfo->x1($key),
            $colInfo->x2($key),
            $total,
            $dec
        );
    }
    $rep->Line($rep->row - 5);
    $rep->Font();

    $rep->NewLine(2);
    
    $groupTotal = array_merge(
        [
            'Cash' => 0,
            'CreditCard' => 0,
            'BankTransfer' => 0,
            'OnlinePayment' => 0
        ],
        $groupTotal
    );
    foreach ($groupTotal as $payment_method => $total) {
        $rep->TextCol(
            $colInfo->x1('user_dimension'),
            $colInfo->x2('date_alloc'),
            trans("Total {$payment_method}")
        );
        $rep->AmountCol(
            $colInfo->x1('payment_ref'),
            $colInfo->x2('payment_ref'),
            $total,
            $dec
        );
        $rep->NewLine();
    }

    hook_tax_report_done();

    $rep->End();
}


