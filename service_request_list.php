<?php include "header.php"; ?>

<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">
            <div class="kt-container  kt-grid__item kt-grid__item--fluid">
                <div class="row">
                    <div class="kt-portlet">
                        <div class="kt-portlet__head">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title">
                                    <?= trans('LIST OF SERVICE REQUESTS') ?>
                                </h3>
                            </div>

                            <div class="kt-portlet__head-label">
                                <a href="new_service_request.php" class="btn btn-sm btn-primary">Add New Service Request</a>
                            </div>
                        </div>

                        <!--begin::Form-->
                        <div style="padding: 14px">
                            <form id="filter_form">
                                <div class="form-group row">
                                    <div class="col-lg-2">
                                        <label class=""><?= trans('From Date') ?>:</label>
                                        <input type="text"
                                            name="fl_start_date"
                                            class="form-control ap-datepicker config_begin_fy"
                                            readonly
                                            placeholder="Select date"
                                            value="<?= Today() ?>"/>
                                    </div>

                                    <div class="col-lg-2">
                                        <label class=""><?= trans('To Date') ?>:</label>
                                        <input type="text"
                                            name="fl_end_date"
                                            class="form-control ap-datepicker config_begin_fy"
                                            readonly
                                            placeholder="Select date"
                                            value="<?= Today() ?>"/>
                                    </div>

                                    <div class="col-lg-3">
                                        <label class=""><?= trans('Status') ?>:</label>
                                        <select class="form-control kt-selectpicker" name="fl_status">
                                            <option value=""><?= trans('All') ?></option>
                                            <option selected value="NOT_FULLY_COMPLETED"><?= trans('Not Fully Completed') ?></option>
                                            <option value="PARTIALLY_COMPLETED"><?= trans('Partially Completed') ?></option>
                                            <option value="WITHOUT_TRANS_COMPLETED"><?= trans('Completed without Transaction') ?></option>
                                            <option value="PENDING"><?= trans('Pending Only') ?></option>
                                            <option value="COMPLETED"><?= trans('Completed') ?></option>
                                            <option value="TRANS_COMPLETED"><?= trans('Completed with Transaction') ?></option>
                                        </select>
                                    </div>

                                    <div class="col-lg-1">
                                        <label class="">&nbsp</label>
                                        <button
                                            type="button"
                                            id="search_btn"
                                            class="form-control btn btn-sm btn-primary">
                                            Search
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!--end::Form-->

                        <div class="table-responsive" style="padding: 7px 7px 7px 7px;">
                            <table class="table table-bordered" id="service_req_list_table">
                                <thead>
                                    <tr>
                                        <th><?= trans('Date') ?></th>
                                        <th><?= trans('Reference') ?></th>
                                        <th><?= trans('Token') ?></th>
                                        <th><?= trans('Customer') ?></th>
                                        <th><?= trans('Memo') ?></th>
                                        <th><?= trans('Invoice Number') ?></th>
                                        <th><?= trans('Transaction IDs') ?></th>
                                        <th><?= trans('Status') ?></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="tbody"></tbody>
                            </table>

                            <div id="pg-link"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end:: Content -->
        </div>
    </div>
</div>

<div class="modal" tabindex="-1" id="serviceRequestItemsModal">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Service Request Items</h5>
            </div>
            <div class="modal-body">
                <div class="table-responsive w-100">
                    <table class="table table-striped table-bordered w-100 text-nowrap">
                        <thead>
                            <tr>
                                <th><?= trans('#') ?></th>
                                <th class="w-400px"><?= trans('Item Name') ?></th>
                                <th class="text-end"><?= trans('Qty') ?></th>
                                <th class="text-end"><?= trans('Govt Fee') ?></th>
                                <th class="text-end"><?= trans('Service Chg') ?></th>
                                <th class="text-end"><?= trans('Item Total') ?></th>
                                <th class="text-center"><?= trans('Inv Ref') ?></th>
                                <th class="text-start"><?= trans('Trans #') ?></th>
                                <th class="text-center"><?= trans('Status') ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="serviceRequestItemsTBody">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-action="makeLineItemInvoice">Make Invoice</button>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
