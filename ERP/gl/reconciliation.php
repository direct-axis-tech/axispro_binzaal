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

use App\Models\Accounting\Ledger;

// $page_security = 'SA_RECONCILE';
$page_security = 'SA_DENIED';
$path_to_root = "..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/includes/banking.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(800, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();

page(trans($help_context = "Bank Reconciliation"), false, false, "", $js);

//-----------------------------------------------------------------------------------
// Ajax updates
//
if (get_post('submit_options') || get_post('SearchSubmit')) {
    $Ajax->activate('journal_tbl');
}
//--------------------------------------------------------------------------------------
$bank_info = get_bank_account(get_post('bank'));

if (isset($_POST['submit_options'])) {

    if ($_FILES['statement_csv']["size"] > 0) {

        $from_date = date2sql(get_post('FromDate'));
        $to_date = date2sql(get_post('ToDate'));
        $bank = get_post('bank');
        $account = get_bank_gl_account($bank);

        $date_col = get_post('date_col');
        $ref_col = get_post('ref_col');
        $desc_col = get_post('desc_col');
        $amount_col = get_post('amount_col');

        /** For CBD */
        $bank_charge_col = get_post('bank_charge_col');
        $vat_col = get_post('vat_col');

        $date_format_col = get_post('date_format');
        $csv_date_format = db_escape($date_format_col);


        $ref_inside_desc = check_value('ref_inside_desc');

        if (empty($date_col)) {
            display_error('Please Specify Date Column ');
            return false;
        }
        if (empty($ref_col)) {
            display_error('Please Specify Transaction Reference Column');
            return false;
        }
        if (empty($desc_col)) {
            display_error('Please Specify Transaction Description Column');
            return false;
        }
        if (empty($amount_col)) {
            display_error('Please Specify Amount Column');
            return false;
        }

        /**
         * Map CSV Cols to Table Cols
         * Date,Reference,Desc,Amount
         */
        $stmt_date_col = "col_" . $date_col;
        $stmt_ref_col = "col_" . $ref_col;
        $stmt_desc_col = "col_" . $desc_col;
        $stmt_amount_col = "col_" . $amount_col;

        $stmt_bank_charge_col = "col_" . $bank_charge_col;
        $stmt_vat_col = "col_" . $vat_col;

        /** Uploading the Bank statement csv to the system */
        $dir = $path_to_root . "/gl";
        $tmpname = $_FILES['statement_csv']['tmp_name'];
        $filename_exploded = explode(".", $_FILES['statement_csv']['name']);
        $ext = end($filename_exploded);
        $filesize = $_FILES['statement_csv']['size'];
        $filetype = $_FILES['statement_csv']['type'];

        if ($filetype != "application/vnd.ms-excel") {
            display_error(trans("File type should be CSV"));
            return false;
        }

        $file_name = "bank_statement" . rand(10, 100) . "." . $ext;
        move_uploaded_file($tmpname, $dir . "/$file_name");

        $csv_path = "$file_name";

        $sql = "TRUNCATE TABLE 0_bank_statement_csv";
        db_query($sql);

        /** Importing bank statement CSV to temp table */
        $sql = "LOAD DATA LOCAL INFILE '$csv_path' INTO TABLE 0_bank_statement_csv  
                    FIELDS 
                        TERMINATED BY ',' 
                        OPTIONALLY ENCLOSED BY '\"' 
                        ESCAPED BY '\"'
                    LINES 
                        TERMINATED BY '\r\n'";

        db_query($sql);

        unlink($csv_path);
        unset($_POST);

        if (db_error_no() > 0) {
            display_error(trans("CSV upload failed"));
            return false;
        }


        /** FOR CBD */
        $amount_col_update_sql = "UPDATE 0_bank_statement_csv SET col_I = ROUND(";
        $amount_col_update_sql .= "CAST(REPLACE($stmt_amount_col, ',', '') AS DECIMAL(18,2))";
        if(!empty($bank_charge_col))
            $amount_col_update_sql .= "+CAST(REPLACE($stmt_bank_charge_col, ',', '') AS DECIMAL(18,2))";
        if(!empty($vat_col))
            $amount_col_update_sql .= "+CAST(REPLACE($stmt_vat_col, ',', '') AS DECIMAL(18,2))";
        $amount_col_update_sql .= ",2)";

        db_query($amount_col_update_sql);

        /** Set Amount Col  - SUM of Amount,BankCharge and VAT columns*/
        $stmt_amount_col = "col_I";

        if(Ledger::isNoqudiAccount($account)) { //NOQODI

            /** Consider reference cols as col_H, If transaction reference is in the description field of bank statement.
             * Eg: NOQODI Bank Statement
             */
//            $stmt_ref_col = 'col_H';

            /** Updating transaction references for columns do not contains it. eg: 3.00,0.15 columns in NOQODI */
            $sql = "UPDATE 0_bank_statement_csv t1
                    INNER JOIN 0_bank_statement_csv t2 ON t2.$stmt_ref_col = t1.$stmt_ref_col 
                    SET t1.$stmt_desc_col = t2.$stmt_desc_col";

            db_query($sql);

            /**  Extract Transaction ID from the description and store it to col_H */
            $sql = "UPDATE 0_bank_statement_csv SET $stmt_ref_col = 
                    TRIM(BOTH ' 'FROM SUBSTRING_INDEX(SUBSTRING_INDEX($stmt_desc_col,'Ref',-2), ', ', 1))";

//            db_query($sql);


        }


        /** DELETE older entries in the table */
        $sql = "TRUNCATE TABLE 0_reconcile_result";
        db_query($sql);

        /** Casting comma separated varchar to decimal  */
        $stmt_amount_col = "CAST(REPLACE($stmt_amount_col, ',', '') as DECIMAL(18,2))";

        $sql = "INSERT INTO 0_reconcile_result (sw_date, bank_date, sw_amount,bank_amount,transaction_)
        /* Get all transactions between the date, and merge with bank stmt. gl_trans+bank_report */
        SELECT sw_date,bank_date,ROUND(SUM(ABS(sw_amount)),2) as sw_amount,bank_amount,transaction_ref FROM 
        (
          SELECT a.counter, a.tran_date AS sw_date, STR_TO_DATE($stmt_date_col, $csv_date_format) AS bank_date, ABS(a.amount) AS sw_amount, 
            (
              SELECT SUM($stmt_amount_col) FROM 0_bank_statement_csv 
                WHERE $stmt_ref_col = a.transaction_id
            ) AS bank_amount, a.transaction_id AS transaction_ref
            FROM 0_gl_trans a
          LEFT JOIN 0_bank_statement_csv b ON $stmt_ref_col = a.transaction_id
          WHERE a.transaction_id != '' AND a.tran_date>='$from_date' AND a.tran_date <= '$to_date' AND a.account='$account' 
          GROUP BY a.transaction_id,a.counter
        ) AS MyTable
        GROUP BY transaction_ref 
        
        UNION ALL 
        
        /* Get all transactions not in software but in bank statement by given date */
        SELECT null AS sw_date, STR_TO_DATE($stmt_date_col, $csv_date_format) AS bank_date, 0 AS sw_amount, 
        IFNULL(SUM($stmt_amount_col),0) AS bank_amount, $stmt_ref_col AS transaction_ref FROM 0_bank_statement_csv a
          WHERE $stmt_ref_col NOT IN 
          (
            SELECT transaction_id FROM 0_gl_trans WHERE transaction_id<>'' 
            AND tran_date>='$from_date' AND tran_date <= '$to_date' AND account='$account'
          ) AND STR_TO_DATE($stmt_date_col, $csv_date_format) >='$from_date' AND  
          STR_TO_DATE($stmt_date_col, $csv_date_format) <='$to_date' 
        GROUP BY $stmt_ref_col
        
        UNION ALL 
        
        /* Get transactions by transaction id specified in bank stmt. but not within the specified date range */
        SELECT sw_date,bank_date, ROUND(SUM(sw_amount),2) AS sw_amount,bank_amount,transaction_ref FROM 
        (
            SELECT a.tran_date AS sw_date, STR_TO_DATE($stmt_date_col, $csv_date_format) AS bank_date, 
            ABS(a.amount) AS sw_amount,SUM($stmt_amount_col) AS bank_amount, a.transaction_id AS transaction_ref FROM 0_gl_trans a
            LEFT JOIN 0_bank_statement_csv b ON $stmt_ref_col = a.transaction_id
            WHERE transaction_id IN (
                SELECT $stmt_ref_col FROM 0_bank_statement_csv
                WHERE $stmt_ref_col NOT IN 
                (
                    SELECT transaction_id
                    FROM 0_gl_trans
                    WHERE tran_date>='$from_date' AND tran_date <= '$to_date' AND account='$account'
                )
            ) AND a.transaction_id <> '' 
        GROUP BY a.transaction_id,a.amount
        ) AS MyTable group by transaction_ref";


        //dd($sql);

        db_query($sql);



        $result_url = "$path_to_root/gl/reconciliation_result.php?";
        $result_url .= "bank=" . $bank_info['bank_account_name'] . "&";
        $result_url .= "date_period=" . sql2date($from_date) . " to " . sql2date($to_date);

        meta_forward($result_url, "");

        exit();

    } else {

        display_error("No CSV file uploaded", true);
    }

} else {

    $_POST['ToDate'] = get_post('FromDate');
    $Ajax->activate('ToDate');

}


