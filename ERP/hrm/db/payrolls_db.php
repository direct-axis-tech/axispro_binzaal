<?php

use App\Models\Accounting\JournalTransaction;
use App\Models\Entity;
use App\Models\Hr\Employee;
use App\Models\Hr\PayElement;
use App\Models\Hr\Payroll;
use App\Models\System\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Retrieve the list of all the payrolls
 *
 * @param array $filters
 * @return mysqli_result
 */
function getPayrolls($filters = []) {
    $where = '1 = 1';

    if (!empty($filters['is_processed'])) {
        $filters['is_processed'] = (int)(bool)$filters['is_processed'];

        $where .= " AND payroll.is_processed = '{$filters['is_processed']}'";
    }

    if (!empty($filters['year'])) {
        $where .= " AND payroll.`year` = {$filters['year']}";
    }

    if (!empty($filters['month'])) {
        $where .= " AND payroll.`month` = {$filters['month']}";
    }
    
    if (!empty($filters['payroll_id'])) {
        $where .= " AND payroll.`id` = {$filters['payroll_id']}";
    }

    return db_query(
        "SELECT
            payroll.*,
            CONCAT(payroll.year, '/', payroll.month) custom_id
        FROM `0_payrolls` payroll
        WHERE {$where}",
        "Could not retreive the list of payrolls"
    );
}

/**
 * Retreive the payrolls contstrained by the filters and key it by the id
 *
 * @param array $filters
 * @return array
 */
function getPayrollsKeyedByID($filters = []) {
    $mysqliResult = getPayrolls($filters);

    $payrolls = [];
    while ($payroll = $mysqliResult->fetch_assoc()) {
        $payrolls[$payroll['id']] = $payroll;
    }

    return $payrolls;
}

/**
 * Retrieves the payroll of the specified month
 * 
 * @param int $year
 * @param int $month The number representing the month
 * 
 * @return array|null
 */
function getPayrollOfMonth($year, $month) {
    $filters = compact('year', 'month');
    $mysqliResult = getPayrolls($filters);
    
    return $mysqliResult->fetch_assoc();
}

/**
 * Get the payroll with the given id
 *
 * @param array $id
 * @return array|null
 */
function getPayroll($id) {
    $mysqliResult = getPayrolls([
        "payroll_id" => $id
    ]);

    return $mysqliResult->fetch_assoc();
}

/**
 * Insert a payroll master data in the database.
 * 
 * @param int $year,
 * @param int $month 1: Jan, 2: Feb etc.
 * @param string $from The begning date for the payroll period
 * @param string $till The ending date for the payroll period
 * @param int $work_days The number of days used as standard for calculations
 * 
 * @return bool
 */
function insertPayroll($year, $month, $from, $till, $work_days) {
    $sql = (
        "INSERT INTO `0_payrolls` 
        (`year`, `month`, `from`, `till`, `work_days`)
        VALUES
        ('{$year}', '{$month}', '{$from}', '{$till}', '{$work_days}')"
    );

    return db_query($sql, "Could not insert payroll");
}

/**
 * update a payroll master data in the database.
 * 
 * @param int $id,
 * @param int $year,
 * @param int $month 1: Jan, 2: Feb etc.
 * @param string $from The begning date for the payroll period
 * @param string $till The ending date for the payroll period
 * @param int $work_days The number of days used as standard for calculations
 * 
 * @return bool
 */
function updatePayroll($id, $year, $month, $from, $till, $work_days) {
    $sql = (
        "UPDATE `0_payrolls`
        SET
            `year` = '{$year}',
            `month` = '{$month}',
            `from` = '{$from}',
            `till` = '{$till}',
            `work_days` = '{$work_days}'
        WHERE id = '{$id}'"
    );

    return db_query($sql, "Could not insert payroll");
}

/**
 * Insert or update a payroll master data in the database.
 * 
 * @param int $year,
 * @param int $month 1: Jan, 2: Feb etc.
 * @param string $from The beginning date for the payroll period
 * @param string $till The ending date for the payroll period
 * @param int $work_days The number of days used as standard for calculations
 * 
 * @return bool
 */
function insertOrUpdatePayroll($year, $month, $from, $till, $work_days) {
    if ($payroll = getPayrollOfMonth($year, $month)) {
        return updatePayroll($payroll['id'], $year, $month, $from, $till, $work_days);
    }

    else {
        return insertPayroll($year, $month, $from, $till, $work_days);
    }
}

