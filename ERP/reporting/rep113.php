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

use App\Models\Labour\Contract;

$page_security = $_POST['PARAM_0'] == $_POST['PARAM_1'] ?
	'SA_PRINTSALESCREDIT' : 'SA_SALESBULKREP';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Print Credit Notes
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/prefs/sysprefs.inc");
include_once($path_to_root . "/includes/db/connect_db.inc");

if (!user_check_access($page_security)) {
    echo 'The security settings on the system does not allow you to access this function';
    exit();
}

//----------------------------------------------------------------------------------------------------

print_credit_note();

//----------------------------------------------------------------------------------------------------

function print_credit_note()
{
	global $Ajax, $credit_note;
	
	if (empty($_POST['PARAM_0']) || empty($_POST['PARAM_1']))
		return;

	sort(($bulk_range = [explode("-", $_POST['PARAM_0'])[0], explode("-", $_POST['PARAM_1'])[0]]));
	[$from, $to] = $bulk_range;

	try {
		$mpdf = app(\Mpdf\Mpdf::class);
		$mpdf->SetTitle('Credit Note Print');
		$mpdf->WriteHTML(get_contents($from));
	
		if (!in_ajax()) {
			$ref = strtr($credit_note['reference'], '/', '');
			$mpdf->Output("credit-note-{$ref}.pdf", \Mpdf\Output\Destination::INLINE);
			exit();
		}
	
		$filePath = company_path().'/pdf_files/'.random_id().'.pdf';
		$mpdf->Output($filePath, \Mpdf\Output\Destination::FILE);
	
		// when embeded pdf viewer used otherwise use faster method
		user_rep_popup() ? $Ajax->popup($filePath) : $Ajax->redirect($filePath);
	}
	catch (ErrorException $e) {
		die("Error occurred while preparing PDF");
	}
}

/**
 * Get the HTML content for printing
 *
 * @param int $trans_no
 * @return string
 */
