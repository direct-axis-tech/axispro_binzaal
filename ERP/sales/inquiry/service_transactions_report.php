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
$page_security = 'SA_SALESALLOC';
$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();
page(trans($help_context = "Service Transaction Report"), false, false, "", $js);


if (get_post('RefreshInquiry')) {
    $Ajax->activate('_trans_tbl_span');
}


if (isset($_GET['customer_id'])) {
    $_POST['customer_id'] = $_GET['customer_id'];
}


//------------------------------------------------------------------------------------------------

if (!isset($_POST['customer_id']))
    $_POST['customer_id'] = null;

if (!isset($_POST['user_id']))
    $_POST['user_id'] = null;

if (isset($_GET['from_date']))
    $_POST['from_date'] = $_GET['from_date'];

if (isset($_GET['to_date']))
    $_POST['to_date'] = $_GET['to_date'];


if ($_POST['refreshFilter']) {

    $_POST['customer_id'] = null;
    $_POST['from_date'] = Today();
    $_POST['to_date'] = Today();

}

function payment_method_cell($label,$name,$selected_id=null)
{
    echo "<td>$label</td>
            <td>" . array_selector(
            $name, $selected_id,
            [
                "" => "All",
                "not_settled"=>"Not Settled",
                "settled"=>"Settled",
            ]
        ) . "</td>";

}


start_form();
start_table(TABLESTYLE_NOBORDER);
table_section(1);
start_table(TABLESTYLE2);
start_row();

customer_list_cells(trans("Select a customer: "), 'customer_id', $_POST['customer_id'], 'Select a Customer',true);
payment_method_cell(trans("Show Not Settled:"),'settled');

submit_cells('SearchSubmit', trans("Submit"), '', '', 'default');
end_row();
start_row();

date_cells("From Date:", 'from_date', trans('Filter by Date'),
    true, 0, 0, 0, null, true);

date_cells("To Date:", 'to_date', trans('Filter by Date'),
    true, 0, 0, 0, null, true);


end_row();
end_table();


$customer_id = $_POST['customer_id'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$settled = get_post('settled');


$Ajax->activate('_page_body');

//------------------------------------------------------------------------------------------------

function check_redeemed($row)
{
    return false;
}

function payment_status($row)
{
    return [
        0 => 'All',
        1 => 'Fully Paid',
        2 => 'Not Paid',
        3 => 'Partially Paid',
    ][$row['payment_status']];
}


//------------------------------------------------------------------------------------------------

$sql = get_service_transactions_report($customer_id, $from_date, $to_date,$settled);

//------------------------------------------------------------------------------------------------
$cols = array(
    trans("Invoice No.") => array('align' => 'center'),
    trans("Invoice Date") => array('align' => 'center', 'type' => 'date'),
    trans("Customer") => array('align' => 'center'),
    trans("Service") => array('align' => 'center'),
    trans("TR-ID 1") => array('align' => 'center'),
    trans("TR-ID 2") => array('align' => 'center'),
    trans("Ref.Name") => array('align' => 'center'),
    trans("TR-Date") => array('align' => 'center', 'type' => 'date'),
    trans("Service Amount") => array('align' => 'center', 'type' => 'amount'),
    trans("Invoice Status") => array('align' => 'center','fun' => 'payment_status')
);

if(!empty($customer_id)) {
    $cols[trans("Customer")] = 'skip';
}

$table =& new_db_pager('trans_tbl', $sql, $cols);

$table->set_marker('check_redeemed', null);

$table->width = "80%";

display_db_pager($table);

end_form();


/** EXPORT */
$Ajax->activate("PARAM_0");
$Ajax->activate("PARAM_1");
$Ajax->activate("PARAM_2");
$Ajax->activate("PARAM_3");


start_form(false, false, $path_to_root . "/reporting/reports_main.php", "export_from");
hidden("Class", "6");
hidden("REP_ID", "1060");
hidden("PARAM_0", $_POST['customer_id']);
hidden("PARAM_1", $_POST['from_date']);
hidden("PARAM_2", $_POST['to_date']);
hidden("PARAM_3", $_POST['settled']);

echo array_selector("DESTINATION", null, [1=>"Export to EXCEL"]);
br(2);

submit_cells('Rep1060', trans("EXPORT"), '', "Export to PDF or EXCEL", 'default');

end_form();

/** END -- EXPORT */


end_page();

?>

<style>
    form[name="export_from"] {
        text-align: center;
    }

    table.tablestyle tr td:nth-child(4) {
        width: 20% !important;
    }

    table.tablestyle tr td:nth-child(3) {
        width: 20% !important;
    }
</style>




