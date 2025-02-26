<?php include "header.php";  ?>


<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-container  kt-grid__item kt-grid__item--fluid">
                <div class="row">
                    <?php if (!$_SESSION['wa_current_user']->can_access('SA_CASH_HANDOVER_LIST')): ?>
                        <div class="col text-center py-5">
                            <span>The security settings on your account do not permit you to access this function</span>
                        </div>
                    <?php else: ?>
                        <?php if (empty(trim(get_company_pref('cash_handover_round_off_adj_act')))) : ?>
                            <div class="alert alert-warning mx-5 w-100 text-center mt-4" role="alert">
                                <p class="w-100">
                                    <span class="fa fa-exclamation-triangle mt-1 mr-2"></span>
                                    Round off adjustment account for cash handover is not set.
                                    Please set the account from <strong>Settings > General Ledger Setup</strong> before approving any request! 
                                </p>
                            </div>
                        <?php endif; ?>
                        <div class="kt-portlet">
                            <div class="kt-portlet__head">
                                <div class="kt-portlet__head-label">
                                    <h3 class="kt-portlet__head-title">
                                        <?= trans('CASH HANDOVER LIST') ?>
                                    </h3>
                                </div>

                                <!-- <div class="kt-portlet__head-label">
                                    <a href="purchase_request_new.php" class="btn btn-sm btn-primary">Add New Purchase
                                        Request</a>
                                </div> -->
                            </div>

                            <!-- begin::Form -->
                            <!-- <div style="padding: 14px">
                                <form id="filter_form">
                                    <div class="form-group row">
                                        <div class="col-lg-1">
                                            <label class=""><?//= trans('Reference') ?>:</label>
                                            <input type="text" name="fl_ref"
                                                    class="form-control"/>
                                        </div>

                                        <div class="col-lg-2">
                                            <label class=""><?//= trans('From Date') ?>:</label>
                                            <input type="text" name="fl_start_date"
                                                    class="form-control ap-datepicker config_begin_fy"
                                                    readonly placeholder="Select date"
                                                    value="<?//= sql2date(APConfig('curr_fs_yr', 'begin')) ?>"/>
                                        </div>

                                        <div class="col-lg-2">
                                            <label class=""><?//= trans('To Date') ?>:</label>
                                            <input type="text" name="fl_end_date"
                                                    class="form-control ap-datepicker config_begin_fy"
                                                    readonly placeholder="Select date"
                                                    value="<?//= sql2date(APConfig('curr_fs_yr', 'end')) ?>"/>
                                        </div>

                                        <div class="col-lg-2">
                                            <label class=""><?//= trans('Requested By') ?>:</label>
                                            <select name="fl_requested_by" class="form-control kt-select2 ap-select2">
                                            </select>
                                        </div>

                                        <div class="col-lg-3">
                                            <label class=""><?//= trans('Status') ?>:</label>
                                            <select class="form-control kt-selectpicker" name="fl_status">
                                                <option value=""><?//= trans('All') ?></option>
                                                <option value="WFSMA"><?//= trans('Waiting For Staff Manager Approval') ?></option>
                                                <option value="WFPMA"><?//= trans('Waiting for Purchase Manager Approval') ?></option>
                                                <option value="RBSM"><?//= trans('Rejected By Staff Manager') ?></option>
                                                <option value="ABPM"><?//= trans('Approved By Purchase Manager') ?></option>
                                                <option value="RBPM"><?//= trans('Rejected By Purchase Manager') ?></option>
                                                <option value="POC"><?//= trans('Purchase Order Created') ?></option>
                                            </select>
                                        </div>

                                        <div class="col-lg-1">
                                            <label class="">&nbsp</label>
                                            <button type="button" id="search_btn" onclick="GetReport()"
                                                    class="form-control btn btn-sm btn-primary">
                                                Search
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div> -->


                            <div class="table-responsive" style="padding: 7px 7px 7px 7px;">
                                <table class="table table-bordered" id="cash_handover_list_table">
                                    <thead>
                                    <th><?= trans('Date') ?></th>
                                    <th><?= trans('Reference') ?></th>
                                    <th><?= trans('Cashier User') ?></th>
                                    <th><?= trans('Cashier A/C') ?></th>
                                    <th><?= trans('Tot. to Pay') ?></th>
                                    <th><?= trans('Adjustments') ?></th>
                                    <th><?= trans('Amount') ?></th>
                                    <th><?= trans('Balance') ?></th>
                                    <th><?= trans('Denominations') ?></th>
                                    <th><?= trans('Status') ?></th>
                                    <th></th>
                                    </thead>
                                    <tbody id="tbody">

                                    </tbody>
                                </table>
                                <div id="pg-link"></div>
                            </div>
                            <!--end::Form-->
                        </div>
                    <?php endif ?>
                </div>
            </div>
            <!-- end:: Content -->
        </div>
    </div>
