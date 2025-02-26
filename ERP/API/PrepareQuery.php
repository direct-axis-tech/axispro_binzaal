<?php
/**
 * Class PrepareQuery
 * Created By : Bipin
 */
//$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/taxes/tax_calc.inc");
include_once($path_to_root . "/admin/db/shipping_db.inc");
include_once($path_to_root . "/themes/daxis/kvcodes.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/admin/db/company_db.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/API/API_Call.php");
class PrepareQuery {
    public static function ServiceReport($filters) {
        $where = "";
        if(!empty($filters)) {
            if (!empty($filters['invoice_number'])) {
                $where .= " and  dt.reference = '{$filters['invoice_number']}'";
            }
            if (!empty($filters['date_from'])) {
                $date_from = date2sql($filters['date_from']);
                $where .= " and  dt.tran_date >= '$date_from'";
            }
            if (!empty($filters['date_to'])) {
                $date_to = date2sql($filters['date_to']);
                $where .= " and  dt.tran_date <= '$date_to'";
            }

            if(!isset($filters['customer'])) {
                $filters['customer'] = [];
            }

            if (!empty(array_filter($filters['customer']))) {

                $filters['customer'] = implode(",",array_filter($filters['customer']));

                $where .= " and  dt.debtor_no in (".$filters['customer'].")";
            }

            if (!user_check_access('SA_SRVREPORTALL')) {
                if (user_check_access('SA_SRVREPORTDEP')) {
                    $conditions = array_map(function($v) {
                        return "json_contains(cat.belongs_to_dep, json_quote('{$v}'))";
                    }, $_SESSION['wa_current_user']->allowed_dims ?: [-1]);
                    $conditions = implode(' OR ', $conditions);
                    $where .= " AND ({$conditions})";
                } else {
                    $where .= " and  (dt_detail.created_by =".$_SESSION['wa_current_user']->user." 
                                        OR dt_detail.transaction_id_updated_by = ".$_SESSION['wa_current_user']->user." )";
                }
            }
            
            if (!empty($filters['employee'])) {
                $where .= " and  dt_detail.created_by =".$filters['employee'];
            }

            if (!empty($filters['completed_by'])) {
                $where .= " and  dt_detail.transaction_id_updated_by =".$filters['completed_by'];
            }

            if (!empty($filters['customer_type'])) {
                $where .= " and  dm.customer_type = ".db_escape($filters['customer_type']);
            }

            if (!empty($filters['salesman'])) {
                $where .= " and  i.salesman_code =".$filters['salesman'];
            }

            if (!empty($filters['display_customer'])) {
                $where .= " and  dt.display_customer LIKE '%" . $filters['display_customer']."%'";
            }

            if (!empty($filters['payment_status'])) {
                if ($filters['payment_status'] == 1) //Fully Paid
                    $where .= " and  dt.alloc >= (dt.ov_amount+dt.ov_gst+dt.ov_freight+dt.ov_freight_tax+dt.ov_discount)";
                if ($filters['payment_status'] == 2) //Not Paid
                    $where .= " and  dt.alloc = 0";
                else if ($filters['payment_status'] == 3) //Partially Paid
                    $where .= " and  (dt.alloc < (dt.ov_amount+dt.ov_gst+dt.ov_freight+dt.ov_freight_tax+dt.ov_discount) and dt.alloc <> 0)";
            }

            if (!empty($filters['category'])) {
                $where .= " and  stk.category_id =".$filters['category'];
            }

            if (!empty($filters['dimension_id'])) {
                $where .= " AND dt.dimension_id = " . $filters['dimension_id']; 
            }

            if (!empty($filters['customer_category'])) {
                $where .= " and  dm.category = '{$filters['customer_category']}'";
            }


            if (!empty($filters['service'])) {
                $where .= " and  dt_detail.stock_id ='".$filters['service']."'";
            }

            if (!empty($filters['ref_name'])) {
                $where .= " and  dt_detail.ref_name LIKE ". db_escape("%".$filters['ref_name']."%");
            }

            if (!empty($filters['transaction_id'])) {
                $where .= " and  dt_detail.transaction_id LIKE '%" . $filters['transaction_id'] . "%'";
            }

            if (!empty($filters['transaction_id_updated_at'])) {
                $where .= " AND dt_detail.transaction_id_updated_at = '" . date2sql($filters['transaction_id_updated_at']) ."'";
            }

            if (!empty($filters['customer_mobile'])) {
                $_mobileNo = strtr($filters['customer_mobile'], ['-' => '', ' ' => '']);
                if (preg_match(UAE_MOBILE_NO_PATTERN, $_mobileNo)) {
                    $_mobileNo = preg_replace(UAE_MOBILE_NO_PATTERN, "$2", $_mobileNo);
                }

                $where .= " and  dt.customer_mobile like '%{$_mobileNo}%'";
            }

            if (!empty($filters['customer_email'])) {
                $where .= " and  dt.customer_email like '%".trim($filters['customer_email'])."%'";
            }


            if(isset($filters['transaction_status'])) {

                if ($filters['transaction_status'] == 1) {
                    $where .= " and  dt_detail.transaction_id <> ' '";
                }

                if ($filters['transaction_status'] == 2) {
                    $where .= " and  dt_detail.transaction_id = ' '";
                }
            }

            if (!empty($filters['application_id'])) {
                $where .= " and dt_detail.application_id LIKE '%{$filters['application_id']}%'";
            }

            if (!empty($filters['invoice_type'])) {
                switch ($filters['invoice_type']) {
                    case 'Cash':
                        $where .= " and dt.payment_method not in ('CustomerCard', 'CenterCard')";
                        break;
                    case 'CenterCard':
                    case 'CustomerCard':
                        $where .= " and dt.payment_method = '{$filters['invoice_type']}'";
                        break;
                }
            }
        }