$bank_format_cols = $bank_info['bank_address'];
if (empty($bank_format_cols)) $bank_format_cols = "B,C,D,E,F,G";
$bank_format_cols = explode(",", $bank_format_cols);

$_POST['date_col'] = $bank_format_cols[0];
$_POST['ref_col'] = $bank_format_cols[1];
$_POST['desc_col'] = $bank_format_cols[2];
$_POST['amount_col'] = $bank_format_cols[3];
$_POST['bank_charge_col'] = isset($bank_format_cols[4])?$bank_format_cols[4] : "";
$_POST['vat_col'] = isset($bank_format_cols[5])?$bank_format_cols[5] : "";

$_POST['date_format'] = "%d/%m/%Y";

$Ajax->activate('date_col');
$Ajax->activate('ref_col');
$Ajax->activate('desc_col');
$Ajax->activate('amount_col');
$Ajax->activate('bank_charge_col');
$Ajax->activate('vat_col');


start_form(true);

start_table(TABLESTYLE2, "style='width:50%'");
table_section_title(trans("Upload Bank Statement (CSV File)"));

bank_accounts_list_row(trans('Bank:'), 'bank', null, true);
date_row(trans("From:"), 'FromDate', '', null, -1, 0, 0, null, true);
date_row(trans("To:"), 'ToDate', '', null, -1, 0, 0);



