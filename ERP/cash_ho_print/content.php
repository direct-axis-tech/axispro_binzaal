<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Hand Over</title>

    <style>
        .invoice-box {
            max-width: 800px;
            margin: auto;
            /* padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            */
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            /*padding: 5px;*/
            /*vertical-align: top;*/
        }

        /*.invoice-box table tr td:nth-child(2) {*/
        /*text-align: right;*/
        /*}*/

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            /*border-bottom: 1px solid #ddd;*/
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td{
            /*border-bottom: 1px solid #eee;*/
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        /*.invoice-box table tr.total td:nth-child(2) {*/
            /*border-top: 2px solid #eee;*/
            /*font-weight: bold;*/
        /*}*/

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        .rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .rtl table {
            text-align: right;
        }

        #footer {
            clear: both;
            position: fixed;
            height: 100%;
            margin-top: 8%;
            width: 90%;
            margin-left: 30px;

        }

        .denom-table{
            border-collapse: collapse;
        }

        .denom-table th, .denom-table td {
            border: 1px solid black;
            border-collapse: collapse;
            text-align: center !important;
        }
        .dotted {
            border-bottom: 1px dotted;
        }
    </style>
</head>
<body>
<?php
    date_default_timezone_set('Asia/Dubai');
    $current_username = $_SESSION['wa_current_user']->username;

    function get_cash_handover($id) {
        $sql = "select * from 0_cash_handover_requests where id = $id";
        return db_fetch(db_query($sql, "Cant retrieve voucher"));
    }

    function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }

    $req_id = $_GET['req_id'];
    $myrow = get_cash_handover($req_id);
    $_GET['trans_date'] = DateTime::createFromFormat(DB_DATE_FORMAT, $myrow['trans_date'])->format(getDateFormatInNativeFormat());
    $_GET['user_id'] = $myrow['cashier_id'];
    $summary = $api->getPaymentSummaryByMethod('array')['data'];
    $total = (
        $summary['Cash']
        + $summary['CreditCard']
        + $summary['BankTransfer']
        + $summary['OnlinePayment']
    );
    $cashier_info = get_user($myrow['cashier_id']);
    $approved_by = get_user($myrow['approve_rejected_by']);
    $cost_center = get_dimension($cashier_info['dflt_dimension_id']);
    $voucher_title = "PURCHASE REQUEST";
    $pv_rv_label = "REQUEST NO:";
    $paidto_rcvd_to_label = "";
