@extends('reports.managementReport.base')

@section('report')
    <h1 class="mb-10">Service Report</h1>
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
                    <label for="category">Category</label>
                    <select data-control="select2" name="category" id="category" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($categories as $c)
                        <option {{ 
                            class_names([
                                'selected' => $c->category_id == $inputs['category']
                            ])
                        }} value="{{ $c->category_id }}">{{ $c->description }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="invoice_type">Card Type</label>
                    <select name="invoice_type" id="invoice_type" class="form-control">
                        <option value="">-- All --</option>
                        @foreach (card_types() as $k => $v)
                        <option {{ 
                            class_names(['selected' => $k == $inputs['invoice_type']])
                        }} value="{{ $k }}">{{ $v }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="transaction_status">Transaction Status</label>
                    <select name="transaction_status" id="transaction_status" class="form-control">
                        <option value="">-- All --</option>
                        @foreach (transaction_statuses() as $k => $v)
                        <option {{ 
                            class_names(['selected' => $k == $inputs['transaction_status']])
                        }} value="{{ $k }}">{{ $v }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="sales_man_id">Sales Man</label>
                    <select data-control="select2" name="sales_man_id" id="sales_man_id" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($salesMen as $s)
                        <option {{ 
                            class_names(['selected' => $s->salesman_code == $inputs['sales_man_id']])
                        }} value="{{ $s->salesman_code }}">{{ $s->salesman_name }}
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
                    <label for="ref_name">Ref. Name</label>
                    <input
                        type="text"
                        name="ref_name"
                        id="ref_name"
                        value="{{ $inputs['ref_name'] }}"
                        class="form-control">
                </div>
            </div>
            
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="transaction_id">Transaction ID</label>
                    <input
                        type="text"
                        name="transaction_id"
                        id="transaction_id"
                        value="{{ $inputs['transaction_id'] }}"
                        class="form-control">
                </div>
            </div>

            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="service">Service</label>
                    <select data-control="select2" name="service" id="service" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($stockItems as $s)
                        <option {{ 
                            class_names(['selected' => $s->stock_id == $inputs['service']])
                        }} value="{{ $s->stock_id }}">{{ $s->description }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
                    
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="display_customer">Display Customer</label>
                    <input
                        type="text"
                        name="display_customer"
                        id="display_customer"
                        value="{{ $inputs['display_customer'] }}"
                        class="form-control">
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
                    data-url="{{ url(route('reports.sales.exportServiceTransactions')) }}"
                    data-export="xlsx"
                    class="btn btn-primary btn-sm-block mx-2">Excel</button>
                <button
                    type="button"
                    data-url="{{ url(route('reports.sales.exportServiceTransactions')) }}"
                    data-export="pdf"
                    class="btn btn-primary btn-sm-block mx-2">PDF</button>
            </div>
        </div>
    </form>
    <div class="card mt-5">
        <div class="card-body">
            <div class="w-100 table-responsive" id="dataTableWrapper">
                <table data-control="dataTable"
                    data-url="{{ url(route('api.dataTable.serviceTransactions')) }}"
                    class="table table-striped table-sm table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Display Customer</th>
                            <th>Sales Man</th>
                            <th>Service</th>
                            <th>Category</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Total Service Charge</th>
                            <th>Unit Tax</th>
                            <th>Total Tax</th>
                            <th>Discount Amount</th>
                            <th>Govt. Fee</th>
                            <th>Total Govt. Fee</th>
                            <th>Bank Charge</th>
                            <th>VAT(Bank Charge)</th>
                            <th>PF.Amount</th>
                            <th>Customer Commission</th>
                            <th>Salesman Commission</th>
                            <th>Employee Commission</th>
                            <th>Transaction ID</th>
                            <th>Ref.Name</th>
                            <th>Employee</th>
                            <th>Payment Status</th>
                            <th>Card Type</th>
                            <th>Net Service Amount</th>
                            <th>Invoice Amount</th>
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
            { data: 'salesman_name', name: 'salesman_name' },
            { data: 'service_eng_name', name: 'service_eng_name' },
            { data: 'category_name', name: 'category_name' },
            { data: 'unit_price', name: 'unit_price' },
            { data: 'quantity', name: 'quantity' },
            { data: 'total_price', name: 'total_price' },
            { data: 'unit_tax', name: 'unit_tax' },
            { data: 'total_tax', name: 'total_tax' },
            { data: 'discount_amount', name: 'discount_amount' },
            { data: 'govt_fee', name: 'govt_fee' },
            { data: 'total_govt_fee', name: 'total_govt_fee' },
            { data: 'bank_service_charge', name: 'bank_service_charge' },
            { data: 'bank_service_charge_vat', name: 'bank_service_charge_vat' },
            { data: 'pf_amount', name: 'pf_amount' },
            { data: 'customer_commission', name: 'customer_commission' },
            { data: 'customer_commission2', name: 'customer_commission2' },
            { data: 'user_commission', name: 'user_commission' },
            { data: 'transaction_id', name: 'transaction_id' },
            { data: 'ref_name', name: 'ref_name' },
            { data: 'created_employee', name: 'created_employee' },
            { data: 'payment_status', name: 'payment_status' },
            { data: 'invoice_type', name: 'invoice_type' },
            { data: 'net_service_charge', name: 'net_service_charge' },
            { data: 'invoice_amount', name: 'invoice_amount' },
        ])
    })
</script>
@endpush