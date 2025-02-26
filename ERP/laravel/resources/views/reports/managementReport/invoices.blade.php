@extends('reports.managementReport.base')

@section('report')
    <h1 class="mb-10">Invoice List</h1>
    <form action="" id="filterForm">
        @csrf
        <div class="row g-5">
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="invoice_no">Invoice No</label>
                    <input type="text" class="form-control" id="invoice_no" name="invoice_no">
                </div>
            </div>

            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="customer">Customer</label>
                    <select id="customer"
                        data-control="select2"
                        data-placeholder="-- All --"
                        name="customer[]"
                        class="form-control"
                        multiple>
                        @foreach ($customers as $c)
                        <option {{ 
                            class_names([
                                'selected' => in_array($c->debtor_no, $inputs['customer'])
                            ])
                        }} value="{{ $c->debtor_no }}">{{ $c->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="trans_date_range">Date</label>
                    <div class="input-group input-daterange" id="trans_date_range">
                        <input
                            type="text"
                            name="tran_date_from"
                            id="tran_date_from"
                            data-control="bsDatepicker"
                            value="{{ $inputs['tran_date_from'] }}"
                            class="form-control">
                        <div class="input-group-text">to</div>
                        <input
                            type="text"
                            name="tran_date_to"
                            data-control="bsDatepicker"
                            id="tran_date_to"
                            value="{{ $inputs['tran_date_to'] }}"
                            class="form-control">
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="employee">Employee</label>
                    <select data-control="select2" name="employee" id="employee" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($users as $u)
                        <option {{ 
                            class_names(['selected' => $u->id == $inputs['employee']])
                        }} value="{{ $u->id }}">{{ $u->user_id }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
                    
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="payment_status">Payment Status</label>
                    <select name="payment_status" id="payment_status" class="form-control">
                        <option value="">-- All --</option>
                        @foreach (payment_statuses() as $k => $v)
                        <option {{ 
                            class_names(['selected' => $k == $inputs['payment_status']])
                        }} value="{{ $k }}">{{ $v }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-12 text-end">
                <button name="submit"
                    type="button"
                    class="btn btn-primary btn-sm-block mx-2">Submit</button>
                <button
                    type="button"
                    data-url="{{ url(route('reports.sales.exportInvoices')) }}"
                    data-export="xlsx"
                    class="btn btn-primary btn-sm-block mx-2">Excel</button>
                <button
                    type="button"
                    data-url="{{ url(route('reports.sales.exportInvoices')) }}"
                    data-export="pdf"
                    class="btn btn-primary btn-sm-block mx-2">PDF</button>
            </div>
        </div>
    </form>
    <div class="card mt-5">
        <div class="card-body">
            <div class="w-100 table-responsive" id="dataTableWrapper">
                <table data-control="dataTable"
                    data-url="{{ url(route('api.dataTable.invoices')) }}"
                    class="table table-striped table-sm table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Display Customer</th>
                            <th>Employee</th>
                            <th>Payment Status</th>
                            <th>Invoice Amount</th>
                            <th>Allocated Amount</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function() {
        ServerSideDataTable([
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'tran_date', name: 'tran_date' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'reference_customer', name: 'reference_customer' },
            { data: 'created_employee', name: 'created_employee' },
            { data: 'payment_status', name: 'payment_status' },
            { data: 'invoice_amount', name: 'invoice_amount' },
            { data: 'alloc', name: 'alloc' },
        ])
    })
</script>
@endpush