?>
<div class="invoice-box">
    <table cellpadding="0" cellspacing="0">
        <tr class="top">
            <td colspan="4">
                <table>
                    <tr>
                        <td class="title" style="text-align: center">
                            <img src="<?= pdf_header_path($cashier_info['dflt_dimension_id']) ?>" style="width:100%;">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <table style="padding: 0; margin-top: -30px">
                    <tr>
                        <td class="title" style="text-align: center; font-size: 15px">
                            <b>Cash Hand Over Report تقرير التسليم النقدي</b>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="head-info" style=" width: 90%; margin-left: 30px; ">
        <table>
            <tr>
                <td>Ref. No: <span style="color: #cc4444; font-weight: bold"><?= $myrow['reference'] ?></span></td>
                <td style="text-align: right">Date: <?= sql2date($myrow['trans_date']) ?></td>
            </tr>
        </table>
    </div>
    <div class="head-info" style=" width: 90%; margin-left: 30px; ">
        <table>
            <tr>
                <td colspan="6" style="text-align: center; font-size: 12px ">
                    <h4 style=" border: 1px solid black; border-radius:10px;  padding: 15px">&nbsp;&nbsp;&nbsp;Depositor المودع&nbsp;&nbsp;&nbsp;</h4>
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

            <tr>
                <td style="width:14%;">Employee Name:</td>
                <td style="float: left" class="dotted"><?= $cashier_info['real_name'] ?></td>
                <td style="text-align:right;width:10%;"> اسم الموظف</td>
                <td style="width:11%;">Department:</td>
                <td class="dotted"><?= $cost_center['name'] ?></td>
                <td style="text-align:right;width:5%;">قسم</td>
            </tr>
        </table>
    </div>
    <br>
    <div class="head-info" style=" width: 90%; margin-left: 30px; ">
        <table>
            <tr>
                <td colspan="6" style="text-align: center; font-size: 12px ">
                    <h4 style=" border: 1px solid black; border-radius:10px;  padding: 15px">&nbsp;&nbsp;&nbsp;Receiver المتلقي&nbsp;&nbsp;&nbsp;</h4>
                </td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td style="width:14%;">Employee Name:</td>
                <td class="dotted"><?= $approved_by['real_name'] ?></td>
                <td style="text-align:right;width:10%;"> اسم الموظف</td>
                <td style="width:11%;">Department:</td>
                <td class="dotted">Finance</td>
                <td style="text-align:right;width:5%;">قسم</td>
            </tr>
        </table>
    </div>
    <br>
    <div class="head-info" style=" width: 90%; margin-left: 30px; ">
        <table>
            <tr>
                <td></td>
                <td></td>

            </tr>
            <tr>
                <td style="width:14%;">Currency:</td>
                <td style="float: left" class="dotted">AED</td>
                <td style="text-align:right;width:5%;">عملة</td>
                <td style="width:15%; text-align: right">Total Amount:</td>
                <td style="float: left; font-weight: bold" class="dotted"><?= $total ?>/-</td>
                <td style="text-align:right;width:10%;">المبلغ الإجمالي</td>
            </tr>
        </table>
    </div>
    <br>
    <div class="head-info" style=" width: 90%; margin-left: 30px; ">
        <table>
            <tr>
                <td></td>
                <td></td>
                <td></td>

            </tr>
            <tr>
                <td style="width: 14%">Amount in Words:</td>
                <td class="dotted"><?= price_in_words($total, ST_CUSTPAYMENT); ?></td>
                <td style="width: 10%">المبلغ بالكلمات</td>
            </tr>
        </table>
    </div>
    <br>
    <div class="">
        <caption style="color: black !important; font-family: Sans-Serif; font-size: 15px">
            Denominations الطوائف
        </caption>
        <table class="denom-table" style="border: 1px solid black;width:50%;margin: 0 auto;">
            <tr>
                <td style="text-align: center; width: 30%">Denom</td>
                <td style="width: 10%; text-align: center"></td>
                <td style=" text-align: center">Pcs</td>
                <td style="width: 10%; text-align: center"></td>
                <td style="width: 30%; text-align: center">Amount</td>
            </tr>
            <tr>
                <td style="text-align: center">1000</td>
                <td>x</td>
                <td><?= $myrow['denom1000'] ?></td>
                <td>=</td>
                <td><?= 1000*$myrow['denom1000'] ?></td>
            </tr>
            <tr>
                <td>500</td>
                <td>x</td>
                <td><?= $myrow['denom500'] ?></td>
                <td>=</td>
                <td><?= 500*$myrow['denom500'] ?></td>
            </tr>
            <tr>
                <td>200</td>
                <td>x</td>
                <td><?= $myrow['denom200'] ?></td>
                <td>=</td>
                <td><?= 200*$myrow['denom200'] ?></td>
            </tr>
            <tr>
                <td>100</td>
                <td>x</td>
                <td><?= $myrow['denom100'] ?></td>
                <td>=</td>
                <td><?= 100*$myrow['denom100'] ?></td>
            </tr>
            <tr>
                <td>50</td>
                <td>x</td>
                <td><?= $myrow['denom50'] ?></td>
                <td>=</td>
                <td><?= 50*$myrow['denom50'] ?></td>
            </tr>
            <tr>
                <td>20</td>
                <td>x</td>
                <td><?= $myrow['denom20'] ?></td>
                <td>=</td>
                <td><?= 20*$myrow['denom20'] ?></td>
            </tr>
            <tr>
                <td>10</td>
                <td>x</td>
                <td><?= $myrow['denom10'] ?></td>
                <td>=</td>
                <td><?= 10*$myrow['denom10'] ?></td>
            </tr>
            <tr>
                <td>5</td>
                <td>x</td>
                <td><?= $myrow['denom5'] ?></td>
                <td>=</td>
                <td><?= 5*$myrow['denom5'] ?></td>
            </tr>
            <tr>
                <td>1</td>
                <td>x</td>
                <td><?= $myrow['denom1'] ?></td>
                <td>=</td>
                <td><?= 1*$myrow['denom1'] ?></td>
            </tr>
            <tr>
                <td>0.5</td>
                <td>x</td>
                <td><?= $myrow['denom0_5'] ?></td>
                <td>=</td>
                <td><?= 0.5*$myrow['denom0_5'] ?></td>
            </tr>
            <tr>
                <td>0.25</td>
                <td>x</td>
                <td><?= $myrow['denom0_25'] ?></td>
                <td>=</td>
                <td><?= 0.25*$myrow['denom0_25'] ?></td>
            </tr>

            <tr>
                <td colspan="3">Total Cash Amount المبلغ النقدي الإجمالي</td>
                <td>=</td>
                <td><?= $myrow['amount'] ?></td>
            </tr>
        </table>
    </div>
