<?php

// Clear out all frontaccounting transactions
// Leaves customers/bank accounts alone, but removes all transaction items
// BE CAREFUL YOU WILL LOSE YOUR TRANSACTION DATA IF YOU RUN THIS SCRIPT
// BACKUP BEFORE YOU RUN IT!!!
// IF YOU DON'T KNOW EXACTLY WHAT YOUR ARE DOING, DON'T RUN THIS SCRIPT

// ask for input
fwrite(STDOUT, "Enter your MySQL FrontAccounting database name: ");
// get input
$db = get_input();

fwrite(STDOUT, "Enter your Company Number eg. 1, 2 etc [0]: ");
// get input
$company_number = get_input('0');

// ask for input
fwrite(STDOUT, "Enter your MySQL host [localhost]: ");
// get input
$host = get_input('localhost');

fwrite(STDOUT, "Enter your MySQL user id [root]: ");
// get input
$userid = get_input('root');

fwrite(STDOUT, "Enter your MySQL password: ");
// get input
$pword = get_input();

fwrite(STDOUT, "Do you want a full reset(F) or just transactions(T)?[T]: ");
// get input
$clean = get_input('T') == 'F';

// Confirmation - must be Y in capitals, or I stop right here.
fwrite(STDOUT, "You are going to clear the database : $db company number : $company_number\n" . "Are you absolutely sure you want to do this? (Y/N)");
$confirm = trim(fgets(STDIN));
if ($confirm!="Y") {
    echo "OK...aborting\n";
    exit();
}

//$conn = mysql_connect($host,$userid,$pword); //<---enter your host, user id and password for MySQL here
$conn = mysqli_connect($host, $userid, $pword, $db);

if ($conn==null) {
    echo "Could not connect to MySQL with the host/username/password you provided. Try again.\n";
    exit();
}

run_qry("SET FOREIGN_KEY_CHECKS = 0;");

// Process each table clearing it.
foreach (get_tables_list($clean) as $tbl) {
    if (substr($tbl,0,1)!="#") {
        $sql = "truncate table " . $company_number . "_" . $tbl ;
        if (run_qry($sql)) {
            echo "Cleared " . $company_number . "_" . $tbl . "\n";
        }
    }
}

run_qry("UPDATE `{$company_number}_meta_transactions` SET `next_trans_no` = 0;");
run_qry("UPDATE `{$company_number}_debtors_master` SET `balance` = 0;");
run_qry("DELETE FROM `{$company_number}_tasks` WHERE task_type IN (SELECT id FROM `{$company_number}_task_types` WHERE module_permission != 'HEAD_MENU_HR')");
run_qry("DELETE FROM `{$company_number}_task_transitions` WHERE task_id NOT IN (SELECT id FROM `{$company_number}_tasks`)");

run_qry("DELETE FROM `{$company_number}_calendar_events` WHERE type_id IN (3, 4, 5) ");
run_qry("DELETE FROM `{$company_number}_notifications` WHERE `data` IS NOT NULL  AND JSON_CONTAINS_PATH(`data`, 'one', '$.contractId')");

if ($clean) {
    truncate_except(1, 'branch_code', "{$company_number}_cust_branch");
    truncate_except(1, 'debtor_no', "{$company_number}_debtors_master");
    truncate_except(1, 'id', "{$company_number}_users");
    truncate_except(-1, 'salesman_code', "{$company_number}_salesman");
    run_qry("UPDATE `{$company_number}_sys_prefs` SET `value` = '-1' WHERE `name` IN ("
        . " 'default_shift_id',"
        . " 'dep_amer',"
        . " 'dep_tadbeer',"
        . " 'dep_tasheel',"
        . " 'dep_tawjeeh',"
        . " 'excluded_customers',"
        . " 'staff_mistake_customer_id',"
        . " 'ts_auto_stock_category',"
        . " 'ts_next_auto_stock_no'"
    . ")");
    run_qry("UPDATE `{$company_number}_pay_elements` SET `account_code` = NULL");
    run_qry("DELETE FROM `{$company_number}_entity_groups` WHERE category != 1");
    set_auto_increment('id', "{$company_number}_entity_groups");
}

run_qry("SET FOREIGN_KEY_CHECKS = 1;");

echo "Finished clearing transaction tables\n";
exit();

// A function to clear data from a table you specify
function run_qry($sql) {
    global $conn;

    $result = mysqli_query($conn, $sql ) ;

    if (!$result) {
        echo "Warning: SQL statement " . $sql . " failed\n";
        echo "with an error message of " .mysqli_connect_errno() . mysqli_error($conn);
        return false;
    }

    return $result;
}

