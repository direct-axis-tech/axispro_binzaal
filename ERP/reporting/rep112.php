<?php
/**********************************************************************
    Direct Axis Technology L.L.C.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/

use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

$page_security = $_POST['PARAM_0'] == $_POST['PARAM_1'] ?
	'SA_PRINTSALESRCPT' : 'SA_SALESBULKREP';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Receipts
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
require_once($path_to_root . '/BarcodeGenerator/BarcodeGenerator.php');
require_once($path_to_root . '/BarcodeGenerator/BarcodeGeneratorPNG.php');

//----------------------------------------------------------------------------------------------------

print_receipts();

//----------------------------------------------------------------------------------------------------
function get_receipt($type, $trans_no, $voided=false)
{
	if ($voided) {
		$sql = "SELECT trans.*,
				(trans.ov_amount + trans.ov_gst + trans.ov_discount + trans.ov_freight + trans.ov_freight_tax) AS Total,
				trans.ov_discount, 
				debtor.name AS DebtorName,
				debtor.debtor_ref, 
				debtor.name As display_customer,
  				debtor.mobile As customer_mobile,
  				debtor.tax_id As customer_trn,
   				debtor.curr_code,
   				debtor.payment_terms,
   				debtor.tax_id AS tax_id,
   				debtor.address,
				debtor.should_send_sms,
				debtor.should_send_email,
   				usr.real_name user_name,
				bAct.bank_account_name
    			FROM 0_debtors_master debtor
    			INNER JOIN 0_voided_debtor_trans trans ON trans.debtor_no = debtor.debtor_no
				LEFT JOIN 0_users usr ON usr.id = trans.created_by
				LEFT JOIN 0_voided_bank_trans bTrans ON bTrans.type = trans.type and bTrans.trans_no = trans.trans_no
				LEFT JOIN 0_bank_accounts bAct ON bAct.id = bTrans.bank_act
				WHERE trans.type = ".db_escape($type)."
				AND trans.trans_no = ".db_escape($trans_no);
	}
	
	else{
    	$sql = "SELECT trans.*,
				(trans.ov_amount + trans.ov_gst + trans.ov_discount + trans.ov_freight + trans.ov_freight_tax) AS Total,
				trans.ov_discount, 
				debtor.name AS DebtorName,
				debtor.debtor_ref, 
				debtor.name As display_customer,
  				debtor.mobile As customer_mobile,
  				debtor.tax_id As customer_trn,
				debtor.curr_code,
   				debtor.payment_terms,
   				debtor.tax_id AS tax_id,
   				debtor.address,
				debtor.should_send_sms,
				debtor.should_send_email,
   				usr.real_name user_name,
				bAct.bank_account_name
    		FROM 0_debtors_master debtor
			INNER JOIN 0_debtor_trans trans ON trans.debtor_no = debtor.debtor_no
    		LEFT JOIN 0_users usr ON usr.id = trans.created_by
			LEFT JOIN 0_bank_trans bTrans ON bTrans.type = trans.type and bTrans.trans_no = trans.trans_no
			LEFT JOIN 0_bank_accounts bAct ON bAct.id = bTrans.bank_act
			WHERE trans.type = ".db_escape($type)."
				AND trans.trans_no = ".db_escape($trans_no);
	}
	
   	$result = db_query($sql, "The remittance cannot be retrieved");
   	if (db_num_rows($result) == 0)
   		return false;
    return db_fetch($result);
}

function print_receipts()
{
    global $path_to_root, $systypes_array;

	$from = $_POST['PARAM_0'];
	$to = $_POST['PARAM_1'];
	$currency = $_POST['PARAM_2'];
	$comments = $_POST['PARAM_4'];
	$orientation = $_POST['PARAM_5'];
	$voided = $_POST['PARAM_6'];

	if (!$from || !$to) return;

	$orientation = ($orientation ? 'L' : 'P');
	$dec = user_price_dec();

 	$fno = explode("-", $from);
	$tno = explode("-", $to);
	$from = min($fno[0], $tno[0]);
	$to = max($fno[0], $tno[0]);
	$types = $fno[0] == $tno[0] ? array($fno[1]) : array(ST_BANKDEPOSIT, ST_CUSTPAYMENT);
	$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
	$contents = [];
	$trans = null;

	for ($trans_no = $from; $trans_no <= $to; $trans_no++) {
		foreach ($types as $type) {
			if (
				!($myrow = get_receipt($type, $trans_no, $voided)) ||
				($currency != ALL_TEXT && $myrow['curr_code'] != $currency)
			) {
				continue;
			}

			$allocations = get_allocatable_to_cust_transactions($myrow['debtor_no'], $myrow['trans_no'], $myrow['type'])->fetch_all(MYSQLI_ASSOC);
			$contents[] = get_contents($myrow, $allocations, $generator);
			$trans = $myrow;
		}
	}

	$file_name = 'receipt-'.($fno[0] == $tno[0] ? ($trans['reference'] ?? '') : random_id()).'.pdf';
	return generatePdf($contents, $file_name, $trans);
}

function generatePdf($contents, $readableFileName, $trans) {
	if (!($count = count($contents))) {
		return false;
	}

	$isEmailingOnly = $count == 1 && ($_POST['PARAM_3'] ?? false);
	$isTextingOnly = $count == 1 && ($_POST['PARAM_9'] ?? false);
	$shouldSendEmailAutomatically = ($count == 1 && shouldSendEmailAutomatically($trans));
	$shouldSendSMSAutomatically = ($count == 1 && shouldSendSMSAutomatically($trans));
	$shouldSendEmail = $isEmailingOnly || $shouldSendEmailAutomatically;
    $shouldSendSMS = $isTextingOnly || $shouldSendSMSAutomatically;

	$mpdf = app(\Mpdf\Mpdf::class);
	$mpdf->SetTitle('RECEIPT');
    $mpdf->WriteHTML('<body>');

	foreach ($contents as $i => $content) {
		$mpdf->WriteHTML($content);
		if ($i < $count - 1) {
			$mpdf->AddPage();
		}
	}
	$mpdf->WriteHTML('</body>');

    if (!file_exists($dir =  join_paths(company_path(), 'pdf_files'))) {
        mkdir ($dir, 0777);
    }
	$filePath = join_paths($dir, random_id().'.pdf');

    $mpdf->Output($filePath, \Mpdf\Output\Destination::FILE);

	if ($shouldSendEmail || $shouldSendSMS) {
        $cloudPath = Storage::disk('s3')->putFile(config('filesystems.disks.s3.project_dir'), new File($filePath));
        $lifeTime = pref('axispro.sent_link_lifetime', '0');
        $cloudUrl = $lifeTime
            ? Storage::disk('s3')->temporaryUrl($cloudPath, (new Carbon)->addMinutes($lifeTime))
            : Storage::disk('s3')->url($cloudPath);

        $shortUrl = generateShortUrl($cloudUrl);
        $context = [
            'customer_name' => $trans['display_customer'],
            'company_name' => pref('company.coy_name'),
            'type' => $trans['type'],
            'type_name' => 'receipt',
            'trans_no' => $trans['trans_no'],
            'reference' => $trans['reference'],
            'link' => $shortUrl,
            'total' => price_format($trans['Total'])
        ];

        if ($shouldSendEmail) {
            $result = sendBillingEmail($trans['customer_email'], $context, [$filePath => $readableFileName]);
    
            if ($isEmailingOnly) {
                return $result ? display_notification("Email sent successfully.") : display_error("Email didn't sent successfully."); 
            }
        }
    
        if ($shouldSendSMS) {
            $result = sendSMS($trans['customer_mobile'], $context);
    
            if ($isTextingOnly) {
                return $result ? display_notification("SMS sent successfully.") : display_error("SMS didn't sent successfully."); 
            }
        }
    }

	if (!in_ajax()) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="'.$readableFileName.'"');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        readfile($filePath);
        exit();
    }

    if (!$isEmailingOnly && !$isTextingOnly) {
        global $Ajax;

        // when embedded pdf viewer used otherwise use faster method
        user_rep_popup() ? $Ajax->popup($filePath) : $Ajax->redirect($filePath);
    }
}

function get_contents($myrow, $allocations, $generator) {
	ob_start(); ?>
	<img class="w-100" src="<?= pdf_header_path($myrow['dimension_id'], $myrow['type']) ?>">

	<div class="text-center pt-3">
		<h2 class="font-weight-normal">
			<b><span class="pb-3">RECEIPT &nbsp;-&nbsp;<span lang="ar">الإيصال</span></span></b>
		</h2>
	</div>

	<table class="w-100 table-sm mt-4">
		<tbody>
			<tr>
				<td class="border">
					<table class="w-100">
						<tbody>
							<tr>
								<td class="w-33"><b>Receipt No.</b></td>
								<td><?= $myrow['reference'] ?></td>
							</tr>

							<tr>
								<td><b>Customer/<span lang="ar">المتعامل</span></b></td>
								<td><?= $myrow['DebtorName'] ?></td>
							</tr>

							<tr>
								<td><b>Payment Method/<span lang="ar">طريقة الدفع</span></b></td>
								<td><?= $myrow['payment_method'] ?></td>
							</tr>

							<tr>
								<td><b>Into Account/<span lang="ar">داخل الحساب</span></b></td>
								<td><?= $myrow['bank_account_name'] ?></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td class="border align-bottom">
					<table class="w-100">
						<tbody>
							<tr>
								<td class="w-33"><b>Created At <br><span lang="ar">أنشئت في</span></b></td>
								<td><?= Carbon::parse($myrow['transacted_at'])->format(dateformat().' h:i:s A') ?></td>
							</tr>
							<tr>
								<td><b>Date/<span lang="ar">التاريخ والوقت</span></b></td>
								<td><?= sql2date($myrow['tran_date']) ?></td>
							</tr>

							<tr>
								<td><b>Mobile No./<span lang="ar">رقم الهاتف المتحرك</span></b></td>
								<td><?= $myrow['customer_mobile'] ?></td>
							</tr>

							<tr>
								<td><b>Remarks/<span lang="ar">ملاحظات</span></b></td>
								<td><?= get_comments_string($myrow['type'], $myrow['trans_no']); ?></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>

	<caption class="text-center mt-2" style="font-size: 14px; font-family: 'Times New Roman'">
		<b>Particulars <span lang="ar">تفاصيل</span></b>
	</caption>

	<table class="table-sm text-center table-bordered w-100 mt-4">
		<thead>
			<tr>
				<th style="width: 10%">Sl. No<br><span lang="ar">الرقم</span></th>
				<th style="width: 23%">Barcode<br><span lang="ar">الخدمات</span></th>
				<th style="width: 23%">Invoice No<br><span lang="ar">الكمية</span></th>
				<th style="width: 24%">Invoice Amount<br><span lang="ar">تكلفة المعاملة</span></th>
				<th>This Alloc<br><span lang="ar">هذا التخصيص</span></th>
			</tr>
		</thead>
		<tbody>
		<?php if (count($allocations) > 0): 
			foreach ($allocations as $i => $myrow2): ?>
				<tr>
					<td><?= $i + 1 ?></td>
					<td>
						<img height='25' width='80' src='data:image/png;base64,<?= base64_encode($generator->getBarcode($myrow2['barcode'], $generator::TYPE_CODE_128)) ?>'>
						<br>
						<span><?= $myrow2['barcode'] ?></span>
					</td>
					<td><?= $myrow2['reference'] ?></td>
					<td><?= price_format($myrow2['Total']) ?></td>
					<td><?= price_format($myrow2['amt']) ?></td>
				</tr>
			<?php endforeach;
		else: ?>
			<tr><td colspan='5'>ADVANCE RECEIPT</td></tr>
		<?php endif; ?>
		</tbody>

		<tfoot class="text-right">
			<tr>
                <td colspan="4">TOTAL RECEIPT AMOUNT</td>
                <td><?= price_format($myrow['Total']) ?></td>
            </tr>
			
			<?php if ($myrow['credit_card_charge']): ?>
            <tr>
                <td colspan="4">CREDIT CARD CHARGE</td>
                <td><?= price_format($myrow['credit_card_charge']) ?></td>
            </tr>
            <?php endif; ?>

			<?php if ($myrow['ov_discount']): ?>
			<tr>
				<td colspan="4">DISCOUNT</td>
				<td><?= price_format($myrow['ov_discount']) ?></td>
			</tr>
			<?php endif; ?>

            <?php if ($myrow['round_of_amount']): ?>
            <tr>
                <td colspan="4">ROUND OFF</td>
                <td><?= price_format($myrow['round_of_amount']) ?></td>
            </tr>
            <?php endif; ?>

            <tr>
                <td colspan="4"><b>TOTAL COLLECTED AMOUNT</b></td>
                <td><b><?= price_format($myrow['Total'] + $myrow['credit_card_charge'] + $myrow['round_of_amount'] - $myrow['ov_discount']) ?></b></td>
            </tr>

            <?php if ($myrow['alloc']): ?>
            <tr>
                <td colspan="4">ALLOCATED AMOUNT</td>
                <td><?= price_format($myrow['alloc']) ?></td>
            </tr>

            <tr>
                <td colspan="4">BALANCE</td>
                <td><?= price_format($myrow['Total'] - $myrow['alloc']) ?></td>
            </tr>
            <?php endif; ?>
			<tr>
                <td colspan="5" class="text-center">
                    <span>This receipt is generated electronically</span>
					<span> - </span>
					<span>تم دفع المعاملة إلكترونيا</span>
				</td>
            </tr>
		</tfoot>
	</table>


	<div class="w-100" style="height: 59mm;"></div>
	<div class="w-100 fixed-bottom border-top border-white">
		<table class="w-100">
			<tbody>
				<tr>
					<td class="text-center"><?= $myrow['user_name'] ?></td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<td class="text-center">Authorized Signatory <br> <span lang="ar">المخول بالتوقيع</span></td>
					<td class="text-right">
						الرجاء التأكد من الفاتورة والمستندات قبل مغادرة الكاونتر
						<br>
						Kindly check the invoice and documents before leaving the counter
					</td>
				</tr>
			</tbody>
		</table>
		<img class="w-100" src="<?= pdf_footer_path($myrow['dimension_id'], $myrow['type']) ?>">
	</div>
	<?php $content = ob_get_clean();

	return $content;
}