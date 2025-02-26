<style>
<?php if (!user_check_access('SA_SALESINVOICEDATEMOD')) : ?>
    input[name="OrderDate"] {
        display: none;
    }
<?php endif; ?>

    .main-panel {
        z-index: unset;
    }

    button.payment-chooser [class^="la-"],
    button.payment-chooser [class*=" la-"] {
        font-size: 4rem;
    }
    
    input[name="ref"] {
        pointer-events: none;
        background: #ccc;
    }

    #_invoice_payment_sel {
        position: relative;
        height: 100%;
        display: block;
        width: max-content;
    }

    #_invoice_payment_sel::after {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        cursor: pointer;
    }

    .modal .modal-content .modal-header .close:before {
        content: none !important;
    }

    #pendingSrqModel .modal-content {
        /*width: 900px*/
        width: 65% !important;
    }

    #pendingSrqModel table td,
    #pendingSrqModel table th {
        border: 1px solid #ccc !important;
    }

    input[name="OrderDate"],
    input[name="ref"],
    input[name="token_no"] {
        width: 230px !important;
    }

    #_stock_id_sel .select2-container {
        width: 430px !important;

    }

    #batchModel table td,
    #batchModel table th {
        border: 1px solid #ccc !important;
    }

    #batchModel td:first-child,
    #batchModel th:first-child {
        text-align: center;
    }

    #batchModel input[type="checkbox"] {
        vertical-align: middle;
    }

    #items_table table.tablestyle td:nth-child(10) input {
        width: 150px;
    }

    .select2-dropdown,
    .select2-search__field {
        width: 600px !important;
    }

    .select2-search__field {
        max-width: 580px !important;
    }

    /* The Modal (background) */
    .modal {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        z-index: 1072;
        /* Sit on top */
        padding-top: 100px;
        /* Location of the box */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgb(0, 0, 0);
        /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4);
        /* Black w/ opacity */
    }

    /* Modal Content */
    .modal-content {
        position: relative;
        background-color: #fefefe;
        margin: auto;
        padding: 0;
        border: 1px solid #888;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        -webkit-animation-name: animatetop;
        -webkit-animation-duration: 0.4s;
        animation-name: animatetop;
        animation-duration: 0.4s
    }

    /* Add Animation */
    @-webkit-keyframes animatetop {
        from {
            top: -300px;
            opacity: 0
        }

        to {
            top: 0;
            opacity: 1
        }
    }

    @keyframes animatetop {
        from {
            top: -300px;
            opacity: 0
        }

        to {
            top: 0;
            opacity: 1
        }
    }

    /* The Close Button */
    .close {
        color: white;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }

    .modal-header {
        padding: 1px 14px;
        background-color: #009688;
        color: white;
    }

    .modal-body {
        padding: 2px 16px;
    }

    .modal-footer {
        padding: 2px 16px;
        background-color: #009688;
        color: white;
        text-align: right;
    }

    #btn_update_fee_info,
    #btn_req_for_approval {
        background: #585858 !important;
        box-shadow: none !important;
        padding: 0px 20px !important;
        margin: 2px;
    }

    #delivery,
    .sales-type-list-row,
    .customer-branch-list-row {
        display: none;
    }

    #items_table select {
        width: 85% !important;
    }

    input[name="OrderDate"],
    input[name="ref"],
    input[name="Disc"],
    input[name="total_bank_charge"],
    /* input[name="discount_amount"] {
        pointer-events: none;
        background: #ccc;
    } */

    .select2-results__option {
        font-size: 12px;
    }

    #items_table table {
        text-align: center !important;
    }

    table.tablestyle td {
        padding: 2px !important;
    }

    #_stock_id_edit {
        width: 53px !important;
    }

    input[name="balance_amount"] {
        pointer-events: none;
        background: #ccc;
    }

    input[name="total_amount"] {
        pointer-events: none;
        background: #ccc;
    }

    input[name="card_amount"] {
        display: none;
    }

    .cash_amount {
        display: none;
    }

    input[name="cash_amount"] {
        display: none;
    }

    .card_amount {
        display: none;
    }