function truncate_except($ids, $id_column, $table) {
    if (!is_array($ids)) {
        $ids = [$ids];
    }
    $ids = implode(',', array_map('quote', array_filter($ids)) ?: [-1]);
    run_qry("DELETE FROM `$table` WHERE `{$id_column}` NOT IN ({$ids});");
    set_auto_increment($id_column, $table);
}

function set_auto_increment($id_column, $table) {
    $next_id = run_qry("SELECT MAX(`{$id_column}`) + 1 as next_id FROM `{$table}`")->fetch_assoc()['next_id'] ?: 1;
    run_qry("ALTER TABLE `{$table}` AUTO_INCREMENT={$next_id}");
}

function get_input($default = '') {
    $input = trim(fgets(STDIN));
    return $input === '' ? $default : $input;
}

function get_tables_list($clean) {
    $transaction_tables = [
        'attachments',
        'gl_trans',
        'bank_trans',
        'customer_balances',
        'debtor_trans',
        'debtor_trans_details',
        'trans_tax_details',
        'purch_orders',
        'purch_order_details',
        'purch_data',
        'sales_orders',
        'sales_order_details',
        'wo_issues',
        'wo_issue_items',
        'wo_manufacture',
        'wo_requirements',
        'supp_invoice_items',
        'trans_tax_details',
        'supp_allocations',
        'grn_batch',
        'grn_items',
        'audit_trail',
        'voided',
        'refs',
        'comments',
        'cust_allocations',
        'stock_moves',
        'journal',
        'other_charges_trans_details',
        'vouchers',
        'voucher_transactions',
        'credit_requests',
        'purchase_requests',
        'supp_trans',
        'stock_moves',
        'voided_bank_trans',
        'voided_customer_rewards',
        'voided_cust_allocations',
        'voided_debtor_trans',
        'voided_debtor_trans_details',
        'voided_gl_trans',
        'voided_journal',
        'voided_purch_orders',
        'voided_sales_orders',
        'voided_sales_order_details',
        'voided_stock_moves',
        'voided_supp_allocations',
        'voided_supp_trans',
        'voided_trans_tax_details',
        'voided_customer_rewards',
        'voided_sales_order_details',
        'voided_stock_moves',
        'voided_supp_invoice_items',
        'voided_purch_order_details',
        'voided_grn_items',
        'cash_handover_requests',
        'sent_history',
        'service_requests',
        'service_request_items',
        'axis_front_desk',
        'autofetched_trans',
        'discount_trans',
        'logs',
        'meta_references',
        'other_charges_trans_details',
        'purchase_request_items',
        'sql_trail',
        'workorders',
        'labour_contracts',
        'stock_replacement',
        'asset_assignments',
        'stock_depreciation_details',
        'contract_installments',
        'contract_installment_details'
    ];

    $master_tables = [
        'attendance',
        'attendance_metrics',
        'bank_statement_csv',
        'bom',
        'budget_trans',
        'calendar_events',
        'category_groups',
        'companies',
        'credit_requests',
        'crm_contacts',
        'crm_persons',
        'customer_feedback',
        'custom_reports',
        'customer_discount_items',
        'customer_rewards',
        'department_shifts',
        'departments',
        'designations',
        'documents',
        'email_notification',
        'emp_contacts',
        'emp_doc_access_log',
        'emp_doc_release_requests',
        'emp_jobs',
        'emp_leave_details',
        'emp_leaves',
        'emp_salaries',
        'emp_salary_details',
        'emp_salary_holded',
        'emp_shifts',
        'emp_trans',
        'empl_punchinouts',
        'employees',
        'group_members',
        'item_codes',
        'labours',
        'loc_stock',
        'notifications',
        'other_charges_master',
        'payrolls',
        'payslip_details',
        'payslip_elements',
        'payslips',
        'po_terms_and_conditions',
        'prices',
        'purch_data',
        'quick_entries',
        'quick_entry_lines',
        'reconcile_result',
        'recurrent_invoices',
        'shifts',
        'stock_category',
        'stock_master',
        'sub_customers',
        'sub_ledgers',
        'subcategories',
        'suppliers',
        'tasks',
        'task_transitions',
        'warning_categories',
        'warning_grades',
        'workflows',
        'workflow_definitions',
        'wo_costing',
        'leave_carry_forward',
        'emp_reward_deductions',
        'emp_reward_deductions_details',
        'emp_timeouts',
        'general_requests'
    ];

    return $clean ? array_merge($transaction_tables, $master_tables) : $transaction_tables;
}


/**
 * Returns a quoted string
 *
 * @param string $str string to quote
 * @param string $quote type of quote to use
 * @return sting
 */
function quote($str, $quote = "'") {
    return $quote . $str . $quote;
}