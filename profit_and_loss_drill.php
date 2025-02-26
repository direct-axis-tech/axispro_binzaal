<?php include "header.php" ?>

<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-container  kt-grid__item kt-grid__item--fluid">


                <div class="kt-portlet ">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                <?= trans('PROFIT AND LOSS STATEMENT') ?>
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body" style="padding: 25px !important;">


                        <form method="post" action="#" class=" kt-form kt-form--fit kt-form--label-right">


                            <div class="kt-portlet__body">
                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label"><?= trans('Start Date') ?>:</label>
                                    <div class="col-lg-2">
                                        <input type="text" name="FROM_DATE" id="FROM_DATE"
                                               class="form-control ap-datepicker config_begin_fy"
                                               readonly placeholder="Select date"
                                               data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                               value="<?= add_days(Today(), -30) ?>"/>

                                    </div>
                                    <label class="col-lg-2 col-form-label"><?= trans('End Date') ?>:</label>
                                    <div class="col-lg-2">
                                        <input type="text" name="TO_DATE" id="TO_DATE"
                                            class="form-control ap-datepicker"
                                            data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                            readonly
                                            placeholder="Select date"
                                            value="<?= sql2date(APConfig('curr_fs_yr', 'end')) ?>"/>
                                    </div>


                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-success"
                                                onclick="LoadProfitAndLossDrillDownReport();"><?= trans('GET REPORT') ?>
                                        </button>
                                    </div>

                                </div>


                            </div>

                        </form>

                        <!--begin::Accordion-->
                        <div class="accordion accordion-light accordion-svg-icon" id="AccordProfitAndLoss">


                        </div>

                        <!--end::Accordion-->
                    </div>
                </div>

            </div>

            <!-- end:: Content -->
        </div>
    </div>
</div>

<?php include "footer.php"; ?>

<style>


    .gl-trans-table-div {

        max-height: 450px;
        overflow-y: scroll;

    }

    .kt-svg-icon g [fill] {
        fill: #009487 !important;
    }

    .card-body {
        padding: 10px !important;
    }

    .card-title {
        padding: 3px !important;
        font-size: 13px !important;
        font-weight: normal !important;
    }

    .card-header {

        background-color: #f7f8fa !important;
        padding-left: 8px !important;
        border-radius: 11px !important;
        padding-right: 20px !important;
        margin-bottom: 8px !important;

    }

    .accordion.accordion-light .card {
        border: none !important;
    }

    .accordion.accordion-light .card:last-child {
        margin-bottom: 0 !important;
    }

    .accordion.accordion-light .card .card-body {
        margin-bottom: 0 !important;
    }


</style>


