<?php

class salaryCertificateHelpers {
    /**
     * Render the salaryCertificate
     *
     * @param array $employee, $userData
     * @return string
     */
    public static function renderSalaryCertificate($employee, $userData) {
        $company = get_company_prefs();
        ob_start(); ?>
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
                                        <p>
                                            <strong>
                                                TO WHOM IT MAY CONCERN <br>
                                                <?= $userData['addressee'] ?>
                                            </strong> <br>
                                            <?= strtr(e($userData['address']), ["\n", '<br>']) ?>
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
                                    <td style="font-size: 12pt;">Subject: Salary Certificate</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 12pt;"> <strong>Dear Sir/Madam, </strong> </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 12pt;">
                                        <p>
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
                                        <p>
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
                                        <p> For and behalf of: <br> <strong> <?= $company['coy_name'] ?> </strong> </p>
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
                                        <p> <strong><?= $userData['authorized_signatory'] ?> <br> Human Resources Executive </strong> </p> 
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php $renderedHtml = ob_get_clean();
        return $renderedHtml;
    }

    public static function handlePrintSalaryCertificateRequest($renderedHtml, $employee)
    {
        try {
            $mpdf = new \Mpdf\Mpdf([
                "margin_left"     => 15,
                "margin_right"    => 15,
                "margin_top"      => 45,
                "margin_bottom"   => 15,
                "margin_header"   => 15,
                "margin_footer"   => 11
            ]);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetTitle('Salary Certificate');

            $footer_html = '';
            $mpdf->SetHTMLFooter($footer_html, 'O');
            $mpdf->SetHTMLFooter($footer_html, 'E');
            $mpdf->showWatermarkText = true;
            $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
            $mpdf->WriteHTML(file_get_contents(dirname(dirname(dirname(__DIR__))) . '/assets/css/mpdf_default.css'), 1); // The parameter 1 tells that this is css/style only and no body/html/text
            $mpdf->WriteHTML('<body>'.$renderedHtml.'</body>');

            $refDate = date(DB_DATE_FORMAT);
            $day = date('d', strtotime($refDate));
            $month = date('m', strtotime($refDate));
            $year = date('Y', strtotime($refDate));

            $fileName = "salaryCertificate_{$year}{$month}{$day}_{$employee['formatted_name']}" . random_id(64) . ".pdf";
            $mpdf->Output($fileName, \Mpdf\Output\Destination::INLINE);
            exit();
        } catch (Exception $e) {
            return display_error("Error occurred while preparing PDF");
        }
    }
}