start_row();

label_cell(trans("Date Format in Bank Statement:"),"class='label'");

echo "<td>";
echo array_selector("date_format", null,
    [
        '%d/%m/%Y' => trans('d/m/Y - 30/12/1975'),
        '%m/%d/%Y' => trans('m/d/Y - 12/30/1975'),
        '%Y/%m/%d' => trans('Y/m/d - 1975/12/30'),
        '%d-%m-%Y' => trans('d-m-Y - 30-12-1975'),
        '%m-%d-%Y' => trans('m-d-Y - 12-30-1975'),
        '%Y-%m-%d' => trans('Y-m-d - 1975-12-30')
    ]);
echo "</td>";


text_row(trans("Date Column:"), 'date_col', $_POST['date_col'], 1, 10);
text_row(trans("Transaction Ref Column:"), 'ref_col', $_POST['ref_col'], 1, 10);
text_row(trans("Description Column:"), 'desc_col', $_POST['desc_col'], 1, 10);
text_row(trans("Amount Column:"), 'amount_col', $_POST['amount_col'], 1, 10);
text_row(trans("Bank Charge Column:"), 'bank_charge_col', $_POST['bank_charge_col'], 1, 10,"","","Leave it blank, if no value");
text_row(trans("VAT Column:"), 'vat_col', $_POST['vat_col'], 1, 10,"","","Leave if blank, if no value");

//check_row(trans("Check if Transaction ID is in description"), 'ref_inside_desc');

file_row(trans('Upload CSV:'), 'statement_csv', 'statement_csv');
label_row('', '');
submit_row('submit_options', trans("Upload"), '1', '', '','default');

end_table();
end_form();

end_page();


