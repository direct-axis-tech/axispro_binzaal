@extends('reports.managementReport.base')

@section('report')
    <h1 class="mb-10">Invoice Payment Report</h1>
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
                    <label for="receipt_no">Receipt No</label>
                    <input type="text" class="form-control" id="receipt_no" name="receipt_no">
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
                    <label for="bank">Collected Bank</label>
                    <select data-control="select2" name="bank" id="bank" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($banks as $b)
                        <option {{ 
                            class_names([
                                'selected' => $b->id == $inputs['bank']
                            ])
                        }} value="{{ $b->id }}">{{ $b->formatted_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-control">
                        <option value="">-- All --</option>
                        @foreach (payment_methods() as $k => $v)
                        <option {{ 
                            class_names(['selected' => $k == $inputs['payment_method']])
                        }} value="{{ $k }}">{{ $v }}
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
                            data-date-clear-btn="true"
                            data-date-keep-empty-values="true"
                            data-date-today-btn="true"
                            data-date-today-highlight="true"
                            value="{{ $inputs['tran_date_from'] }}"
                            class="form-control">
                        <div class="input-group-text">to</div>
                        <input
                            type="text"
                            name="tran_date_to"
                            data-control="bsDatepicker"
                            data-date-clear-btn="true"
                            data-date-keep-empty-values="true"
                            data-date-today-btn="true"
                            data-date-today-highlight="true"
                            id="tran_date_to"
                            value="{{ $inputs['tran_date_to'] }}"
                            class="form-control">
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="invoice_date_range">Invoice Date</label>
                    <div class="input-group input-daterange" id="invoice_date_range">
                        <input
                            type="text"
                            name="invoice_date_from"
                            id="invoice_date_from"
                            data-control="bsDatepicker"
                            data-date-clear-btn="true"
                            data-date-keep-empty-values="true"
                            data-date-today-highlight="true"
                            data-date-today-btn="true"
                            value="{{ $inputs['invoice_date_from'] }}"
                            class="form-control">
                        <div class="input-group-text">to</div>
                        <input
                            type="text"
                            name="invoice_date_to"
                            data-control="bsDatepicker"
                            data-date-clear-btn="true"
                            data-date-keep-empty-values="true"
                            data-date-today-btn="true"
                            data-date-today-highlight="true"
                            id="invoice_date_to"
                            value="{{ $inputs['invoice_date_to'] }}"
                            class="form-control">
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="user">User</label>
                    <select data-control="select2" name="user" id="user" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($users as $u)
                        <option {{ 
                            class_names(['selected' => $u->id == $inputs['user']])
                        }} value="{{ $u->id }}">{{ $u->user_id }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
           
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="payment_invoice_date_relationship">Payment & Invoice Date Relationship</label>
                    <select
                        data-control="select2"
                        name="payment_invoice_date_relationship"
                        id="payment_invoice_date_relationship"
                        class="form-control">
                        <option value="">-- All --</option>
                        @foreach ([
                            'payment_before_or_after_invoice' => 'Different days',
                            'payment_after_invoice' => 'After Invoice Date',
                            'payment_before_invoice' => 'Before Invoice Date',
                            'payment_on_invoice_date' => 'Same day',
                        ] as $k => $v)
                        <option {{ 
                            class_names(['selected' => $k == $inputs['payment_invoice_date_relationship']])
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
                    data-url="{{ url(route('reports.sales.exportInvoicesPayments')) }}"
                    data-export="xlsx"
                    class="btn btn-primary btn-sm-block mx-2">Excel</button>
                <button
                    type="button"
                    data-url="{{ url(route('reports.sales.exportInvoicesPayments')) }}"
                    data-export="pdf"
                    class="btn btn-primary btn-sm-block mx-2">PDF</button>
            </div>
        </div>
    </form>
    <div class="card mt-5">
        <div class="card-body">
            <div class="w-100 table-responsive" id="dataTableWrapper">
                <table data-control="dataTable"
                    data-url="{{ url(route('api.dataTable.invoicesPayments')) }}"
                    class="table table-striped table-sm table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Receipt No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Invoice</th>
                            <th>Inv Date</th>
                            <th>Collected Bank</th>
                            <th>User</th>
                            <th>Payment Method</th>
                            <th>Invoice Amt</th>
                            <th>Alloc Amt</th>
                            <th>Payment Amt</th>
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
            { data: 'receipt_no', name: 'receipt_no' },
            { data: 'tran_date', name: 'tran_date' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'invoice_number', name: 'invoice_number' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'bank_account_name', name: 'bank_account_name' },
            { data: 'user_id', name: 'user_id' },
            { data: 'payment_method', name: 'payment_method' },
            { data: 'invoice_amt', name: 'invoice_amt' },
            { data: 'alloc_amt', name: 'alloc_amt' },
            { data: 'payment_amt', name: 'payment_amt' },
        ])
    })
</script>
@endpush