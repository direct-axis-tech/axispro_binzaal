<?php

use App\Models\Accounting\JournalTransaction;
use App\Models\Hr\Employee;
use Carbon\Carbon;

require_once __DIR__ . "/../db/employees_db.php";
require_once __DIR__ . "/../db/emp_salary_details_db.php";
require_once __DIR__ . "/../../includes/ui/items_cart.inc";

class LeaveAccrualHelpers {
    /**
     * Retrieve the validated inputs
     *
     * @return array
     */
    public static function getValidatedInputs()
    {
        $userDateFormat = dateformat();
        $filters = [
            'employee_ids' => [],
            'as_of_date' => date(DB_DATE_FORMAT),
            'trans_date' => Carbon::now()->endOfMonth()->format(DB_DATE_FORMAT),
            'memo' => ''
        ];

        if (
            !empty($_POST['as_of_date'])
            && ($dt_as_of = DateTime::createFromFormat($userDateFormat, $_POST['as_of_date']))
            && $dt_as_of->format($userDateFormat) == $_POST['as_of_date']
        ) {
            $filters['as_of_date'] = $dt_as_of->format(DB_DATE_FORMAT);
        }

        if (
            !empty($_POST['trans_date'])
            && ($dt_trans = DateTime::createFromFormat($userDateFormat, $_POST['trans_date']))
            && $dt_trans->format($userDateFormat) == $_POST['trans_date']
        ) {
            $filters['trans_date'] = $dt_trans->format(DB_DATE_FORMAT);
        }

        if (
            !empty($_POST['employee_ids'])
            && is_array($_POST['employee_ids'])
            && count($_POST['employee_ids']) === Employee::whereIn('id', $_POST['employee_ids'])->count()
        ) {
            $filters['employee_ids'] = $_POST['employee_ids'];
        }

        if (
            !empty($_POST['memo'])
            && preg_match('/^[\p{L}\p{M}\p{N}_\-\/,\. ]+$/u', $_POST['memo'])
        ) {
            $filters['memo'] = $_POST['memo'];
        }

        return $filters;
    }

    /**
     * Handles the HTTP request for showing details
     *
     * @param array $inputs
     * @return void
     */
    public static function handleShowDetailsRequest($inputs)
    {
        $accruals = self::getLeaveAccruals($inputs['as_of_date'], $inputs['employee_ids']);
        $total = [
            'accumulated_amount' => array_sum(array_column($accruals, 'accumulated_amount')),
            'accrued_amount' => array_sum(array_column($accruals, 'accrued_amount')),
            'this_postings' => array_sum(array_column($accruals, 'this_postings')),
        ];

        echo json_encode([
            'status' => 200,
            'data' => compact('accruals', 'total')
        ]);
        exit();
    }

    /**
     * Handles the HTTP request for posting GL
     *
     * @param array $inputs
     * @return void
     */
    public static function handlePostGLRequest($inputs)
    {
        $trans = self::postLeaveAccruals(
            $inputs['as_of_date'],
            $inputs['trans_date'],
            $inputs['memo'],
            $inputs['employee_ids']
        );

        $responseCode = empty($trans) ? 400 : 200;
        http_response_code($responseCode);
        echo json_encode([
            'status' => $responseCode,
            'data' => $trans
        ]);
        exit();
    }

    /**
     * Calculate the leave accruals
     *
     * @param string $asOfDate
     * @param array $employeeIds
     * @return array
     */
    public static function getLeaveAccruals($asOfDate, $employeeIds)
    {
        $accumulation = self::getAccumulatedLeaveAccrualsKeyedById(
            $asOfDate,
            $employeeIds
        );
        $workDays = pref('hr.standard_days', 30);
        $employees = getEmployeesKeyedById([
            'employee_id' => $employeeIds,
            'joined_on_or_before' => $asOfDate
        ]);
        $salaryDetails = getSalaryDetailsGroupedBySalaryId([
            "salary_id" => array_column($employees, 'salary_id') ?: -1
        ]);

        $dec = user_price_dec();
        $leaveAccruals = [];
        foreach ($employees as $employeeId => $employee) {
            $leaveSalary = HRPolicyHelpers::getAnnualLeaveSalary($employee, $salaryDetails[$employee['salary_id']]);
            $availableLeaveBalance = data_get(
                HRPolicyHelpers::getLeaveBalance(
                    $employeeId,
                    LT_ANNUAL,
                    $employee['date_of_join'],
                    $asOfDate
                ),
                'balanceLeaves'
            );
            $accumulatedAmount = $accumulation[$employeeId]['amount'] ?? 0;
            $lastAccrualOn = $accumulation[$employeeId]['last_accrual'] ?? '0001-01-01';
            $currentAccrualAmount = round2($leaveSalary / $workDays * $availableLeaveBalance, $dec);
            $difference = round2($currentAccrualAmount - $accumulatedAmount, $dec);
            $leaveAccruals[] = [
                'employee_id' => $employeeId,
                'employee_ref' => $employee['emp_ref'],
                'employee_name' => $employee['name'],
                'formatted_name' => $employee['formatted_name'],
                'date_of_join' => $employee['date_of_join'],
                'monthly_salary' => $employee['monthly_salary'],
                'leave_salary' => $leaveSalary,
                'last_accrual_on' => $lastAccrualOn,
                'work_days' => $workDays,
                'as_of_date' => $asOfDate,
                'available_leaves' => $availableLeaveBalance,
                'accumulated_amount' => $accumulatedAmount,
                'accrued_amount' => $currentAccrualAmount,
                'this_postings' => $difference
            ];
        }
        
        return $leaveAccruals;
    }