</style>
<link href="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.css" rel="stylesheet" type="text/css"/>
<script src="<?= $path_to_root ?>/js/auto_fetch.js?id=v1.0.7"></script>
<script type="text/javascript">

    function toggleCreditCardNo() {
        var payment_method = $('[name="invoice_payment"]').val();
        
        var enabledFor = [];
        parseInt(<?= pref('axispro.req_card_no_4_cr_cd_pmt', 0) ?>) && enabledFor.push("PayNoWCC");
        parseInt(<?= pref('axispro.req_card_no_4_cn_cd_pmt', 0) ?>) && enabledFor.push("PayByCenterCard");

        if (enabledFor.indexOf(payment_method) !== -1) {
            $("input[name='credit_card_no']").closest('tr').show();
        } else {
            $("input[name='credit_card_no']").closest('tr').hide();
        }
    }

    function togglePaymentRef() {
        var payment_method = $('[name="invoice_payment"]').val();
        if (["PayOnline", "PayByBankTransfer"].indexOf(payment_method) !== -1) {
            $("input[name='payment_ref']").closest('tr').show();
        } else {
            $("input[name='payment_ref']").closest('tr').hide();
        }
    }

    function toggleAuthCode() {
        var payment_method = $('[name="invoice_payment"]').val();
        if (["PayNoWCC", "PayCashAndCard"].indexOf(payment_method) !== -1) {
            $("input:not([type='hidden'])[name='auth_code']").closest('tr').show();
        } else {
            $("input:not([type='hidden'])[name='auth_code']").closest('tr').hide();
        }
    }

    function toggleTotalToPay() {
        var payment_method = $('[name="invoice_payment"]').val();
        if (["PayOnline", "PayCashAndCard", "PayNoWCC"].indexOf(payment_method) !== -1) {
            $("input[name='total_to_collect']").closest('tr').show();
        } else {
            $("input[name='total_to_collect']").closest('tr').hide();
        }
    }
    
    function togglePaymentAccount() {
        var payment_method = $('[name="invoice_payment"]').val();
        var isEditing = parseInt($('[name="is_editing_invoice"]').val()) || 0;
        if (
            payment_method.length
            && payment_method != 'PayLater'
            && (
                !isEditing
                || (['PayByCenterCard', 'PayByCustomerCard']).indexOf(payment_method) != -1
            )
        ) {
            $("select[name='payment_account']").closest('tr').show();
        } else {
            $("select[name='payment_account']").closest('tr').hide();
        }
    }

    function toggleCashNCard() {
        var payment_method = $('[name="invoice_payment"]').val();

        if (payment_method === "PayCashAndCard") {
                $("input[name='cash_amount']").show();
                $("input[name='card_amount']").show();
                $(".cash_amount").show();
                $(".card_amount").show();
                $('input[name="ov_card_amount"]').closest('tr').show();
            } else {
                $("input[name='cash_amount']").hide();
                $("input[name='card_amount']").hide();
                $(".cash_amount").hide();
                $(".card_amount").hide();
                $('input[name="ov_card_amount"]').closest('tr').hide();
            }
    }

    function _togglePaymentMethod() {
        let trans_type = $('[name="trans_type"]').val();
        $('select[name="invoice_payment"]').closest('tr')[trans_type == '<?= ST_SALESINVOICE ?>' ? 'show' : 'hide']();
    }

    function toggleDimension() {
        var isDimThroughUrl = parseInt($('[name="is_dim_through_url"]').val()) || 0;

        if (!isDimThroughUrl) {
            $("select[name='dimension_id']").closest('tr').show();
        } else {
            $("select[name='dimension_id']").closest('tr').hide();
        }
    }

    function togglePaymentMethod() {
        togglePaymentAccount();
        toggleCreditCardNo();
        togglePaymentRef();
        toggleCashNCard();
        toggleTotalToPay();
        toggleAuthCode();
        toggleDimension();
        _togglePaymentMethod();
    }

    $(function() {
        if (performance.navigation.type == 2) {
            window.location.href = "<?= $path_to_root ?>";
        }

        $(document).on("change", "input[name='discount_amount']", function() {
            var discount_amount = $(this).val();
            var price = $("input[name='price']").val();
            var discount_percentage = (discount_amount / price) * 100;

            $("input[name='Disc']").val(discount_percentage.toFixed(2))
        });

        $(document).on("change", "input[name='Disc']", function() {
            var discount_percentage = $(this).val();
            var price = $("input[name='price']").val();
            var discount_amount = (price * discount_percentage) / 100;

            $("input[name='discount_amount']").val(discount_amount.toFixed(2));
        });

        $(document).on("click", "#mobileSearch", function() {
            var token_number = $("input[name='token_no']").val();
            var curr_url = $(this).attr('data-href');

            $.ajax("../API/hub.php", {
                method: 'get',
                data: {
                    method: 'fetchTodaysServiceRequestsByToken',
                    token: token_number
                },
            }).done(function(r) {
                var data = JSON.parse(r);

                if (data.length > 1) {
                    $("#srv_pop_title").html("PENDING SERVICE REQUESTS OF TOKEN NUMBER : " + token_number);
                    srq_list_modal.style.display = "block";

                    var tbody_html = "";
                    $.each(data, function(key, value) {
                        tbody_html += "<tr>";
                        tbody_html += "<td>" + value.reference + "</td>";
                        tbody_html += "<td>" + value.token_number + "</td>";
                        tbody_html += "<td>" + value.display_customer + "</td>";
                        tbody_html += "<td>" + value.mobile + "</td>";
                        tbody_html += "<td>" + value.staff_name + "</td>";
                        tbody_html += "<td>" + value.amount + "</td>";
                        tbody_html += "<td style='text-align: center'>" +
                            "<button type='button' " +
                            "class='call_token_btn btn btn-sm btn-primary' data-req_id='" + value.id + "' data-token='" + value.token_number + "'>Call</button></td>";
                        tbody_html += "</tr>";
                    });

                    if (data.length === 0) {
                        var tr_len = $("#srq_list_tbody").closest("table").find("th").length;
                        tbody_html = "<tr><td colspan='" + tr_len + "' style='text-align: center'>You have no pending service request.!</td></tr>";
                    }

                    $("#srq_list_tbody").html(tbody_html);
                } else {
                    var req_id = 0;
                    if (data.length === 1) {
                        req_id = data[0].id;
                    }

                    var check_url = curr_url + "&SRQ_TOKEN=" + token_number + "&req_id=" + req_id;
                    window.location.href = check_url;
                }
            });
        });

        $(document).on("change", "select[name='customer_type']", function() {
            $("select[name='customer_id']").trigger('change')
        });

        $(document).on("click", ".create_cust_button", function() {
            var e = jQuery.Event("keydown");
            e.which = 113;
            $("input").trigger(e);
        });

        $('input[name="credit_card_no"]').closest('tr').hide();
        $('input[name="payment_ref"]').closest('tr').hide();

        document.addEventListener("keydown", function(event) {
            if (event.keyCode == 117)
                $(".item_list_options").trigger('click');
        });

        //FOR MODAL
        var modal = document.getElementById('myModal');

        // Get the button that opens the modal
        var btn = document.getElementById("myBtn");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];
        $(document).on("click", "#myBtn", function() {
            modal.style.display = "block";

            var service_charge = $("input[name='price']").val();
            var supp_amount = $("input[name='govt_fee']").val();

            service_charge = service_charge.replace(/,/g, '');
            supp_amount = supp_amount.replace(/,/g, '');

            if (!service_charge) service_charge = 0;
            if (!supp_amount) supp_amount = 0;

            var tot_amount = parseFloat(service_charge) + parseFloat(supp_amount);

            $("#tot_amount").val(tot_amount.toFixed(2));
            $("#supp_amount").val(parseFloat(supp_amount).toFixed(2))
        });

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        };

        //FOR REQUESY MODAL
        var req_modal = document.getElementById('req_cr_modal');

        // Get the button that opens the modal
        // var req_btn = document.getElementById("req_credit_button");

        // Get the <span> element that closes the modal
        var req_span = document.getElementsByClassName("close_rq_model")[0];

        // When the user clicks on <span> (x), close the modal
        req_span.onclick = function() {
            req_modal.style.display = "none";
        };

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal || event.target == req_modal) {
                modal.style.display = "none";
                req_modal.style.display = "none";
            }
        };

        $(document).on("click", "#btn_update_fee_info", function() {
            var total_amount = $("#tot_amount").val();
            var supp_amount = $("#supp_amount").val();

            if (!total_amount) total_amount = 0;
            if (!supp_amount) supp_amount = 0;

            var service_charge = parseFloat(total_amount) - parseFloat(supp_amount);

            $("input[name='price']").val(parseFloat(service_charge).toFixed(2));
            $("input[name='govt_fee']").val(parseFloat(supp_amount).toFixed(2));

            modal.style.display = "none";
        });

        $("input[name='govt_fee']").bind('keypress', function(e) {
            if (e.keyCode == 13) {
                // Enter pressed... do anything here...
            }
        });

        $(document).on("click", ".req_credit_button", function() {
            req_modal.style.display = "block";

            var customer_id = $('#customer_id').val();
            var customer_name = $('#customer_id option:selected').text();

            $("#req_model_cust_name ").html(customer_name);
            $("#req_model_cust_id").val(customer_id);

            $("#req_note").val("");
            $("#req_amount").val(0);
        });

        $(document).on("click", "#btn_req_for_approval", function() {
            var customer_id = $("#req_model_cust_id").val();
            var req_amount = $("#req_amount").val();
            var req_desc = $("#req_note").val();

            $("#ajaxmark").attr("visibility", "visible");
            $.ajax({
                url: "credit_requests_ajax.php?cr_request=1",
                type: "post",
                dataType: 'json',
                data: {
                    customer_id: customer_id,
                    req_amount: req_amount,
                    req_desc: req_desc
                },
                success: function(response) {
                    $("#ajaxmark").attr("visibility", "hidden");
                    alert(response.msg);
                    req_modal.style.display = "none";
                },
                error: function(xhr) {}
            });
        });

        $(document).on("change", "select[name='sub_customer_list']", function() {
            var sub_customer_name = $("select[name='sub_customer_list'] option:selected").text();
            if ($(this).val() === '0') {
                sub_customer_name = "";
            }

            $("input[name='display_customer']").val(sub_customer_name);
        });

        var srq_list_modal = document.getElementById('pendingSrqModel');
        var add_sub_cust_close = document.getElementsByClassName("srqModelClose")[0];
        add_sub_cust_close.onclick = function() {
            srq_list_modal.style.display = "none";
        };

        window.onclick = function(event) {
            if (event.target == srq_list_modal) {
                srq_list_modal.style.display = "none";
            }
        };

        $(document).on("click", "#srv_req_list_btn", function() {
            srq_list_modal.style.display = "block";

            var dim_id = $("[name='dimension_id']").val();
            $.ajax("../API/hub.php", {
                method: 'get',
                data: {
                    method: 'getPendingServiceRequests',
                    cost_center_id: dim_id
                },
            }).done(function(r) {
                var data = JSON.parse(r);

                $("#srv_pop_title").html("PENDING SERVICE REQUESTS");

                var tbody_html = "";
                $.each(data, function(key, value) {
                    tbody_html += "<tr>";
                    tbody_html += "<td>" + value.reference + "</td>";
                    tbody_html += "<td>" + value.token_number + "</td>";
                    tbody_html += "<td>" + value.display_customer + "</td>";
                    tbody_html += "<td>" + value.mobile + "</td>";
                    tbody_html += "<td>" + value.staff_name + "</td>";
                    tbody_html += "<td>" + value.amount + "</td>";
                    tbody_html += "<td style='text-align: center'>" +
                        "<button type='button' " +
                        "class='call_token_btn btn btn-sm btn-primary' data-req_id='" + value.id + "' data-token='" + value.token_number + "'>Call</button></td>";
                    tbody_html += "</tr>";
                });

                if (data.length === 0) {
                    var tr_len = $("#srq_list_tbody").closest("table").find("th").length;
                    tbody_html = "<tr><td colspan='" + tr_len + "' style='text-align: center'>You have no pending service request.!</td></tr>";
                }

                $("#srq_list_tbody").html(tbody_html);
            });
        });

        $(document).on("click", ".call_token_btn", function() {
            var selected_token = $(this).data('token');
            var req_id = $(this).data('req_id');

            $("input[name='token_no']").val(selected_token);
            var curr_url = $("#srv_req_list_btn").attr('data-href');
            var check_url = curr_url + "&SRQ_TOKEN=" + selected_token + "&req_id=" + req_id;

            window.location.href = check_url;
            // srq_list_modal.style.display = "none";
        });

        $(document).on("click", "#_invoice_payment_sel", function() {
            /** @type {HTMLSelectElement} _this */
            var _this = this.querySelector('select[name="invoice_payment"]');

            var isEmptySelect = true;
            for (var i in _this.options) {
                if (_this.options[i].value != '') {
                    isEmptySelect = false;
                    break;
                }
            }

            if (!isEmptySelect) {
                var paymentMethodsContainer = document.getElementById('payment-methods-container');
                empty(paymentMethodsContainer);

                for (var i = 0; i < _this.options.length; i++) {
                    if (_this.options[i].value != '') {
                        paymentMethodsContainer.appendChild(createPaymentMethodElement(_this.options[i].value, _this.options[i].textContent));
                    }
                }

                $('#payment-methods-modal').modal('show');
            }
        })

        $(document).on("click", ".payment-chooser", function() {
            var paymentMethod = this.value;
            var paymentMethodText = this.dataset.desc;

            Swal.fire({
                title: 'Are you sure?',
                text: "You have selected: " + paymentMethodText,
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Process!'
            }).then(function(result) {
                if (result.value) {
                    var paymentSelect = document.querySelector('select[name="invoice_payment"]');
                    paymentSelect.value = paymentMethod;

                    var event = document.createEvent('HTMLEvents');
                    event.initEvent('change', true, true);
                    paymentSelect.dispatchEvent(event);

                    $('#payment-methods-modal').modal('hide');
                }
            });
        })

        function createPaymentMethodElement(paymentMethod, paymentMethodText) {
            var button = document.createElement('button');
            button.classList.add('btn', 'btn-primary', 'shadow-none', 'm-2', 'payment-chooser', 'w-100');
            button.style.height = '150px';
            button.style.fontSize = '25px';
            button.value = paymentMethod;
            button.dataset.desc = paymentMethodText;

            button.appendChild(getIconContainerForPaymentMethod(paymentMethod));
            button.append(paymentMethodText);

            var col = document.createElement('div');
            col.classList.add('col-md-4', 'p-2');
            col.append(button)

            return col;
        }

        function getIconContainerForPaymentMethod(paymentMethod) {
            var icons = {
                PayNow: 'la-money',
                PayNoWCC: 'la-cc-visa',
                PayLater: 'la-dollar',
                PayByBankTransfer: 'la-arrow-circle-right',
                PayByCustomerCard: 'la-user',
                PayByCenterCard  : 'la-building',
                PayOnline: 'la-link'
            }

            var div = document.createElement('div');
            if (paymentMethod == 'PayCashAndCard') {
                var iconCash = document.createElement('span');
                iconCash.classList.add('la', icons.PayNow);

                var iconCard = document.createElement('span');
                iconCard.classList.add('la', icons.PayNoWCC);

                div.append(iconCash, '+', iconCard);
            } else {
                var icon = document.createElement('span');
                icon.classList.add('la', icons[paymentMethod]);

                div.appendChild(icon);
            }

            return div;
        }

        togglePaymentMethod();
        $(document).on("change", "select[name='invoice_payment']", togglePaymentMethod);

        $(document).on("change", "input[name='cash_amount'],input[name='card_amount']", function() {
            var name = this.name;
            var sname =  '_'+name+'_update';
            JsHttpRequest.request(sname, this.form);
        });

        $(document).on(
            "keyup",
            "input[name='given_amount']"
            +",input[name='total_amount']"
            +",input[name='round_of_amount']",
            calculateBalance
        );

        function calculateBalance() {
            var given_amount = $("input[name='given_amount']").val();
            var payment_amount = $("input[name='total_amount']").val();
            var round_of_amount = $("input[name='round_of_amount']").val();

            var noCommas = given_amount.replace(/,/g, ''),
                asANumber = +noCommas;
            given_amount = noCommas;

            var noCommas = payment_amount.replace(/,/g, ''),
                asANumber = +noCommas;
            payment_amount = noCommas;

            var noCommas = round_of_amount.replace(/,/g, ''),
                asANumber = +noCommas;
            round_of_amount = noCommas;

            if (!round_of_amount)
                round_of_amount = 0;

            given_amount = parseFloat(given_amount) - parseFloat(round_of_amount);

            var balance = 0;
            if (payment_amount) {
                balance = (given_amount) - payment_amount
            }

            if (isNaN(balance))
                balance = 0;

            $("input[name='balance_amount']").val(balance.toFixed(2));
        }
    });