function get_contents($trans_no) {
	global $credit_note;

	$credit_note = get_customer_trans($trans_no, ST_CUSTCREDIT);
	$comments = get_comments(ST_CUSTCREDIT, $trans_no)->fetch_assoc()['memo_'] ?? '';
	$is_voided = false;
	if($credit_note['Total'] == 0){
		if(!empty(get_voided_entry(ST_CUSTCREDIT, $trans_no))){
			$is_voided = true;
			$credit_note = get_customer_trans($trans_no, ST_CUSTCREDIT, null, null, $is_voided);
		}
	}
	$transacted_at = new DateTime($credit_note['transacted_at']);
	$is_from_labour_contract = !empty($credit_note['contract_id']);
	$is_credited_from_invoice = !empty($credit_note['credit_inv_no']);
	$is_tax_included = !empty($credit_note['tax_included']);
	if ($is_credited_from_invoice) {
		$invoice = get_customer_trans($credit_note['credit_inv_no'], ST_SALESINVOICE);
	}
	if ($is_from_labour_contract) {
		$contract = Contract::find($credit_note['contract_id']);
	}
	if ($is_tax_included) {
		$credit_note['credit_note_charge'] -= $credit_note['credit_note_charge_tax'];
		$credit_note['income_recovered'] -= $credit_note['income_recovered_tax'];
	}
	$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
	$credit_note_barcode = base64_encode($generator->getBarcode($credit_note['barcode'], $generator::TYPE_CODE_128));
	$trans_details = get_customer_trans_details(ST_CUSTCREDIT, $trans_no, $is_voided)->fetch_all(MYSQLI_ASSOC);
	$trans_details = array_filter($trans_details, function($line){
		return $line['quantity'] != 0;
	});
	$trans_details = array_values($trans_details);

	$sub_total = 0;
	$gross_total = 0;
	$total_discount = 0;
	$total_tax = 0;
	$total_govt_fee = 0;
	$total_price = 0;
	$should_show_cost = false;
	foreach($trans_details as $i => $line) {
		$refs = [];
		if ($line['transaction_id']) {
			$refs[] = $line['transaction_id'];
		}
		$taxable_amount = $line['unit_price'] + $line['returnable_amt'] + $line['extra_srv_chg'];
		$unit_price = $taxable_amount - ($is_tax_included ? $line['unit_tax'] : 0);
		$cost = (
			$line['govt_fee']
			+ $line['bank_service_charge']
			+ $line['bank_service_charge_vat']
			- $line['returnable_amt']
		);
		$line_sub_total = ($unit_price + $cost) * $line['quantity'];
		$line_total  = (
			$unit_price
			+ $cost
			+ $line['unit_tax']
		) * $line['quantity'];

		$sub_total      += $line_sub_total;
		$total_price    += $unit_price * $line['quantity'];
		$gross_total    += $line_total;
		$total_discount += $line['discount_amount'] * $line['quantity'];
		$total_tax      += $line['unit_tax'] * $line['quantity'];
		$total_govt_fee += $cost * $line['quantity'];
		
		$trans_details[$i]['unit_price']    = $unit_price;
		$trans_details[$i]['_refs']         = implode('&nbsp;&nbsp;&nbsp;', $refs);
		$trans_details[$i]['_cost']         = $cost;
		$trans_details[$i]['_sub_total']    = $line_sub_total;
		$trans_details[$i]['_total']        = $line_total;
		$should_show_cost                   = $should_show_cost || round2($cost, user_price_dec()) != 0;
	}
	$colspan = 5 + intval($should_show_cost);
	ob_start(); ?>
	<body>
		<!-- Begin: Header Section -->
		<img class="w-100" src="<?= pdf_header_path($credit_note['dimension_id'], $credit_note['type']) ?>">
		<div class="text-center pt-2">
			<h2 class="font-weight-normal">CREDIT NOTE  <span lang="ar">إشعار الدائن</span></h2>
		</div>
		<!-- End: Header Section -->

		<table class="w-100 table-sm mt-4">
			<tbody>
				<tr>
					<td class="border w-50">
						<table class="w-100">
							<tbody>
								<tr>
									<td colspan="2">
										<img height="40" width="80" src="data:image/png;base64,<?= $credit_note_barcode ?>">
										<p><?= $credit_note['barcode'] ?></p>
									</td>
								</tr>
								<tr>
									<td><b>Credit Note No./<span lang="ar">رقم مذكرة الائتمان</span></b></td>
									<td><?= $credit_note['reference'] ?></td>
								</tr>
								<tr>
									<td><b>Date/<span lang="ar">تاريخ الصفقة</span></b></td>
									<td><?= sql2date($credit_note['tran_date']) ?></td>
								</tr>
								<?php if ($is_credited_from_invoice): ?>
								<tr>
									<td><b>Credited Invoice No./<span lang="ar">رقم الفاتورة المعتمدة</span></b></td>
									<td><?= $invoice['reference'] ?></td>
								</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</td>
					<td class="border w-50 align-bottom">
						<table class="w-100">
							<tbody>
								<tr>
									<td><b>Credited At/<span lang="ar">تم التعامل في</span></b></td>
									<td><?= $transacted_at->format(dateformat() . ' h:i:s A') ?></td>
								</tr>
								<tr>
									<td><b>Customer/<span lang="ar">المتعامل</span></b></td>
									<td><?= $credit_note['DebtorName'] ?></td>
								</tr>
								<tr>
									<td><b>Mobile No./<span lang="ar">رقم الهاتف المتحرك</span></b></td>
									<td><?= $credit_note['customer_mobile'] ?></td>
								</tr>
								<?php if ($is_from_labour_contract): ?>
									<tr>
										<td><b>Maid/<span lang="ar">خادمة</span></b></td>
										<td><?= $contract->maid->name ?></td>
									</tr>
									<tr>
										<td><b>Contract Ref/<span lang="ar">مرجع العقد</span></b></td>
										<td><?= $contract->reference ?></td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>

		<caption>
			<h3>Particulars <span lang="ar">تفاصيل</span></h3>
		</caption>

		<table class="w-100 mt-4 table-bordered table-sm">
			<thead>
				<tr>
					<th style="width: 5%;">Sl. No<br><span lang="ar">الرقم</span></th>
					<th>Service<br><span lang="ar">الخدمات</span></th>
					<th style="width: 5%;">Qty<br><span lang="ar">الكمية</span></th>
					<?php if($should_show_cost): ?>
					<th style="width: 12%;">Cost<br><span lang="ar">التكلفة</span></th>
					<?php endif; ?>
					<th>Price<br><span lang="ar">سعر</span></th>
					<th style="width: 7%;">Tax<br><span lang="ar">ضريبة</span></th>
					<th style="width: 12%;">Total<br><span lang="ar">الاجمالى بالدرهم</span></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($trans_details as $i => $line): ?>
				<tr>
					<td class="text-center"><?= $i + 1 ?></td>
					<td class="text-center">
						<table class="w-100 table-borderless">
							<tbody>
								<tr><td><?= $line['StockDescription'] ?></td></tr>
								<?php if (!empty($line['_refs'])): ?>
									<tr><td><?= $line['_refs'] ?></td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</td>
					<td class="text-center">
						<?= number_format2($line['quantity'], get_qty_dec($line['stock_id'])) ?>
					</td>
					<?php if ($should_show_cost): ?>
						<td class="text-right"><?= price_format($line['_cost']) ?></td>
					<?php endif; ?>
					<td class="text-right"><?= price_format($line['unit_price']) ?></td>
					<td class="text-right"><?= price_format($line['unit_tax']) ?></td>
					<td class="text-right"><?= price_format($line['_total']) ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>

			<tfoot class="text-right">
				<tr>
					<td colspan="<?= $colspan ?>"><b>Sub Total</b></td>
					<td><?= price_format($sub_total) ?></td>
				</tr>
				<?php if ($total_discount): ?>
				<tr>
					<td colspan="<?= $colspan ?>"><b>(-) Discount</b></td>
					<td><?= price_format($total_discount) ?></td>
				</tr>
				<?php endif; ?>
				<?php if ($credit_note['income_recovered']): ?>
					<tr>
						<td colspan="<?= $colspan ?>">
							<b>(-) Income Recovered (for <?= (float)$credit_note['days_income_recovered_for'] ?> days)</b>
						</td>
						<td><?= price_format($credit_note['income_recovered']) ?></td>
					</tr>
                <?php endif; ?>
				<?php if ($credit_note['credit_note_charge']): ?>
					<tr>
						<td colspan="<?= $colspan ?>"><b>(-) Credit Charge</b></td>
						<td><?= price_format($credit_note['credit_note_charge']) ?></td>
					</tr>
				<?php endif; ?>
				<tr>
					<td colspan="<?= $colspan ?>"><b>(+) Tax</b></td>
					<td><?= price_format($credit_note['inc_ov_gst']) ?></td>
				</tr>
                <?php if($credit_note['round_of_amount'] != 0): ?>
                <tr>
                    <td colspan="<?= $colspan ?>" class="text-right">
                        <b>(+) Round Off</b>
                    </td>
                    <td class="text-right"><?= price_format($credit_note['round_of_amount']) ?></td>
                </tr>
                <?php endif; ?>
				<tr>
					<td colspan="<?= $colspan ?>"><b>Total</b></td>
					<td><?= price_format($credit_note['Total']) ?></td>
				</tr>
			</tfoot>
		</table>
		<table class="w-100 small">
			<tbody>
				<tr>
					<td class="text-left py-1"><em>Kindly check the documents before leaving the counter</em></td>
					<td class="text-right py-1"><em><span lang="ar">الرجاء التأكد من المستندات قبل مغادرة الكاونتر</span></em></td>
				</tr>
			</tbody>
		</table>
		<div class="w-100">
			<h4 class="mb-2">Comments</h4>
			<?= strtr($comments, ["\n" => '<br>']) ?>
		</div>
		<?php $is_voided && is_voided_display(ST_SALESINVOICE, $trans_no, trans("This transaction has been voided.")); ?>
		<div style="height: 49mm;"></div>
		<div class="fixed-bottom border-top border-white">
			<table>
				<tbody>
					<tr>
						<td class="text-center"><?= $credit_note['transacted_user'] ?></td>
					</tr>
					<tr>
						<td class="text-center">Authorized Signatory <span lang="ar">المخول بالتوقيع</span></td>
					</tr>
				</tbody>
			</table>
			<div class="w-100 text-center">
				<img class="w-100" src="<?= pdf_footer_path($credit_note['dimension_id'], $credit_note['type']) ?>">
			</div>
		</div>
	</body>
	<?php $contents = ob_get_clean();

	return $contents;
}