<br>
<br>
    <div class="head-info" style=" width: 90%; margin-left: 30px; ">
        <table>
            <tr>
                <td style="width:50%">
                    <table>
                        <tr>
                            <td style="width: 35%">Cash: </td>
                            <td class="dotted" style="width: 30%"><?= $summary['Cash'] ?> AED</td>
                            <td dir="rtl" style="width: 35%">النقدية</td>
                        </tr>
                        <tr>
                            <td style="width: 35%">Credit Card: </td>
                            <td class="dotted" style="width: 30%"><?= $summary['CreditCard'] ?> AED</td>
                            <td dir="rtl" style="width: 35%">بطاقة ائتمان</td>
                        </tr>
                        <tr>
                            <td style="width: 35%">Bank Transfer: </td>
                            <td class="dotted" style="width: 30%"><?= $summary['BankTransfer'] ?> AED</td>
                            <td dir="rtl" style="width: 35%">حوالة بنكية</td>
                        </tr>
                        <tr>
                            <td style="width: 35%">Online Payment: </td>
                            <td class="dotted" style="width: 30%"><?= $summary['OnlinePayment'] ?> AED</td>
                            <td dir="rtl" style="width: 35%">الدفع الالكتروني</td>
                        </tr>
                    </table>
                </td>
                <td style="width:50%">
                    &nbsp;
                    <?php /*
                    <table>
                        <tr>
                            <td style="width: 35%">Credit Customer: </td>
                            <td class="dotted" style="width: 30%"><?= $summary['Cash'] ?> AED</td>
                            <td dir="rtl" style="width: 35%">عميل الائتمان</td>
                        </tr>
                        <tr>
                            <td style="width: 35%">Credit Card: </td>
                            <td class="dotted" style="width: 30%"><?= $summary['CreditCard'] ?> AED</td>
                            <td dir="rtl" style="width: 35%">بطاقة ائتمان</td>
                        </tr>
                        <tr>
                            <td style="width: 35%">Bank Transfer: </td>
                            <td class="dotted" style="width: 30%"><?= $summary['BankTransfer'] ?> AED</td>
                            <td dir="rtl" style="width: 35%">حوالة بنكية</td>
                        </tr>
                    </table>
                    */ ?>
                </td>
            </tr>
        </table>
    </div>
    <br>
    <br>
    <div class="head-info" style=" width: 90%; margin-left: 30px; ">

        <table>

            <tr>
                <td colspan="6" style="text-align: center; font-size: 12px ">
                    <h4 style=" border-radius:10px;  padding: 15px">&nbsp;&nbsp;&nbsp; Signature التوقيع &nbsp;&nbsp;&nbsp;</h4>
                </td>
            </tr>

            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

            </tr>

            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

            </tr>

<!--            <tr>-->
<!--                <td>Depositor: .................................................................................المودع</td>-->
<!--                <td>Receiver: ....................................................................................المتلقي</td>-->
<!---->
<!--            </tr>-->

            <tr>
                <td style="width: 9%">Depositor:</td>
                <td class="dotted"></td>
                <td style="width: 8%">المودع</td>
                <td style="width: 9%">Receiver:</td>
                <td class="dotted"></td>
                <td style="width: 6%">المتلقي</td>

            </tr>

        </table>

    </div>


</div>
</body>
</html>