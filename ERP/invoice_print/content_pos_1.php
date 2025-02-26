<?php

use App\Models\Inventory\StockCategory;

?>
    <?php if ($is_watermarked): ?>
    <watermarktext content="COPY" alpha="0.1" />
    <?php endif; ?>
    <!-- Begin: Header Section -->
    <img class="w-100" src="<?= pdf_header_path($trans['dimension_id'], $trans['type']) ?>">
    <div class="text-center pt-2">
        <h2 class="font-weight-normal"><span class="pb-3 border-bottom"><?= $title ?> &nbsp;&nbsp;&nbsp;<span lang="ar"><?= $title_ar ?></span></span></h2>
    </div>
    <!-- End: Header Section -->
    <table class="w-100 table-sm mt-3">
        <tr>
            <td class="border w-50">
                <table class="w-100">
                    <tbody>
                        <tr>
                            <td colspan="2">
                                <img height="40" width="80" src="data:image/png;base64,<?= $barcodeImage ?>">
                                <p><?= $trans['barcode'] ?></p>
                            </td>
                        </tr>
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
                            <td><?= sql2date($trans['tran_date']) ?></td>
                        </tr>
                        <tr>
                            <td><b>Invoiced At <span lang="ar">بفاتورة في</span></b></td>
                            <td><?= $created_at->format('j-M-Y h:i:s A') ?></td>
                        </tr>
                        <?php if ($is_from_labour_contract && $contract->category_id == StockCategory::DWD_PACKAGETWO): ?>
                            <tr>
                                <td><b>Invoice Period <span lang="ar">فترة الفاتورة</span></b></td>
                                <td><?= sql2date($trans['period_from']) . ' - ' . sql2date($trans['period_till']) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </td>
            <td class="border w-50 align-bottom">
                <table class="w-100">
                    <tbody>
                        <tr>
                            <td class="align-top w-50"><b>Customer Ref. <span lang="ar">كود العميل</span></b></td>
                            <td class="w-50"><?= $trans['debtor_ref'] ?></td>
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
                                <td class="w-50"><?= $trans['customer_mobile'] ?></td>
                            </tr>
                            <?php if(!empty(trim($trans['customer_email']))): ?>
                            <tr>
                                <td class="w-50"><b>Email <span lang="ar">البريد الإلكتروني</span></b></td>
                                <td class="w-50"><?= $trans['customer_email'] ?></td>
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

    <table class="table-sm text-right table-bordered w-100 mt-4">
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
                                <td><?= $line['StockDescription'] ?></td>
                            </tr>
                            <tr>
                                <td><?= $line['_refs'] ?></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td class="text-center">
                    <?= number_format2($line['quantity'], get_qty_dec($line['stock_id'])) ?>
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

            <?php if($round_off != 0): ?>
            <tr>
                <td colspan="<?= $colspan ?>" class="text-right">
                    <b>Round Off <span lang="ar">تقريب المبلغ</span></b>
                </td>
                <td class="text-right"><?= price_format($round_off) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="<?= $colspan ?>" class="text-right">
                    <b>Net Total <span lang="ar">صافي الإجمالي</span></b>
                </td>
                <td class="text-right"><?= price_format($net_total) ?></td>
            </tr>
            <?php if($is_customer_card_payment && $total_payable > 0.001): ?>
            <tr>
                <td colspan="<?= $colspan ?>" class="text-right">
                    <b>Total Paid<span lang="ar">إجمالي المدفوعة</span></b>
                </td>
                <td class="text-right"><?= price_format($total_paid) ?></td>
            </tr>
            <tr>
                <td colspan="<?= $colspan ?>" class="text-right">
                    <b>Total Payable<span lang="ar">إجمالي مستحق الدفع</span></b>
                </td>
                <td class="text-right"><?= price_format($total_payable) ?></td>
            </tr>
            <?php endif; ?>
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
    <div class="w-100">
        <h4 class="mb-2">Comments</h4>
        <?= strtr($comments, ["\n" => '<br>']) ?>
    </div>
    <?php $is_voided && is_voided_display(ST_SALESINVOICE, $trans_no, trans("This transaction has been voided.")); ?>

    <div style="height: <?= $heights[count($allocations)] ?? $heights[0] ?>mm;"></div>
    <div class="fixed-bottom border-top border-white">
        <?php if (floatcmp($trans['alloc'], $trans['Total']) >= 0): ?>
        <div class="w-100 text-right">
            <img src="<?= company_path() ?>/images/paid_stamp.png" style="width: 20%; margin-right: 15mm;">
        </div>
        <?php endif; ?>
        <table class="w-100 table-borderless my-2">
            <tbody>
                <tr>
                    <td style="width:34%;">
                        <table class="w-100 text-center">
                            <tbody>
                                <?php if(!in_array($trans['dimension_id'], [DT_ADHEED, DT_ADHEED_OTH, DT_DUBAI_COURT])): ?>
                                <tr>
                                    <td><?= $created_by ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td>Authorized Signatory - <span lang="ar">المخول بالتوقيع</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td class="align-middle">
                        &nbsp;
                    </td>
                    <td style="width:45%;" class="align-bottom text-right pr-3"> 
                        <?php if ($is_emailing) : ?>
                            <small style="color: red;">This is system generated Invoice hence no signature required.</small>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php if(
            pref('axispro.show_receipt_info_in_invoice', 0)
            && count($allocations) > 0
            && count($allocations) < 3
        ): ?>
        <h4 class="text-muted"><?= $is_voided ? 'VOIDED RECEIPT INFO' : 'RECEIPT INFO'?></h4>
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
                    <td><?= \Carbon\Carbon::parse($allocator['tran_date'])->format(dateformat()) ?></td>
                    <td><?= price_format($allocator['amt'] + $allocator['round_of_amount']) ?></td>
                    <td><?= $allocator['_created_by'] ?></td>
                    <td><?= $allocator['payment_method'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="w-100 text-center">
            <img class="w-100" src="<?= pdf_footer_path($trans['dimension_id'], $trans['type']) ?>">
        </div>
    </div>