</script>

<?php function echo_modal_templates()
{ ?>
    <div id="myModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <p><?= trans("Fee Details") ?></p>

            </div>
            <div class="modal-body">
                <table class="tablestyle" cellpadding="2" cellspacing="0">
                    <thead>
                    <tbody>
                        <tr>
                            <td><?= trans("Total Amount") ?></td>
                            <td><input type="number" id="tot_amount"></td>
                        </tr>
                        <tr>
                            <td><?= trans("Govt. Fee") ?></td>
                            <td><input type="number" id="supp_amount"></td>
                        </tr>

                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn_update_fee_info"><?= trans("Update") ?></button>
            </div>
        </div>
    </div>

    <!--Request for aditional credit  - Modal Form-->
    <div id="req_cr_modal" class="modal modal-sm">
        <!-- Modal content -->
        <div class="modal-content" style="width: 30%">
            <div class="modal-header">
                <span class="close close_rq_model">&times;</span>
                <p>Request for additional credit</p>
            </div>
            <div class="modal-body">
                <input type="hidden" id="req_model_cust_id">
                <table class="tablestyle" cellpadding="2" cellspacing="0">
                    <thead>
                    <tbody>
                        <tr>
                            <td>Customer</td>
                            <td id="req_model_cust_name"></td>
                        </tr>
                        <tr style="display: none">
                            <td>Requesting Amount</td>
                            <td><input type="number" id="req_amount"></td>
                        </tr>
                        <tr>
                            <td>Note</td>
                            <td><textarea id="req_note" rows="6" cols="80"></textarea></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn_req_for_approval">Send Request</button>
            </div>
        </div>
    </div>

    <div class="modal fade" role="dialog" id="batchModel--">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"  aria-label="Close"><i class="fa fa-close"></i>
                    </button>
                    <h4 class="modal-title" style="position: absolute;">Admin Approval</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="discount_for_admin_approval" class="col-form-label">Discount</label>
                        <input type="number" step="any" class="form-control numpad" placeholder="Enter Discount" id="discount_for_admin_approval">
                    </div>
                    <div class="form-group">
                        <label for="admin_password" class="col-form-label">Password for user: <b>'admin'</b></label>
                        <input type="password" class="form-control" id="admin_password" placeholder="Password">
                    </div>
                    <button type="submit" class="btn btn-primary mb-2 pull-right" onclick="check_password();" id="admin_password_confirm">Confirm
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    <div id="pendingSrqModel" class="modal">
        <!-- Modal content -->
        <div class="" role="document" style="width: 100% !important;">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="close srqModelClose">&times;</span>
                    <h4 class="modal-title" style="position: absolute; color: white" id="srv_pop_title">Pending Service Request List</h4>
                </div>
                <div class="modal-body">
                    <div style="    height: 400px; overflow-y: scroll;">
                        <table class="tablestyle" cellpadding="2" cellspacing="0">
                            <thead>
                                <th><?= trans("REFERENCE") ?></th>
                                <th><?= trans("TOKEN") ?></th>
                                <th><?= trans("CUSTOMER NAME") ?></th>
                                <th><?= trans("MOBILE") ?></th>
                                <th><?= trans("COUNTER STAFF NAME") ?></th>
                                <th><?= trans("AMOUNT") ?></th>
                                <th><?= trans("") ?></th>
                            </thead>
                            <tbody id="srq_list_tbody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="payment-methods-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content w-100">
                <div class="modal-body p-4">
                    <div class="row justify-content-around pr-3" id="payment-methods-container">

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>