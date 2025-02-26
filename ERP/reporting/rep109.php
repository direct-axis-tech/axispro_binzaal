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
use Carbon\Carbon;

$page_security = $_POST['PARAM_0'] == $_POST['PARAM_1'] ?
	'SA_SALESTRANSVIEW' : 'SA_SALESBULKREP';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Print Sales Orders
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/taxes/tax_calc.inc");

//----------------------------------------------------------------------------------------------------

print_sales_orders();

function print_sales_orders()
{
	global $path_to_root, $SysPrefs;

	include_once($path_to_root . "/reporting/includes/pdf_report.inc");

	$from = $_POST['PARAM_0'];
	$to = $_POST['PARAM_1'];
	$currency = $_POST['PARAM_2'];
	$email = $_POST['PARAM_3'];
	$print_as_quote = $_POST['PARAM_4'];
	$orientation = $_POST['PARAM_6'];

	if (!$from || !$to) return;

	$orientation = ($orientation ? 'L' : 'P');
	$contents = [];
	$references = [];

	for ($i = $from; $i <= $to; $i++)
	{
		$myrow = get_sales_order_header($i, ST_SALESORDER);
		if ($currency != ALL_TEXT && $myrow['curr_code'] != $currency) {
			continue;
		}

		$contents[] = get_contents($myrow, $print_as_quote);
		$references[] = $myrow['reference'];
	}

	$file_name = 'order-'.($fno[0] == $tno[0] ? strtr(reset($references), '/', '') : random_id()).'.pdf';
	return generatePdf($contents, $file_name);
}

function generatePdf($contents, $file_name) {
	if (!($count = count($contents))) {
		return false;
	}
	
	$mpdf = app(Mpdf\Mpdf::class);
    $mpdf->WriteHTML('<body>');

	foreach ($contents as $i => $content) {
		$mpdf->WriteHTML($content);
		if ($i < $count - 1) {
			$mpdf->AddPage();
		}
	}
	$mpdf->WriteHTML('</body>');

    if (!in_ajax()) {
        $mpdf->Output($file_name, \Mpdf\Output\Destination::INLINE);
        exit();
    }

    $dir =  company_path(). '/pdf_files';
    if (!file_exists($dir)) {
        mkdir ($dir,0777);
    }
    $filePath = $dir.'/'.random_id().'.pdf';

    $mpdf->Output($filePath, \Mpdf\Output\Destination::FILE);

    if (in_ajax()) {
        global $Ajax;

        // when embeded pdf viewer used otherwise use faster method
        if (user_rep_popup())  {
            $Ajax->popup($filePath);
        } else {
            $Ajax->redirect($filePath);
        }
    }
}

