<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AxisPro - Invoice</title>
    <style>
        html {
            font-size: 9pt;
        }

        body {
            max-width: 76.2mm;
            color: black;
            padding: 0.125rem;
            margin: auto;
        }

        header {
            margin-bottom: 1rem;
        }

        .logo {
            height: auto;
            width: 15%;
            vertical-align: middle;
            display: inline-block;
            margin: auto 0.6rem;
        }

        .h1 {
            font-size: 1.325rem;
            font-weight: bold;
            display: inline-block;
            width: 78%;
            vertical-align: middle;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            font-weight: bold;
            font-size: 1.25rem;
        }

        tfoot td {
            text-align: right;
        }

        hr {
            margin-top: 0.125rem;
            margin-bottom: 0.125rem;
            border: 0;
            border-top: 1px dashed black;
        }

        .w-100 {
            width: 100%;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .desc {
            width: 50%;
        }

        .price {
            width: 20%;
        }

        .qty,
        .price {
            text-align: right;
        }

        .sl {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="text-center">
        <header>
            <span class="h1"><?= $company_name ?></span>
        </header>

        <p>
            <?php if ($is_tax_registered): ?>
                <span>TRN: <?= $dimension['gst_no'] ?></span><br>
            <?php endif; ?>
            <span><?= $title ?></span>
        </p>
    </div>

    <hr>

    <p class="text-center">
        <span>Bill # : <?= $trans['reference'] ?></span></br>
        <span>Date : <?= $created_at->format('d/m/Y h:i A') ?></span></br>
    </p>

    <hr>

    <table>
        <thead>
            <tr class="tabletitle">
                <th class="sl">#</th>
                <th class="desc">Item</th>
                <th class="qty">Qty</th>
                <th class="price">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($trans_details as $i => $line) : ?>
                <tr>
                    <th><?= $i + 1 ?></th>
                    <td class="desc"><?= $line['description'] ?></td>
                    <td class="qty"><?= $line['quantity'] ?></td>
                    <td class="price"><?= price_format($line['_total']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">
                    <b>Sub Total</b>
                </td>
                <td><?= price_format($total_price + $total_govt_fee) ?></td>
            </tr>
            <?php foreach ($tax_items as ["name" => $name, "amount" => $amount]): ?>
            <tr>
                <td colspan="3">
                    <b><?= $name ?></b>
                </td>
                <td><?= price_format($amount) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3">
                    <b>Total</b>
                </td>
                <td><?= price_format($net_total) ?></td>
            </tr>
        </tfoot>
    </table>

    <hr>

    <p class="text-center">Thank you for your business!</p>
</body>

</html>