$(function () {
    GetReport();

    $('#search_btn').on('click', GetReport);
    
    $(document).on("click", ".pg-link", function (e) {
        e.preventDefault();

        ajaxRequest($(this).attr("href"))
            .done(function (resp, msg, xhr) {
                if (!resp.rep) {
                    return defaultErrorHandler(xhr);
                }

                DisplayReport(resp)
            })
            .fail(defaultErrorHandler);
    });

    $(document).on('click', '[data-delete-id]', function (e) {
        DeleteServiceRequest(this.dataset.deleteId);
    });
    
    $(document).on('click', '[data-action="showItems"]', function (e) {
        const btn = e.target;
        const row = btn.closest('tr');
        ajaxRequest({
            method: 'POST',  
            data: {req_id: row.dataset.id},                
            url: route('API_Call', {method: 'getServiceRequestItems'}),
        }).done(function(resp, msg, xhr) {
            if (!resp.data) {
                return defaultErrorHandler(xhr);
            }

            const dec = <?= user_price_dec() ?>;
            const itemsTable = document.querySelector('#serviceRequestItemsTBody');
            let invoicableItems = 0;
            empty(itemsTable);
            itemsTable.dataset.req_id = row.dataset.id;
            itemsTable.dataset.cost_center_id = row.dataset.cost_center_id;
            itemsTable.dataset.token_number = row.querySelector('[data-token_number]').dataset.token_number;
            
            const fragment = document.createDocumentFragment();
            resp.data.items.forEach(item => {
                invoicableItems += +(item.is_invoiced == '0');
                const checkbox = `<input type="checkbox" class="form-check-input ml-2" data-line-id="${item.id}">`;
                const tr = $(
                    `<tr>
                        <td>${item.id}</td>
                        <td class="text-wrap">${item.description}</td>
                        <td class="text-end">${(parseFloat(item.qty) || 0)}</td>
                        <td class="text-end">${(parseFloat(item.total_govt_fee) || 0).toFixed(dec)}</td>
                        <td class="text-end">${(parseFloat(item.unit_price) || 0).toFixed(dec)}</td>
                        <td class="text-end">${(parseFloat(item.line_total) || 0).toFixed(dec)}</td>
                        <td class="text-center">${item.invoice_ref || ''}</td>
                        <td class="text-start">${item.transaction_id || ''}</td>
                        <td class="text-center"><span class="badge badge-${parseInt(item.is_invoiced) ? 'success' : 'warning'} p-2">${item.status}</span></td>
                        <td>${parseInt(item.is_invoiced) ? '' : checkbox}</td>
                    </tr>`
                )[0];
                fragment.appendChild(tr);
            });

            const makeInvoiceBtn = document.querySelector('[data-action="makeLineItemInvoice"]');
            if (makeInvoiceBtn) {
                makeInvoiceBtn.disabled = (invoicableItems == 0);
            }

            itemsTable.appendChild(fragment);
            $('#serviceRequestItemsModal').modal('show');
        }).fail(defaultErrorHandler);
    });

    $('#serviceRequestItemsModal').on('hidden.bs.modal', function () {
        const itemsTable = document.querySelector('#serviceRequestItemsTBody');
        empty(itemsTable);
        itemsTable.dataset.req_id = '';
        itemsTable.dataset.cost_center_id = '';
        itemsTable.dataset.token_number = '';
    });

    $('[data-action="makeLineItemInvoice"]').on('click', function () {
        const itemsTable = document.querySelector('#serviceRequestItemsTBody');
        const lineItems = [];
        document.querySelectorAll('input[type="checkbox"]:checked')
            .forEach((checkbox, i) => {
                lineItems.push(checkbox.dataset.lineId);
            });
        
        if (!lineItems.filter(id => Boolean(parseInt(id))).length) {
            return toastr.error('Please select at least one line item to invoice');
        }

        const makeInvoiceUrl = url('ERP/sales/sales_order_entry.php', {
            NewInvoice: 0,
            dim_id: itemsTable.dataset.cost_center_id,
            SRQ_TOKEN: itemsTable.dataset.token_number,
            req_id: itemsTable.dataset.req_id,
            item_ids: lineItems.filter(id => Boolean(parseInt(id))).join(',')
        })
        setBusyState();
        $('#serviceRequestItemsModal').modal('hide');
        window.location = makeInvoiceUrl;
    });

    function GetReport() {
        ajaxRequest({
            method: 'post',
            url: route('API_Call', {method: 'getServiceRequests'}),
            data: $("#filter_form").serialize()
        }).done(function (resp, msg, xhr) {
            if (!resp.rep) {
                return defaultErrorHandler(xhr);
            }

            DisplayReport(resp)
        }).fail(defaultErrorHandler);
    }

    function DisplayReport(data) {
        var rep = data.rep;
        var tbody_html = "";

        $.each(rep, function (key, value) {
            tbody_html += `<tr data-id="${value.id}" data-cost_center_id="${value.cost_center_id}">`;
            tbody_html += "<td>" + moment(value.created_at).format('<?= dateformat('momentJs') ?>') +"</td>";
            tbody_html += "<td>" + value.reference + "</td>";
            tbody_html += `<td data-token_number="${value.token_number}">${value.token_number}</td>`;
            tbody_html += "<td><u>" + value.customer_name + "</u><br>"+value.display_customer+"</td>";
            tbody_html += "<td>" + value.memo + "</td>";
            tbody_html += "<td>" + value.invoice_number + "</td>";
            tbody_html += "<td>" + value.transaction_ids + "</td>";
            tbody_html += "<td>" + value.req_status + "</td>";
            tbody_html += "<td class='action_td'>";
            
            if (!parseInt(value.is_fully_invoiced)) {
                <?php if (user_check_access('SA_MKINVFRMSRVREQ')): ?>
                var make_invoice_url = url("/ERP/sales/sales_order_entry.php", {
                    NewInvoice: 0,
                    dim_id: value.cost_center_id,
                    SRQ_TOKEN: value.token_number,
                    req_id: value.id
                });
                tbody_html += "<a href = '"+make_invoice_url+"' class='btn btn-sm btn-primary mx-1'>Make Invoice</a>";
                <?php endif; ?>
            }

            <?php if(user_check_access("SA_SRVREQLNITMINV")): ?>
            tbody_html += `<button type='button' class='btn btn-sm btn-info mx-1' data-action="showItems">Show Items</button>`;
            <?php endif; ?>

            if (!parseInt(value.is_invoiced_once)) {
                <?php if (user_check_access('SA_EDITSERVICEREQ')): ?>
                var edit_url = url("new_service_request.php", {edit_id: value.id});
                tbody_html += " <a href = '"+edit_url+"' class='btn btn-sm btn-warning mx-1'>Edit</a>";
                <?php endif; ?>

                <?php if (user_check_access('SA_DELSERVICEREQ')): ?>
                tbody_html += "<button type='button' class='btn btn-sm btn-danger mx-1' data-delete-id='" + value.id +"'>Delete</button>";
                <?php endif; ?>
            }
            
            <?php if(user_check_access("SA_PRINTSERVICEREQ")): ?>
            tbody_html += " <a href='"+url("ERP/service_request/print.php", {id: value.id})+"' target='_blank' class='btn btn-sm btn-primary mx-1'><i class='fa fa-print'></i></a>";
            <?php endif; ?>
            
            tbody_html += "</td>";
            tbody_html += "</tr>";
        });

        $("#tbody").html(tbody_html);
        $("#pg-link").html(data.pagination_link);
    }

    function DeleteServiceRequest(id) {
        ajaxRequest({
            method: 'POST',  
            data:{
                id: id
            },                
            url: route('API_Call', {method: 'deleteServiceRequest'}),
        }).done(function(data, msg, xhr) {
            if (!data.message) {
                return defaultErrorHandler(xhr);
            }

            swal.fire('Deleted', data.message, 'warning').then(GetReport);
        }).fail(defaultErrorHandler);
    }
});
</script>

<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); include "footer.php"; 