function get_contents($trans, $print_as_quote)
{
	$title = $print_as_quote ? 'QUOTE' : 'SALES ORDER';
	$title_ar = $print_as_quote ? 'اقتباس' : 'طلب المبيعات';

	$trans_no = $trans['order_no'];
	$trans_type = ST_SALESORDER;
	$dimension = get_dimension($trans['dimension_id']);
	$customer = get_customer($trans['debtor_no']);

	$is_tax_registered = (
		!empty($dimension['gst_no'])
		&& (empty($dimension['tax_effective_from']) || $dimension['tax_effective_from'] >= $trans['ord_date'])
	);
	$is_from_labour_contract = !!$trans['contract_id'];
	$is_cost_grouped = false;

	$created_at = Carbon::parse($trans['transacted_at']);
	$contract = $is_from_labour_contract ? Contract::find($trans['contract_id']) : null;

	$trans_details = get_sales_order_details($trans_no, $trans_type)->fetch_all(MYSQLI_ASSOC);
	$trans_details = array_filter($trans_details, function($line){
        return $line['quantity'] != 0;
    });
    $trans_details = array_values($trans_details);

	$allocations = get_allocatable_from_cust_transactions($trans['debtor_no'], $trans_no, $trans_type)
		->fetch_all(MYSQLI_ASSOC);
	$allocations = array_map(function($trans) {
		$created_by = get_entered_by_user($trans['trans_no'], $trans['type']);
		$trans['_created_by'] = '';
		if(!empty($created_by)){
			$trans['_created_by'] = empty(trim($created_by['real_name'])) 
				? $created_by['user_id']
				: trim($created_by['real_name']);
		}
		return $trans;
	}, $allocations);

	$tax_group_array = get_tax_group_items_as_array($trans['tax_group_id']);
	$gross_total = 0;
    $net_total = 0;
    $total_discount = 0;
    $total_tax = 0;
    $total_govt_fee = 0;
    $total_price = 0;
    $processing_fee = 0;
    $colspan = 6 - intval($is_cost_grouped);

	foreach ($trans_details as $i => $line) {
        $refs = [];
        if ($line['transaction_id']) {
            $refs[] = $line['transaction_id'];
        }
        if ($line['application_id']) {
            $refs[] = $line['application_id'];
        }
        if ($line['passport_no']) {
            $refs[] = $line['passport_no'];
        }
        if ($line['ed_transaction_id']) {
            $refs[] = $line['ed_transaction_id'];
        }
        if ($line['ref_name']) {
            $refs[] = $line['ref_name'];
        }

        $taxable_amount = $line['unit_price'] + $line['returnable_amt'] + $line['extra_srv_chg'];
		$discount = $line['discount_amount'] * $line['quantity'];
		$tax_free_price = get_tax_free_price_for_item(
			$line['stk_code'],
			($taxable_amount * $line['quantity']) - $discount,
			0,
			$trans['tax_included'],
			$tax_group_array
		);
		$line['unit_tax'] = (
			get_full_price_for_item(
				$line['stk_code'],
				($taxable_amount * $line['quantity']) - $discount,
				0,
				$trans['tax_included'],
				$tax_group_array
			)
			- $tax_free_price
		) / $line['quantity'];

		$prices[] = ($taxable_amount * $line['quantity']) - $discount;
		$items[] = $line['stk_code'];

        $unit_price = $taxable_amount - ($trans['tax_included'] ? $line['unit_tax'] : 0);
        $extra_srv_chg = $taxable_amount ? ($line['extra_srv_chg'] / $taxable_amount * $unit_price) : 0;
        $cost = (
            $line['govt_fee']
            + $line['bank_service_charge']
            + $line['bank_service_charge_vat']
            - $line['returnable_amt']
        );
        $line_total  = (
            $unit_price
            + $cost
            + $line['unit_tax']
        ) * $line['quantity'];

        $total_price    += $unit_price * $line['quantity'];
        $gross_total    += $line_total;
        $total_discount += $line['discount_amount'] * $line['quantity'];
        $total_tax      += $line['unit_tax'] * $line['quantity'];
        $total_govt_fee += $cost * $line['quantity']; 
        
        $trans_details[$i]['unit_price']    = $unit_price;
        $trans_details[$i]['unit_tax']      = $line['unit_tax'];
        $trans_details[$i]['_refs']         = implode('&nbsp;&nbsp;&nbsp;', $refs);
        $trans_details[$i]['_cost']         = $cost;
        $trans_details[$i]['_total']        = $line_total;
    }

	$net_total = $gross_total;
    if ($customer['show_discount'] && $total_discount > 0.001) {
        $net_total -= $total_discount;
    }

	$tax_items = array_filter(array_map(
        function ($tax_item) {
            if ($tax_item['Value'] == 0) {
                return null;
            }

            $name = 'Total ' . $tax_item['tax_type_name'];
            $amount = round2($tax_item['Value'], user_price_dec());

            return compact('name', 'amount');
        },
        get_tax_for_items(
			$items,
			$prices,
			$trans["freight_cost"],
			0,
			$trans['tax_included'],
			$tax_group_array
		)
    ));

	$heights = [0 => 48, 1 => 65, 2 => 71];

	ob_start(); ?>
	<!-- Begin: Header Section -->
	<img class="w-100" src="<?= pdf_header_path($trans['dimension_id'], $trans['trans_type']) ?>">
    <div class="text-center pt-2">
        <h2 class="font-weight-normal"><span class="pb-3 border-bottom"><?= $title ?> &nbsp;&nbsp;&nbsp;<span lang="ar"><?= $title_ar ?></span></span></h2>
    </div>
    <!-- End: Header Section -->

	<table class="table w-100 table-sm mt-3">
        <tr>
            <td class="border w-50">
                <table class="w-100">
                    <tbody>
                        <?php if ($is_tax_registered): ?>
                        <tr>
                            <td><b>TRN <span lang="ar">رقم تسجيل ضريبة</span></b></td>
                            <td><?= $dimension['gst_no'] ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td><b>Invoice No <span lang="ar">رقم الفاتورة</span></b></td>
                            <td><?= $trans['reference'] ?></td>
                        </tr>
                        <tr>
                            <td><b>Date <span lang="ar">التاريخ والوقت</span></b></td>
                            <td><?= sql2date($trans['ord_date']) ?></td>
                        </tr>
                        <tr>
                            <td><b>Stamp <span lang="ar">الطابع الزمني</span></b></td>
                            <td><?= $created_at->format('j-M-Y h:i:s A') ?></td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td class="border w-50 align-bottom">
                <table class="w-100">
                    <tbody>
                        <tr>
                            <td class="align-top w-50"><b>Customer Ref. <span lang="ar">كود العميل</span></b></td>
                            <td class="w-50"><?= $customer['debtor_ref'] ?></td>
                        </tr>
                        <tr>
                            <td class="align-top w-50"><b>Customer Name <span lang="ar">عميل</span></b></td>
                            <td class="w-50"><?= $trans['display_customer'] ?></td>
                        </tr>
                        <?php if (!$is_from_labour_contract): ?>
                            <?php if (!empty($trans['contact_person'])): ?>
                            <tr>
                                <td class="align-top w-50"><b>Contact Person <span lang="ar">المتعامل</span></b></td>
                                <td class="w-50"><?= $trans['contact_person'] ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if(!empty(trim($trans['customer_trn']))): ?>
                            <tr>
                                <td class="w-50"><b>Customer TRN <span lang="ar">رقم تسجيل ضريبة العميل</span></b></td>
                                <td class="w-50"><?= $trans['customer_trn'] ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td class="w-50"><b>Mobile No. <span lang="ar">رقم الهاتف المتحرك</span></b></td>
                                <td class="w-50"><?= $trans['contact_phone'] ?></td>
                            </tr>
                            <?php if(!empty(trim($trans['contact_email']))): ?>
                            <tr>
                                <td class="w-50"><b>Email <span lang="ar">البريد الإلكتروني</span></b></td>
                                <td class="w-50"><?= $trans['contact_email'] ?></td>
                            </tr>
                            <?php endif; ?>
                        <?php else: ?>
                            <tr>
                                <td><b>Contract Ref <span lang="ar">مرجع العقد</span></b></td>
                                <td><?= $contract->reference ?></td>
                            </tr>
                            <tr>
                                <td><b>Maid <span lang="ar">خادمة</span></b></td>
                                <td><?= $contract->maid->name ?></td>
                            </tr>
                            <tr>
                                <td><b>Contract Period <span lang="ar">مرجع العقد</span></b></td>
                                <td><?= $contract->contract_from->format(dateformat()) . ' - ' . $contract->contract_till->format(dateformat()) ?></td>
                            </tr>
                            <tr>
                                <td><b>Contract Amount <span lang="ar">قيمة العقد</span></b></td>
                                <td><?= price_format($contract->amount) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

	<table class="table table-sm text-right table-bordered w-100 mt-4">
        <thead>
            <tr>
                <th style="width: 5%;">Sl. No<br><span lang="ar">الرقم</span></th>
                <th style="width: 50%;">Service<br><span lang="ar">الخدمات</span></th>
                <th style="width: 5%;">Qty<br><span lang="ar">الكمية</span></th>
                <?php if(!$is_cost_grouped): ?>
                <th style="width: 12%;">Govt.Fee & Bank Chrg<br><span lang="ar">الرسوم الحكومية</span></td>
                <?php endif; ?>
                <th>Trans. Chrg<br><span lang="ar">تكلفة المعاملة</span></th>
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
                            <tr>
                                <td><?= $line['description'] ?></td>
                            </tr>
                            <tr>
                                <td><?= $line['_refs'] ?></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td class="text-center">
                    <?= number_format2($line['quantity'], get_qty_dec($line['stk_code'])) ?>
                </td>
                <?php if (!$is_cost_grouped): ?>
                <td><?= price_format($line['_cost']) ?></td>
                <?php endif; ?>
                <td>
                    <?= price_format(
                        $is_cost_grouped
                            ? ($line['unit_price'] + $line['_cost'])
                            : $line['unit_price']
                    )?>
                </td>
                <td><?= price_format($line['unit_tax']) ?></td>
                <td><?= price_format($line['_total']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="<?= $colspan ?>" class="text-right">
                    <b>Total Amount <span lang="ar">اجمالي القيمة</span></b>
                </td>
                <td class="text-right"><?= price_format($total_price + $total_govt_fee) ?></td>
            </tr>
            <?php if($trans['freight_cost'] != 0): ?>
            <tr>
                <td colspan="<?= $colspan ?>" class="text-right">
                    <b>Shipping <span lang="ar">رسوم الشحن</span></b>
                </td>
                <td class="text-right"><?= price_format($trans['freight_cost']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if($customer['show_discount'] && $total_discount > 0.001): ?>
            <tr>
                <td colspan="<?= $colspan ?>" class="text-right">
                    <b>Discount <span lang="ar">خصم</span></b>
                </td>
                <td class="text-right"><?= price_format($total_discount) ?></td>
            </tr>
            <?php endif; ?>
            <?php foreach ($tax_items as ["name" => $name, "amount" => $amount]): ?>
            <tr>
                <td colspan="<?= $colspan ?>" class="text-right">
                    <b><?= $name ?></b>
                </td>
                <td class="text-right"><?= price_format($amount) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if($processing_fee > 0): ?>
            <tr>
                <td colspan="<?= $colspan ?>" class="text-right">
                    <b>Other services <span lang="ar">خدمات أخرى</span></b>
                </td>
                <td class="text-right"><?= price_format($processing_fee) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="<?= $colspan ?>" class="text-right">
                    <b>Net Total <span lang="ar">صافي الإجمالي</span></b>
                </td>
                <td class="text-right"><?= price_format($net_total) ?></td>
            </tr>
        </tfoot>
    </table>
    <table class="w-100 small">
        <tbody>
            <tr>
                <td class="text-left py-1"><em>Kindly check the invoice and documents before leaving the counter</em></td>
                <td class="text-right py-1"><em><span lang="ar">الرجاء التأكد من الفاتورة والمستندات قبل مغادرة الكاونتر</span></em></td>
            </tr>
        </tbody>
    </table>

	<?php if ($trans['comments'] != ''): ?>
    <div class="w-100">
        <h4 class="mb-2">Comments</h4>
        <?= strtr($trans['comments'], ["\n" => '<br>']) ?>
    </div>
	<?php endif; ?>

	<div style="height: <?= $heights[count($allocations)] ?? $heights[0] ?>mm;"></div>
    <div class="fixed-bottom border-top border-white w-100">
        <?php if(
            pref('axispro.show_receipt_info_in_invoice', 0)
            && count($allocations) > 0
            && count($allocations) < 3
        ): ?>
        <h4 class="text-muted"><?= 'RECEIPT INFO'?></h4>
        <table class="table-sm text-center w-100 table-bordered">
            <thead>
                <tr>
                    <th>Receipt No.</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Collected By</th>
                    <th>Payment Mode</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($allocations as $allocator): ?>
                <tr>
                    <td><?= $allocator['reference'] ?></td>
                    <td><?= \Carbon\Carbon::parse($allocator['created_at'])->format(dateformat() . ' h:i:s A') ?></td>
                    <td><?= price_format($allocator['amt'] + $allocator['round_of_amount']) ?></td>
                    <td><?= $allocator['_created_by'] ?></td>
                    <td><?= $allocator['payment_method'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
		
		<div class="text-center w-100 border-top border-white">
			<img class="w-100" src="<?= pdf_footer_path($trans['dimension_id'], $trans['trans_type']) ?>">
		</div>
	</div>
	<?php return ob_get_clean();
}