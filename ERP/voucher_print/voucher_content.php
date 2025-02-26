<body>
    <img class="w-100" src="<?= pdf_header_path($voucher['dimension_id'], $voucher['type']) ?>">
    <div class="text-center mt-4">
        <h1><?= $title ?></h1>
    </div>

    <div class="w-100 text-right py-2"><?= $ref_label . ': ' . $voucher['ref'] ?></div>
    <hr class="mb-3">
    <table class="table-md w-100">
        <tbody>
            <tr>
                <td class="font-weight-bold">Date :</td>
                <td style="width: 65%" class="border-bottom"><?= $voucher['trans_date'] ?></td>
                <td class="text-right" lang="ar">تاريخ :</td>
            </tr>
            <tr>
                <td class="font-weight-bold">Amount :</td>
                <td style="width: 65%" class="border-bottom"><?= $voucher['amount'] ?> AED</td>
                <td class="text-right" lang="ar">مبلغ :</td>
            </tr>
            <!-- <tr>
                <td class="font-weight-bold">Amt. in words :</td>
                <td style="width: 65%" class="border-bottom"><?= $voucher['_amount_in_words'] ?></td>
                <td class="text-right" lang="ar">مبلغ بالكلمات :</td>
            </tr> -->
            <tr>
                <td class="font-weight-bold"><?= $label ?> :</td>
                <td style="width: 65%" class="border-bottom"><?= $voucher['_entity'] ?></td>
                <td class="text-right" lang="ar"><?= $label_in_ar ?> :</td>
            </tr>
            <tr>
                <td class="font-weight-bold">Description :</td>
                <td style="width: 65%" class="border-bottom"><?= $voucher['memo_'] ?></td>
                <td class="text-right" lang="ar">وصف :</td>
            </tr>
        </tbody>
    </table>
    <hr class="mt-5">
    <table class="w-100 mt-5">
        <tbody>
            <tr>
                <td style="width: 60%;">&nbsp;</td>
                <td style="width: 40%;">
                    <table class="w-100 table-sm">
                        <tbody>
                            <tr>
                                <td><b>Received By:</b> <?= $recipient ?></td>
                            </tr>
                            <tr>
                                <td style="height: 30mm" class="text-right align-bottom">Date: <?= date(getDateFormatInNativeFormat()) ?></td>
                            </tr>
                            <tr class="border-light border-top">
                                <td>Signature</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>