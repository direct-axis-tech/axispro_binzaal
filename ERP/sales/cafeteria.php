<?php

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\Dimension;
use App\Models\Sales\Customer;

$path_to_root = '..';
$page_security = 'SA_SALESINVOICE';

require_once $path_to_root . '/includes/session.inc';
require_once $path_to_root . "/sales/includes/ui/sales_order_ui.inc";
require_once $path_to_root . "/sales/includes/sales_db.inc";
require_once $path_to_root . "/sales/includes/db/sales_types_db.inc";

$currDimensionId = $_GET['dim_id'] ?? $_POST['dimension_id'];
$dimension = Dimension::find($currDimensionId);
$api = app('api');

// Set the GET variables for proper api call :-/
$_GET['cost_center'] = $_POST['cost_center_id'] = $currDimensionId;
$permittedCategories = $api->getPermittedCategoriesFromDepartmentForInvoicing('array');
$permittedItems = $api->get_permitted_items_for_invoicing('array')['data'];
$configuredPaymentMethods = array_filter(explode(',', $dimension->enabled_payment_methods)) ?: ['PayNow', 'PayNoWCC'];
$bankAccounts = BankAccount::query()
    ->select('*')
    ->selectRaw("concat_ws(' - ', account_code, bank_account_name) as formatted_name")
    ->get()
    ->keyBy('id');

$paymentMethods = [];
foreach ($configuredPaymentMethods as $key) {
    $value = $GLOBALS['global_pay_types_array'][$key];
    $paymentMethods[$key] = [
        'key' => $key,
        'value' => $value,
        'display_name' => strtr($value, ['CreditCard' => 'Card']),
        'payment_accounts' => collect(get_payment_accounts($value, null, $dimension))
            ->mapWithKeys(function ($k) use ($bankAccounts) {
                return [$k => data_get($bankAccounts[$k], 'formatted_name')];
            })
            ->toArray()
    ];
}

$currency = get_company_currency();
$salesType = get_sales_type(SALES_TYPE_TAX_INCLUDED);

if (isset($_POST['process_invoice'])) {
    $cart = create_cart(
        $salesType,
        $currency,
        $_POST['items'],
        $_POST['payment_method'],
        $_POST['payment_account'],
        $dimension
    );

    $transNo = $cart->write();
    // Just being explicit
    unsetSessionAndPostVariablesUsedForInvoicing();

    echo json_encode([
        "status" => 200,
        "print" => getInvoicePrint($transNo)
    ]);
    exit();
}