/**
 * Checks if the payroll for the month is in the database whether fully or partially.
 * 
 * @param int $year
 * @param int $month The number representing the the month. 1-Jan, 2-Feb etc
 * 
 * @return bool
 */
function payrollExistsForMonth($year, $month) {
    $sql = (
        "SELECT
            proll.id
        FROM `0_payrolls` proll
        WHERE
            pslip.year = {$year}
            AND pslip.month = {$month}"
    );

    return (bool)db_query($sql, "Could not check if the payroll exists")->num_rows;
}

/**
 * Checks if payroll for the month is already processed
 * 
 * @param int $year
 * @param int $month The number representing the the month. 1-Jan, 2-Feb etc
 * 
 * @return bool
 */
function isPayrollProcessedForMonth($year, $month) {
    $sql = (
        "SELECT
            is_processed
        FROM
            `0_payrolls`
        WHERE
            `year` = {$year}
            AND `month` = {$month}"
    );

    $payroll = db_query($sql, "Could not check if the payroll is processed")->fetch_assoc();
    return ($payroll && $payroll['is_processed']);
}

/**
 * Process the given payroll
 *
 * @param int $payrollId
 * @return int
 */
function processPayroll($payrollId) {
    $authUser = user_id();
    $currentTimestamp = date(DB_DATETIME_FORMAT);

    db_query(
        "UPDATE
            `0_payrolls` proll
        SET
            proll.`is_processed` = 1,
            proll.`processed_by` = {$authUser},
            proll.`processed_at` = '{$currentTimestamp}'
        WHERE proll.`id` = '{$payrollId}'",
        "Could not process the payroll"
    );

    return db_num_affected_rows();
}

