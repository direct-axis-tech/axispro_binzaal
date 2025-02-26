@extends('layout.app')

@section('title', 'Sales Order - Line Details')

@push('styles')
    <style>
        .details-table th, .details-table td {
            padding-left: 0.75rem;
            padding-right: 1.5rem !important;
        }

        .details-table th {
            height: initial !important;
            vertical-align: bottom;
        }

        .details-table td {
            height: initial !important;
            vertical-align: middle;
        }

        .details-table tr {
            position: relative;
        }

        .details-table thead th {
            background: #fff;,
            color: #181C32;
        }

        .details-table tbody tr:nth-child(odd) td {
            background-color: var(--bs-table-striped-bg);
            color: var(--bs-table-striped-color);
        }

        .details-table tbody tr:nth-child(even) td {
            background: #fff;
        }
        
        .details-table thead th:last-child,
        .details-table tbody td:last-child {
            position: sticky;
            z-index: 1;
            right: -1px;
        }
    </style>
@endpush

@section('page')

<div class="container-fluid">
    <h1 class="mb-10">Job Order - Transactions</h1>
    <form action="{{ route('sales.orders.details.index') }}" method="get" id="filter_form" enctype="multipart/form-data">               
        <div class="row">
            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="line_reference">Line #</label>
                <input class="form-control" type="text" name="line_reference" id="line_reference" value="{{ $inputs['line_reference'] }}">
            </div>

            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="order_reference">Order #</label>
                <input class="form-control" type="text" name="order_reference" id="order_reference" value="{{ $inputs['order_reference'] }}">
            </div>

            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="debtor_no">Customer</label>
                <select class="form-select" name="debtor_no" id="debtor_no">
                    <option value="">-- select --</option>
                    @if ($inputs['debtor_no'] && ($customer = \App\Models\Sales\Customer::find($inputs['debtor_no'])))
                    <option value="{{ $customer->debtor_no }}" selected>{{ $customer->formatted_name }}</option>
                    @endif
                </select>
            </div>
            
            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="salesman_id">Salesman</label>
                <select class="form-select" name="salesman_id" id="salesman_id">
                    <option value="">-- select --</option>
                    @foreach ($salesMans as $key => $value)
                    <option value="{{ $key }}" @if ($key == $inputs['salesman_id']) selected @endif>
                        {{ $value }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="assignee_id">Assignee</label>
                <select class="form-select" name="assignee_id" id="assignee_id">
                    <option value="">-- select --</option>
                    @foreach ($assignees as $key => $value)
                    <option value="{{ $key }}" @if ($key == $inputs['assignee_id']) selected @endif>
                        {{ $value }}
                    </option>
                    @endforeach
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
                <label for="stock_id">Item</label>
                <select class="form-select" name="stock_id" id="stock_id" data-control="select2">
                    <option value="">-- select --</option>
                    @foreach ($stockItems as $key => $value)
                        <option value="{{ $key }}" @if ($key == $inputs['stock_id']) selected @endif>{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="transaction_status">Status</label>
                <select class="form-select" name="transaction_status" id="transaction_status" data-control="select2">
                    <option value="">-- select --</option>
                    @foreach ($statuses as $key)
                    <option value="{{ $key }}" @if ($key == $inputs['transaction_status']) selected @endif>{{ $key }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="invoice_status">Inv Status</label>
                <select class="form-select" name="invoice_status" id="invoice_status" data-control="select2">
                    <option value="">-- select --</option>
                    @foreach ($invStatuses as $key)
                    <option value="{{ $key }}" @if ($key == $inputs['invoice_status']) selected @endif>{{ $key }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="order_date_range"><?= trans('Order Date') ?>:</label>
                <div 
                    id="order_date_range"
                    class="input-group input-daterange"
                    data-control='bsDatepicker'
                    data-date-keep-empty-values="true"
                    data-date-clear-btn="true">
                    <input
                        type="text" 
                        name="order_date_from" 
                        id="order_date_from"
                        class="form-control"
                        autocomplete="off"
                        placeholder="<?= (new \DateTime('2024-01-01'))->format(dateformat()) ?>"
                        value="<?= $inputs['order_date_from'] ?>">
                    <div class="input-group-text input-group-addon px-4 rounded-0 border-left-0 border-right-0">to</div>
                    <input
                        type="text" 
                        name="order_date_till" 
                        id="order_date_till"
                        class="form-control"
                        autocomplete="off"
                        value="<?= $inputs['order_date_till'] ?>"
                        placeholder="<?= (new \DateTime('2024-01-01'))->format(dateformat()) ?>">
                </div>
            </div>

            <div class="form-group col-md-6 col-lg-3 col-xl-2 mt-5">
                <label for="line_narration">Narration</label>
                <input class="form-control" type="text" name="line_narration" id="line_narration" value="{{ $inputs['line_narration'] }}">
            </div>

            <div class="col-12 text-end">
                <button name="submit" type="submit" class="btn btn-primary btn-sm-block mx-2">Submit</button>
            </div>
        </div>
    </form>

    <div class="mw-100 p-2 bg-white rounded mt-2">
        <table id="order-details-table" class="table details-table table-striped table-bordered table-hover gx-3 w-100 text-nowrap thead-strong">
            <!-- Generated by Data Table -->
        </table>
    </div>
</div>

@include('sales.order.details._markCompleted');
@include('sales.order.details._addExpense');
@include('sales.order.details._showExpenses');

@endsection

@push('scripts')
<script>
window.AuthUser = {
    canViewGL: {{ intval(authUser()->hasPermission('SA_GLTRANSVIEW')) }}
}
$(function () {
    const dateFormat = '{{ dateformat('momentJs') }}';
    const numberFormatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: {{ user_price_dec() }},
        maximumFractionDigits: {{ user_price_dec() }},
    });

    route.push('salesOrder.view', '/ERP/sales/view/view_sales_order.php?trans_no={trans_no}&trans_type={type}');

    route.push('invoice.view', '/ERP/sales/view/view_invoice.php?trans_no={trans_no}&trans_type={type}');
    route.push('suppInvoice.view', '/ERP/purchasing/view/view_supp_invoice.php?trans_no={trans_no}');
    route.push('glTrans.view', '/ERP/gl/view/gl_trans_view.php?type_id={type}&trans_no={trans_no}');

    const table = $('#order-details-table').DataTable({
        ajax: ajaxRequest({
            url: '{{ route('api.dataTable.salesOrderDetails') }}',
            method: 'post',
            processData: false,
            contentType: false,
            data: function (data) {
                return buildURLQuery(data, null, new FormData(document.querySelector('#filter_form')))
            },
            eject: true,
        }),
        processing: true,
        serverSide: true,
        buttons: ['excel'],
        dom:
            // "<'row'<'col-auto'Q>>" +
            "<'row'<'col-sm-6 justify-content-end'B><'col-sm-6 justify-content-end'f>>" +
            "<'table-responsive't>" +
            "<r>" +
            "<'row'" +
            "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'li>" +
            "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
            ">",
        paging: true,
        searchDelay: 500,
        ordering: true,
        order: [[1, 'desc'], [0, 'desc']],
        rowId: 'line_reference',
        columns: [
            {
                data: 'line_reference',
                title: 'Ref',
                responsivePriority: 2,
            },
            {
                data: 'order_date',
                title: 'Order Date',
                searchable: false,
                render: {
                    display: data => moment(data).format(dateFormat)
                },
                responsivePriority: 0,
            },
            {
                data: 'order_reference',
                title: 'Order #',
                render: {
                    display: (data, type, row) => {
                        return `<button type="button" data-href="${route('salesOrder.view', {trans_no: row.order_no, type: row.trans_type})}" class="btn btn-sm p-0 text-info">${data}</button>`
                    }
                },
                responsivePriority: 0,
            },
            {
                defaultContent: '',
                title: 'Supplier #',
                render: {
                    display: (data, type, row) => {
                        let links = row.supp_references.map(inv => {
                            return `<button type="button" data-href="${route('suppInvoice.view', {trans_no: inv.trans_no})}" class="btn btn-sm p-0 text-primary">${inv.reference}</button>`
                        });
                        
                        return links.join('<br>');
                    }
                },
                responsivePriority: 0,
            },
            {
                data: 'formatted_assignee_name',
                title: 'Assignee',
                className: 'text-wrap min-w-175px w-175px',
                responsivePriority: 1,
            },
            {
                data: 'formatted_stock_name',
                title: 'Service',
                className: 'text-wrap min-w-175px w-175px',
                responsivePriority: 1,
            },
            {
                data: 'formatted_customer_name',
                title: 'Customer',
                className: 'text-wrap min-w-175px w-175px',
                responsivePriority: 1,
            },
            {
                data: 'salesman_name',
                title: 'Salesman',
                className: 'text-wrap min-w-175px w-175px',
                responsivePriority: 1,
            },
            {
                data: 'category_name',
                title: 'Category',
                className: 'text-wrap min-w-175px w-175px',
                responsivePriority: 2,
            },
            {
                data: 'line_narration',
                title: 'Narration',
                responsivePriority: 2,
                className: 'text-wrap min-w-175px w-175px',
            },
            {
                data: 'total',
                title: 'Total',
                class: 'text-end',
                render: {
                    display: data => numberFormatter.format(data)
                },
                responsivePriority: 0,
            },
            {
                data: 'total_expense',
                title: 'Expense',
                class: 'text-end',
                render: {
                    display: data => numberFormatter.format(data)
                },
                responsivePriority: 0,
            },
            {
                data: 'profit',
                title: 'Profit',
                class: 'text-end',
                render: {
                    display: data => {
                        let profit = parseFloat(data) || 0;
                        return `<span class="text-${profit > 0 ? 'primary' : 'danger'}">${numberFormatter.format(profit)}</span>`
                    }
                },
                responsivePriority: 0,
            },
            {
                data: 'status',
                title: 'Status',
                render: {
                    display: data => {
                        let classNames = {
                            'Completed': 'text-success',
                            'Work in Progress': 'text-warning',
                            'Pending': 'text-danger',
                            'Partially Completed': 'text-accent'
                        }

                        return `<span class="${classNames[data] || 'text-muted'} fw-bolder">${data}</span>`
                    }
                },
                responsivePriority: 0,
            },
            {
                data: 'inv_status',
                title: 'Inv Status',
                render: {
                    display: (data, type, row) => {
                        let classNames = {
                            'Invoiced': 'text-success',
                            'Not Invoiced': 'text-danger',
                        }

                        let className = classNames[data] || 'text-muted';
                        let links = row.inv_references.map(inv => {
                            return `<button type="button" data-href="${route('invoice.view', {type: inv.type, trans_no: inv.trans_no})}" class="btn btn-sm p-0 ${className}">${inv.reference}</button>`
                        });
                        let text = data == 'Not Invoiced' ? data : links.join('<br>')

                        return `<span class="${className}" fw-bolder">${text}</span>`
                    }
                },
                responsivePriority: 0,
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

                    if (row._canSeeExpenses) {
                        actions.push(
                            `<button
                                type="Button"
                                data-action="viewExpense"
                                title="View the expenses against this transaction"
                                class="btn btn-text-primary px-2 py-0 fs-3">
                                <span class="fa fa-chart-line"></span>
                            </button>`
                        );
                    }

                    if (row._isCompletable) {
                        actions.push(
                            `<button
                                type="Button"
                                data-action="complete"
                                title="Mark transaction as completed"
                                class="btn btn-text-primary px-2 py-0 fs-3">
                                <span class="fa fa-edit"></span>
                            </button>`
                        );
                    }
                    
                    if (row._isExpenseAddable) {
                        actions.push(
                            `<button
                                type="Button"
                                data-action="addExpense"
                                title="Add Expense against transaction"
                                class="btn btn-text-info px-2 py-0 fs-3">
                                <span class="fa fa-dollar-sign"></span>
                            </button>`
                        );
                    }

                    return actions.join("\n");
                }
            },
        ]
    })

    $('#order-details-table').on('click', '[data-action="viewExpense"]', function (ev) {
        let transaction = table.row(ev.target.closest('tr')).data()
        window.Transaction.viewExpenses(transaction);
    })
    
    $('#order-details-table').on('click', '[data-action="addExpense"]', function (ev) {
        let transaction = table.row(ev.target.closest('tr')).data()
        window.Transaction.initiateExpenseAdditionProcess(transaction);
    })
    
    $('#order-details-table').on('click', '[data-action="complete"]', function (ev) {
        let transaction = table.row(ev.target.closest('tr')).data()
        window.Transaction.initiateCompletionProcess(transaction);
    })

    $(document).on('click', 'button[data-href]', function () {
        createPopup(this.dataset.href);
    })
    
    initializeCustomersSelect2('#debtor_no', {
        except: ['{{ \App\Models\Sales\Customer::WALK_IN_CUSTOMER }}']
    });


    $('#filter_form').on('submit', function (e) {
        e.preventDefault();
        table.ajax.reload();
    })

    // Expose the table
    window.OrderDetailsDataTable = table;
});
</script>
@endpush