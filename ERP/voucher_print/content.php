<?php
date_default_timezone_set('Asia/Dubai');
$current_username = $_SESSION['wa_current_user']->username;
function get_voucher($id,$type)
{
    // $type=1;
    $table = "0_bank_trans";
    if($type == 0){
        $table = "0_journal";

        $sql = "SELECT js.*, c.`memo_` FROM 0_journal js left join 0_comments c ON js.type = c.type AND js.trans_no = c.id where js.trans_no = $id and js.type=$type";
        // $sql = "SELECT * FROM $table where trans_no = $id and type=$type";
    }else{
       $sql = " SELECT 
                bt.ref ref,
                bt.cheq_no cheq_no,
                bt.trans_no trans_no,
                bt.type type,
                bt.cheq_date cheq_date,
                bt.trans_date trans_date,
                abs(bt.amount) amount,
                bt.person_id person_id,
                bt.person_type_id person_type_id,
                c.`memo_` `memo_`,
                ba.account_code,
                ba.bank_account_name,
                IF(u.real_name = ' ', u.user_id, u.real_name) entered_by
            FROM 0_bank_trans bt
            LEFT JOIN 0_comments c ON bt.type = c.type AND bt.trans_no = c.id
            LEFT JOIN 0_users u ON bt.created_by = u.id
            LEFT JOIN 0_bank_accounts ba on ba.id = bt.bank_act
            WHERE bt.trans_no = $id AND bt.type = $type";
    }
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

$voucher_id = $_GET['voucher_id'];
$exploded = explode("-",$voucher_id);
$voucher_id = $exploded[0];
$type = $exploded[1];

$myrow = get_voucher($voucher_id,$type);

if($type == 0) {
    $myrow['ref'] = $myrow['reference'];
    $myrow['trans_date'] = $myrow['tran_date'];
}

$trans_type = $myrow['type'];
$trans_no = $myrow['trans_no'];

$gl_trans = get_gl_trans(
    $trans_type,
    $trans_no,
    false,
    $trans_type == ST_JOURNAL ? null : ($trans_type == ST_BANKPAYMENT)
)->fetch_all(MYSQLI_ASSOC);
$person_id = get_counterparty_name($trans_type, $trans_no);
if(empty($person_id)) $person_id = $myrow['person_id'];

if($trans_type == 0) {
    $voucher_title = "JOURNAL VOUCHER";
    $pv_rv_label = "J.V.No:";
}

if($trans_type == 1) {
    $voucher_title = "PAYMENT VOUCHER";
    $pv_rv_label = "P.V.No:";
    $counter_party_label = 'Paid To';
    $counter_party_label_ar = 'تم الدفع لي';
    $account_label = "Paid From";
    $account_label_ar = "تم الدفع من";
}

if($trans_type == 2) {
    $voucher_title = "RECEIPT VOUCHER";
    $pv_rv_label = "R.V.No:";
    $counter_party_label = 'Received From';
    $counter_party_label_ar = 'تم الاستلام من';
    $account_label = "Received To";
    $account_label_ar = "تم الاستلام لي";
}

$paidto_rcvd_to_label = $trans_type == "1" ? "Paid To:" : "Received From:";

?>

<body>
    <img src="<?= pdf_header_path(null, $myrow['type']) ?>" class="w-100">

    <div class="text-center pt-2">
        <h2 class="font-weight-normal">
            <span class="pb-3" style="color: #7e8299;"><?= $voucher_title ?></span>
        </h2>
    </div>

    <table class="table table-bordered table-md mb-3">
        <tr>
            <td><b>Date</b></td>
            <td class="text-center" style="width: 400px;">
                <?= sql2date($myrow['trans_date']) . " " . $invoice_created_time ?>
            </td>
            <td class="text-right"><b><span lang="ar">التاريخ والوقت</span></b></td>
        </tr>
        <tr>
            <td><b>Voucher Number</b></td>
            <td class="text-center"><?= $myrow['ref'] ?></td>
            <td class="text-right"><b><span lang="ar">مرجع</span></b></td>
        </tr>
        <tr>
            <td><b><?= $counter_party_label ?></b></td>
            <td class="text-center"><?= $person_id ?></td>
            <td class="text-right"><b><span lang="ar"><?= $counter_party_label_ar ?></span></b></td>
        </tr>

        <!-- <tr>
            <td><b>Cheque Number</b></td>
            <td class="text-center"><?= $myrow['chq_no'] ?></td>
            <td class="text-right"><b><span lang="ar">المتعامل</span></b></td>
        </tr>

        <tr>
            <td><b>Cheque Date</b></td>
            <td class="text-center"><?= sql2date($myrow['chq_date']) ?></td>
            <td class="text-right"><b><span lang="ar">المتعامل</span></b></td>
        </tr> -->
        
        <?php if ($trans_type != ST_JOURNAL): ?>
        <tr>
            <td><b><?= $account_label ?></b></td>
            <td class="text-center"><?= implode(' - ', [$myrow['account_code'], $myrow['bank_account_name']]) ?></td>
            <td class="text-right"><b><span lang="ar"><?= $account_label_ar ?></span></b></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td><b>Description</b></td>
            <td class="text-center"><?= $myrow['memo_'] ?></td>
            <td class="text-right"><b><span lang="ar">الوصف</span></b></td>
        </tr>
    </table>

    <div class="text-center pt-2">
        <h2 class="font-weight-normal">
            <span class="pb-3" style="color: #7e8299;">Particulars &nbsp;&nbsp;&nbsp;<span lang="ar">تفاصيل</span></span>
        </h2>
    </div>

    <table class="table w-100 table-md text-center">
        <thead class="thead-strong">
            <tr class="heading" style="background-color: #eeeeee;">
                <th>
                    <span>Sl. No</span>
                    <br>
                    <span lang="ar">الرقم</span>
                </th>
                <th>
                    <span>A/c Code</span>
                    <br>
                    <span lang="ar">رمز الحساب</span>
                </th>
                <th>
                    <span>Account Name</span>
                    <br>
                    <span lang="ar">إسم الحساب</span>
                </th>
                <th>
                    <span>Description</span>
                    <br>
                    <span lang="ar">الوصف</span>
                </th>
                <th>
                    <span>Amount</span>
                    <br>
                    <span lang="ar">مبلغ</span>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($gl_trans as $i => $myrow2): ?>
            <tr>
                <td><?= ($i + 1) ?></td>
                <td><?= $myrow2["account"] ?></td>
                <td><?= $myrow2["account_name"] ?></td>
                <td><?= $myrow2["memo_"] ?: '-' ?></td>
                <td class="text-right"><?= price_format(abs($myrow2["amount"])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>

        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><b><span>Net Amount</span><br><span lang="ar">مبلغ الاجمالي</span> </b></td>
                <td class="text-right"><?= price_format($myrow['amount']) ?></td>
            </tr>
        </tfoot>
    </table>

    <div style="height: 66mm;"></div>
    <div class="fixed-bottom border-top border-white">
        <table class="w-100 table-borderless my-2">
            <tbody>
                <tr>
                    <td style="width:34%;">
                        <table class="w-100 text-center">
                            <tbody>
                                <tr>
                                    <td><?= $created_by ?></td>
                                </tr>
                                <tr>
                                    <td>Authorized Signatory - <span lang="ar">المخول بالتوقيع</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td class="align-middle">
                        &nbsp;
                    </td>
                    <td style="width:45%;" class="align-bottom text-center pr-3"> 
                        Note:<span class="arabic">ملاحظات</span><br>
                        <span lang="ar">الرجاء التأكد من الفاتورة والمستندات قبل مغادرة الكاونتر</span><br>
                        <span>Kindly check the voucher and documents before leaving the counter</span>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="w-100 text-center">
            <img class="w-100" src="<?= pdf_footer_path(null, $myrow['type']) ?>">
        </div>
    </div>
</body>