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

use App\Http\Controllers\Sales\Reports\CustomerBalanceInquiry;

$page_security = 'SA_CUSTANALYTIC';

$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();

page(trans($help_context = "Customer Balance Inquiry"), false, false, "", $js);

if (isset($_GET['customer_id'])) {
    $_POST['customer_id'] = $_GET['customer_id'];
}

//------------------------------------------------------------------------------------------------

if (!isset($_POST['customer_id']))
    $_POST['customer_id'] = null;

if (!isset($_POST['from'])) {
    $_POST['from'] = Today();
}

if (!isset($_POST['till'])) {
    $_POST['till'] = Today();
}

if (!isset($_POST['exclude_zero'])) {
    $_POST['exclude_zero'] = 1;
}

$from = get_post('from');
$till = get_post('till');
$customer_type = get_post('customer_type');

if (isset($_POST['EXPORT'])) {
    $report = (new CustomerBalanceInquiry())->getReport(
        date2sql($from),
        date2sql($till),
        get_post('customer_id'),
        check_value('exclude_zero'),
        false,
        '',
        $customer_type,
        get_post('sales_person_id')
    );

    $_POST['REP_ID'] = 1003;
    $export_to_excel = get_post('export_to', 0);
    $export_to_excel
        ? require_once $path_to_root . "/reporting/includes/excel_report.inc"
        : require_once $path_to_root . "/reporting/includes/pdf_report.inc";

    $comments = '';
    $orientation = "L";
    $page = 'A3';
    $dec = user_price_dec();

    $rep = new FrontReport(trans('Customer Balance Inquiry'), "Customer_Balace_Inquiry", $page, 9, $orientation);
    $summary = trans('Summary Report');

    $params = array(0 => $comments,
        1 => array('text' => trans('As of'), 'from' => $today, 'to' => ''),
        2 => array('text' => trans('Type'), 'from' => $summary, 'to' => '')
    );

    $columns = [
        [
            "key" => 'debtor_ref',
            "title" => trans('Cust Ref.'),
            "width" => 35,
            "align" => 'left',
            "type" => 'TextCol'
        ],
        [
            "key" => 'name',
            "title" => trans('Customer'),
            "width" => 70,
            "align" => 'left',
            "additionalParam" => [-3],
            "type" => 'TextCol'
        ],
        [
            "key" => 'salesman_name',
            "title" => trans('Salesman'),
            "width" => 70,
            "align" => 'left',
            "additionalParam" => [-3],
            "type" => 'TextCol'
        ],
        [
            "key" => 'last_inv_date',
            "title" => trans('Last Inv. Date'),
            "width" => 35,
            "align" => 'left',
            "additionalParam" => [true],
            "type" => 'DateCol'
        ],
        [
            "key" => 'last_pmt_date',
            "title" => trans('Last Pmt. Date'),
            "width" => 35,
            "align" => 'left',
            "additionalParam" => [true],
            "type" => 'DateCol'
        ],
        [
            "key" => 'opening_bal',
            "title" => trans('Opening Bal.'),
            "width" => 35,
            "align" => 'right',
            "additionalParam" => [$dec],
            "type" => 'AmountCol'
        ],
        [
            "key" => 'debit',
            "title" => trans('Dr.'),
            "width" => 35,
            "align" => 'right',
            "additionalParam" => [$dec],
            "type" => 'AmountCol'
        ],
        [
            "key" => 'credit',
            "title" => trans('Cr.'),
            "width" => 35,
            "align" => 'right',
            "additionalParam" => [$dec],
            "type" => 'AmountCol'
        ],
        [
            "key" => 'closing_bal',
            "title" => trans('Closing Bal.'),
            "width" => 35,
            "align" => 'right',
            "additionalParam" => [$dec],
            "type" => 'AmountCol'
        ]
    ];
    $colInfo = new ColumnInfo($columns, $page, $orientation);
    $colPoints = $colInfo->cols();

    $rep->Font();
    $rep->Info($params, $colPoints, $colInfo->headers(), $colInfo->aligns());
    $rep->NewPage();

    $collKeys = array_column($columns, 'key');

    // Print the report
    foreach ($report['data'] as $row) {
        foreach ($columns as $col) {
            $_key = $col['key'];
            $_data = isset($col['preProcess']) 
                ? $col['preProcess']($row->{$_key})
                : $row->{$_key};
            
            $_type = $col['type'];
            isset($col['additionalParam'])
                ? $rep->$_type(
                    $colInfo->x1($_key),
                    $colInfo->x2($_key),
                    $_data,
                    ...$col['additionalParam']
                ) : $rep->$_type(
                    $colInfo->x1($_key),
                    $colInfo->x2($_key),
                    $_data
                );
        }
        $rep->NewLine();
        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }

    // Print the total
    $rep->Font('bold');
    $rep->NewLine();
    $rep->Line($rep->row + $rep->lineHeight);
    $rep->TextCol($colInfo->x1('debtor_ref'), $colInfo->x2('last_pmt_date'), trans("Total"));
    foreach (array_slice($collKeys, 5) as $key) {
        $rep->AmountCol($colInfo->x1($key), $colInfo->x2($key), $report['total'][$key], $dec);
    }
    $rep->Line($rep->row - 5);
    $rep->Font();

    $rep->End();
    exit();
}

