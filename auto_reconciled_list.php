<?php include "header.php";

include_once "ERP/API/API_Finance.php";
$finance_api = new API_Finance();

if(!isset($_GET["bank"]) || empty($_GET["bank"]))
    $bank = 0;
else
    $bank = $_GET["bank"];
?>

<style>

    #search_btn:focus {
        background: #5867dd !important;
        color: white !important;
    }

</style>

<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <div class="row">
                    <div class="kt-portlet">
                        <div class="kt-portlet__head">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title">
                                    <?= trans('AUTO BANK RECONCILIATION RESULT') ?>
                                </h3>

                            </div>
                            <span style="margin-top: 10px">
                                <a href="auto_reconcile.php" class="btn btn-warning btn-sm">RECONCILE AGAIN</a>
                            </span>
                        </div>

                        <div style="padding: 14px">

                            <form id="filter_form">

                                <div class="form-group row">
                                    <div class="col-lg-2">
                                        <label class=""><?= trans('Status') ?>:</label>

                                        <select class="form-control kt-selectpicker" name="fl_status">
                                            <option value=""><?= trans('All') ?></option>
                                            <option value="show_reconciled"><?= trans('Show Reconciled') ?></option>
                                            <option value="show_not_reconciled"><?= trans('Show Not Reconciled') ?></option>
                                            <option value="show_ex_bank_entries"><?= trans('Show Extra Entries in Bank') ?></option>
                                            <option value="show_ex_sys_entries"><?= trans('Show Extra Entries in System') ?></option>
                                        </select>

                                    </div>

                                    <div class="col-lg-1">
                                        <label class="">&nbsp</label>
                                        <button type="button" id="search_btn" onclick="GetReport()"
                                                class="form-control btn btn-sm btn-primary">
                                            Search
                                        </button>
                                    </div>

                                    <div class="col-lg-1">
                                        <label class="">&nbsp</label>
                                        <button type="button" id="export_xls_btn"
                                                class="form-control btn btn-sm btn-warning">
                                            Excel
                                        </button>
                                    </div>

                                </div>

                                <div class="row" style="margin-left: 5px">
                                    <p>Bank Account : <span style="color: orangered"><?= $finance_api->getGLBankName($bank) ?></span></p>
                                </div>

                            </form>
                        </div>

                        <div class="table-responsive" style="padding: 7px 7px 7px 7px;">

                            <table class="table table-sm table-bordered text-nowrap" id="service_list_table">
                                <thead>
                                <th class="text-center"><?= trans('Date in S/W') ?></th>
                                <th class="text-center"><?= trans('Date in Bank') ?></th>
                                <th class="text-center"><?= trans('Invoice No') ?></th>
                                <th class="text-center"><?= trans('Transaction ID in S/W') ?></th>
                                <th class="text-center"><?= trans('Transaction ID in Bank') ?></th>
                                <th class="text-center"><?= trans('Amount in S/W') ?></th>
                                <th class="text-center"><?= trans('Amount in Bank') ?></th>
                                <th class="text-center"><?= trans('Status') ?></th>
                                <th class="text-center"><?= trans('Difference') ?></th>

                                <th></th>
                                </thead>
                                <tbody id="tbody">
                                </tbody>
                            </table>
                            <div id="pg-link"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include "footer.php"; ?>


