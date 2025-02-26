<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Voucher</title>

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
            padding: 5px;
            vertical-align: top;
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
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td{
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

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

        /** RTL **/
        .rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .rtl table {
            text-align: right;
        }

        .rtl table tr td:nth-child(2) {
            text-align: left;
        }


        #footer {
            clear: both;
            position: fixed;
            height: 100%;
            margin-top: 8%;
            width: 90%;
            margin-left: 30px;

        }


    </style>
</head>

<body>


<?php

date_default_timezone_set('Asia/Dubai');
$current_username = $_SESSION['wa_current_user']->username;


function get_purchase_request($req_id) {

    $sql = "select * from 0_purchase_requests where id = $req_id";
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

//$req_id = $_GET['req_id'];
$req_id = 2;
$myrow = get_purchase_request($req_id);




$sql = "select * from 0_purchase_request_items where req_id = $req_id";
$req_items = db_query($sql);

$requested_user = get_user($myrow['created_by']);

//display_error($person_id); die;


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
                            <img src="<?= pdf_header_path() ?>" style="width:100%;">
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
                            <b><?= $voucher_title?></b>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>

    </table>


    <!--    <div style="text-align: center !important;">Payment Voucher</div>-->
    <div class="head-info" style=" width: 90%; margin-left: 30px; border: 1px solid black">

        <table>

            <tr>
                <td><?=$pv_rv_label?></td>
                <td style="text-align: left"><?=$myrow['reference']?></td>
                <td></td>
<!--                <td>Cheque#:</td>-->
<!--                <td style="text-align: left">--><?//=$myrow['chq_no']?><!--</td>-->

                <td>Date:</td>
                <td style="text-align: left"><?=sql2date($myrow['created_at'])?></td>

            </tr>
            <tr>

                <td>Requested By:</td>
                <td><?= $requested_user['user_id'] ?></td>

                <td></td>

            </tr>
            <tr>
<!--                <td>Type:</td>-->
<!--                <td style="text-align: left">--><?//=$payment_person_types[$myrow['person_type_id']]?><!--</td>-->
<!--                <td></td>-->

            </tr>
<!--            <tr>-->
<!--                <td>--><?//=$paidto_rcvd_to_label?><!--</td>-->
<!--                <td style="text-align: left">--><?//= $person_id ?><!--</td>-->
<!--                <td></td>-->
<!--                <td></td>-->
<!--                <td></td>-->
<!--            </tr>-->

            <tr>
<!--                <td>Remarks:</td>-->
<!--                <td style="text-align: left">--><?//= $myrow['description'] ?><!--</td>-->
<!--                <td></td>-->
<!--                <td></td>-->
<!--                <td></td>-->
            </tr>

        </table>

    </div>

    <br>

    <div class="trans-table" style="width: 90%; margin-left: 30px;">

        <table style="border: 1px solid black">
            <tr>
                <td style="background: #eee;">S.No</td>
                <td style="background: #eee;">Stock ID</td>
                <td style="background: #eee;">Category</td>
                <td style="background: #eee;">Qty</td>
                <td style="background: #eee;">Description</td>
            </tr>


            <?php

            $sl_no = 1;
            $dr_total = 0;
            $cr_total = 0;
            while ($myrow2 = db_fetch($req_items)) {

                echo "<tr>
                        <td>".$sl_no."</td>
                        <td>".$myrow2['stock_id']."</td>
                        <td>".$myrow2['stock_id']."</td>
                        <td>".$myrow2['qty']."</td>
                        <td>".$myrow2['description']."</td>
                </tr>";

                $sl_no++;

            }

            ?>


        </table>

    </div>


    <div class="footer-table" id="footer">

        <table>
            <tr>
                <td class="center" style="font-size: 13px">Prepared By:</td>
                <td class="center" style="font-size: 13px">Approved By:</td>
                <td class="center" style="font-size: 13px">Received By:</td>
            </tr>
        </table>

    </div>


</div>
</body>
</html>