<script>


    $(document).ready(function (e) {
        LoadProfitAndLossDrillDownReport();
    });


    var accord_svg_icon = {
        class: '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">\n' +
        ' <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">\n' +
        '  <polygon points="0 0 24 0 24 24 0 24" />\n' +
        '    <path d="M12.2928955,6.70710318 C11.9023712,6.31657888 11.9023712,5.68341391 12.2928955,5.29288961 C12.6834198,4.90236532 13.3165848,4.90236532 13.7071091,5.29288961 L19.7071091,11.2928896 C20.085688,11.6714686 20.0989336,12.281055 19.7371564,12.675721 L14.2371564,18.675721 C13.863964,19.08284 13.2313966,19.1103429 12.8242777,18.7371505 C12.4171587,18.3639581 12.3896557,17.7313908 12.7628481,17.3242718 L17.6158645,12.0300721 L12.2928955,6.70710318 Z" fill="#000000" fill-rule="nonzero" />\n' +
        '    <path d="M3.70710678,15.7071068 C3.31658249,16.0976311 2.68341751,16.0976311 2.29289322,15.7071068 C1.90236893,15.3165825 1.90236893,14.6834175 2.29289322,14.2928932 L8.29289322,8.29289322 C8.67147216,7.91431428 9.28105859,7.90106866 9.67572463,8.26284586 L15.6757246,13.7628459 C16.0828436,14.1360383 16.1103465,14.7686056 15.7371541,15.1757246 C15.3639617,15.5828436 14.7313944,15.6103465 14.3242754,15.2371541 L9.03007575,10.3841378 L3.70710678,15.7071068 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(9.000003, 11.999999) rotate(-270.000000) translate(-9.000003, -11.999999) " />\n' +
        ' </g></svg>',

        ledger: '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">\n' +
        '    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">\n' +
        '        <polygon points="0 0 24 0 24 24 0 24"/>\n' +
        '        <rect fill="#000000" opacity="0.3" transform="translate(12.000000, 12.000000) rotate(-90.000000) translate(-12.000000, -12.000000) " x="11" y="5" width="2" height="14" rx="1"/>\n' +
        '        <path d="M9.70710318,15.7071045 C9.31657888,16.0976288 8.68341391,16.0976288 8.29288961,15.7071045 C7.90236532,15.3165802 7.90236532,14.6834152 8.29288961,14.2928909 L14.2928896,8.29289093 C14.6714686,7.914312 15.281055,7.90106637 15.675721,8.26284357 L21.675721,13.7628436 C22.08284,14.136036 22.1103429,14.7686034 21.7371505,15.1757223 C21.3639581,15.5828413 20.7313908,15.6103443 20.3242718,15.2371519 L15.0300721,10.3841355 L9.70710318,15.7071045 Z" fill="#000000" fill-rule="nonzero" transform="translate(14.999999, 11.999997) scale(1, -1) rotate(90.000000) translate(-14.999999, -11.999997) "/>\n' +
        '    </g>\n' +
        '</svg>',

        group: '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">\n' +
        '    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">\n' +
        '        <polygon points="0 0 24 0 24 24 0 24"/>\n' +
        '        <rect fill="#000000" opacity="0.3" transform="translate(12.000000, 12.000000) rotate(-360.000000) translate(-12.000000, -12.000000) " x="7" y="11" width="10" height="2" rx="1"/>\n' +
        '        <path d="M13.7071045,15.7071104 C13.3165802,16.0976347 12.6834152,16.0976347 12.2928909,15.7071104 C11.9023666,15.3165861 11.9023666,14.6834211 12.2928909,14.2928968 L18.2928909,8.29289682 C18.6714699,7.91431789 19.2810563,7.90107226 19.6757223,8.26284946 L25.6757223,13.7628495 C26.0828413,14.1360419 26.1103443,14.7686092 25.7371519,15.1757282 C25.3639594,15.5828472 24.7313921,15.6103502 24.3242731,15.2371577 L19.0300735,10.3841414 L13.7071045,15.7071104 Z" fill="#000000" fill-rule="nonzero" transform="translate(19.000001, 12.000003) rotate(-270.000000) translate(-19.000001, -12.000003) "/>\n' +
        '        <path d="M-0.292895505,15.7071104 C-0.683419796,16.0976347 -1.31658478,16.0976347 -1.70710907,15.7071104 C-2.09763336,15.3165861 -2.09763336,14.6834211 -1.70710907,14.2928968 L4.29289093,8.29289682 C4.67146987,7.91431789 5.28105631,7.90107226 5.67572234,8.26284946 L11.6757223,13.7628495 C12.0828413,14.1360419 12.1103443,14.7686092 11.7371519,15.1757282 C11.3639594,15.5828472 10.7313921,15.6103502 10.3242731,15.2371577 L5.03007346,10.3841414 L-0.292895505,15.7071104 Z" fill="#000000" fill-rule="nonzero" transform="translate(5.000001, 12.000003) rotate(-450.000000) translate(-5.000001, -12.000003) "/>\n' +
        '    </g>\n' +
        '</svg>',

        profit_loss: '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">\n' +
        '    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">\n' +
        '        <rect x="0" y="0" width="24" height="24"/>\n' +
        '        <path d="M2,6 L21,6 C21.5522847,6 22,6.44771525 22,7 L22,17 C22,17.5522847 21.5522847,18 21,18 L2,18 C1.44771525,18 1,17.5522847 1,17 L1,7 C1,6.44771525 1.44771525,6 2,6 Z M11.5,16 C13.709139,16 15.5,14.209139 15.5,12 C15.5,9.790861 13.709139,8 11.5,8 C9.290861,8 7.5,9.790861 7.5,12 C7.5,14.209139 9.290861,16 11.5,16 Z" fill="#000000" opacity="0.3" transform="translate(11.500000, 12.000000) rotate(-345.000000) translate(-11.500000, -12.000000) "/>\n' +
        '        <path d="M2,6 L21,6 C21.5522847,6 22,6.44771525 22,7 L22,17 C22,17.5522847 21.5522847,18 21,18 L2,18 C1.44771525,18 1,17.5522847 1,17 L1,7 C1,6.44771525 1.44771525,6 2,6 Z M11.5,16 C13.709139,16 15.5,14.209139 15.5,12 C15.5,9.790861 13.709139,8 11.5,8 C9.290861,8 7.5,9.790861 7.5,12 C7.5,14.209139 9.290861,16 11.5,16 Z M11.5,14 C12.6045695,14 13.5,13.1045695 13.5,12 C13.5,10.8954305 12.6045695,10 11.5,10 C10.3954305,10 9.5,10.8954305 9.5,12 C9.5,13.1045695 10.3954305,14 11.5,14 Z" fill="#000000"/>\n' +
        '    </g>\n' +
        '</svg>'

    };


    function BlockDiv(element) {
        KTApp.block(element, {
            overlayColor: '#000000',
            type: 'v2',
            state: 'success',
            message: 'Please wait...'
        });
    }

    function UnBlockDiv(element) {
        KTApp.unblock(element);
    }


    function LoadProfitAndLossDrillDownReport() {

        BlockDiv("#AccordProfitAndLoss");

        var params = {
            from: $("#FROM_DATE").val(),
            to: $("#TO_DATE").val(),
            ctype:'4,5,6'
        };

        AxisPro.APICall('GET', route('API_Call', {method: 'get_class_balances'}), params, function (data) {

            UnBlockDiv("#AccordProfitAndLoss");

            var accord_html = "";

            var net_profit_loss = 0;

            $.each(data, function (key, value) {

                accord_html += '<div style="" class="card"><div class="card-header coa-card-header" ' +
                    'onclick="ExpandProfitAndLossAccordion(this)">';

                var symbol = "  (DR) ";

                if(value.amount < 0) symbol = "  (CR) ";

                var display_amt = amount(Math.abs(value.amount))+symbol;

                accord_html += `
               <div class="card-title collapsed" data-card-type="${value.coa_type}" data-bs-toggle="collapse"
                    data-parent-id="${value.id}" data-bs-target="#collapse${value.coa_type}${value.id}" aria-expanded="false">
                ${accord_svg_icon[value.coa_type]}${value.name}   &emsp;    ${display_amt} </div></div>
                <div id="collapse${value.coa_type}${value.id}" class="collapse"  >
                <div class="card-body" id="${value.coa_type}${value.id}">No Data</div></div></div>`;

                net_profit_loss += parseFloat(value.amount);

            });


            var display_net_profit = amount(net_profit_loss * -1);
            //Printing Calculated Return
            accord_html += '<div style="" class="card"><div class="card-header coa-card-header">';
            accord_html += `
               <div class="card-title collapsed"  data-bs-toggle="collapse" aria-expanded="false">
                ${accord_svg_icon['profit_loss']} <b>NET PROFIT/LOSS :    &emsp;    ${display_net_profit} </b> </div></div>
                <div  class="collapse"  >
                <div class="card-body" >No Data</div></div>
                </div>`;

            $("#AccordProfitAndLoss").html(accord_html);

        });


    }


    function ExpandProfitAndLossAccordion($this) {

        var this_type = $($this).find(".card-title").data('card-type');
        var parent_id = $($this).find(".card-title").data('parent-id');

        var method = '';
        if (this_type === 'class')
            method = 'get_top_level_group_balances';

        if (this_type === 'group')
            method = 'get_group_balances';

        if (this_type === 'ledger') {
            method = 'get_ledger_transactions';
        }

        var params = {

            method: method,
            parent_id: parent_id,
            format: 'json'
        };

        GeneratePLAccordionData(params, this_type, parent_id);

    }


    function GeneratePLAccordionData(params, this_type, parent_id, start) {

        if (!start)
            params.start = 0;
        else
            params.start = start;

        params.from= $("#FROM_DATE").val();
        params.to= $("#TO_DATE").val();
        params.ctype= '4,5,6';


        AxisPro.APICall('GET', route('API_Call', {method: params.method}), params, function (data) {

            if (this_type === 'class' || this_type === 'group') {
                var accord_html = '<div class="accordion accordion-light" id="ACCORD' + this_type + parent_id + '">';

                if (data.length > 0) {
                    $.each(data, function (key, value) {

                        var symbol = "  (DR) ";

                        if(value.amount < 0) symbol = "  (CR) ";

                        var display_amt = amount(Math.abs(value.amount))+symbol;

                        accord_html += '<div style="" class="card"><div class="card-header coa-card-header" ' +
                            'onclick="ExpandProfitAndLossAccordion(this)">';

                        accord_html += `<div class="card-title collapsed" data-card-type="${value.coa_type}" data-bs-toggle="collapse" data-parent-id="${value.id}" data-bs-target="#collapse${value.coa_type}${value.id}" aria-expanded="false">
                        ${accord_svg_icon[value.coa_type]}${value.name}   &emsp;    ${display_amt}</div></div>
                        <div id="collapse${value.coa_type}${value.id}" class="collapse"  >
                        <div class="card-body" id="${value.coa_type}${value.id}">No Data</div></div></div>`;

                    });

                    accord_html += "</div>";
                }
                else {
                    accord_html += "No Data";
                }

                $("#" + this_type + parent_id).html(accord_html)
            }

            else {


                data = data.data;

                //printing ledger trans
                if (start > 0) {

                    var last_fetched_count = $("#gl_tbody_" + this_type + parent_id).attr('data-last-fetched-count');

                    if (parseInt(last_fetched_count) >= 10)
                        PaginateGLTransSlideDownTable(data, this_type, parent_id);

                }
                else {
                    DisplayGLTransSlideDown(data, this_type, parent_id);


                    var opening_debit = 0;
                    var opening_credit = 0;
                    if (parseInt(data.op_bal) > 0) {
                        opening_debit = data.op_bal
                    }
                    if (parseInt(data.op_bal) < 0) {
                        opening_credit = data.op_bal
                    }

                    var op_bal_html = "";

                    op_bal_html += "<tr>";
                    op_bal_html += "<th colspan='5' style='text-align: center !important;'>Opening Balance</th>";
                    op_bal_html += "<th style='text-align: right;'>" + amount(opening_debit) + "</th>";
                    op_bal_html += "<th style='text-align: right;'>" + amount(opening_credit) + "</th>";
                    op_bal_html += "<th colspan='2'></th>";
                    op_bal_html += "</tr>";

                    $("#gl_tbody_" + this_type + parent_id).prepend(op_bal_html);

                }

            }


        });

    }


    function PaginateGLTransSlideDownTable(data, this_type, parent_id) {

        var gl_tbody_html = "";
        var balance = $("#gl_tbody_" + this_type + parent_id+" tr:last").find("td.balance_amt").data('amt');

        balance = parseFloat(balance);

        var rows_displayed = $("#gl_tbody_" + this_type + parent_id).attr('data-rows-displayed');
        rows_displayed = parseInt(rows_displayed);

        $("#gl_tbody_" + this_type + parent_id).attr('data-last-fetched-count', data.length);

        $.each(data, function (key, value) {

            var debit = 0;
            var credit = 0;

            rows_displayed += 1;

            balance += parseFloat(value.amount);

            if (parseInt(value.amount) > 0)
                debit = Math.abs(value.amount);

            if (parseInt(value.amount) < 0)
                credit = Math.abs(value.amount);

            gl_tbody_html += "<tr>";
            gl_tbody_html += "<th scope='row'>" + value.reference + "</th>";
            gl_tbody_html += "<td>" + value.type + "</td>";
            gl_tbody_html += "<td>" + value.tran_date + "</td>";
            gl_tbody_html += "<td>" + value.person_name + "</td>";
            gl_tbody_html += "<td>" + value.sub_ledger_name + "</td>";
            gl_tbody_html += "<td style='text-align: right;'>" + amount(debit) + "</td>";
            gl_tbody_html += "<td style='text-align: right;'>" + amount(credit) + "</td>";
            gl_tbody_html += "<td style='text-align: right;' class='balance_amt' data-amt='"+balance.toFixed(2)+"'>" + amount(balance) + "</td>";
            gl_tbody_html += "<td>" + value.memo_ + "</td>";
            gl_tbody_html += "</tr>";

        });

        $("#gl_tbody_" + this_type + parent_id).append(gl_tbody_html);
        $("#gl_tbody_" + this_type + parent_id).attr('data-rows-displayed', rows_displayed);

    }


    function DisplayGLTransSlideDown(data, this_type, parent_id) {
    const  systypes_array  = {
                "Journal Entry": 0,
                "Payment Voucher": 1,
                "Receipt Voucher": 2,
                "Funds Transfer": 4,
                "Sales Invoice": 10,
                "Customer Credit Note": 11,
                "Customer Payment": 12,
                "Delivery Note": 13,
                "Location Transfer": 16,
                "Inventory Adjustment": 17,
                "Purchase Order": 18,
                "Supplier Invoice": 20,
                "Supplier Credit Note": 21,
                "Supplier Payment": 22,
                "Purchase Order Delivery": 25,
                "Work Order": 26,
                "Work Order Issue": 28,
                "Work Order Production": 29,
                "Sales Order": 30,
                "Sales Quotation": 32,
                "Cost Update": 35,
                "Cost Center": 40,
                "Customer": 41,
                "Supplier": 42
                };


        var table_html = '<div class="kt-portlet__head">' +
            '<div class="kt-portlet__head-label">' +
            '<h3 class="kt-portlet__head-title">GL Transactions</h3>' +
            '</div></div>';

        table_html += '<div class="table-responsive ' +
            'gl-trans-table-div" data-this-type="' + this_type + '" ' +
            'data-parent-id="' + parent_id + '" onscroll="PaginateGLTransTable(this);">\n' +
            '<table class="table table-bordered">\n' +
            '<thead>' +
            '<tr>' +
            '<th>Reference</th>\n' +
            '<th>Type</th>' +
            '<th>Date</th>' +
            '<th>Person/Item</th>' +
            '<th>Sub-Ledger</th>' +
            '<th>Debit</th>' +
            '<th>Credit</th>' +
            '<th>Balance</th>' +
            '<th>Memo</th>' +
            '</tr>' +
            '</thead>' +
            '<tbody id="gl_tbody_' + this_type + parent_id + '">' +

            '</tbody>\n' +
            // '<tfoot>' +
            // '    <tr>' +
            // '      <th colspan="5" style="text-align: center">Closing Balance</th>' +
            // '      <td></td>' +
            // '      <td></td>' +
            // '      <td colspan="2"></td>' +
            // '    </tr>' +
            // '  </tfoot>'+
            '</table>\n' +
            '</div>';

        $("#" + this_type + parent_id).html(table_html);

        var gl_tbody_html = "";
        var balance = 0;
        var rows_displayed = 0;
        var current_count = data.length;

        $("#gl_tbody_" + this_type + parent_id).attr('data-last-fetched-count', current_count);

        $.each(data, function (key, value) {

            var debit = 0;
            var credit = 0;
            var ref= value.reference;
            var type=value.type;
            var gl_viewer = "ERP/gl/view/gl_trans_view.php";

            rows_displayed += 1;

            balance += parseFloat(value.amount);

            if (parseInt(value.amount) > 0)
                debit = Math.abs(value.amount);

            if (parseInt(value.amount) < 0)
                credit = Math.abs(value.amount); 

            gl_tbody_html += "<tr>";
            gl_tbody_html += "<th scope='row' id='referenceId'><a target='popup' onclick='openPopupWindow()' href='" +gl_viewer + '?' +  'type_id=' + systypes_array[type] +'&trans_no=' + value.type_no + "'>" + value.reference + "</a></th>";
            gl_tbody_html += "<td>" + value.type + "</td>";
            gl_tbody_html += "<td>" + value.tran_date + "</td>";
            gl_tbody_html += "<td>" + value.person_name + "</td>";
            gl_tbody_html += "<td>" + value.sub_ledger_name + "</td>";
            gl_tbody_html += "<td style='text-align: right;'>" + amount(debit) + "</td>";
            gl_tbody_html += "<td style='text-align: right;'>" + amount(credit) + "</td>";
            gl_tbody_html += "<td style='text-align: right;' class='balance_amt' data-amt='"+balance.toFixed(2)+"'>" + amount(balance) + "</td>";
            gl_tbody_html += "<td>" + value.memo_ + "</td>";
            gl_tbody_html += "</tr>";

        });

        $("#gl_tbody_" + this_type + parent_id).html(gl_tbody_html);
        $("#gl_tbody_" + this_type + parent_id).attr('data-rows-displayed', rows_displayed);
        

    }
    function openPopupWindow() {
    const url = "{{gl_viewer}}?type_id={{systypes_array[type]}}&trans_no={{value.type_no}}";
    const windowFeatures = "width=650,height=650,resizable=yes,scrollbars=yes";
    window.open(url, "popup", windowFeatures);
    return false;
  }

    function PaginateGLTransTable($this) {

        if ($($this).scrollTop() + $($this).innerHeight() >= $($this)[0].scrollHeight) {

            console.log('end reached');

            var this_type = $($this).data('this-type');
            var parent_id = $($this).data('parent-id');


            var params = {
                method: 'get_ledger_transactions',
                parent_id: parent_id,
                format: 'json',

            };

            var start = $("#gl_tbody_" + this_type + parent_id).attr('data-rows-displayed');

            GeneratePLAccordionData(params, this_type, parent_id, start)

        }

    }

</script>