<script>

    var curr_user_id = '<?php echo $_SESSION['wa_current_user']->user ?>';
    $(document).ready(function () {
        GetReport();
    });

    $(document).on("click",".btn_gl_view", function () {

        let link = $(this).data("href");
        popupCenter(link,"Tax Submission Journal",800,500)
    });

    $(document).on("click", ".pg-link", function (e) {

        e.preventDefault();

        var req_url = $(this).attr("href");
        AxisPro.BlockDiv("#kt_content");

        $(".error_note").hide();
        AxisPro.APICall('GET', req_url, {}, function (data) {
            DisplayReport(data);
        });


    });


    function GetReport() {

        AxisPro.BlockDiv("#kt_content");

        var $form = $("#filter_form");
        var params = AxisPro.getFormData($form);

        $(".error_note").hide();
        AxisPro.APICall('POST',route('API_Call', {method: 'getReconciledResult'}), params, function (data) {
            DisplayReport(data);
        });
    }


    function DisplayReport(data) {

        var rep = data.rep;
        var tbody_html = "";
       
        $.each(rep, function (key, value) {

            var erp_link = erpUrl();
            var print_params = "PARAM_0="+value.trans_no+"-10&PARAM_1="+value.trans_type+"-10&PARAM_2=&PARAM_3=0&PARAM_4=&PARAM_5=&PARAM_6=&PARAM_7=0&REP_ID=107";
            var print_link = erp_link + "/invoice_print/index.php?" + print_params;
            var invoice_no = (value.invoice_no !== null) ? value.invoice_no : "";
            var print_invoice = "<a id='inv_print' target='_blank' href='" + print_link + "'>" + invoice_no + "</a>";   


            tbody_html += "<tr data-id='"+value.id+"'>";
            tbody_html += "<td class='text-center'>"+clean(value.sw_date)+"</td>";
            tbody_html += "<td class='text-center'>"+clean(value.bank_date)+"</td>";
            tbody_html += "<td class='text-center'>" + print_invoice + "</td>";
            tbody_html += "<td class='text-center'>"+ clean(value.transaction_) +"</td>";
            tbody_html += "<td class='text-center'>"+ clean(value.transaction_bnk) +"</td>";
            tbody_html += "<td class='text-right'>"+parseFloat(value.sw_amount).toFixed(2)+"</td>";
            tbody_html += "<td class='text-right'>"+parseFloat(value.bank_amount).toFixed(2)+"</td>";
            tbody_html += "<td class='text-center'>"+clean(value.status)+"</td>";
            tbody_html += "<td class='text-right'>"+parseFloat(value.diff).toFixed(2)+"</td>";
            tbody_html += "<td class='text-center'><a class='btn_gl_view' style='cursor: pointer' data-href='"+erpUrl()+"/gl/view/gl_trans_view.php" +
                "?type_id="+value.trans_type+"&trans_no="+value.trans_no+"'><i class='fas fa-receipt text-success fa-bold'></i></a></td>";
            tbody_html += "</tr>";

        });

        $("#tbody").html(tbody_html);
        $("#pg-link").html(data.pagination_link);
        AxisPro.UnBlockDiv("#kt_content");

    }

    $("#export_xls_btn").click(function (e) {

        AxisPro.BlockDiv("#kt_content");

        var $form = $("#filter_form");
        var params = AxisPro.getFormData($form);

        $.ajax({
            type: 'POST',
            url: route('API_Call', {method: 'exportAutoReconciledResult'}),
            data: params,
            success: function(result) {
                setTimeout(function() {
                    var dlbtn = document.getElementById("dlbtn");
                    var file = new Blob([result], {type: 'text/csv'});
                    dlbtn.href = URL.createObjectURL(file);
                    dlbtn.download = 'AutoReconciledResult.csv';
                    dlbtn.click();
                    AxisPro.BlockDiv("#kt_content");
                }, 2000);
            }
        });

    });


    function popupCenter(url, title, w, h) {
        // Fixes dual-screen position                             Most browsers      Firefox
        var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
        var dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;

        var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var systemZoom = width / window.screen.availWidth;
        var left = (width - w) / 2 / systemZoom + dualScreenLeft
        var top = (height - h) / 2 / systemZoom + dualScreenTop
        var newWindow = window.open(
            url,
            title,
            'scrollbars=yes'
            + ',width=' + w / systemZoom
            + ',height=' + h / systemZoom
            + ',top=' + top
            + ',left=' + left
        )

        if (window.focus) newWindow.focus();
    }


</script>

<!--This hidden button is needed for excel export purpose-->
<a href="javascript:void(0)" id="dlbtn" style="display: none;"></a>
