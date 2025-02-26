<?php

use Carbon\Carbon;
use Carbon\CarbonInterface;

require_once $path_to_root . "/hrm/db/employees_db.php";
require_once $path_to_root . "/hrm/db/emp_salary_details_db.php";

class EndOfServiceReportHelpers
{

    /** 
     * Returns the validated user inputs or the default value.
     * 
     * @param $currentEmployee The employee defined for the current user
     * @return array
     */
    public static function getValidatedInputs()
    {
        // defaults
        $filters = [
            "employee_id" => null
        ];

        if (
            isset($_POST['employee_id'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['employee_id']) === 1
        ) {
            $filters['employee_id'] = $_POST['employee_id'];
        }
        return $filters;
    }

    /**
     * Render the endOfService and annual leave salary
     *
     * @param string $employeeId
     * @return string
     */
    public static function renderEndOfService($employeeId)
    {
        $employee = getEmployees([
            "employee_id" => $employeeId,
            "status" => ES_ALL,
            "not_status" => ES_ACTIVE
        ])->fetch_assoc();
        
        $salaryDetails = array_column(
            getSalaryDetails($employee['salary_id'])->fetch_all(MYSQLI_ASSOC),
            'amount',
            'pay_element_id'
        );
        $heldSalaryAmount = data_get(
            db_query(
                "SELECT sum(amount) * -1 as amount FROM 0_emp_trans WHERE employee_id = " . db_escape($employee['id']),
                "Could not query for held salary amount"
            )->fetch_assoc(),
            'amount',
            '0.00'
        );
        $workDays = 30;
        $dec = user_price_dec();
        $result = HRPolicyHelpers::calculateGratuity($employee);
        $noticePeriod = Carbon::parse($employee['cancel_requested_on'])->startOfDay()
            ->diffForHumansWithoutWeeks(
                Carbon::parse($employee['last_working_date'])->startOfDay(),
                CarbonInterface::DIFF_ABSOLUTE,
                false,
                3
            );
        $leaveBalance = data_get(
            HRPolicyHelpers::getLeaveBalance(
                $employee['id'],
                LT_ANNUAL,
                $employee['date_of_join'],
                $employee['last_working_date']
            ),
            'balanceLeaves'
        );
        $leaveBalanceEncashment = round2(
            HRPolicyHelpers::getAnnualLeaveEncashmentSalary($employee, $salaryDetails) / $workDays * $leaveBalance,
            $dec
        );

        ob_start() ?>
        <div>
            <!-- <img src="<?= erp_url('/assets/images/Payslip_header.jpg') ?>" style="width: 100%; height: 180px"> -->

            <table class="w-100 table-sm">
                <thead>
                    <tr style="background-color:rgb(250,240,230);">
                        <th class="border w-50 text-center" colspan="4">
                            <h3>
                                <span style="color:rgb(128,0,0);">
                                    Employee End of Service Report
                                </span>
                            </h3>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border w-50">
                            <table class="w-100">
                                <tbody>
                                    <tr>
                                        <td> ID/Ref. No. </td>
                                        <td><?= $employee['emp_ref'] ?></td>
                                    </tr>
                                    <tr>
                                        <td> <strong> Employee Name </strong> </td>
                                        <td><strong><?= $employee['name'] ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td> Nationality </td>
                                        <td><?= $employee['country'] ?></td>
                                    </tr>
                                    <tr>
                                        <td> Designation </td>
                                        <td><?= $employee['designation_name'] ?></td>
                                    </tr>
                                    <tr>
                                        <td> Employment Status </td>
                                        <td><?= $GLOBALS['employment_statuses'][$employee['status']] ?? 'NA' ?></td>
                                    </tr>
                                    <tr>
                                        <td> Has Pension </td>
                                        <td><?= $employee['has_pension'] ? 'Yes' : 'No'; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td class="border w-50 align-top">
                            <table class="w-100">
                                <tbody>
                                    <tr>
                                        <td> Joining Date </td>
                                        <td><?= sql2date($employee['date_of_join']) ?></td>
                                    </tr>
                                    <tr>
                                        <td> Leaving Date (Requested on) </td>
                                        <td><?= sql2date($employee['cancel_requested_on']) ?></td>
                                    </tr>
                                    <tr>
                                        <td> Last Working Date </td>
                                        <td><?= sql2date($employee['last_working_date']) ?></td>
                                    </tr>
                                    <tr>
                                        <td> Service Period </td>
                                        <td><?= $result['service_period']['for_humans'] ?></td>
                                    </tr>
                                    <tr>
                                        <td> Notice Period </td>
                                        <td> <?= $noticePeriod ?> </td>
                                    </tr>
                                    <tr>
                                        <td> Annual Leave Balance </td>
                                        <td class="text-left"><?= price_format($leaveBalance) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="w-100 table-sm mt-3">
                <thead>
                    <tr class="border w-50" style="background-color:rgb(250,240,230);">
                        <th colspan="2">
                            <h3 style="text-align:center;"><span style="color:rgb(128,0,0);"> Salary Details </span></h3>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border w-50 align-top">
                            <table class="w-100">
                                <tbody>
                                    <tr>
                                        <td> Monthly Salary </td>
                                        <td class="text-right"><?= price_format($employee['monthly_salary']) ?></td>
                                    </tr>
                                    <tr>
                                        <td> Basic Salary </td>
                                        <td class="text-right"><?= price_format($employee['basic_salary']) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td class="border w-50 align-top">
                            <table class="w-100">
                                <tbody>
                                    <tr>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <?php if (!$employee['has_pension']): ?>
                    <tr>
                        <td class="border w-50 align-top">
                            <table class="w-100">
                                <thead>
                                    <tr class="border w-50" style="background-color:rgb(250,240,230);">
                                        <th colspan="2">
                                            <h5 style="text-align:center;"><span style="color:rgb(128,0,0);"> Upto 5 Years of Service </span></h5>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td> Per Year Amount </td>
                                        <td class="text-right"><?= price_format($result['gratuity']['upto_5_years']['per_year']) ?></td>
                                    </tr>
                                    <tr>
                                        <td> Per Month Amount </td>
                                        <td class="text-right"><?= price_format($result['gratuity']['upto_5_years']['per_month']) ?></td>
                                    </tr>
                                    <tr>
                                        <td> Per Day Amount </td>
                                        <td class="text-right"><?= price_format($result['gratuity']['upto_5_years']['per_day']) ?></td>
                                    </tr>
                                    <tr>
                                        <td> Total End of Service Amount </td>
                                        <td class="text-right"><?= price_format($result['gratuity']['upto_5_years']['total']) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td class="border w-50 align-top">
                            <?php if (
                                $result['service_period']['years'] > 5
                                || (
                                    $result['service_period']['years'] == 5
                                    && ($result['service_period']['months'] > 0 || $result['service_period']['days'] > 0)
                                )
                            ): ?>
                            <table class="w-100">
                                <thead>
                                    <tr class="border w-50" style="background-color:rgb(250,240,230);">
                                        <th colspan="2">
                                            <h5 style="text-align:center;"><span style="color:rgb(128,0,0);"> After 5 Years of Service </span></h5>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="h-100">
                                    <tr>
                                        <td> Per Year Amount </td>
                                        <td class="text-right"><?= price_format($result['gratuity']['after_5_years']['per_year']) ?></td>
                                    </tr>
                                    <tr>
                                        <td> Per Month Amount </td>
                                        <td class="text-right"><?= price_format($result['gratuity']['after_5_years']['per_month']) ?></td>
                                    </tr>
                                    <tr>
                                        <td> Per Day Amount </td>
                                        <td class="text-right"><?= price_format($result['gratuity']['after_5_years']['per_day']) ?></td>
                                    </tr>
                                    <tr>
                                        <td> Total End of Service Amount </td>
                                        <td class="text-right"><?= price_format($result['gratuity']['after_5_years']['total']) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="border w-50 align-top" colspan="2">
                            <table class="w-100">
                                <tbody>
                                    <tr>
                                        <td><b> Total Gratuity </b></td>
                                        <td class="text-right"><b><?= price_format($result['gratuity']['total_amount']) ?></b></td>
                                    </tr>
                                    <tr>
                                        <td><b> Excess Gratuity (More than 2 years salary) </b></td>
                                        <td class="text-right"><b><?= price_format($result['gratuity']['excess_amount']) ?></b></td>
                                    </tr>
                                    <tr>
                                        <td><b> Net Gratuity </b></td>
                                        <td class="text-right"><b><?= price_format($result['gratuity']['net_amount']) ?></b></td>
                                    </tr>
                                    <tr>
                                        <td><b> Annual Leave Balance (<?= price_format($leaveBalance) ?>) </b></td>
                                        <td class="text-right"><b><?= price_format($leaveBalanceEncashment) ?></b></td>
                                    </tr>
                                    <tr>
                                        <td><b> Salary on Hold </b></td>
                                        <td class="text-right"><b><?= price_format($heldSalaryAmount) ?></b></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr class="border w-50">
                        <td colspan="2" class="text-center py-2 font-weight-bold">
                            <h4> Net Settlement: <?= price_format(
                                $result['gratuity']['net_amount']
                                + $leaveBalanceEncashment
                                + $heldSalaryAmount
                            ) ?> AED</h4>
                        </td>
                    </tr>
                </tfoot>
            </table>
        <?php $renderedHtml = ob_get_clean();

        return $renderedHtml;
    }

    public static function handlePrintEndOfServiceRequest($renderedHtml, $employeeId)
    {
        try {
            $employee = getEmployees([
                "employee_id" => $employeeId,
                "status" => ES_ALL,
                "not_status" => ES_ACTIVE
            ])->fetch_assoc();

            $mpdf = new \Mpdf\Mpdf([
                "margin_left"     => 15,
                "margin_right"    => 15,
                "margin_top"      => 15,
                "margin_bottom"   => 15,
                "margin_header"   => 15,
                "margin_footer"   => 11
            ]);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetTitle('Employee End of Service Calculation');

            $footer =
                '<div>
                <table class="w-100 table-sm mt-3">
                    <tbody>
                        <tr>
                            <td class="w-50 align-top">
                                <table class="w-100">
                                    <tbody>
                                        <tr>
                                            <td> 
                                                <p> 
                                                    <br><br><br> _______________________________________________ <br> 
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Employee signature <br> 
                                                    I acknowledge that i have received all my dues 
                                                </p> 
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>' . $employee['name'] . '</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td class="w-50 align-top">
                                <table class="w-100">
                                    <tbody class="h-100">
                                        <tr>
                                            <td class="text-right"> 
                                                <p> 
                                                    <br><br><br> ________________________________ <br> 
                                                    Approved by &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <br> 
                                                    Executive Director &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                </p> 
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><strong> Saeed Ahmed Suhail Al Ayali </strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>';

            // $footer_html = $footer . '<img src="' . erp_url('/assets/images/Payslip_footer.jpg') . '" style="width: 100%; height: 160px">';
            $footer_html = $footer;
            $mpdf->SetHTMLFooter($footer_html, 'O');
            $mpdf->SetHTMLFooter($footer_html, 'E');
            $mpdf->showWatermarkText = true;
            $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
            $mpdf->WriteHTML(file_get_contents(dirname(dirname(dirname(__DIR__))) . '/assets/css/mpdf_default.css'), 1); // The parameter 1 tells that this is css/style only and no body/html/text
            $mpdf->WriteHTML($renderedHtml);

            // $employee = getendOfService($endOfServiceId);
            $employee = ''; // cater this one also when sending email
            $fileName = "endOfService_{$employee['year']}{$employee['month']}_{$employee['name']}" . random_id(64) . ".pdf";
            $mpdf->Output($fileName, \Mpdf\Output\Destination::INLINE);
            exit();
        } catch (Exception $e) {
            return display_error("Error occurred while preparing PDF");
        }
    }
}
