$(function() {
    var systemId = null;
    var modal = null;

    listenForSystemID();

    window.AutoFetch = {
        init: function (callback) {
            if (!document.querySelector('[data-dx-control="autofetch"]')) {
                return;
            }

            appendAutoFetchModalToBody();

            $(document).on('click', `[data-dx-control="autofetch"]`, function (e) {
                // if (!systemId) {
                //     return toastr.error('Autofetch is not running');
                // }

                if (!this.dataset.dxDimension) {
                    return toastr.error('Please select the cost center');
                }
        
                loadAutoBatchData(systemId, this.dataset.dxFrom || 'invoice', this.dataset.dxDimension)
            });

            $(document).on("click", "#batch_auto_add", function () {
                callback(getSelectedItemsFromAutoFetchTable());
                modal.hide();
                $('#batchModel table th input[type="checkbox"]').prop('checked', false);
            });
        }
    }
    
    $(document).on("click", '#batchModel th input:checkbox', function () {
        $('#batchModel tbody tr input:checkbox').prop('checked', this.checked);
    });
    
    /**
     * Loads the items into a model for the user to select
     * 
     * @param {string} systemId 
     */
    function loadAutoBatchData(systemId, from, dimension) {
        ajaxRequest({
            url: route('api.autofetch.pending', {systemId: systemId || Date.now()}),
            method: 'get',
            data: { dimension, from },
            dataType: 'json'
        }).done(resp => {
            var _addedApplicationIds = {};
            var tbody_html = "";
            var pendingTransactions = resp.data;

            pendingTransactions.forEach(function (item) {
                var application_id = item.application_id;
    
                if (_addedApplicationIds[application_id] == undefined) {
                    tbody_html += (
                            "<tr data-id='"+item.id+"'>"
                        +       "<td><input type='checkbox' class='auto_batch_checked'/></td>"
                        +       "<td class='af_srv_name mw-400px text-wrap' data-type='"+item.type+"' >" + item.service_en + "</td>"
                        +       "<td class='af_srv_name_ar mw-400px text-wrap'>" + (item.service_ar || '') + "</td>"
                        +       "<td class='mw-400px text-wrap'>" + (item.company || '') + "</td>"
                        +       "<td class='mw-400px text-wrap'>" + (item.contact_name || '') + "</td>"
                        +       "<td>" + (item.contact_no || '') + "</td>"
                        +       "<td class='af_tot'>" + item.total + "</td>"
                        +       "<td class='af_srv_amt'>" + item.service_chg + "</td>"
                        +       "<td class='af_tr_id'>" + item.transaction_id + "</td>"
                        +       "<td class='af_app_id'>" + application_id + "</td>"
                        +       "<td class='mw-400px text-wrap'>" + item.web_user + "</td>"
                        +   "</tr>"
                    );
                    _addedApplicationIds[application_id] = true;
                }
            });

            $("#batch_auto_tbody").html(tbody_html);
            modal.show();
        }).fail(function () {
            toastr.error("Something went wrong! Could not fetch applications");
        });
    }

    function listenForSystemID() {
        const autoFetchBtn = document.querySelector('[data-dx-control="autofetch"]');
        
        if (!autoFetchBtn) {
            return;
        }

        if (autoFetchBtn.dataset.dxSystemId) {
            systemId = autoFetchBtn.dataset.dxSystemId;
            return;
        }

        // Create a MutationObserver instance
        const observer = new MutationObserver((mutationsList, observer) => {
            for (const mutation of mutationsList) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-dx-system-id') {
                    systemId = mutation.target.dataset.dxSystemId;
                    observer.disconnect();
                }
            }
        });

        // Start observing the target node for attribute changes
        observer.observe(autoFetchBtn, { attributes: true });

        // To disconnect the observer later
        setTimeout(() => observer.disconnect(), 8000)
    }

    // Append the autofetch modal to body
    function appendAutoFetchModalToBody() {
        $(document.body).append(`
            <div id="batchModel" class="modal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered" style="max-width:1299px; width: 100%;">
                    <div class="modal-content">
                        <div class="modal-header py-3">
                            <h4 class="modal-title">Auto Batch</h4>
                            <button type="button" class="btn py-0 btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive scroll-y" style="max-height: 500px">
                                <table class="table table-striped thead-strong table-bordered gx-2">
                                    <thead class="text-nowrap">
                                        <tr>
                                            <th><input type='checkbox'/></th>
                                            <th>Service Name</th>
                                            <th>Service Name Ar</th>
                                            <th>Applicant Name</th>
                                            <th>Contact Name</th>
                                            <th>Contact No</th>
                                            <th>Amount</th>
                                            <th>Srv Chrg.</th>
                                            <th>Transaction ID</th>
                                            <th>Application ID</th>
                                            <th>Web user</th>
                                        </tr>
                                    </thead>
                                    <tbody id="batch_auto_tbody">


                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer py-2">
                            <button type="button" class="btn shadow-none bg-active-lighten bg-gray-700 text-white" id="batch_auto_add">Add Selected Items</button>
                        </div>
                    </div>
                </div>
            </div>`
        )

        modal = bootstrap.Modal.getOrCreateInstance(document.querySelector('#batchModel'));
    }

    function getSelectedItemsFromAutoFetchTable() {
        let items = [];

        $('#batchModel table tbody').find('tr').each(function () {
            var row = $(this);
            if (row.find('input[type="checkbox"]').is(':checked')) {
                var item = {
                    id: row[0].dataset.id,
                    type : row.find(".af_srv_name").attr('data-type'),
                    name_en : row.find(".af_srv_name").html(),
                    name_ar : row.find(".af_srv_name_ar").html(),
                    total : row.find(".af_tot").html(),
                    srv_chrg : row.find(".af_srv_amt").html(),
                    transaction_id : row.find(".af_tr_id").html(),
                    application_id : row.find(".af_app_id").html(),
                };

                if (!item.srv_chrg) {
                    return;
                }

                items.push(item);
            }
        });

        return items;
    }
})