    /**
     * Post the leave accruals
     *
     * @param string $asOfDate
     * @param string $transDate
     * @param string $memo
     * @param array $employeeIds
     * 
     * @return mixed
     */
    public static function postLeaveAccruals($asOfDate, $transDate, $memo = null, $employeeIds = [])
    {
        if (
            empty($accruals = self::getLeaveAccruals($asOfDate, $employeeIds))
            || empty(pref('hr.leave_accrual_payable_account'))
            || empty(pref('hr.leave_accrual_expense_account'))
        ) {
            return false;
        }

        begin_transaction();
        $transType = JournalTransaction::JOURNAL;
        $cart = new items_cart($transType);
        $cart->event_date = sql2date($asOfDate);
        $cart->tran_date = $cart->doc_date = sql2date($transDate);
        $cart->reference = $transRef = $GLOBALS['Refs']->get_next($transType, null, $cart->tran_date, true);
        $cart->memo_ = $memo ?: 'Leave accrual as of '. $cart->event_date;
        
        if (!is_date_in_fiscalyear($cart->tran_date)) {
            $cart->tran_date = end_fiscalyear();
        }
        
        $payableAccount = pref('hr.leave_accrual_payable_account');
        $expenseAccount = pref('hr.leave_accrual_expense_account');
        $totalExpense = round2(array_sum(array_column($accruals, 'this_postings')), user_price_dec());

        if ($totalExpense != 0) {
            $cart->add_gl_item(
                $expenseAccount,
                0,
                0,
                $totalExpense,
                $cart->memo_,
                null
            );
        }
        foreach ($accruals as $accrual) {
            if ($accrual['this_postings'] != 0) {
                $cart->add_gl_item(
                    $payableAccount,
                    0,
                    0,
                    -$accrual['this_postings'],
                    $cart->memo_,
                    null,
                    $accrual['employee_id']
                );   
            }
        }

        if (empty($cart->gl_items)) {
            return false;
        }

        $transNo = write_journal_entries($cart);
        commit_transaction();
        
        return [
            'type' => $transType,
            'trans_no' => $transNo,
            'reference' => $transRef,
            'view_link' => erp_url("/ERP/gl/view/gl_trans_view.php", [
                "type_id" => $transType,
                "trans_no" => $transNo
            ])
        ];
    }

    /**
     * Get accumulated leave accruals as of date
     *
     * @param string $asOfDate
     * @param array $employeeIds
     * @return array
     */
    public static function getAccumulatedLeaveAccrualsKeyedById($asOfDate, $employeeIds)
    {
        if (empty($accrualPayableAccount = pref('hr.leave_accrual_payable_account'))) {
            throw new Exception('Accrual accounts not configured');
        }
        
        $conditions = "trans.amount <> 0"
            . " AND trans.account = '{$accrualPayableAccount}'"
            . " AND journal.event_date <= '{$asOfDate}'"
            . " AND trans.person_type_id = ".PT_EMPLOYEE;
        
        if (!empty($employeeIds) && is_array($employeeIds)) {
            $conditions .= " AND trans.person_id IN (".implode(',', $employeeIds).")";
        }

        $sql = (
            "SELECT
                trans.person_type_id,
                trans.person_id,
                min(trans.person_name) as person_name,
                round(ifnull(sum(-1 * trans.amount), 0), 2) as amount,
                ifnull(max(journal.event_date), '0001-01-01') as last_accrual
            FROM `0_gl_trans` trans
            INNER JOIN `0_journal` journal ON
                journal.type = trans.type
                AND journal.trans_no = trans.type_no
            WHERE {$conditions}
            GROUP BY trans.person_type_id, trans.person_id"
        );

        $data = [];
        $result = db_query($sql, 'Could not query for accumulated leave accrual amounts');
        while ($row = db_fetch_assoc($result)) {
            $data[$row['person_id']] = $row;
        }

        return $data;
    }
}