function postGlTransactionsForPayroll($payrollId) {
    global $Refs;

    begin_transaction();
    $payroll = Payroll::find($payrollId);
    $salaryPayableAccount = pref('hr.default_salary_payable_account');
    $commissionPayableAccount = pref('axispro.emp_commission_payable_act');
    $commissionExpenseAccount = pref('axispro.emp_commission_expense_act');
    $payElements = PayElement::all()->keyBy('id');
    $commissionElementId = pref('hr.commission_el');
    $basicSalElementId = pref('hr.basic_pay_el');
    $payElements[$commissionElementId]->account_code = $commissionPayableAccount;
    $oTransDate = Carbon::parse($payroll->till)->endOfMonth();
    $transDate = $oTransDate->format(dateformat());
    $pensionElementId = pref('hr.pension_el');
    $users = User::where('type', Entity::EMPLOYEE)->pluck('id', 'employee_id')->toArray();

    // Custom pay element for pension
    $pensionExpenseElId = -1;
    $payElements[$pensionExpenseElId] = PayElement::make([
        'id' => $pensionExpenseElId,
        'name' => 'Pension Expense',
        'account_code' => pref('hr.pension_expense_account'),
        'type' => PayElement::TYPE_ALLOWANCE
    ]);
    
    $subLedgers = [];
    foreach ($payElements as $payElement) {
        $subLedgers[$payElement->id] = is_subledger_account($payElement->account_code);
    }

    $payslipElementsGroupedByPayslipId = DB::table('0_payrolls as payroll')
        ->join('0_payslips as slip', 'slip.payroll_id', 'payroll.id')
        ->join('0_payslip_elements as slipEl', 'slipEl.payslip_id', 'slip.id')
        ->join('0_pay_elements as payEl', 'payEl.id', 'slipEl.pay_element_id')
        ->select('slip.*', 'slipEl.pay_element_id', 'slipEl.amount')
        ->selectRaw("IF(slipEl.pay_element_id in (".implode(',', array_keys(array_filter($subLedgers)))."), payEl.name, '') as memo")
        ->where('payroll.id', $payrollId)
        ->get()
        ->groupBy('id');

    $cart = new items_cart(JournalTransaction::PAYROLL);
    $cart->tran_date = $cart->doc_date = $cart->event_date = $transDate;
    $cart->reference = $Refs->get_next(JournalTransaction::PAYROLL, null, $cart->tran_date, true);
    $cart->memo_ = 'Salary for the month of '.$oTransDate->format('M');
    
    if (!is_date_in_fiscalyear($cart->tran_date)) {
        $cart->tran_date = end_fiscalyear();
    }

    foreach ($payslipElementsGroupedByPayslipId as $payslipElements) {
        $salaryPayable = 0;
        $payslip = $payslipElements->first();
        $payslipElements = $payslipElements->toArray();

        // Append the contribution of employer, if this employee has pension
        if ($payslip->pension_employer_share > 0) {
            // insert the pension employer share right after the pension
            // employee share if it exists
            $key = array_search($pensionElementId, array_column($payslipElements, 'pay_element_id'));
            array_splice($payslipElements, $key+1, 0, [(object)[
                'pay_element_id' => $pensionElementId,
                'amount' => $payslip->pension_employer_share,
                'memo' => 'Pension Employer Share'  
            ]]);
            
            // insert the pension expense at the beginning
            $payslipElements = array_merge(
                [(object)[
                    'pay_element_id' => $pensionExpenseElId,
                    'amount' => $payslip->pension_employer_share,
                    'memo' => ''
                ]],
                $payslipElements
            );
        }

        if ($payslip->expense_offset > 0) {
            // insert the expense offset
            array_splice(
                $payslipElements,
                intval(array_search($commissionElementId, array_column($payslipElements, 'pay_element_id'))),
                0,
                [
                    (object)[
                        'pay_element_id' => $basicSalElementId,
                        'amount' => -$payslip->expense_offset,
                        'memo' => ''  
                    ],
                    (object)[
                        'pay_element_id' => $commissionElementId,
                        'amount' => $payslip->expense_offset,
                        'memo' => 'Salary Offset'
                    ]
                ]
            );
        }

        foreach ($payslipElements as $payslipElement) {
            $payElementId = $payslipElement->pay_element_id;
            $payElement = $payElements->get($payElementId);
            $account = $payElement->account_code;
            $personId = $payslip->employee_id;

            // Handle the special cases for commission payable
            if ($account == $commissionPayableAccount) {
                $personId = $users[$payslip->employee_id] ?? null;
                if (!$personId) {
                    $account = $commissionExpenseAccount;
                }
            }

            if (!$account) {
                return ['error' => "Account for pay element {$payElement->name} is not set"];
            }

            $previousGl = Arr::first($cart->gl_items, function ($item) use ($account) {
                return $item->code_id == $account;
            });
            
            $amount = $payElement->type * $payslipElement->amount;
            $salaryPayable += $amount;
            if (empty($subLedgers[$payElementId]) && $previousGl) {
                $previousGl->amount += $amount;
            }

            else {
                $cart->add_gl_item(
                    $account,
                    0,
                    0,
                    $amount,
                    $payslipElement->memo,
                    null,
                    $personId
                );
            }
        }

        if (floatcmp($salaryPayable, $payslip->net_salary)) {
            $employee = Employee::find($payslip->employee_id);
            return ['error' => "Unexpected value encountered for the salary of employee '{$employee->formatted_name}'"];
        }

        $cart->add_gl_item(
            $salaryPayableAccount,
            0,
            0,
            -$salaryPayable,
            $cart->memo_,
            null,
            $payslip->employee_id
        );
    }

    // Sort the gl entries by the order expenses, subledger
    usort($cart->gl_items, function ($itemA, $itemB) use ($salaryPayableAccount) {
        $sort = intval(!empty($itemA->person_type_id)) <=> intval(!empty($itemB->person_type_id));

        if (!$sort) {
            $sort = Str::after($itemA->person_name, '- ') <=> Str::after($itemB->person_name, '- ');
        }

        if (!$sort) {
            $sort = intval($itemA->amount < 0) <=> intval($itemB->amount < 0);
        }
        
        if (!$sort) {
            $sort = intval($itemA->code_id == $salaryPayableAccount) <=> intval($itemB->code_id == $salaryPayableAccount);
        }

        return $sort;
    });

    $transNo = write_journal_entries($cart);
    db_query(
        'UPDATE `0_payrolls` SET'
            . ' trans_type = '.db_escape(JournalTransaction::PAYROLL).','
            . ' trans_no = '.db_escape($transNo).','
            . ' trans_ref = '.db_escape($cart->reference).','
            . ' journalized_at = '.quote(date(DB_DATETIME_FORMAT))
        . ' WHERE id = '.db_escape($payrollId),
        "Could not set the payroll as journalized"
    );
    commit_transaction();

    return ['success' => true, 'trans_no' => $transNo];
}