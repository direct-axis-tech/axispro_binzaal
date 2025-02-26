<?php include "header.php" ?>

<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-container  kt-grid__item kt-grid__item--fluid">
                <div class="row">
                    <div class="kt-portlet">

                        <div class="kt-portlet__head">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title">
                                    <?= trans('PURCHASE REQUESTS') ?>
                                </h3>
                            </div>
                            <div class="kt-portlet__head-label">
                                <a href="purchase_request_new.php" class="btn btn-sm btn-primary">Add New Purchase Request</a>
                            </div>
                        </div>

                        <!--begin::Form-->
                        <div style="padding: 14px">
                            <form id="filter_form">
                                <div class="form-group row">
                                    <div class="col-lg-1">
                                        <label class=""><?= trans('Reference') ?>:</label>
                                        <input type="text" name="fl_ref"
                                               class="form-control"/>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class=""><?= trans('From Date') ?>:</label>
                                        <input type="text" name="fl_start_date"
                                               class="form-control ap-datepicker config_begin_fy"
                                               readonly placeholder="Select date"
                                               value="<?= sql2date(APConfig('curr_fs_yr', 'begin')) ?>"/>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class=""><?= trans('To Date') ?>:</label>
                                        <input type="text" name="fl_end_date"
                                               class="form-control ap-datepicker config_begin_fy"
                                               readonly placeholder="Select date"
                                               value="<?= sql2date(APConfig('curr_fs_yr', 'end')) ?>"/>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class=""><?= trans('Requested By') ?>:</label>
                                        <select name="fl_requested_by" class="form-control kt-select2 ap-select2">
                                            <?= prepareSelectOptions($api->get_records_from_table('0_users', ['id', 'user_id']), 'id', 'user_id') ?>
                                        </select>
                                    </div>

                                    <div class="col-lg-3">
                                        <label class=""><?= trans('Status') ?>:</label>
                                        <select class="form-control kt-selectpicker" name="fl_status">
                                            <option value=""><?= trans('All') ?></option>
                                            <option value="WFSMA"><?= trans('Waiting For Staff Manager Approval') ?></option>
                                            <option value="WFPMA"><?= trans('Waiting for Purchase Manager Approval') ?></option>
                                            <option value="RBSM"><?= trans('Rejected By Staff Manager') ?></option>
                                            <option value="ABPM"><?= trans('Approved By Purchase Manager') ?></option>
                                            <option value="RBPM"><?= trans('Rejected By Purchase Manager') ?></option>
                                            <option value="POC"><?= trans('Purchase Order Created') ?></option>
                                        </select>
                                    </div>

                                    <div class="col-lg-1">
                                        <label class="">&nbsp</label>
                                        <button type="button" id="search_btn" onclick="GetReport()" class="form-control btn btn-sm btn-primary"> Search </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive" style="padding: 7px 7px 7px 7px;">
                            <table class="table table-bordered" id="service_list_table">
                                <thead>
                                <th><?= trans('Date') ?></th>
                                <th><?= trans('Reference') ?></th>
                                <th><?= trans('Requested By') ?></th>
                                <th><?= trans('Status') ?></th>
                                <th><?= trans('Memo') ?></th>

                                <th></th>
                                </thead>
                                <tbody id="tbody">

                                </tbody>
                            </table>
                            <div id="pg-link"></div>
                        </div>

                        <!--end::Form-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>

<div class="modal fade" id="ShowRequestModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="COA_change_group_modal_title"><?= trans('View Purchase Request') . " - <span id='v_rqst_ref'></span>" ?></h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"  aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <table class="table table-bordered" id="item_detail_table">
                        <thead>
                        <tr>
                            <th scope="col" style="10%">Item Code</th>
                            <th scope="col" style="">Item Name</th>
                            <th scope="col">Qty Requested</th>
                            <th scope="col">Qty In-Stock</th>
                            <th scope="col">Qty to be Ordered</th>
                            <th scope="col">Description</th>
                        </tr>
                        </thead>
                        <tbody id="item_detail_table_tbody">

                        </tbody>
                        <span id="attached_link"></span>
                    </table>
                </form>
                <div>
                    <h5>Request Log</h5>
                    <table class="table table-bordered" id="item_detail_table">
                        <thead>
                        <tr>
                            <th scope="col" style="10%">DateTime</th>
                            <th scope="col" style="">Description</th>
                            <th scope="col">User</th>
                        </tr>
                        </thead>
                        <tbody id="req_log_tbody">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" id="modal_footer">

            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="UploadDocModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                ><?= trans('Upload Document') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="upload_doc_form">

                    <div class="form-group row">

                        <label class=""><?= trans('Upload File') ?>:</label>
                        <input type="hidden" id="upload_doc_pr_id" name="upload_doc_pr_id">
                        <input type="file" name="upload_doc" id="upload_doc"
                               class="form-control"/>

                    </div>

                </form>
            </div>
            <div class="modal-footer" id="modal_footer">

                <button type="button" class="btn btn-success" id="upload_doc_btn">Upload Document</button>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="IssueItemsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    ><?= trans('Issue Items') ?></h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"  aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" id="issueItemReqID">

                    <table class="table table-bordered" id="issue_items_table">
                        <thead>
                        <tr>
                            <th scope="col" style="10%">Item Code</th>
                            <th scope="col" style="">Item Name</th>
                            <th scope="col">Issuing Qty</th>
                            <th scope="col">Unit Cost</th>
                            <th scope="col">Total</th>
                        </tr>
                        </thead>
                        <tbody id="issue_items_table_tbody">

                        </tbody>
                    </table>
                </form>
            </div>
            <div class="modal-footer" id="modal_footer">
                <button type="button" class="btn btn-success" id="btn_issue_items">Issue Items</button>
            </div>
        </div>
    </div>
