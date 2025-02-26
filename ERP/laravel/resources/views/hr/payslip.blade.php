<div>
    <table class="w-100 table-sm">
        <thead>
            <tr style="background-color:rgb(250,240,230);">
                <th class="border w-50 text-center" colspan="4">
                    <h3>
                        <span style="color:rgb(128,0,0);">
                            Payslip for the Month of <?= DateTime::createFromFormat('!m', $payroll->month)->format('F') . ", " . $payroll->year ?>
                        </span>
                    </h3>
                </th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td class="border w-50">
                    <table class="w-100">
                        <thead>
                            <tr>
                                <th class="text-center" colspan="2" style="color: rgb(128,0,0);">
                                    <h4>Personal Details</h4>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td> ID/Ref. No. </td>
                                <td><?= $payslip['emp_ref'] ?></td>
                            </tr>
                            <tr>
                                <td> <strong> Employee Name </strong> </td>
                                <td><strong><?= $payslip['name'] ?></strong></td>
                            </tr>
                            <tr>
                                <td> Nationality </td>
                                <td><?= $payslip['country'] ?></td>
                            </tr>
                            <tr>
                                <td> Designation </td>
                                <td><?= $payslip['designation'] ?></td>
                            </tr>
                            <tr>
                                <td> Joining Date </td>
                                <td><?= sql2date($payslip['date_of_join']) ?></td>
                            </tr>
                            <tr>
                                <td> Emirates ID </td>
                                <td><?= $payslip['emirates_id'] ?></td>
                            </tr>
                            <tr>
                                <td> Mode of Payment </td>
                                <td><?= $payslip['mode_of_payment'] ?></td>
                            </tr>
                            <?php if ($payslip['mode_of_pay'] == MOP_BANK): ?>
                            <tr>
                                <td> Bank </td>
                                <td><?= $payslip['bank_name'] ?></td>
                            </tr>
                            <tr>
                                <td> IBAN </td>
                                <td><?= $payslip['iban_no'] ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </td>
                <td class="border w-50 align-top">
                    <table class="w-100">
                        <thead>
                            <tr>
                                <th class="text-center" colspan="2" style="color:rgb(128,0,0);">
                                    <h4>Duty Details</h4>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td> Working Days </td>
                                <td class="text-right text-end"><?= price_format($payslip['work_days']) ?></td>
                            </tr>
                            <tr>
                                <td> Working Hours </td>
                                <td class="text-right text-end"><?= price_format($payslip['work_hours']) ?></td>
                            </tr>
                            <tr>
                                <td> Days Absent </td>
                                <td class="text-right text-end"><?= price_format($payslip['days_absent']) ?></td>
                            </tr>
                            <tr>
                                <td> Days on Leave </td>
                                <td class="text-right text-end"><?= price_format($payslip['days_on_leave']) ?></td>
                            </tr>
                            <tr>
                                <td> Days off </td>
                                <td class="text-right text-end"><?= price_format($payslip['days_off']) ?></td>
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
                        <thead>
                            <tr>
                                <th colspan="2" class="text-center">
                                    <h4>
                                        <span style="color:rgb(128,0,0);"> Basic Pay and Allowances </span>
                                    </h4>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($allowances as $payElement): ?>
                            <tr>
                                <td><?= $payElement['name'] ?? '&nbsp;'?></td>
                                <td class="text-right text-end">
                                    <?= isset($payElement['amount']) ? price_format($payElement['amount']) : '' ?>&nbsp;
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="font-weight-bold">Total Allowances</td>
                                <td class="text-right text-end pr-2 font-weight-bold">
                                    <?= price_format($totalAllowances) ?>&nbsp;
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </td>
                <td class="border w-50 align-top">
                    <table class="w-100">
                        <thead>
                            <tr>
                                <th colspan="2" class="text-center">
                                    <h4><span style="color:rgb(128,0,0);"> Deductions </span></h4>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="h-100">
                        <?php foreach($deductions as $payElement): ?>
                            <tr>
                                <td><?= $payElement['name'] ?? '&nbsp;'?></td>
                                <td class="text-right text-end">
                                    <?= isset($payElement['amount']) ? price_format($payElement['amount']) : '' ?>&nbsp;
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="font-weight-bold">Total Deductions</td>
                                <td class="text-right text-end pr-2 font-weight-bold">
                                    <?= price_format($totalDeductions) ?>&nbsp;
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
        </tbody>
        <tbody>
            <tr class="border w-50">
                <td colspan="2" class="text-center py-2 font-weight-bold">
                    <h4> Total Salary: <?= price_format($payslip['net_salary']) ?> </h4>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <small> This is a system generated Payslip hence doesn't required any signature. </small>
                </td>
            </tr>
        </tbody>
    </table>
</div>