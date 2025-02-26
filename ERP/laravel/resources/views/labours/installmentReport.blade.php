@extends('layout.app')

@section('title', 'Installment Report')

@section('page')
<div class="container-fluid">
    <h1 class="my-10">Upcoming Installments</h1>

    <form action="" id="filterForm">
        @csrf
        <div class="row g-5">
           
            <div class="col-3">
                <div class="form-group">
                    <label for="shift_date_range">Date</label>
                    <div class="input-group input-daterange" id="movement_date_range">
                        <input
                            type="text"
                            name="from"
                            id="from"
                            data-control="bsDatepicker"
                            value="{{ $defaultFilters['from'] }}"
                            class="form-control">
                        <div class="input-group-text">to</div>
                        <input
                            type="text"
                            name="till"
                            data-control="bsDatepicker"
                            id="till"
                            value="{{ $defaultFilters['till'] }}"
                            class="form-control">
                    </div>
                </div>
            </div>

            <div class="col-3">
                <div class="form-group">
                    <label for="type">Customer</label>
                    <select name="debtor_no" id="debtor_no" class="form-select" data-control="select2">
                        <option value="">-- select --</option>
                        @foreach ($customers as $cust)
                        <option value="{{ $cust->debtor_no }}" >{{ $cust->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        

            <div class="col-12 text-end">
                <button
                    id="submitBtn"
                    type="button"
                    class="btn btn-primary btn-sm-block mx-2">
                    Submit
                </button>

                <button
                    type="button"
                    data-url="{{ url(route('labour.reports.installmentReport.export')) }}"
                    data-export="xlsx"
                    class="btn btn-primary btn-sm-block mx-2">
                    Excel
                </button>

                <button
                    type="button"
                    data-url="{{ url(route('labour.reports.installmentReport.export')) }}"
                    data-export="pdf"
                    class="btn btn-primary btn-sm-block mx-2">
                    PDF
                </button>
            </div>
        </div>
    </form>

    <div class="card mt-5">
        <div class="card-body">
            <div class="w-100" id="dataTableWrapper">
                <table
                    id="installmentReportTable"
                    data-control="dataTable"
                    data-url="{{ url(route('api.dataTable.installmentReport')) }}"
                    class="table table-striped table-row-bordered g-3 text-nowrap thead-strong mh-500px">
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $(function() {
        const form = document.querySelector('#filterForm');
        let formData = new FormData(form);
        const tableSelector = '#installmentReportTable';
        const $table = $(tableSelector);
        
        // When form is submitted, regenerate the report
        $('#submitBtn').on('click', () => {
            $table.DataTable().ajax.reload();
        });

        // Handle the export button click
        $('button[data-export]').on('click', e => {
            const btn = e.target;
            const formData = new FormData(form);
            formData.append('to', btn.dataset.export);
            ajaxRequest({
                url: btn.dataset.url,
                contentType: false,
                processData: false,
                method: 'post',
                data: formData
            }).done((resp, msg, xhr) => {
                if (!resp.redirect_to) {
                    return defaultErrorHandler(xhr);
                }

                window.location = resp.redirect_to;
            }).fail(defaultErrorHandler);
        })

        $table.DataTable({
            processing: true,
            serverSide: true,
            order: [[ 3, 'asc' ]],
            ajax: ajaxRequest({
                url: $table[0].dataset.url,
                method: 'post',
                processData: false,
                contentType: false,
                eject: true,
                data: function (data) {
                    const formData = new FormData(form);
                    return buildURLQuery(data, null, formData);
                },
            }),
            columns: [
                {
                    data: 'contract_ref',
                    title: 'Contract Ref',
                },
                {
                    data: 'trans_date',
                    title: 'Entry Date',
                },
                {
                    data: 'customer_name',
                    title: 'Customer',
                },
                {
                    data: 'due_date',
                    title: 'Due Date',
                },
                {
                    data: 'payee_name',
                    title: 'Payee Name',
                    className: 'text-center'
                },
                {
                    data: 'bank_name',
                    title: 'Bank',
                },
                {
                    data: 'cheque_no',
                    title: 'Cheque No',
                },
                {
                    data: 'amount',
                    title: 'Amount',
                },
                {
                    data: 'due_date_difference',
                    title: 'Due Date Difference',
                },
                {
                    data: 'invoice_ref',
                    title: 'Invoice Ref',
                },
                {
                    data: null,
                    defaultContent: '',
                    title: '',
                    width: '20px',
                    searchable: false,
                    orderable: false,
                    render: (data, type, row) => { 
                        var dueDate = new Date(moment(row.due_date).toDate());
                        var currentDate = new Date();
                        let actions = '<ul class="list-inline m-0">';
                        if (dueDate < currentDate & row.invoice_ref == null) {
                            actions += `<li class="list-inline-item">
                                <a class="btn btn-sm py-1 px-3 btn-primary align-middle" href="${ url("ERP/sales/sales_order_entry.php", {
                                    NewInvoice: 0,
                                    ContractID: row.contract_id,
                                    InstallmentDetailId: row.id,
                                    CheckNo: row.cheque_no,
                                    TransDate: row.due_date,
                                    PeriodFrom: row.contract_from,
                                    PeriodTill: row.contract_till,
                                    TransAmount: row.amount,
                                    dim_id: row.dimension_id
                                }) }">Make Invoice</a>
                                </li>`;
                        }
                        actions += '</ul>';
                        return actions;
                    }
                },
                
            ]
        });
    })
</script>
@endpush