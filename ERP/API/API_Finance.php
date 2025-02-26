<?php

/**
 * Class API_Finance
 * Created By : Bipin
 */

use App\Models\Accounting\Dimension;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Reader;

include_once("AxisPro.php");
include_once("PrepareQuery.php");

Class API_Finance
{

    /**
     * @param $inputData
     * @param $fileData
     * @return mixed
     * Validate inputs for auto reconciliation
     */
    public function validateAutoReconcileInputs($inputData, $fileData) {

        if (empty($inputData['bank']))
            return AxisPro::SendResponse(
                [
                    'msg' => "Please choose a bank",
                    'status' => 'FAIL'
                ]
            );

        if (empty($inputData['date_col']))
            return AxisPro::SendResponse(
                [
                    'msg' => "DATE COL must have a value",
                    'status' => 'FAIL'
                ]
            );

        if (empty($inputData['amount_col']))
            return AxisPro::SendResponse(
                [
                    'msg' => "AMOUNT COL must have a value",
                    'status' => 'FAIL'
                ]
            );

        if (empty($inputData['ref_col']))
            return AxisPro::SendResponse(
                [
                    'msg' => "TRANS ID COL / APPLICATION ID COL must have a value",
                    'status' => 'FAIL'
                ]
            );

        if (empty($fileData['tmp_name']))
            return AxisPro::SendResponse(
                [
                    'msg' => "Please upload bank statement",
                    'status' => 'FAIL'
                ]
            );

        if (
            empty($inputData['date_format'])
            || !in_array($inputData['date_format'], [
                'd/m/Y',
                'm/d/Y',
                'Y/m/d',
                'd-m-Y',
                'm-d-Y',
                'Y-m-d',
                'd-m-y',
                'm-d-y',
                'y-m-d',
                'd/m/y',
                'm/d/y',
                'y/m/d'
            ])
        ) {
            return AxisPro::SendResponse(
                [
                    'msg' => "Please select a date format",
                    'status' => 'FAIL'
                ]
            );
        }

        $fileSize = $fileData['size'];
        $fileType = $fileData['type'];

//        if ($fileType != "application/vnd.ms-excel" && $fileType != "text/csv") {
//
//            return AxisPro::SendResponse(
//                [
//                    'msg' => "File type should be CSV",
//                    'status' => 'FAIL'
//                ]
//            );
//        }

        return true;

    }

    /**
     * @param string $format
     * @return mixed
     * Process the the bank statement comparison
     */
    public function processAutoReconciliation($format = "json")
    {
        $inputData = $_POST;

        $bank = $inputData["bank"];
        $dateFormat = "%d-%m-%Y";
        $dateCol = $inputData["date_col"];
        $refCol = $inputData["ref_col"];
        $isBasedOnApplicationId = !!($inputData['appl_col'] ?? null);
        $amountCol = $inputData["amount_col"];
        $bankChargeCol = $inputData["bank_charge_col"];
        $vatCol = $inputData["vat_col"];
        $desCol = $inputData['desc_col'];
        $from_date = date2sql($inputData["from_date"]);
        $to_date = date2sql($inputData["to_date"]);
        $selectedDateFormat = $inputData['date_format'];
        $processedRows = 0;

        $fileCsv = $_FILES['statement_csv'];
        $this->validateAutoReconcileInputs($inputData,$fileCsv);

        $tmpName = $fileCsv['tmp_name'];
        $fileNameExploded = explode(".", $fileCsv['name']);
        $ext = end($fileNameExploded);

//        dd($inputData);

        //test for noqodi
//        $bank = 54;
//        $dateCol = 'B';
//        $refCol = 'C';
//        $desCol = 'D';
//        $amountCol = 'E';

        //test for amwal
//        $bank = 48;
//        $dateCol = 'A';
//        $refCol = 'C';
//        $desCol = 'D';
//        $amountCol = 'G';

        $csvDateFormat = db_escape($dateFormat);
        $account = get_bank_account($bank);

        $stmt_date_col = "col_" . $dateCol;
        $stmt_ref_col = "col_" . $refCol;
        $stmt_desc_col = "col_" . $desCol;
        $stmt_amount_col = "col_" . $amountCol;
        $stmt_bank_charge_col = "col_" . $bankChargeCol;
        $stmt_vat_col = "col_" . $vatCol;

        /** Uploading the Bank statement csv to the system */
        $dir = getcwd().'/reconcile';

        $fileName = "bank_statement" . rand(10, 100) . "." . $ext;
        move_uploaded_file($tmpName, $dir . "/$fileName");
        $csv_path = $dir.'/'."$fileName";

        $letters_array=[];
        foreach( range('A', 'Z') as $elements) {
            $letters_array[]=$elements;
        }
        $letters_array_key = array_flip($letters_array);
        $dateLength = strlen(date($selectedDateFormat));

        /** DELETE older entries in the table */
        $sql = "TRUNCATE TABLE 0_bank_statement_csv";
        db_query($sql);

        $reader = $ext == 'csv' ? new Reader\Csv() : new Reader\Xlsx();
        $spreadsheet = $reader->load($csv_path);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();

        if(!empty($sheetData)) {
            foreach($sheetData as $Row) {
                $dateColIndex = $letters_array_key[$dateCol];
                $date = substr($Row[$dateColIndex], 0, $dateLength);
                if (!Carbon::canBeCreatedFromFormat($date, $selectedDateFormat)) {
                    continue;
                }

                $processedRows++;
                $TOTALCOLUMNS = sizeof($Row);
                $columns='';
                $values='';

                for($i=0; $i <= $TOTALCOLUMNS - 1; $i++)
                {
                    if($i == $dateColIndex) {
                        $Row[$i] = Carbon::createFromFormat($selectedDateFormat, $date)->format('d-m-Y');
                    }

                    $columns.='col_'.$letters_array[$i].',';
                    $values.=db_escape($Row[$i]).',';
                }

                $qry="INSERT INTO 0_bank_statement_csv (";
                $qry.=rtrim($columns, ',').')';
                $qry.=" values (";
                $qry.=rtrim($values, ',').')';
                db_query($qry);
            }

            if (!$processedRows) {
                return AxisPro::SendResponse(
                    [
                        'msg' => "Data could not be parsed",
                        'status' => 'FAIL'
                    ]
                );
            }
        }

        unlink($csv_path);
        unset($_POST);

        /** FOR CBD */
        $amount_col_update_sql = "UPDATE 0_bank_statement_csv SET $stmt_amount_col = ROUND(";
        $amount_col_update_sql .= "CAST(REPLACE($stmt_amount_col, ',', '') AS DECIMAL(18,2))";
        if (!empty($bank_charge_col))
            $amount_col_update_sql .= "+CAST(REPLACE($stmt_bank_charge_col, ',', '') AS DECIMAL(18,2))";
        if (!empty($vat_col))
            $amount_col_update_sql .= "+CAST(REPLACE($stmt_vat_col, ',', '') AS DECIMAL(18,2))";
        $amount_col_update_sql .= ",2)";

        $result = db_query($amount_col_update_sql);

        /** Set Amount Col  - SUM of Amount,BankCharge and VAT columns*/
//        $stmt_amount_col = "col_I";

        $centerCardAccounts = array_unique(array_filter(array_merge(
            [],
            explode(',', pref('axispro.center_card_accounts')),
            ...Dimension::query()
                ->pluck('center_card_accounts')
                ->map(function ($v) { return explode(',', $v); })
                ->toArray()
        )));
        if (in_array($account, $centerCardAccounts)) {
            /** Consider reference cols as col_H, If transaction reference is in the description field of bank statement.
             * Eg: NOQODI Bank Statement
             */
            // $stmt_ref_col = 'col_E';

            /** Updating transaction references for columns do not contains it. eg: 3.00,0.15 columns in NOQODI */
            $sql = "UPDATE 0_bank_statement_csv t1
                    INNER JOIN 0_bank_statement_csv t2 ON t2.$stmt_ref_col = t1.$stmt_ref_col 
                    SET t1.$stmt_desc_col = t2.$stmt_desc_col";

            $result = db_query($sql);

            /**  Extract Transaction ID from the description and store it to col_H */
            $sql = "UPDATE 0_bank_statement_csv SET $stmt_ref_col = 
                    TRIM(BOTH ' 'FROM SUBSTRING_INDEX(SUBSTRING_INDEX($stmt_desc_col,'Ref',-2), ', ', 1))";

            $result = db_query($sql);
        }

        /** DELETE older entries in the table */
        $result = db_query("TRUNCATE TABLE 0_reconcile_result");


        /** Casting comma separated varchar to decimal  */
        $stmt_amount_col = "CAST(REPLACE($stmt_amount_col, ',', '') as DECIMAL(18,2))";

        $joinOnCol = $isBasedOnApplicationId ? 'application_id' : 'transaction_id';
        $trans_delay = $account['dflt_trans_delay'] ?: 10;
        $trans_from_date = Carbon::parse($from_date)->subDays($trans_delay)->toDateString();
        $trans_till_date = Carbon::parse($to_date)->addDays($trans_delay)->toDateString();

        $stmt_table = (
            "(
                SELECT
                    {$stmt_date_col} as tran_date,
                    {$stmt_amount_col} as amount,
                    {$stmt_ref_col} as trans_id,
                    ROW_NUMBER() OVER (PARTITION BY {$stmt_date_col}, {$stmt_ref_col}, {$stmt_amount_col} ORDER BY {$stmt_date_col}, {$stmt_ref_col}, {$stmt_amount_col}) as seq_no
                FROM 0_bank_statement_csv
                ORDER BY
                    {$stmt_date_col},
                    {$stmt_ref_col},
                    {$stmt_amount_col}
            )"
        );

        $trans_table = (
            "(
                SELECT
                    *,
                    ROW_NUMBER() OVER (PARTITION BY `type`, type_no, trans_id, amount ORDER BY `type`, type_no, trans_id, amount) as seq_no
                FROM (
                    SELECT
                        gt.`type`,
                        gt.type_no,
                        gt.tran_date,
                        SUM(ABS(gt.amount)) AS amount,
                        ref.reference,
                        gt.line_reference,
                        gt.{$joinOnCol} as trans_id
                    FROM 0_gl_trans as gt
                    LEFT JOIN 0_refs as ref ON
                        ref.`type` = gt.`type`
                        and ref.id = gt.type_no
                    WHERE
                        gt.amount <> 0
                        AND gt.account = '{$account['account_code']}'
                        AND gt.tran_date >= '{$trans_from_date}'
                        AND gt.tran_date <= '{$trans_till_date}'
                    GROUP BY
                        gt.`type`,
                        gt.type_no,
                        gt.line_reference,
                        gt.{$joinOnCol},
                        IF(gt.memo_ in ('Govt.Fee', 'VAT for Bank service charge', 'Bank service charge'), 0, gt.counter)
                ) as t
                ORDER BY
                    `type`,
                    type_no,
                    trans_id,
                    amount
            )"
        );

        $sql = (
            "INSERT INTO 0_reconcile_result (
                sw_date,
                bank_date,
                sw_amount,
                bank_amount,
                transaction_,
                transaction_bnk,
                trans_no,
                trans_type,
                invoice_no
            )
            SELECT
                sw.tran_date as sw_date,
                stmt.tran_date as bank_date,
                sw.amount as sw_amount,
                stmt.amount as bank_amount,
                sw.trans_id as transaction_,
                stmt.trans_id as transaction_bnk,
                sw.type_no as trans_no,
                sw.`type` as trans_type,
                sw.reference as invoice_no
            FROM 
                {$stmt_table} as stmt
            LEFT JOIN 
                {$trans_table} as sw ON
                sw.trans_id = stmt.trans_id
                AND sw.seq_no = stmt.seq_no
                AND sw.trans_id != ''
            
            UNION ALL
            
            SELECT
                sw.tran_date as sw_date,
                stmt.tran_date as bank_date,
                sw.amount as sw_amount,
                stmt.amount as bank_amount,
                sw.trans_id as transaction_,
                stmt.trans_id as transaction_bnk,
                sw.type_no as trans_no,
                sw.`type` as trans_type,
                sw.reference as invoice_no
            FROM 
                {$trans_table} as sw
            LEFT JOIN 
                {$stmt_table} as stmt ON
                sw.trans_id = stmt.trans_id
                AND sw.seq_no = stmt.seq_no
                AND sw.trans_id != ''
            WHERE
                sw.tran_date >= '{$from_date}'
                AND sw.tran_date <= '{$to_date}'
                AND stmt.trans_id IS NULL"
        );

        db_query($sql);

        return AxisPro::SendResponse(['msg' => "Click OK to view the result", 'status' => 'OK'], $format);
    }



    public function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * @param $filters
     * @return string
     * Prepare the where condition for auto reconciled result
     */
    public function prepareAutoReconcileQueryWhere($filters)
    {

        $where = "";
        $filter_result = $filters["fl_status"];

        if ($filter_result == 'show_reconciled') {
            $where .= " AND (r.bank_amount = r.sw_amount AND r.transaction_bnk = r.transaction_)";
        }

        if ($filter_result == 'show_not_reconciled') {
            $where .= " AND( r.bank_amount != r.sw_amount AND r.transaction_bnk = r.transaction_)";
        }

        if ($filter_result == 'show_ex_bank_entries') {
            $where .= " AND (!isnull(nullif(r.transaction_bnk, '')) AND isnull(nullif(r.transaction_, '')))";
        }

        if ($filter_result == 'show_ex_sys_entries') {
            $where .= " AND (isnull(nullif(r.transaction_bnk, '')) AND !isnull(nullif(r.transaction_, '')))";
        }

        return $where;

    }

    /**
     * @param $filters
     * @return string
     * Prepare the query for auto reconciled result
     */
    public function prepareAutoReconcileQuery($filters)
    {

        $where = $this->prepareAutoReconcileQueryWhere($filters);

        $query = "SELECT r.* FROM 0_reconcile_result r
        LEFT JOIN 0_voided v on v.id=r.trans_no AND v.type=r.trans_type 
        WHERE v.id IS null $where";

        return $query;

    }

    /**
     * @param $sql
     * @return array
     * Preparing the reconciliation result
     */
    public function prepareReconciledResult($sql)
    {

        $result = db_query($sql);
        $report = [];
        while ($myrow = db_fetch_assoc($result)) {

            $myrow["status"] = "";

            if ($myrow["bank_amount"] == $myrow["sw_amount"]) {
                $myrow["status"] = "Reconciled";
            }

            if ($myrow["bank_amount"] != $myrow["sw_amount"]) {
               $myrow["status"] = "Not Reconciled";
            }

            if (!empty($myrow["transaction_bnk"]) && empty($myrow["transaction_"])) {
               $myrow["status"] = "Extra Entry in Bank";
            }

            if (empty($myrow["transaction_bnk"]) && !empty($myrow["transaction_"])) {
               $myrow["status"] = "Extra Entry in System";
            }

            if (empty($myrow["sw_amount"])) {
                $myrow["sw_amount"] = 0;
            }

            if (empty($myrow["bank_amount"])) {
                $myrow["bank_amount"] = 0;
            }

            $myrow["diff"] = abs($myrow["sw_amount"] - $myrow["bank_amount"]);

            if (preg_match('/^\d+$/', $myrow['transaction_'])) {
                $myrow['transaction_'] = "" . $myrow['transaction_'];
            }

            $report[] = $myrow;
        }

        return $report;

    }

    /**
     * @param string $format
     * @return mixed
     * End point for reconciliation result
     */
    public function getReconciledResult($format = "json")
    {

        $sql = $this->prepareAutoReconcileQuery($_POST);

        $total_count_sql = "select count(*) as cnt from ($sql) as tmpTable";
        $total_count_exec = db_fetch_assoc(db_query($total_count_sql));
        $total_count = $total_count_exec['cnt'];

        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $perPage = 200;
        $offset = ($page * $perPage) - $perPage;

        $sql = $sql . " LIMIT $perPage OFFSET $offset";

        $report = $this->prepareReconciledResult($sql);


        return AxisPro::SendResponse(
            [
                'rep' => $report,
                'total_rows' => $total_count,
                'pagination_link' => AxisPro::paginate($total_count),
                'aggregates' => $total_count_exec,]
        );


    }

    /**
     * @param $bank
     * @return mixed
     * Get gl account name by bank id
     */
    public function getGLBankName($bank)
    {

        return get_bank_account($bank)["bank_account_name"];
    }

    /**
     * Export reconciliation result to CSV
     */
    public function exportAutoReconciledResult()
    {

        $where = $this->prepareAutoReconcileQueryWhere($_POST);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=DevelopersData.csv');

        $output = fopen("php://output", "w");
        fputcsv($output, array('S/W DATE', 'BANK DATE', 'INVOICE NO', 'S/W TRANSACTION ID', 'BANK TRANSACTION ID', 'S/W AMOUNT', 'BANK AMOUNT', 'STATUS', 'DIFFERENCE'));

        $sql = "SELECT
            r.sw_date,
            r.bank_date,
            r.invoice_no,
            r.transaction_,
            r.transaction_bnk,
            r.sw_amount,
            r.bank_amount
        FROM 0_reconcile_result r
        LEFT JOIN 0_voided v ON v.id = r.trans_no AND v.type = r.trans_type 
        WHERE v.id IS null $where";

        $report = $this->prepareReconciledResult($sql);

        foreach ($report as $myrow) {
            fputcsv($output, $myrow);
        }

        fclose($output);
    }

    /**
     * @param string $format
     * @return array|mixed
     * Get all bank accounts
     */
    public function fetch_all_bank_accounts($format = "json")
    {

        try {

            global $bank_account_types;

            $account_type = $_GET['acc_type'];
            $sql = "SELECT * FROM 0_bank_accounts";

            if (!empty($account_type)) {
                $sql .= " WHERE account_type in ($account_type)";
            }

            $result = db_query($sql);
            $return_result = [];
            while ($myrow = db_fetch_assoc($result)) {
                $myrow['bank_account_type_name'] = $bank_account_types[$myrow['account_type']];
                $return_result[] = $myrow;
            }

            return AxisPro::SendResponse($return_result, $format);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }

    /**
     * @return array|mixed
     * Save bank account | ADD or EDIT
     */
    public function save_bank_account()
    {
        try {
            $edit_id = $_POST['edit_id'];
            $bank_account_name = $_POST['bank_account_name'];
            $account_type = $_POST['bank_account_type'];
            $bank_account_gl_code = $_POST['bank_account_gl_code'];
            $bank_charge_act = $_POST['bank_charges_account'];
            $bank_name = $_POST['bank_name'];
            $bank_account_number = $_POST['bank_account_number'];
            $bank_address = $_POST['excel_columns'];
            $dflt_bank_chrg = $_POST['default_bank_charge'];
            $dflt_curr_act = $_POST['default_account'];
            $inactive = $_POST['inactive_status'];

            $array = [
                "account_code" => db_escape($bank_account_gl_code),
                "bank_account_name" => db_escape($bank_account_name),
                "account_type" => db_escape($account_type),
                "bank_charge_act" => db_escape($bank_charge_act),
                "bank_name" => db_escape($bank_name),
                "bank_account_number" => db_escape($bank_account_number),
                "bank_address" => db_escape($bank_address),
                "dflt_bank_chrg" => db_escape($dflt_bank_chrg),
                "dflt_curr_act" => db_escape($dflt_curr_act),
                "inactive" => db_escape($inactive),
            ];

            $response = [];

            if (!empty($bank_account_name)
                && isset($account_type)
                && $account_type != ""
                && !empty($bank_account_gl_code)
                && isset($inactive)
            ) {

                if (empty($edit_id)) {

                    $count = "SELECT * FROM 0_bank_accounts WHERE account_code='" . $bank_account_gl_code . "'";

                    $result = db_query($count);
                    $count = db_num_rows($result);

                    if ($count > 0) {
                        $response['status'] = 'FAIL';
                        $response['msg'] = 'Bank Account for select GL Account Already Exists !';
                        return AxisPro::SendResponse($response);
                    }

                    db_insert('0_bank_accounts', $array);
                    $response['status'] = 'OK';
                    $response['msg'] = 'Bank Account Saved';
                } else {
                    db_update('0_bank_accounts', $array, ["id=$edit_id"]);
                    $response['status'] = 'OK';
                    $response['msg'] = 'Bank Account Updated';
                }

            } else {
                $response['status'] = 'FAIL';
                $response['msg'] = 'Fill all the required fields';
            }

            return AxisPro::SendResponse($response);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }

    /**
     * @return array|mixed
     * Delete bank account
     */
    public function delete_bank_account()
    {
        try {
            $id = $_POST['id'];
            $account = $_POST['account'];
            if (key_in_foreign_table($account, 'gl_trans', 'account')) {
                return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'This Bank Account is used in the system, can not be deleted.']);
            } else {
                $sql = "DELETE FROM 0_bank_accounts WHERE id=$id";
                db_query($sql);
                return AxisPro::SendResponse(['status' => 'OK', 'msg' => 'Your report has been deleted.']);
            }

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }

    /**
     * @return mixed
     * Get bank account info by bank id
     */
    public function getBankAccount()
    {

        $id = $_GET["id"];
        $sql = "select * from 0_bank_accounts where id=$id";

        $result = db_fetch(db_query($sql));
        return AxisPro::SendResponse($result);

    }

}