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
$page_security = 'SA_CUSTOMER';
$path_to_root = "../..";

include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();

page(trans($help_context = "Customers"), false, false, "", $js);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/banking.inc");

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui/contacts_view.inc");
include_once($path_to_root . "/includes/ui/customer_item_discounts_view.inc");


import_customer();

function import_customer()
{


    begin_transaction();

    $sql = "select * from csv_cust_list";

    $result = db_query($sql);

    while ($row = db_fetch($result)) {
        $custName = $row['name'];
        $custRef = $row['code'];
        $addr = $row['addr'];
        $tax_id = '';
        $curr_code = 'AED';
        $dimension_id = 0;
        $dimension2_id = 0;
        $credit_status = 1;
        $payment_terms = 0;
        $discount = 0;
        $pymt_discount = 0;
        $credit_limit = 1000;
        $sales_type = 1;
        $notes = '';
        $mobile = !empty($row['mob1']) ? $row['mob1'] : $row['mob2'];
        $email = "";
        $salesman = 0;
        $area = 2;
        $tax_group_id = 1;
        $location = 'DEF';
        $ship_via = 1;

        add_customer($custName, $custRef, $addr,
            $tax_id, $curr_code, $dimension_id, $dimension2_id,
            $credit_status, $payment_terms, $discount, $pymt_discount,
            $credit_limit, $sales_type, $notes, $mobile, $email);

        $selected_id = $_POST['customer_id'] = db_insert_id();

        add_branch($selected_id, $custName, $custRef,
            $addr, $salesman, $area, $tax_group_id, '',
            get_company_pref('default_sales_discount_act'), get_company_pref('debtors_act'), get_company_pref('default_prompt_payment_act'),
            $location, $addr, 0, $ship_via, $notes, '');

        $selected_branch = db_insert_id();

        add_crm_person($custRef, $custName, '', $addr,
            '', '', '', '', '', '');

        $pers_id = db_insert_id();
        add_crm_contact('cust_branch', 'general', $selected_branch, $pers_id);

        add_crm_contact('customer', 'general', $selected_id, $pers_id);

    }


    $sql = "TRUNCATE TABLE csv_cust_list";

    db_query($sql);

    commit_transaction();


    display_note('Customer List Imported from Table csv_cust_list');


}

