@extends('layout.app')

@section('title', 'Contract Inquiry')

@section('page')

<div class="container-fluid">
    <h1 class="mb-10">Contracts Inquiry</h1>
    <form action="{{ route('contract.index') }}" method="get" id="contracts_filter_form" enctype="multipart/form-data">               
        <div class="row">
            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="reference">Reference</label>
                <input class="form-control" type="text" name="reference" id="reference" value="{{ $inputs['reference'] }}">
            </div>

            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="debtor_no">Customer</label>
                <select class="form-select" name="debtor_no" id="debtor_no">
                    <option value="">-- select customer --</option>
                    @if ($inputs['debtor_no'] && ($customer = \App\Models\Sales\Customer::find($inputs['debtor_no'])))
                    <option value="{{ $customer->debtor_no }}" selected>{{ $customer->formatted_name }}</option>
                    @endif
                </select>
            </div>

            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="type">Type</label>
                <select name="type" id="type" class="form-select">
                    <option value="">-- select --</option>
                    @foreach ($labour_contract_types as $key => $val)
                        <option value="{{ $key }}" @if ($key == $inputs['type']) selected @endif> {{ $val }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="labour_id">Domestic Worker</label>
                <select class="form-select" name="labour_id" id="labour_id">
                    <option value="">-- select --</option>
                </select>
            </div>
            
            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="category_id">Category</label>
                <select class="form-select" name="category_id" id="category_id" data-control="select2">
                    <option value="">-- select --</option>
                    @foreach ($categories as $key => $value)
                        <option value="{{ $key }}" @if ($key == $inputs['category_id']) selected @endif>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="invoice_status">Invoice Status</label>
                <select class="form-select" name="invoice_status" id="invoice_status" data-control="select2">
                    <option value="">-- select --</option>
                    @foreach (["Fully Invoiced", "Partially Invoiced", "Not Invoiced"] as $key)
                    <option value="{{ $key }}" @if ($key == $inputs['invoice_status']) selected @endif>{{ $key }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="payment_status">Payment Status</label>
                <select class="form-select" name="payment_status" id="payment_status" data-control="select2">
                    <option value="">-- select --</option>
                    @foreach (["Fully Paid", "Partially Paid", "Not Paid"] as $key)
                    <option value="{{ $key }}" @if ($key == $inputs['payment_status']) selected @endif>{{ $key }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="contract_from_range"><?= trans('Contract From') ?>:</label>
                <div 
                    id="contract_from_range"
                    class="input-group input-daterange"
                    data-control='bsDatepicker'
                    data-date-keep-empty-values="true"
                    data-date-clear-btn="true">
                    <input
                        type="text" 
                        name="contract_from_start" 
                        id="contract_from_start"
                        class="form-control"
                        autocomplete="off"
                        placeholder="d-MMM-yyyy"
                        value="<?= $inputs['contract_from_start'] ?>">
                    <div class="input-group-text input-group-addon px-4 rounded-0 border-left-0 border-right-0">to</div>
                    <input
                        type="text" 
                        name="contract_from_end" 
                        id="contract_from_end"
                        class="form-control"
                        autocomplete="off"
                        value="<?= $inputs['contract_from_end'] ?>"
                        placeholder="d-MMM-yyyy">
                </div>
            </div>

            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="contract_till_range"><?= trans('Contract Till') ?>:</label>
                <div 
                    id="contract_till_range"
                    data-control='bsDatepicker'
                    class="input-group input-daterange"
                    data-date-keep-empty-values="true"
                    data-date-clear-btn="true">
                    <input
                        type="text" 
                        name="contract_till_start" 
                        id="contract_till_start"
                        class="form-control"
                        autocomplete="off"
                        placeholder="d-MMM-yyyy"
                        value="<?= $inputs['contract_till_start'] ?>">
                    <div class="input-group-text input-group-addon px-4 rounded-0 border-left-0 border-right-0">to</div>
                    <input
                        type="text" 
                        name="contract_till_end" 
                        id="contract_till_end"
                        class="form-control"
                        autocomplete="off"
                        value="<?= $inputs['contract_till_end'] ?>"
                        placeholder="d-MMM-yyyy">
                </div>
            </div>

            <div class="col-12 text-end">
                <button name="submit" type="submit" class="btn btn-primary btn-sm-block mx-2">Submit</button>
            </div>
        </div>
    </form>

    <div class="mw-100 p-2 bg-white rounded mt-2">
        <table id="contracts-table" class="table table-striped table-bordered table-hover gx-3 w-100 min-h-300px text-nowrap thead-strong">
            <!-- Generated by Data Table -->
        </table>
    </div>
</div>

@include('labours.contract._deliverMaid')

@endsection

@push('scripts')
<script>
$(function () {
    const dateFormat = '{{ dateformat('momentJs') }}';
    route.push('contract.print', '{{ rawRoute('contract.print') }}');
    route.push('installment.create', '{{ rawRoute('contract.installment.create') }}');
    route.push('maidReturn.create', '{{ rawRoute("contract.maidReturn.create") }}');
    route.push('maidReplacement.create', '{{ rawRoute("contract.maidReplacement.create") }}');
    route.push('installment.destroy', '{{ rawRoute("contract.installment.destroy") }}');

    const table = $('#contracts-table').DataTable({
        ajax: ajaxRequest({
            url: '{{ route('api.dataTable.contracts') }}',
            method: 'post',
            processData: false,
            contentType: false,
            data: function (data) {
                return buildURLQuery(data, null, new FormData(document.querySelector('#contracts_filter_form')))
            },
            eject: true,
        }),
        processing: true,
        serverSide: true,
        dom:
            "<'row'<'col-sm-12 justify-content-end'f>>" +
            "<'mw-100'tr>" +
            "<'row'" +
            "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'li>" +
            "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
            ">",
        responsive: true,
        paging: true,
        searchDelay: 500,
        ordering: true,
        order: [[11, 'desc'], [3, 'desc']],
        rowId: 'id',
        columns: [
            {
                data: 'id',
                title: 'ID',
                visible: false,
                orderable: false,
                searchable: false
            },
            {
                data: 'contract_no',
                title: '#',
                responsivePriority: 2,
            },
            {
                data: 'created_at',
                title: 'Created Date',
                responsivePriority: 0,
            },
            {
                data: 'reference',
                title: 'Contract Ref',
                responsivePriority: 0,
            },
            {
                data: 'order_reference',
                title: 'Order #',
                responsivePriority: 0,
            },
            {
                data: 'maid_ref',
                title: 'Maid Code',
                responsivePriority: 0,
            },
            {
                data: 'labour_name',
                title: 'Maid',
                width: '180px',
                className: 'text-wrap',
                responsivePriority: 1,
            },
            {
                data: 'agent_name',
                title: 'Agent',
                className: 'text-wrap',
                responsivePriority: 3,
            },
            {
                data: 'customer_name',
                title: 'Customer',
                className: 'text-wrap',
                responsivePriority: 1,
            },
            {
                data: 'stock_name',
                title: 'Service',
                width: '180px',
                className: 'text-wrap',
                responsivePriority: 1,
            },
            {
                data: 'category',
                title: 'Category',
                responsivePriority: 2,
            },
            {
                data: 'contract_from',
                title: 'Start From',
                render: {
                    display: data => moment(data).format(dateFormat)
                },
                responsivePriority: 0,
            },
            {
                data: 'contract_till',
                title: 'End Date',
                render: {
                    display: data => moment(data).format(dateFormat)
                },
                responsivePriority: 0,
            },
            {
                data: 'amount',
                title: 'Amount',
                responsivePriority: 2,
            },
            {
                data: 'added_tax',
                title: 'Added Tax',
                responsivePriority: 2,
            },
            {
                data: 'order_total',
                title: 'Total',
                responsivePriority: 0,
            },
            {
                data: 'total_payment',
                title: 'Total Payment',
                responsivePriority: 0,
            },
            {
                data: 'balance_payment',
                title: 'Bal. Pay',
                responsivePriority: 0,
            },
            {
                data: 'creator_name',
                title: 'Created By',
                className: 'text-wrap',
                responsivePriority: 0,
            },
            {
                data: 'status',
                title: 'Status',
                responsivePriority: 0,
            },
            {
                data: 'memo',
                title: 'Memo',
                className: 'text-wrap',
                responsivePriority: 3,
            },
            {
                data: 'no_of_invoices',
                title: 'No of Invoices',
                responsivePriority: 3,
            },
            {
                data: 'invoices',
                title: 'Invoices',
                className: 'text-wrap',
                responsivePriority: 3,
            },
            {
                data: 'last_invoiced_till',
                title: 'Last Invoiced At',
                responsivePriority: 3,
                defaultContent: '',
                render: {
                    display: data => {
                        const date = moment(data);
                        if (date.isValid()) {
                            return date.format(dateFormat)
                        }

                        return null;
                    }
                }
            },
            {
                data: 'invoiced_amount',
                title: 'Total Invoiced',
                responsivePriority: 3,
            },
            {
                data: 'processing_fee',
                title: 'Processing Fee',
                responsivePriority: 3,
            },
            {
                data: 'no_of_payments',
                title: 'No of Payments',
                responsivePriority: 3,
            },
            {
                data: 'payments',
                title: 'Payments',
                responsivePriority: 3,
                className: 'text-wrap'
            },
            {
                data: null,
                defaultContent: '',
                title: '',
                width: '20px',
                searchable: false,
                orderable: false,
                responsivePriority: 0,
                render: (data, type, row) => {
                    if (type != 'display') {
                        return null;
                    }

                    const actions = [];

                    actions.push(
                        `<a
                            title="Print"
                            class="dropdown-item"
                            href="${ route('contract.print', {contract: row.id}) }" target="_blank">
                            <span class="fa fa-print w-20px"></span>
                            <span>Print Contract</span>
                        </a>
                        <a
                            title="View"
                            class="dropdown-item"
                            href="${ url("ERP/sales/view/view_sales_order.php", {
                                    trans_no: row.contract_no,
                                    trans_type: row.type
                                }) }" target="_blank">
                            <span class="fa fa-eye w-20px"></span>
                            <span>View Contract</span>
                        </a>`
                    );

                    if(row._isConvertibleToInstallments){
                        actions.push(
                            `<a
                                class="dropdown-item"
                                href="${ route('installment.create', {contract: row.id}) }">
                                <span class="fas fa-money-check-alt w-20px"></span>
                                <span>Convert to Installments</span>
                            </a>`
                        ); 
                    }
                    
                    if (row._isContractInvoicable) {
                        actions.push(
                            `<a
                                class="dropdown-item"
                                href="${ url("ERP/sales/sales_order_entry.php", {
                                    NewInvoice: 0,
                                    ContractID: row.id,
                                    dim_id: row.dimension_id,
                                }) }">
                                <span class="fa fa-file-invoice w-20px"></span>
                                <span>Make Invoice</span>
                            </a>`
                        );
                    }

                    if (row._isContractCreditable) {
                        actions.push(
                            `<a
                                class="dropdown-item"
                                href="${ url("ERP/sales/credit_note_entry.php", {
                                    NewCredit: 'Yes',
                                    ContractID: row.id,
                                    DimensionID: row.dimension_id,
                                }) }">
                                <span class="fa fa-reply w-20px"></span>
                                <span>Issue Sales Return</span>
                            </a>`
                        );
                    }

                    if (row._isContractDeliverable) {
                        actions.push(
                            `<button
                                class="dropdown-item"
                                data-btn="deliverMaid">
                                <span class="fa fa-shipping-fast w-20px"></span>
                                <span>Deliver Maid</span>
                            </button>`
                        )
                    }

                    if (row._isPaymentReceivable) {
                        actions.push(
                            `<a
                                class="dropdown-item"
                                href="${ url("ERP/sales/customer_payments.php", {
                                    customer_id: row.debtor_no,
                                    contract_id: row.id,
                                    dimension_id: row.dimension_id,
                                }) }">
                                <span class="fa fa-dollar-sign w-20px"></span>
                                <span>Receive Payment</span>
                            </a>`
                        )
                    }

                    if(row._isContractMaidReturnable){
                        actions.push(
                            `<a
                                class="dropdown-item"
                                href="${ route('maidReturn.create') }?contract_id=${row.id}">
                                <span class="fa fa-reply w-20px"></span>
                                <span>Maid Return</span>
                            </a>`
                        )
                    }
                    if(row._isContractMaidReplaceable){
                        actions.push(
                            `<a
                                class="dropdown-item"
                                href="${ route('maidReplacement.create') }?contract_id=${row.id}">
                                <span class="fa fa-exchange-alt w-20px"></span>
                                <span>Maid Replacement</span>
                            </a>`
                        )
                    }

                    if(row._isInstallmentDeletable){
                        actions.push(
                            `<button
                                class="dropdown-item"
                                data-action="installmentDelete"> 
                                <span class="fa fa-trash w-20px"></span>
                                <span>Delete Installment</span>
                            </button>`
                        ); 
                    }

                    return (
                        `<div class="dropdown dropdown-menu-sm">
                            <button class="btn dropdown-toggle p-0" type="button" id="actionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="fa fa-ellipsis-v"></span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="actionsDropdown">
                                ${ actions.map(action => ('<li>' + action + '</li>')).join("\n") || '<li class="text-center">--</li>'}
                            </ul>
                        </div>`
                    )
                }
            },
        ]
    })
    
    initializeCustomersSelect2('#debtor_no', {
        except: ['{{ \App\Models\Sales\Customer::WALK_IN_CUSTOMER }}']
    });

    initializeLaboursSelect2('#labour_id');

    $('#contracts_filter_form').on('submit', function (e) {
        e.preventDefault();
        table.ajax.reload();
    })

    LabourContract.addEventListener('DeliverySuccessful', () => {
        table.ajax.reload();
    })

    $('#contracts-table').on('click', '[data-btn="deliverMaid"]', function(e) {
        const row = table.row(e.target.closest('tr')).data();
        LabourContract.initiateMaidDelivery(row);
    });

    $('#contracts-table').on('click', '[data-action="installmentDelete"]', function (event) {
        const contractData = table.row(event.target.closest('tr')).data();
        
        Swal.fire({
            title: 'Are you sure?',
            html: `Your are about to delete the installment against contract. <br>This process cannot be reversed!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (!result.value) {
                return;
            }

            ajaxRequest({
                method: 'post',
                url: route('installment.destroy', {installment: contractData.installment_id}),
                data: {
                    '_method': 'delete'
                }
            }).done(resp => {
                if (resp && resp.message) {
                    Swal.fire(resp.message, '', 'success');
                    table.ajax.reload(null, false);
                    return;
                }

                defaultErrorHandler();
            }).fail(defaultErrorHandler);
        })
    });
});
</script>
@endpush