        $unitPrice = '(`dt_detail`.`unit_price` + `dt_detail`.`returnable_amt` - `dt_detail`.`pf_amount` - if(`dt`.`tax_included`, `dt_detail`.`unit_tax`, 0))';
        $factor = 'if(`dt_detail`.`debtor_trans_type` = '.ST_CUSTCREDIT.', -1, 1)';
        $sql = (
            "SELECT 
                dt.trans_no,
                dt_detail.stock_id,
                dt_detail.description, 
                {$factor} * {$unitPrice} as unit_price,
                dt.reference AS invoice_number,
                DATE_FORMAT(dt.tran_date,'%d-%m-%Y') AS tran_date,
                {$factor} * dt_detail.discount_amount as discount_amount,
                {$factor} * dt_detail.user_commission as user_commission,
                dt_detail.created_by,
                {$factor} * dt_detail.customer_commission as customer_commission,
                if (dt_detail.transaction_id = ' ', 'Not Completed', 'Completed') as transaction_status,
                dt_detail.transaction_id_updated_by,
                completer.user_id as completed_by,
                completer.real_name as completer_name,
                {$factor} * dt_detail.customer_commission2 as customer_commission2,
                -- IFNULL(reward.reward_amount,0) AS reward_amount,
                CASE 
                    WHEN ROUND(dt.alloc) >= ROUND(dt.ov_amount + dt.ov_gst) THEN 'Fully Paid' 
                    WHEN dt.alloc = 0 THEN 'Not Paid' 
                    WHEN ROUND(dt.alloc) < ROUND(dt.ov_amount + dt.ov_gst) THEN 'Partially Paid' 
                END AS payment_status,
                {$factor} * ROUND(
                    (
                        dt_detail.govt_fee 
                        + dt_detail.bank_service_charge
                        + dt_detail.bank_service_charge_vat
                        + dt_detail.pf_amount
                    ) * (dt_detail.quantity),
                    2
                ) AS total_govt_fee,
                {$factor} * ROUND(
                    (
                        dt_detail.unit_price
                        + dt_detail.govt_fee
                        + dt_detail.bank_service_charge
                        + dt_detail.bank_service_charge_vat
                        + IF(dt.tax_included, 0, dt_detail.unit_tax)
                        - dt_detail.discount_amount
                    ) * dt_detail.quantity,
                    2
                ) AS line_total,
                {$factor} * ROUND(dt.ov_amount + dt.ov_gst, 2) AS invoice_total,
                {$factor} * ROUND({$unitPrice} * dt_detail.quantity, 2) AS total_service_charge,
                {$factor} * ROUND(
                    (
                          {$unitPrice}
                        + dt_detail.receivable_commission_amount
                        - dt_detail.discount_amount
                        - dt_detail.user_commission
                        - `dt_detail`.`customer_commission`
                    ) * dt_detail.quantity,
                    2
                ) AS net_service_charge,
                dm.debtor_ref,
                dm.category customer_category,
                dm.name debtor_name,
                dm.customer_type,
                dt.debtor_no,
                dt.display_customer,
                {$factor} * dt_detail.unit_tax as unit_tax,
                {$factor} * dt_detail.unit_tax * dt_detail.quantity AS total_tax,
                {$factor} * dt_detail.quantity as quantity,
                {$factor} * dt_detail.discount_amount * dt_detail.quantity AS line_discount_amount,
                {$factor} * dt_detail.govt_fee as govt_fee,
                dt_detail.govt_bank_account,
                {$factor} * dt_detail.bank_service_charge as bank_service_charge,
                {$factor} * dt_detail.bank_service_charge_vat as bank_service_charge_vat,
                {$factor} * dt_detail.pf_amount as pf_amount,
                dt_detail.transaction_id,
                DATE_FORMAT(dt_detail.transaction_id_updated_at, '%e-%b-%Y') as transaction_id_updated_at,
                dt_detail.ed_transaction_id,
                dt_detail.application_id,
                dt_detail.passport_no,
                {$factor} * (dt_detail.user_commission + dt_detail.cust_comm_emp_share) * dt_detail.quantity AS gross_employee_commission,
                {$factor} * dt_detail.cust_comm_emp_share * dt_detail.quantity AS cust_comm_emp_share,
                {$factor} * dt_detail.user_commission * dt_detail.quantity AS employee_commission,
                dt_detail.ref_name,
                dt_detail.created_at,
                dt.customer_mobile,
                if (dt.payment_method not in ('CustomerCard', 'CenterCard'), 'Cash Eq.', dt.payment_method) as invoice_type,
                dt.customer_email,
                i.salesman_name,
                {$factor} * dt_detail.returnable_amt as returnable_amt,
                dt_detail.returnable_to,
                dim.name AS dimension,
                DATE_FORMAT(dt.transacted_at, '%b-%e %l:%i %p') as 'invoice_time',
                {$factor} * dt_detail.receivable_commission_amount as receivable_commission_amount,
                dt_detail.receivable_commission_account
            FROM 0_debtor_trans_details dt_detail 
                LEFT JOIN 0_debtor_trans dt 
                    ON dt.trans_no = dt_detail.debtor_trans_no
                        AND dt.type = dt_detail.debtor_trans_type
                LEFT JOIN 0_debtors_master dm ON dm.debtor_no = dt.debtor_no
                LEFT JOIN 0_salesman i ON dm.salesman_id = i.salesman_code
                LEFT JOIN 0_stock_master stk ON stk.stock_id = dt_detail.stock_id 
                LEFT JOIN 0_stock_category cat ON cat.category_id = stk.category_id 
                LEFT JOIN `0_customer_discount_items` cust_disc_items 
                    ON cust_disc_items.item_id = stk.category_id 
                        AND dt.`debtor_no` = cust_disc_items.customer_id 
                -- LEFT JOIN 0_customer_rewards reward ON reward.detail_id = dt_detail.id 
                LEFT JOIN 0_users u ON dt_detail.created_by = u.id
                LEFT JOIN 0_users completer ON dt_detail.transaction_id_updated_by = completer.id
                LEFT JOIN 0_dimensions dim ON dim.id = dt.dimension_id
            WHERE dt_detail.debtor_trans_type in (".ST_SALESINVOICE.", ".ST_CUSTCREDIT.")
                AND dt_detail.quantity <> 0 
                AND dim.closed = 0
                AND dt.ov_amount <> 0
                $where "
        );

        // dd($sql);
        return $sql;
    }
}