</div>


<?php include "footer.php"; ?>





<script>

    $.date = function (dateObject) {
        var d = new Date(dateObject);
        var day = d.getDate();
        var month = d.getMonth() + 1;
        var year = d.getFullYear();
        if (day < 10) {
            day = "0" + day;
        }
        if (month < 10) {
            month = "0" + month;
        }
        var date = day + "-" + month + "-" + year;

        return date;
    };


    var curr_user_id = '<?php echo $_SESSION['wa_current_user']->user ?>';


    $(document).ready(function () {

        GetList();

    });




    $(document).on("click", ".pg-link", function (e) {

        e.preventDefault();

        var req_url = $(this).attr("href");

        AxisPro.BlockDiv("#kt_content");


        $(".error_note").hide();

        AxisPro.APICall('GET', req_url, {}, function (data) {

            DisplayList(data);


        });


    });


    function GetList() {

        AxisPro.BlockDiv("#kt_content");

        var $form = $("#filter_form");
        var params = AxisPro.getFormData($form);

        $(".error_note").hide();

        AxisPro.APICall('POST', route('API_Call', {method: 'getAllCashHandoverRequests'}), params, function (data) {

            DisplayList(data);


        });

    }


    function DisplayList(data) {

        var rep = data.rep;
        var users = data.users;
        var bank_ledgers = data.bl;

        var tbody_html = "";

        $.each(rep, function (key, value) {

            tbody_html += "<tr>";
            tbody_html+= "<td>"+value.trans_date+"</td>";
            tbody_html+= "<td>"+value.reference+"</td>";
            tbody_html+= "<td>"+users[value.cashier_id]+"</td>";
            tbody_html+= "<td>"+value.cash_acc_code+"-"+bank_ledgers[value.cash_acc_code].account_name+"</td>";
            tbody_html+= '<td class="text-right">'+ value.total_to_pay +"</td>";
            tbody_html+= '<td class="text-right">'+ value.adj +"</td>";
            tbody_html+= '<td class="text-right">'+ value.amount +"</td>";
            tbody_html+= '<td class="text-right">'+ value.balance +"</td>";
            
            tbody_html+= (
                    '<td>'
                +       '<button '
                +           'class="btn collapsed text-primary" '
                +           'type="button" '
                +           'data-bs-toggle="collapse" '
                +           'data-bs-target="#collapse--t--' + key + '" '
                +           'aria-expanded="false" '
                +           'aria-controls="collapseExample">'
                +           '<u>Show denominations</u>'
                +       '</button>'
                +       '<div id="collapse--t--' + key + '" class="collapse">'
                +           '<table class="table-borderless">'
                +           '<tbody>'
            );
            value.denoms.forEach(function(item) {
                tbody_html+= (
                                '<tr class="w-100">'
                    +               '<td class="text-right" style="width:45%">' + item.key + '</td>'
                    +               '<td style="width:10%"> - </td>'
                    +               '<td style="width:45%">' + item.val + '</td>'
                    +           '</tr>'
                );
            })
            tbody_html+= '</div></tbody></table></td>';

            tbody_html+= "<td>"+value.status+"</td>";

            var action_html = "<button type='button' class='btn-approve button btn btn-sm btn-success' onclick='approveRequest("+value.id+")'>APPROVE</button>";
            action_html += "<button type='button' class='btn-reject button btn btn-sm btn-warning' onclick='rejectRequest("+value.id+")'>REJECT</button>";

            var print_link = "ERP/cash_ho_print?req_id="+value.id;
            var print_btn = "<button type='button' class='button btn btn-sm btn-primary'>" +
                "<a style='color: white' target='_blank' href='"+print_link+"'>PRINT<a/></button>";
            if(value.status !== "PENDING")
                action_html = "";

            tbody_html+= "<td>"+action_html+print_btn+"</td>";

            tbody_html += "</tr>";

        });

        $("#tbody").html(tbody_html);


        $("#pg-link").html(data.pagination_link);

        // $('.double-scroll-table').doubleScroll();

        AxisPro.UnBlockDiv("#kt_content");

    }

    function approveRequest(id) {

        swal.fire({
            title: 'Are you sure?',
            text: "Confirm whether you want to APPROVE this Cash Handover",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve!'
        }).then(function (result) {
            if (result.value) {

                takeAction(id, 'APPROVED', function (data) {

                    swal.fire(
                        data.status === 'SUCCESS' ? 'Success!' : 'FAILED',
                        data.msg,
                        data.status === 'SUCCESS' ? 'success' : 'warning'
                    ).then(function () {
                        window.location.reload();
                    });

                });


            }
        });

    }


    function takeAction(req_id, action, callback) {

        var params = {
            req_id: req_id,
            action: action
        };

        AxisPro.APICall('POST', route('API_Call', {method: 'cashHandoverRequestActionHandler'}),
            params, function (data) {

                if (callback)
                    callback(data);

            });

    }

    function rejectRequest(id) {

        swal.fire({
            title: 'Are you sure?',
            text: "Confirm whether you want to REJECT this Cash Handover Request",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, REJECT!'
        }).then(function (result) {
            if (result.value) {

                takeAction(id, 'REJECTED', function (data) {

                    swal.fire(
                        data.status === 'SUCCESS' ? 'Success!' : 'FAILED',
                        data.msg,
                        data.status === 'SUCCESS' ? 'success' : 'warning'
                    ).then(function () {
                        window.location.reload();
                    });

                });

            }
        });

    }

    function deleteRequest(id) {

        swal.fire({
            title: 'Are you sure?',
            text: "Confirm whether you want to DELETE this request",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, DELETE!'
        }).then(function (result) {
            if (result.value) {

                // alert(11)

            }
        });

    }



    function showRequest(id, $this) {

        var params = {
            id: id
        };

        AxisPro.APICall('GET', route('API_Call', {method: 'getPurchaseRequest'}), params, function (data) {


            var req = data.req;

            $("#v_rqst_ref").html(req.reference);


            $("#attached_link").hide();
            if(req.upload_file) {
                var file = 'assets/uploads/'+req.upload_file;
                $("#attached_link").html("<a target='_blank' href='"+file+"'>Attached Document</a>");
                $("#attached_link").show();
            }


            var tr_html = "";
            $.each(data.items, function (key, val) {

                tr_html += "<tr>";
                tr_html += "<td>" + val.stock_id + "</td>";
                tr_html += "<td>" + val.item_name + "</td>";
                tr_html += "<td>" + val.qty + "</td>";
                tr_html += "<td>" + val.qty_in_stock + "</td>";
                tr_html += "<td>" + val.qty_to_be_ordered + "</td>";
                tr_html += "<td>" + val.description + "</td>";
                tr_html += "</tr>";

            });

            $("#item_detail_table_tbody").html(tr_html);


            var log_body_html = "";
            $.each(data.log, function (key, val) {

                log_body_html += "<tr>";
                log_body_html += "<td>" + val.created_at + "</td>";
                log_body_html += "<td>" + val.description + "</td>";
                log_body_html += "<td>" + val.user_id + "</td>";
                log_body_html += "</tr>";

            });

            $("#req_log_tbody").html(log_body_html);

        });


        var footer_html = $($this).find('.action_td').html();


        $("#modal_footer").html(footer_html);

        $("#ShowRequestModel").modal('show');

    }



</script>
