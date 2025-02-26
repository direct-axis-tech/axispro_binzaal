<div id="deliver-maid-model" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <!-- Modal content-->
        <div class="modal-content">
            <form action="" method="post" id="deliver_maid_model__deliver_form">
                <div class="modal-header">
                    <h4 class="modal-title">Deliver Maid Against This Contract</h4>
                </div>
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="contract_id" id="deliver_maid_model__contract_id">
                    <input type="hidden" name="dimension_id" id="deliver_maid_model__dimension_id">
                    <input type="hidden" name="maid_id" id="deliver_maid_model__maid_id">
                    <div class="form-group row mb-1">
                        <label
                            class="col-sm-4 col-form-label"
                            for="deliver_maid_model__lbl_reference">
                            Reference
                        </label>
                        <div class="col-sm-8">
                            <input
                                type="text"
                                class="form-control-sm form-control-plaintext px-4"
                                id="deliver_maid_model__lbl_reference"
                                readonly>
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label
                            class="col-sm-4 col-form-label"
                            for="deliver_maid_model__lbl_maid">
                            Maid
                        </label>
                        <div class="col-sm-8">
                            <input
                                type="text"
                                class="form-control-sm form-control-plaintext px-4"
                                id="deliver_maid_model__lbl_maid"
                                readonly>
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label
                            class="col-sm-4 col-form-label"
                            for="deliver_maid_model__lbl_contract_period">
                            Contract Period
                        </label>
                        <div class="col-sm-8">
                            <div id="deliver_maid_model__lbl_contract_period" class="input-group input-daterange">
                                <input
                                    type="text"
                                    class="form-control-sm form-control-plaintext px-4 w-125px text-start"
                                    id="deliver_maid_model__lbl_contract_from"
                                    value=""
                                    readonly>
                                <div class="input-group-text input-group-addon px-4 rounded-0 border-0 fw-normal text-inverse-light">to</div>
                                <input
                                    type="text"
                                    id="deliver_maid_model__lbl_contract_till"
                                    class="form-control-sm form-control-plaintext px-4 w-125px text-end"
                                    value=""
                                    readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label
                            class="col-sm-4 col-form-label"
                            for="deliver_maid_model__lbl_amount">
                            Amount
                        </label>
                        <div class="col-sm-8">
                            <input
                                type="text"
                                class="form-control-sm form-control-plaintext px-4"
                                id="deliver_maid_model__lbl_amount"
                                readonly>
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label
                            class="col-sm-4 col-form-label"
                            for="deliver_maid_model__delivery_date">
                            Delivery Date
                        </label>
                        <div class="col-sm-8">
                            <input
                                type="text"
                                required
                                data-parsley-trigger-after-failure="change"
                                data-parsley-date="{{ dateformat('momentJs') }}"
                                data-control="bsDatepicker"
                                class="form-control-sm form-control"
                                data-date-today-btn="linked"
                                autocomplete="off"
                                name="delivery_date"
                                id="deliver_maid_model__delivery_date"
                                value="">
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label
                            class="col-sm-4 col-form-label"
                            for="deliver_maid_model__lbl_maid_status">
                            Maid Availability
                        </label>
                        <div class="col-sm-8">
                            <label
                                class="form-control-sm form-control-plaintext px-4"
                                id="deliver_maid_model__lbl_maid_status">
                                --
                            </label>
                        </div>
                    </div>

                    @if (authUser()->hasPermission(\App\Permissions::SA_SUPPLIERINVOICE))
                    @include('labours.contract._addDirectSupplierInvoice')
                    @endif
                </div>
                <div class="modal-footer w-100 text-end">
                    <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Deliver</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>

route.push('labour.checkAvailability', '{{ rawRoute('labour.checkAvailability') }}');

$(() => {
    window.LabourContract = window.LabourContract || new EventTarget();

    const dateFormat = '{{ dateformat('momentJs') }}';
    const form = document.querySelector("#deliver_maid_model__deliver_form");
    const parsleyDeliveryForm = $(form).parsley();
    
    parsleyDeliveryForm.on('form:submit', function() {
        const contractId = form.elements.contract_id.value;

        ajaxRequest({
            method: 'post',
            url: url('/ERP/sales/labour_contract.php', {
                action: 'deliverMaid',
                contract: contractId
            }),
            data: new FormData(form),
            contentType: false,
            processData: false
        }).done(function(data, textStatus, xhr) {
            if (xhr && xhr.status == 204) {
                toastr.success("Maid Delivered Successfully");
                $('#deliver-maid-model').modal('hide');
                LabourContract.dispatchEvent(new Event('DeliverySuccessful'));
                return;
            }

            defaultErrorHandler()
        }).fail(function(xhr, desc, err) {
            if (xhr && xhr.status == 422) {
                return toastr.error(xhr.responseJSON.message || 'Invalid Data');
            }
                
            defaultErrorHandler()
        });

        return false;
    });

    parsleyDeliveryForm.$element.on('reset', function() {
        parsleyDeliveryForm.reset();
        setTimeout(() => {
            $('[data-parsley-trigger-after-failure]')
                .datepicker('update')
                .trigger('changeDate')
                .trigger('change');

            $('#deliver_maid_model__lbl_maid_status')
                .removeClass('text-success text-danger')
                .text('--');
        }, 0);
    })

    LabourContract.initiateMaidDelivery = (contract) => {
        $('#deliver_maid_model__lbl_reference').val(contract.reference);
        $('#deliver_maid_model__lbl_maid').val(contract.labour_name);
        $('#deliver_maid_model__lbl_amount').val(contract.amount);
        $('#deliver_maid_model__lbl_contract_from').val(moment(contract.contract_from).format(dateFormat));
        $('#deliver_maid_model__lbl_contract_till').val(moment(contract.contract_till).format(dateFormat));
        $('#deliver_maid_model__delivery_date').datepicker('setStartDate', moment(contract.contract_from).toDate());
        $('#deliver_maid_model__contract_id').val(contract.id);
        $('#deliver_maid_model__maid_id').val(contract.labour_id);
        $('#deliver_maid_model__dimension_id').val(contract.dimension_id);

        $('#deliver-maid-model').modal('show');
    }

    $(form.elements.delivery_date).on('change', function () {
        const delivery_date = moment(form.elements.delivery_date.value, dateFormat);
        const maid_id = $('#deliver_maid_model__maid_id').val()
        let diffInDays = 0;

        if (
            !delivery_date.isValid()
            || !maid_id.length
        ) {
            return false;
        }

        ajaxRequest({
            url: route('labour.checkAvailability'),
            method: 'post',
            data: {
                maid_id: maid_id,
                delivery_date: delivery_date.format(dateFormat)
            }
        })
        .done((respJson, msg, xhr) => {
            if (!respJson.data) {
                return defaultErrorHandler(xhr);
            }

            let color = respJson.data.is_available ? 'success' : 'danger';
            $('#deliver_maid_model__lbl_maid_status')
                .removeClass('text-success text-danger')
                .addClass(`text-${color}`)
                .text(respJson.data.status);

            if (window.SupplierInvoice) {
                (!respJson.data.is_available && !respJson.data.first_following_out_date)
                    ? SupplierInvoice.initialize(
                        $('#deliver_maid_model__contract_id').val(),
                        $('#deliver_maid_model__dimension_id').val()
                    )
                    : SupplierInvoice.Collapse.hide();
            }
        })
        .fail(defaultErrorHandler)
    })

    $('#deliver-maid-model').on('hidden.bs.modal', function () {
        parsleyDeliveryForm.element.reset();
    });
})
</script>
@endpush