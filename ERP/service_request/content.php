<?php
$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
$barcodeImage = base64_encode($generator->getBarcode($srv_req['barcode'], $generator::TYPE_CODE_128));
?>
<body>
    <img src="<?= pdf_header_path($srv_req['dimension_id'], null) ?>" class="w-100">
    <table class="w-100 table-sm mt-4">
        <tr>
            <td class="border w-50">
                <table class="w-100">
                    <tbody>
                        <tr>
                            <td colspan="2"><b>SERVICE REQUEST</b></td>
                        </tr>
                        <tr>
                            <td><b>Req Ref.</b></td>
                            <td><?= $srv_req['reference'] ?></td>
                        </tr>
                        <tr>
                            <td><b>Token No.</b></td>
                            <td><?= $srv_req['token_number'] ?></td>
                        </tr>
                        <tr>
                            <td><b>Barcode</b></td>
                            <td><img height="40" width="80"
                                     src="data:image/png;base64,<?= $barcodeImage ?>">
                                &nbsp;&nbsp;<p style="text-align: center;"><?= $srv_req['barcode'] ?></p></td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td class="border w-50">
                <table class="w-100">
                    <tbody>
                        <tr>
                            <td><b>Date - <span lang="ar">التاريخ والوقت</span></b></td>
                            <td><?= $srv_req['created_at'] ?></td>
                        </tr>
                        <tr>
                            <td><b>Customer - <span lang="ar">المتعامل</span></b></td>
                            <td><?= $srv_req['display_customer'] ?></td>
                        </tr>
                        <tr>
                            <td><b>Mobile No. - <span lang="ar">رقم الهاتف المتحرك</span></b></td>
                            <td><?= $srv_req['mobile'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <h3>Details Of Invoice</h3>
    <table class="table-sm table-bordered w-100">
        <thead>
            <tr>
                <th style="width: 8%;">Sl. No.<br><span lang="ar">الرقم</span></th>
                <th style="width: 46%;">Service<br><span lang="ar">الخدمات</span></th>
                <th style="width: 8%;">Qty<br><span lang="ar">الكمية</span></th>
                <th style="width: 10%;">Fee<br><span lang="ar">الرسوم</span></th>
                <th style="width: 10%;">Trans. Chg.<br><span lang="ar">رسوم الصفقة</span></th>
                <th style="width: 8%;">Tax Amt.<br><span lang="ar">قيمة المضافة</span></th>
                <th style="width: 10%;">Total<br><span lang="ar">الاجمالى بالدرهم</span></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($srv_req['_items'] as $i => $line): ?>
            <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td>
                    <?= $line['description'] ?>
                    <?php if(!empty($line['_extra'])): ?>
                        <br><?= $line['_extra'] ?>
                    <?php endif ?>
                </td>
                <td class="text-center"><?= $line['qty'] ?></td>
                <td class="text-right"><?= price_format($line['_fee']) ?></td>
                <td class="text-right"><?= price_format($line['price']) ?></td>
                <td class="text-right"><?= price_format($line['unit_tax']) ?></td>
                <td class="text-right"><?= price_format($line['_total']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot class="text-right">
            <tr>
                <td colspan="6"><b>Total Amount <span lang="ar">اجمالي القيمة</span></b></td>
                <td><?= price_format($srv_req['_sub_total']) ?></td>
            </tr>
            <tr>
                <td colspan="6"><b>Discount <span lang="ar">خصم</span></b></td>
                <td><?=  price_format($srv_req['_discount_amt']) ?></td>
            </tr>
            <tr>
                <td colspan="6"><b>Total VAT <span lang="ar">اجمالي الضريبة</span></b></td>
                <td><?= price_format($srv_req['_tax_amt']) ?></td>
            </tr>
            <tr>
                <td colspan="6"><b>Net Amount <span lang="ar">كمية الشبكة</span></b></td>
                <td><?=  price_format($srv_req['_net_amt']) ?></td>
            </tr>
        </tfoot>
    </table>
    <div class="w-100 text-right text-muted small py-1"><i>Entered by: <?= $srv_req['employee'] ?></div>

    <div style="height: 20mm;"></div>
    <div class="w-100 fixed-bottom">
        <img src="<?= pdf_footer_path($srv_req['dimension_id'], null) ?>">
    </div>
</body>