$Ajax->activate('reports_table');

start_form();
    start_table(TABLESTYLE_NOBORDER);
        start_row();
            customer_list_cells(trans("Select a customer: "), 'customer_id', null, true);
            array_selector_cells(
                trans("Customer Type"),
                'customer_type',
                null,
                $GLOBALS['customer_types'],
                array('disabled' => null, 'id' => 'customer_type', 'spec_option'=>'--ALL'),
            );
            sales_persons_list_cells(trans("Sales Person"), 'sales_person_id', null, '-- select sales person --', '');
            date_cells("From", 'from');
            date_cells("Till", 'till');
            check_cells("Exclude Zero", 'exclude_zero');
            submit_cells('RefreshInquiry', trans("Search"), '', trans('Refresh Inquiry'), 'default');

            array_selector_cells('Export to', 'export_to', null, ['PDF', 'Excel']);
            submit_cells('EXPORT', trans('Export'), '', 'Export', true);
        end_row();
    end_table();
            
set_global_customer($_POST['customer_id']);
//-----------------------------------------------------------------------------------------------
br(2);
div_start('reports_table');
$sql = get_sql_for_customer_balance_inquiry(
    date2sql($from),
    date2sql($till),
    get_post('customer_id'),
    check_value('exclude_zero'),
    false,
    '',
    $customer_type,
    get_post('sales_person_id')
);
$table =& new_db_pager(
    'cust_bal_inquiry',
    $sql,
    [
        trans("Customer Ref") => ['align' => 'left', 'name' => 'debtor_ref'],
        trans("Customer Name") => ['align' => 'left', 'name' => 'name'],
        trans("Customer Email") => ['align' => 'left', 'type' => 'email', 'name' => 'debtor_email'],
        trans("Salesman") => ['align' => 'left', 'name' => 'salesman_name'],
        trans("Last Inv. Date") => ['align' => 'center', 'type' => 'date', 'name' => 'last_inv_date'],
        trans("Last Pmt. Date") => ['align' => 'center', 'type' => 'date', 'name' => 'last_pmt_date'],
        trans("Opening Bal.") => ['align' => 'right', 'type' => 'amount', 'name' => 'opening_bal'],
        trans("Dr.") => ['align' => 'right', 'type' => 'amount', 'name' => 'debit'],
        trans("Cr.") => ['align' => 'right', 'type' => 'amount', 'name' => 'credit'],
        trans("Closing Bal.") => ['align' => 'right', 'type' => 'amount', 'name' => 'closing_bal']
    ]
);
display_db_pager($table);
div_end();
end_form();
ob_start(); ?>
<script>
    $('[name="sales_person_id"]').select2();
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean();
end_page();