</div>

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
        GetReport();
    });

    $(document).on("click", "#btn_issue_items", function (e) {
        e.preventDefault();
        var items_array = [];

        $("#issue_items_table_tbody tr").each(function (i, row) {
            var $row = $(row);
            var obj = {
                stock_id: $row.find(".iss_item_code").val(),
                qty: $row.find(".iss_issue_qty").val(),
                standard_cost: $row.find(".iss_unit_cost").val(),
            };
            items_array.push(obj);
        });

        // for testing purpose only
        // console.log(items_array);

        var params = {
            issueItemReqID : $("#issueItemReqID").val()
        }
        params.items = items_array;
        AxisPro.BlockDiv("#kt_content");
        $(".error_note").hide();

        AxisPro.APICall('POST', route('API_Call', {method: 'issueStockItems'}), params, function (data) {
            swal.fire(
                data.status === 'OK' ? 'Success!' : 'FAILED',
                data.msg,
                data.status === 'OK' ? 'success' : 'warning'
            ).then(function () {
                window.location.reload();
            });
        });
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

        AxisPro.APICall('POST', route('API_Call', {method: 'getPurchaseRequests'}), params, function (data) {
            DisplayReport(data);
        });
    }

    function DisplayReport(data) {
        var rep = data.rep;
        var users = data.users;
        var tbody_html = "";

        $.each(rep, function (key, value) {

            tbody_html += "<tr ondblclick='showRequest(" + value.id + ",this)'>";


            if (!value.approved_by)
                value.approved_by = '';


            console.log(value);

            var status = '';
            if (value.po_id !== '0')
                status = 'PO Created';

            if (value.staff_mgr_action === '0')
                status = 'Waiting for Staff Manager ('+users[value.staff_mgr_id]+') Approval';

            if (value.staff_mgr_action === '1')
                status = 'Waiting for Purchase Manager ('+users[value.purch_mgr_id]+') Approval';
            if (value.staff_mgr_action === '2')
                status = 'Rejected By Staff Manager ('+users[value.staff_mgr_id]+')';

            if (value.purch_mgr_action === '1')
                status = 'Approved By Purchase Manager ('+users[value.purch_mgr_id]+')';
            if (value.purch_mgr_action === '2')
                status = 'Rejected By Purchase Manager ('+users[value.purch_mgr_id]+')';


            var item_issued = false;
            if (value.issued_from_stock === '1') {
                status = 'Issued from stock';
                item_issued = true;
            }

            if (value.po_id !== '0')
                status = 'Purchase Order Created';


            tbody_html += "<td>" + $.date(new Date(value.created_at));
            +"</td>";
            tbody_html += "<td>" + value.reference + "</td>";
            tbody_html += "<td>" + users[value.created_by] + "</td>";
            tbody_html += "<td>" + status + "</td>";
            tbody_html += "<td>" + value.memo + "</td>";

            tbody_html += "<td class='action_td'>";

            if (
                (
                    (curr_user_id == value.staff_mgr_id && value.staff_mgr_action !== '1') ||
                    (curr_user_id == value.purch_mgr_id && value.purch_mgr_action !== '1')
                )
                &&
                (value.staff_mgr_action !== '2') && (value.purch_mgr_action !== '2')
            ) {
                tbody_html += "<a href='#' onclick='approveRequest(" + value.id + ")' class='btn btn-success btn-sm'><i class=\"fa fa-check-circle\" aria-hidden=\"true\"></i></i></a>";
            }

            if (
                (
                    (curr_user_id == value.staff_mgr_id && value.staff_mgr_action !== '1') ||
                    (curr_user_id == value.purch_mgr_id && value.purch_mgr_action !== '1')
                )
                &&
                (value.staff_mgr_action !== '2') && (value.purch_mgr_action !== '2')
            ) {
                tbody_html += "<a href='#' onclick='rejectRequest(" + value.id + ")' class='btn btn-warning btn-sm'><i class=\"fas fa-times-circle\"></i></a>";
            }

            if (
                (
                    (curr_user_id == value.staff_mgr_id && value.staff_mgr_action !== '1') ||
                    (curr_user_id == value.purch_mgr_id && value.purch_mgr_action !== '1')
                )
                &&
                (value.staff_mgr_action !== '2') && (value.purch_mgr_action !== '2')
            ) {
                tbody_html += "<a href='purchase_request_new.php?edit_id=" + value.id + "' class='btn btn-primary btn-sm'><i class='fa fa-pencil-alt'></i></a>";
            }

            if (
                (
                    (curr_user_id == value.staff_mgr_id && value.staff_mgr_action !== '1') ||
                    (curr_user_id == value.purch_mgr_id && value.purch_mgr_action !== '1')
                )
                &&
                (value.staff_mgr_action !== '2') && (value.purch_mgr_action !== '2')
            ) {
                tbody_html += "<a href='#' onclick='deleteRequest(" + value.id + ")' data-id='' class='btn btn-danger btn-sm'>";
                tbody_html += "<i class='fa fa-trash-alt'></i></a>";
            }

            if ((curr_user_id == value.purch_mgr_id && value.purch_mgr_action === '1') && (value.po_id === '0')) {
                if (!item_issued) {

                    tbody_html += "<a href='#' onclick='issueItem(" + value.id + ")' data-id='' class='btn btn-warning btn-sm'>Issue Item</a>";
                    tbody_html += "<a href='#' onclick='createPO(" + value.id + ")' data-id='' class='btn btn-success btn-sm'>Create PO</a>";
                }
            }

            if ((curr_user_id == value.purch_mgr_id)) {
                tbody_html += "<a href='#' class='btn btn-success btn-sm' onclick='loadUploadPopup(" + value.id + ")'>Upload Document</a>";
            }

            tbody_html += "</td>";
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
            text: "Confirm whether you want to APPROVE this request",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve!'
        }).then(function (result) {
            if (result.value) {
                takeAction(id, 1, function (data) {
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
        AxisPro.APICall('POST', route('API_Call', {method: 'purchaseRequestActionHandler'}),
            params, function (data) {
                if (callback)
                    callback(data);
            });
    }

    function rejectRequest(id) {
        swal.fire({
            title: 'Are you sure?',
            text: "Confirm whether you want to REJECT this request",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, REJECT!'
        }).then(function (result) {
            if (result.value) {
                takeAction(id, 2, function (data) {
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

    function createPO(id) {
        swal.fire({
            title: 'Are you sure?',
            text: "Confirm whether you want to Create Purchase Order against this request",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, CREATE PURCHASE ORDER!'
        }).then(function (result) {
            if (result.value) {
                window.location.href = 'ERP/purchasing/po_entry_items.php?NewOrder=Yes&req_id=' + id
            }
        });
    }

    function issueItem(id) {
        swal.fire({
            title: 'Are you sure?',
            text: "Confirm whether you want to Issue Items against this request",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, ISSUE!'
        }).then(function (result) {
            if (result.value) {

                var params = {
                    pr_id:id
                };

                AxisPro.APICall('GET', route('API_Call', {method: 'get_issuing_items'}), params, function (data) {
                    $("#issueItemReqID").val(id);
                    $("#activityID").val(activityID);
                    var table_html = '';

                    $.each(data, function(key,val) {
                        table_html += '<tr>' +
                            '<td><input type="text" readonly class="iss_item_code form-control" value="'+val.item_code+'"></td>' +
                            '<td><input type="text" readonly class="iss_desc form-control" value="'+val.item_name+'"></td>' +
                            '<td><input type="text" class="iss_issue_qty form-control" value="'+val.issue_qty+'"></td>' +
                            '<td><input type="text" readonly class="iss_unit_cost form-control" value="'+val.unit_cost+'"></td>' +
                            '<td><input type="text" readonly class="iss_total_cost form-control" value="'+val.total_cost+'"></td>' +
                            '</tr>';
                    });
                    $("#issue_items_table_tbody").html(table_html);
                });

                $("#IssueItemsModal").modal('show');
                // window.location.href = 'ERP/inventory/adjustments.php?NewAdjustment=1&req_id=' + id
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

    function loadUploadPopup(id) {


        $("#upload_doc_pr_id").val(id);

        $("#UploadDocModal").modal("show")


    }

    $(document).on("click", "#upload_doc_btn", function (e) {


        var form = $("#upload_doc_form");
        var params = form.serializeArray();
        var files = $("#upload_doc")[0].files;
        var formData = new FormData();
        for (var i = 0; i < files.length; i++) {
            formData.append('upload_doc', files[i]);
        }
        $(params).each(function(index, element) {
            formData.append(element.name, element.value);
        });

        

        $.ajax({
            type: "POST",
            url: route('API_Call', {method: 'upload_purchase_req_doc'}),
            data: formData,
            contentType: false,
            processData: false,
            success: function (result) {
                var res = JSON.parse(result);
                if(res.status==='OK')
                {
                    toastr.success(res.msg);
                    location.reload();
                }
                if(res.status==='FAIL')
                {
                    toastr.error(res.msg);
                }
            }
        });


    })


</script>
