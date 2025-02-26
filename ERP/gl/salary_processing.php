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
$page_security = 'SA_RECONCILE';
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

page(trans($help_context = "Compare Bank Statement"), false, false, "", $js);

display_note("CSV file should contain 3 columns (Transaction description,Debit,Credit)",true);

if($_GET['download_csv']) {
    header('Location: unreconciled.csv.php');
    exit;
}

if (isset($_POST['submit_options'])) {

    if ($_FILES['statement_csv']["size"] > 0) {

        //Uploading the Bank statement csv to the system
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

        $sql = "DELETE FROM temp_bank_statements";
        db_query($sql);

        //Importing bank statement CSV to temp table
        $sql = "LOAD DATA LOCAL INFILE '$csv_path' INTO TABLE temp_bank_statements  
                FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'";
        db_query($sql);

        unlink($csv_path);
        unset($_POST);

        if (db_error_no() > 0) {
            display_error(trans("CSV upload failed"));
            return false;
        }

        // Set reconcile flag to corresponding gl entry if transaction id exists in bank statement
        $sql = "UPDATE " . TB_PREF . "gl_trans a 
              JOIN temp_bank_statements  b
              ON b.transaction_ LIKE CONCAT('%',a.transaction_id,'%') 
              SET a.reconciled=CURDATE(), b.reconciled=1  
              WHERE a.transaction_id != '' AND ROUND(ABS(b.debit),2) = ROUND(ABS(a.amount),2)";

        db_query($sql);

        //Get reconciled rows from uploaded bank statement
        $sql = "select * from temp_bank_statements where reconciled=1";
        $result = db_query($sql);
        display_notification_centered(db_num_rows($result) . " records are reconciled");

        //Get unreconciled rows from uploaded bank statements
        $sql = "select * from temp_bank_statements where reconciled=0";
        $result = db_query($sql);
        if (db_num_rows($result) > 0) {
            display_notification_centered(db_num_rows($result) . " records are not reconciled, <a href='?download_csv=1'>click here</a> to download");
        }

    }
    else {
        display_error("No CSV file uploaded", true);
    }

}

start_form(true);
start_table(TABLESTYLE, "width='60%'");
file_row(trans("File") . " (CSV):", 'statement_csv', 'statement_csv');
end_table();
br();
submit('submit_options', trans("Upload"));
end_form();
end_page();