ob_start(); ?>
    <link href="<?= $path_to_root ?>/../assets/plugins/general/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css"/>

    <style>
        #filter-items {
            position: relative;
            z-index: 1;
        }

        .modal .modal-content .modal-header .close {
            font-family: "Font Awesome 5 Free";
        }

        .modal .modal-content .modal-header .close:before {
            content: "\f00d";
        }

        .head-bg {
            background: #cdecff;
            box-shadow: inset 0 2px 0 rgba(255, 255, 255, 0.5), 0 2px 2px rgba(0, 0, 0, 0.19);
            border-bottom: solid 1px #b5b5b5;
            margin-bottom: 5px;
            margin-top: 2px;

            padding-top: 5px;

        }

        .cat-tile {
            width: 225px;
            font-size: 1.5rem;
            height: 150px;
            text-align: center;
            box-shadow: inset 0 2px 0 rgba(255, 255, 255, 0.5), 0 2px 2px rgba(0, 0, 0, 0.19);
            border-bottom: solid 2px #b5b5b5;
            border-radius: 35px;
        }

        .cat-tile:hover {
            transform: scale(1.05);
        }

        .cat-tile-l2 {
            width: 180px;
            height: 100px;
            text-align: center;
            line-height: 75px;
            margin-left: 5px;
        }

        .qty-control .input-group-btn,
        .qty-control .form-control {
            font-size: 1.25rem;
            height: calc(1.5em + 1.3rem + 2px);
        }

        .qty-control .input-group-btn {
            padding: 0rem 1.65rem;
        }

        .qty-control .form-control {
            padding: 0rem 0.25rem;
        }        

        .rm-control .btn [class^="fa-"],
        .rm-control .btn [class*=" fa-"] {
            font-size: 1.75rem;
        }

        .search-control .form-control,
        .search-control .btn {
            height: calc(1.5em + 2.3rem + 2px);
        }

        h5 {
            font-size: 2rem;
        }

        #container1 {
            height: 100%;
            width: 100%;
            overflow: hidden;
        }

        #container2 {
            width: 100%;
            height: 99%;
            overflow: auto;
            padding-right: 15px;
        }

        .amount-to-be-collected {
            margin: 12px;
            color: #063f08;
            text-align: center;
            border: 1px solid #CCC;
        }

        .plaintext {
            background-color: transparent;
            color: inherit;
            font-size: inherit;
            border: 0;
        }

        .bttn {
            display: inline-block;
            text-decoration: none;
            color: rgba(152, 152, 152, 0.43);
            width: 55px;
            height: 55px;
            font-size: 40px;
            border-radius: 50%;
            text-align: center;
            vertical-align: middle;
            margin-right: 1rem;
            margin-left: 1rem;
            padding: 0.1rem 0.5rem;
            overflow: hidden;
            font-weight: bold;
            background-image: -webkit-linear-gradient(#e8e8e8 0%, #d6d6d6 100%);
            background-image: linear-gradient(#e8e8e8 0%, #d6d6d6 100%);
            text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.66);
            box-shadow: inset 0 2px 0 rgba(255, 255, 255, 0.5), 0 2px 2px rgba(0, 0, 0, 0.19);
            border-bottom: solid 2px #b5b5b5;
        }

        .my-button {
            box-shadow: inset 0 2px 0 rgba(255, 255, 255, 0.5), 0 2px 2px rgba(0, 0, 0, 0.19);
            border-bottom: solid 2px #b5b5b5;
            border-radius: 35px;
        }

        .bttn .fa {
            line-height: 80px;
        }

        .bttn:active {
            background-image: -webkit-linear-gradient(#efefef 0%, #d6d6d6 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5), 0 2px 2px rgba(0, 0, 0, 0.19);
            border-bottom: none;
        }

        .white {
            color: white;
            border: 1px solid black;
            align-items: center;
        }

        @media screen and (max-height: 768px) {
            .size {
                height: 440px;
            }
        }

        .borders {
            border: 1px solid #9a9a9a;
            margin-bottom: 2px;
            border-radius: 20px;
            padding-left: 10px;
            padding-top: 3px;
        }

        .card {
            border: 1px solid #9a9a9a;
            height: 80vh;
        }

        .card-body {
            height: 68.9vh;
            overflow-y: auto;
        }

        .card-header {
            padding: 0.75rem 1.25rem;
            min-height: 6rem;
            background: #8d9596;
            height: 65px;
        }

        .card-footer {
            background: #a5adae;
        }
    </style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans("Cafeteria - AxisPro"), false, false, "", "", false, '', true);

$items = [];
foreach ($permittedItems as $item) {
    $_item = [
        "stock_id" => $item['stock_id'],
        "category_id" => $item['category_id'],
        "name" => $item['description'],
        "price" => get_price($item['stock_id'], $currency, $salesType['id'], $salesType['factor'])
    ];

    $items[] = $_item;
}

?>
<div class="container-fluid parent-container">
    <div class="row">
        <div class="col-md-12">
        </div>
    </div>
    <div class="row  head-bg d-flex align-items-center">
        <div class="col-md-6">
            <p class="">New Order:</p>
            <h2 class=""><?= pref('company.coy_name') ?></h2>
        </div>
        <div class="col-md-6 text-center">
            <button id="place-order" class="btn shadow-none btn-primary btn-lg my-button">
                Place Order
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-5">
            <div class="card size">
                <div id="filter-items" class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group input-group-lg search-control">
                                <input
                                    id="search-food-by-val"
                                    data-search-by="code";
                                    type="text"
                                    class="form-control"
                                    placeholder="Search by item code">
                                <div class="input-group-append">
                                    <button class="btn btn-secondary shadow-none" type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group input-group-lg search-control">
                                <input
                                    id="search-food-by-name"
                                    type="text"
                                    data-search-by="name"
                                    class="form-control"placeholder="Search food item">
                                <div class="input-group-append">
                                    <button class="btn btn-secondary shadow-none" type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-body" style="height: 375px;overflow-y: scroll">
                    <form action="" id="items-cart">
                        <table class="table" id="items-list">
                            <thead>
                                <th width="30%">Item Name</th>
                                <th width="40%" class="text-center">Qty</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Total</th>
                                <th></th>
                            </thead>
                            <tbody id="items-list_tbody">

                            </tbody>
                        </table>

                        <input type="hidden" name="payment_method" id="payment_method">
                        <input type="hidden" name="payment_account" id="payment_account">
                    </form>
                </div>
                <div class="card-footer" style="position: absolute; bottom: 0;width: 100%;overflow-x: hidden">
                    <div class="row">
                        <div class="col-md-12 text-nowrap">
                            <span
                                class="float-right"
                                style="font-size: 20pt">
                                Total Amount :
                                <strong>
                                    AED&nbsp;
                                    <input
                                        type="number"
                                        min="0.00"
                                        readonly
                                        id="grand-total"
                                        class="plaintext"
                                        value="0.00">
                                </strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7 p-0">
            <div class="card size">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class=" justify-content-start text-white">
                        <h5 class="mt-2"><i class="fas fa-home"></i></h5>
                    </div>
                    <div class="">
                        <button id="backToCategories" class="bttn">
                            <i class="fas fa-arrow-circle-left"></i>
                        </button>
                        <button id="scrollUp" class="bttn">
                            <i class="fas fa-arrow-circle-up"></i>
                        </button>
                        <button id="scrollDown" class="bttn">
                            <i class="fas fa-arrow-circle-down"></i>
                        </button>
                    </div>
                </div>
                <div
                    id="cat-card"
                    class="card-body">
                    <div class="row">
                        <?php foreach($permittedCategories as $cat): ?>
                        <div class="col-auto mt-2">
                            <button
                                id="<?= $cat['category_id'] ?>"
                                data-category="<?= $cat['description'] ?>"
                                data-id="<?= $cat['category_id'] ?>"
                                class="btn btn-primary cat-tile shadow-none"
                                style="background-color: #543f3f">
                                <?= $cat['description'] ?>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <ol id="items-list" class="d-none">
            <?php foreach($items as $item): ?>
            <li
                data-permitted-item=""
                data-stock_id="<?= $item['stock_id'] ?>"
                data-category_id="<?= $item['category_id'] ?>"
                data-price="<?= $item['price'] ?>"
                data-name="<?= $item['name'] ?>">
            </li>
            <?php endforeach; ?>
        </ol>
    </div>

    <!-------------------PAYMENT---------------------->
    <div class="modal fade" role="dialog" id="PaymentModel">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close fa shadow-none" data-dismiss="modal" data-bs-dismiss="modal"  aria-label="Close">
                        <i class="fa fa-close"></i>
                    </button>
                    <h4 class="modal-title" style="position: absolute;">PAYMENT</h4>
                </div>

                <div class="modal-body p-5">
                    <div class="text-center">
                        <?php foreach($paymentMethods as $key => $config): ?>
                        <button
                            type="button"
                            data-target-id="div-<?= $config['key'] ?>"
                            class="btn btn-primary payment-chooser shadow-none text-uppercase min-w-200px h-100px fs-1 mx-10"
                            data-method="<?= $config['value'] ?>"><?= $config['display_name'] ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-5 text-center">
                        <?php foreach($paymentMethods as $key => $config): ?>
                        <div data-div="payment-account-collapse" class="d-none" id="div-<?= $config['key'] ?>">
                            <?php foreach ($config['payment_accounts'] as $id => $name): ?>
                            <button
                                type="button"
                                class="btn mx-10 btn-primary payment-account shadow-none text-nowrap text-uppercase text-center min-w-200px h-50px fs-4"
                                data-account-id="<?= $id ?>"><?= $name ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="modal-footer clearfix">
                    <button
                        class="float-right btn btn-primary shadow-none"
                        id="btn_proceed_to_pay">
                        Proceed
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ob_start(); ?>
<script>
    $(function() {
        var storage = {
            categories: [],
            items: []
        };

        // read the categories from the dom.
        document.querySelectorAll('[data-category]').forEach(function (elem) {
            storage.categories.push({
                id: elem.dataset.id,
                desc: elem.dataset.category
            });
        })

        // read the items from the dom
        document.querySelectorAll('[data-permitted-item]').forEach(function (elem) {
            storage.items.push({
                stock_id: elem.dataset.stock_id,
                category_id: elem.dataset.category_id,
                name: elem.dataset.name,
                price: parseFloat(elem.dataset.price)
            })
        })

        // when clicking back button show categories
        $('#backToCategories').on("click", function () {
            populateContainer(getCategoriesCollectionElem());
        })

        // when clicking down or up button scroll the UI
        $('#scrollUp').on('click', scrollUp);
        $('#scrollDown').on('click', scrollDown);

        // Add handler for search input
        $('[data-search-by]').on('keyup', function() {
            var predicate = null;
            var val = this.value;
            if (this.dataset.searchBy == 'code') {
                predicate = function (item) {
                    return item.stock_id.indexOf(val) !== -1;
                }
            } else {
                predicate = function (item) {
                    return item.name.toLowerCase().indexOf(val.toLowerCase()) !== -1;
                }
            }

            searchItem(predicate);
        });

        // Add handler for clicking on category
        $('#cat-card').on("click", "[data-category]", function() {
            var category_id = this.dataset.id;
            var predicate = function (item) {
                return item.category_id == category_id;
            };
            searchItem(predicate);
        })

        // Add handler for clicking on item
        $('#cat-card').on("click", '[data-stock_id]', function() {
            var item = {
                elemId: this.id,
                stock_id: this.dataset.stock_id,
                name: this.dataset.name,
                price: this.dataset.price
            };

            if (isDuplicateItem(item.stock_id)) {
                return toastr.error('Already added');
            }

            var row = $(
                `<tr data-stock_id="${item.stock_id}">
                    <td
                        class="td-stock-id align-middle"
                        data-val="${item.stock_id}">
                        <input type="hidden" value=${item.name}" class="items">
                        ${item.name}
                    </td>
                    <td class="align-middle qty-control">
                        <div class="input-group">
                            <button
                                type="button"
                                data-btn-decrement
                                class="btn btn-default btn-circle btn-md shadow-none input-group-btn">
                                -
                            </button>
                            <input
                                type="text"
                                data-qty
                                name="items[${item.stock_id}][qty]"
                                style="text-align: center;"
                                class="form-control input-number clsQty"
                                value="1"
                                min="1"
                                max="100">
                            <button
                                data-btn-increment
                                type="button"
                                class="btn btn-default btn-circle btn-md shadow-none input-group-btn">
                                +
                            </button>
                        </div>
                    </td>
                    <td class="align-middle price text-center" >
                        <input type="hidden" data-price name="item[${item.stock_id}][price]" value="${item.price}">
                        <span data-price>
                            ${item.price}
                        </span>
                    </td>
                    <td class="align-middle  text-center" >
                        <span class="total" data-line-total>
                            ${item.price}
                        </span>
                    </td>
                    <td class="align-middle text-center rm-control">
                        <button
                            data-btn-delete
                            class="btn shadow-none"
                            href="#"
                            style="color: #ff4c4d">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </td>
                </tr>`
            )[0];

            $("#items-list_tbody").append(row);
            calculateGrandTotal();
        });

        // Add handler for increment/decrement qty
        $('#items-list_tbody').on(
            'click',
            '[data-btn-increment], [data-btn-decrement]',
            function() {
                var parentTr = $(this).closest('tr')[0];
                var qtyElem = parentTr.querySelector('[data-qty]');
                var lineTotalElem = parentTr.querySelector('[data-line-total]');

                var currentQty = +qtyElem.value;
                var unitPrice = parseFloat(parentTr.querySelector('[data-price]').value);
                var updatedQty = this.dataset.btnDecrement !== undefined ? currentQty - 1 : currentQty + 1;
                var lineTotal = (updatedQty * unitPrice).toFixed(2);

                if (updatedQty < 1) {
                    updatedQty = 1;
                }

                qtyElem.value = updatedQty;
                lineTotalElem.textContent = lineTotal;
                calculateGrandTotal();
            }
        )

        // Add handler for deleting item
        $("#items-list_tbody").on(
            'click',
            '[data-btn-delete]',
            function () {
                $(this).closest('tr').remove();
                calculateGrandTotal()
            }
        );

        // Add handler for placing the order
        $('#place-order').on('click', function() {
            var payingAmount = document.querySelector('#grand-total').textContent;
            $("#paying_amount").val(payingAmount);
            $('#PaymentModel').modal('show');
        })

        // Add handler for choosing payment method
        $(document).on('click', '.payment-chooser', function () {
            var method = $(this).data("method");
            $('#payment_method').val(method).trigger('change');
            $('.payment-chooser').css('background-color', '#384ad7');
            $(this).css('background-color', '#D08221');
        });

        // Handle change in payment method
        $(document).on('change', '#payment_method', function () {
            const targetDivId = paymentAccountTarget();

            // clear any previously selected payment account
            $('#payment_account').val('').trigger('change');
            $(`[data-div="payment-account-collapse"]`).addClass('d-none');

            // if there is only one account in the configuration select it automatically
            const paymentAccounts = $(`#${targetDivId} .payment-account`);
            if (paymentAccounts.length == 1) {
                paymentAccounts.first().trigger('click');
            }

            // else show the div for selecting payment account
            else {
                $(`#${targetDivId}`).removeClass('d-none');
            }
        });

        // Add handler for choosing payment account
        $(document).on('click', '.payment-account', function () {
            var accountId = $(this).data("accountId");
            $('#payment_account').val(accountId).trigger('change');
        });

        // Handle change in payment account
        $(document).on('change', '#payment_account', function () {
            $('.payment-account').css('background-color', '#384ad7');

            $(`#${paymentAccountTarget()} .payment-account[data-account-id="${this.value || -1}"]`).css('background-color', '#D08221');
        });

        // Add handler for proceeding with the payment
        $("#btn_proceed_to_pay").click(function () {
            var payment_method = $("#payment_method").val();
            if (payment_method === "") {
                toastr.error("Please select a payment method")
                return false;
            }

            var payment_account = $("#payment_account").val();
            if (payment_account === "") {
                toastr.error("Please select a payment account. If not showing the option to select the account, Please ask the system administrator to configure it");
                return false;
            }

            $('#PaymentModel').modal('hide');
            $('.payment-chooser .payment-account').css('background-color', '#384ad7');

            var itemsCartForm = document.getElementById('items-cart')
            var formData = new FormData(itemsCartForm);
            formData.set('dimension_id', '<?= $currDimensionId ?>');
            formData.set('process_invoice', '1');

            ajaxRequest({
                url: itemsCartForm.action,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json'
            }).done(function (res) {
                if(res.status == 200) {
                    toastr.success("Order Placed Successfully!");

                    setTimeout(function() {
                        var popup = window.open(res.print, '', 'letf=100,top=100,width=600,height=600');
                        popup.onload = function() {
                            popup.print();

                            popup.onfocus = function() {
                                setTimeout(function() {
                                    popup.close();
                                }, 500);
                            }
                        }
                        
                    }, 0)

                    empty(document.querySelector('#items-list_tbody'));
                    $('.payment-chooser .payment-account').css('background-color', '#384ad7');
                    document.querySelector('#payment_method').value = '';
                    document.querySelector('#payment_account').value = '';
                } else {
                    toastr.error('Something went wrong!')
                }
            }).fail(defaultErrorHandler);

        });

        function paymentAccountTarget() {
            return $(`.payment-chooser[data-method="${$('#payment_method').val()}"]`).data('targetId');
        }

        function calculateGrandTotal() {
            var total = 0;
            var tbody = document.querySelector('#items-list_tbody');
            var lineTotalElems = tbody.querySelectorAll('[data-line-total]');
            var grandTotalEl = document.querySelector('#grand-total');

            lineTotalElems.forEach(function (lineTotalElem) {
                total += parseFloat(lineTotalElem.textContent);
            })
            grandTotalEl.value = total.toFixed(2);
        }

        function searchItem(predicate) {
            var filtered = storage.items.filter(predicate);

            populateContainer(getItemsCollectionElem(filtered))
        }

        function populateContainer(content) {
            categoryContainer = document.getElementById('cat-card');

            empty(categoryContainer);
            categoryContainer.appendChild(content);
        }

        function getCategoriesCollectionElem() {
            var row = document.createElement('div');
            row.classList.add('row');

            storage.categories.forEach(function(category) {
                row.appendChild($(
                    `<div class="col-auto mt-2">
                        <button
                            id="category_${category.id}"
                            data-category="${category.desc}"
                            data-id="${category.id}"
                            class="btn btn-primary cat-tile shadow-none"
                            style="background-color: #543f3f">
                            ${category.desc}
                        </button>
                    </div>`
                )[0])
            })

            return row;
        }

        function getItemsCollectionElem(items) {
            if (items.length == 0) {
                return $('<strong style="color: red">No Data</strong>')[0];
            }

            var row = document.createElement('div');
            row.classList.add('row');

            items.forEach(function(item) {
                row.appendChild($(
                    `<div class="col-auto mt-2">
                        <button
                            id="item_${item.stock_id}"
                            data-stock_id="${item.stock_id}"
                            data-price="${item.price}"
                            data-name="${item.name}"
                            class="btn btn-primary cat-tile shadow-none">
                            ${item.name}
                        </button>
                    </div>`
                )[0])
            })

            return row;
        }

        function scrollDown() {
            $("#cat-card").animate({scrollTop: "+=100px"}, 100)
        }

        function scrollUp() {
            $("#cat-card").animate({scrollTop: "-=100px"}, 100)
        }

        function isDuplicateItem(stock_id) {
            var item = document
                .querySelector("#items-list_tbody")
                .querySelector(`[data-stock_id="${stock_id}"]`);

            return item != null;
        }
    });
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean();
end_page();

/**
 * Creates the items cart used throughout the sales module
 *
 * @param array $salesType
 * @param string $currency
 * @param array $items
 * @param string $paymentMethod
 * @param string $paymentAccount
 * @param Dimension $dimension
 * @return Cart
 */
function create_cart($salesType, $currency, $items, $paymentMethod, $paymentAccount, $dimension) {
    global $Refs;

    $dimensionId = $dimension->id;
    $_SESSION['Items'] = new Cart(ST_SALESINVOICE, 0, false, $dimensionId);
    $doc = &$_SESSION['Items'];

    // Set the department since it is depended on by other parts of the code.
    $doc->dim_id = $dimensionId;

    // Set the customer details
    $customerId = data_get($dimension, 'default_customer_id') ?: Customer::WALK_IN_CUSTOMER;
    $branch = get_default_branch($customerId);
    get_customer_details_to_order($doc, $customerId, $branch['branch_code']);
    $doc->Branch = $branch['branch_code'];
    $doc->dimension_id = $dimensionId;

    // Set other necessarry details
    $doc->reference = $Refs->get_next(ST_SALESINVOICE, null, [
        'date' => Today(),
        'dimension' => $dimension
    ]);
    $doc->pay_type = array_flip($GLOBALS['global_pay_types_array'])[$paymentMethod];
    $doc->payment_account = $paymentAccount;
    $doc->contact_person = "Walk-in";
    $doc->cust_ref = "";
    $doc->barcode = "";
    $doc->customer_name   = "Walk-in";
    $doc->phone           = "";
    $doc->email           = "";
    $doc->tax_id          = "";
    $doc->mistook_staff_id = null;
    $doc->credit_card_no = null;
    $doc->line_items = [];
    $doc->created_by = user_id();

    // Add all the items to the cart
    foreach ($items as $stockId => $item) {
        $unitPrice = get_price($stockId, $currency, $salesType['id'], $salesType['factor']);
        $doc->add_to_cart(count($doc->line_items), $stockId, $item['qty'], $unitPrice, 0);
    }
    
    // Customers can have different sales type. In cafeteria always the sales type is tax included.
    $doc->set_sales_type(
        $salesType['id'],
        $salesType['sales_type'],
        $salesType['tax_included'],
        $salesType['factor']
    );
    
    $_POST['customer_ref'] = '';
    $_POST['OrderDate'] = $doc->document_date;
    $_POST['invoice_type'] = 'Cash';
    $_POST['contact_person'] = $doc->contact_person;

    return $doc;
}

function unsetSessionAndPostVariablesUsedForInvoicing() {
    unset($_SESSION['Items']);
    unset($_POST['customer_ref']);
    unset($_POST['OrderDate']);
    unset($_POST['invoice_type']);
    unset($_POST['contact_person']);
}

function getInvoicePrint($transNo)
{
    $ancorTag = print_document_link(
        "$transNo-".ST_SALESINVOICE,
        '',
        true,
        ST_SALESINVOICE,
        false,
        'printlink',
        '',
        0,
        0,
        false,
        true
    );

    preg_match('/href=(.)(.*?)\\1/', $ancorTag, $matches);

    return $matches[2];
}