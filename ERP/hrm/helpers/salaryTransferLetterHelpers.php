<?php

class salaryTransferLetterHelpers {

     /** 
     * Returns the validated user inputs or the default value.
     * 
     * @param $currentEmployee The employee defined for the current user
     * @return array
     */
    public static function getValidatedInputs() {
        $filters = [
            "employee_id" => null,
            "bank_name" => null
        ];

        if (
            isset($_POST['employee_id'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['employee_id']) === 1
        ) {
            $filters['employee_id'] = $_POST['employee_id'];
        }

        if (
            isset($_POST['bank_name'])            
        ) {
            $filters['bank_name'] = $_POST['bank_name'];
        }
        return $filters;
    }

    /**
     * Render the salaryTransferLatter
     *
     * @param array $employee, $userData
     * @return string
     */
    public static function renderSalaryTransferLetter($employee, $userData) {
        $company = get_company_prefs();
        ob_start() ?>
        <div>
            <table class="w-100 table-sm" style="font-family:'Times New Roman';font-size:20px;"> 
                <thead>
                    <tr>
                        <th class="text-center" colspan="4">
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <table class="w-100">
                                <thead> </thead>
                                <tbody>
                                    <tr>
                                        <td> &nbsp; </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 12pt;"> 
                                            Ref: &nbsp; <?= $userData['ref_no']?> 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td> &nbsp; </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 12pt;"> 
                                            <p style="line-height: 1.9">
                                                <strong> To: </strong> <br>
                                                The Manager <br>
                                                <strong> <?= $userData['bank_name'] ?> </strong> <br> 
                                                <?= $userData['address'] ?> <br>
                                            </p> 
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td class="align-top align-top">
                            <table class="w-100">
                                <tbody>
                                    <tr>
                                        <td> &nbsp; </td>
                                    </tr>
                                    <tr>
                                        <td> &nbsp;</td>
                                        <td class="text-right" style="font-size: 12pt;"> Date: <?= $userData['currentDate'] ?> </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table class="w-100">
                                <thead> </thead>
                                <tbody> 
                                    <tr>
                                        <td style="font-size: 12pt;"> Subject: Salary Transfer Letter </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 12pt;"> <strong>Dear Sir/Madam, </strong> </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 12pt;">
                                            <p style="line-height: 1.9">
                                                This is to certify that <?= $employee['gender'] == 'M' ? 'Mr.' : 'Ms.' ?> <strong><?= $employee['name'] ?></strong> (Employee No: <strong><?= $employee['emp_ref'] ?></strong>) 
                                                holder of <strong><?= $employee['country'] ?></strong> Passport No. <strong><?= $employee['passport_no'] ?></strong> 
                                                is working in our organization as <strong><?= $employee['designation_name'] ?></strong> since 
                                                <strong><?= $userData['joiningDate'] ?></strong> until present 
                                                and <?= $employee['gender'] == 'M' ? 'he' : 'she' ?> is drawing a salary per calendar month of 
                                                AED <strong><?= price_format($employee['monthly_salary']) ?></strong>/-
                                            </p> 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 12pt;"> 
                                            <p style="line-height: 1.9">
                                                This is to confirm that we will transfer his monthly salary and dues. In case of resignation and termination 
                                                all benefits and settlement will be transfer to the below bank account details.
                                            </p> 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 12pt;">
                                            <p style="line-height: 1.9">
                                                <?php if(isset($employee['bank_name'])): ?>
                                                Bank &emsp;&emsp;&emsp;:&nbsp; <strong><?= $employee['bank_name'] ?></strong> <br>
                                                <?php endif; if(isset($employee['iban_no'])): ?>
                                                Iban &emsp;&emsp;&emsp;&nbsp;:&nbsp; <strong><?= $employee['iban_no'] ?></strong> <br>
                                                <?php endif; if(isset($employee['branch_name'])):  ?>
                                                Branch &emsp;&emsp;:&nbsp; <strong><?= $employee['branch_name'] ?></strong>
                                                <?php endif; ?>
                                            </p> 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 12pt;"> 
                                            <p style="line-height: 1.9">
                                                This Certificate is being issued on the request of the bearer without any responsibility / liability towards 
                                                the company and cannot be used for any other purpose if used be treated as null and void.
                                            </p> 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 12pt;"> 
                                            <p> Thanking You,</p> 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td> &nbsp; </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 12pt;"> 
                                            <p style="line-height: 1.9"> For and behalf of: <br> <strong> <?= $company['coy_name'] ?> </strong> </p> 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td> &nbsp; </td>
                                    </tr>
                                    <tr>
                                        <td> &nbsp; </td>
                                    </tr>
                                    <tr>
                                        <td> &nbsp; </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 12pt;"> 
                                            <p style="line-height: 1.9"> <strong> <?= $userData['authorized_signatory'] ?> <br> Human Resources Executive </strong> </p> 
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php $renderedHtml = ob_get_clean();
        return $renderedHtml;
    }

    public static function handlePrintSalaryTransferLetterRequest($renderedHtml, $employee)
    {
        try {
            $mpdf = new \Mpdf\Mpdf([
                "margin_left"     => 15,
                "margin_right"    => 15,
                "margin_top"      => 15,
                "margin_bottom"   => 15,
                "margin_header"   => 15,
                "margin_footer"   => 11,
                'default_font_size' => 9,
                'default_font' => 'Blackadder ITC'
            ]);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetTitle('Salary Transfer Letter');
            $footer_html = '';
            $mpdf->SetHTMLFooter($footer_html, 'O');
            $mpdf->SetHTMLFooter($footer_html, 'E');
            $mpdf->showWatermarkText = true;
            $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
            $mpdf->WriteHTML(file_get_contents(dirname(dirname(dirname(__DIR__))) . '/assets/css/mpdf_default.css'), 1); // The parameter 1 tells that this is css/style only and no body/html/text
            $mpdf->WriteHTML($renderedHtml);

            $refDate = date(DB_DATE_FORMAT);
            $day = date('d', strtotime($refDate));
            $month = date('m', strtotime($refDate));
            $year = date('Y', strtotime($refDate));

            $fileName = "salarySalaryTransferLetter_{$year}{$month}{$day}_{$employee['formatted_name']}" . random_id(64) . ".pdf";
            $mpdf->Output($fileName, \Mpdf\Output\Destination::INLINE);
            exit();
        } catch (Exception $e) {
            return display_error("Error occurred while preparing PDF");
        }
    }
}