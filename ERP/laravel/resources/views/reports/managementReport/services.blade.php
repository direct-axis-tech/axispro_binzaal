@extends('reports.managementReport.base')

@section('report')
    <h1 class="mb-10">Service Report</h1>
    <form action="" id="filterForm">
        @csrf
        <div class="row g-5">

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
                    <label for="bank">Bank Account</label>
                    <select data-control="select2" name="bank" id="bank" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($banks as $b)
                        <option {{ 
                            class_names([
                                'selected' => $b->account_code == $inputs['bank']
                            ])
                        }} value="{{ $b->account_code }}">{{ $b->formatted_name }}
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
                    data-url="{{ url(route('reports.sales.exportServices')) }}"
                    data-export="xlsx"
                    class="btn btn-primary btn-sm-block mx-2">Excel</button>
                <button
                    type="button"
                    data-url="{{ url(route('reports.sales.exportServices')) }}"
                    data-export="pdf"
                    class="btn btn-primary btn-sm-block mx-2">PDF</button>
            </div>
        </div>
    </form>
    <div class="card mt-5">
        <div class="card-body">
            <div class="w-100 table-responsive" id="dataTableWrapper">
                <table data-control="dataTable"
                    data-url="{{ url(route('api.dataTable.services')) }}"
                    class="table table-striped table-sm table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Stock Id</th>
                            <th>Service Name</th>
                            <th>Service Name (Arabic)</th>
                            <th>Category</th>
                            <th>Govt Bank</th>
                            <th>Service Charge</th>
                            <th>Govt. Fee</th>
                            <th>PF. Amount</th>
                            <th>Bank Charge</th>
                            <th>VAT(Bank Charge)</th>
                            <th>Employee(Local) Commission</th>
                            <th>Employee(Non-Local) Commission</th>
                            <th>Recievable Benefits Acc</th>
                            <th>Recievable Benefits Amt</th>
                            <th>Split Govt. Fee Acc</th>
                            <th>Split Govt. Fee Amt</th>
                            <th>Extra Service Chg</th>
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
            { data: 'stock_id', name: 'stock_id' },
            { data: 'description', name: 'description' },
            { data: 'long_description', name: 'long_description' },
            { data: 'category_name', name: 'category_name' },
            { data: 'govt_bank_account', name: 'govt_bank_account' },
            { data: 'service_charge', name: 'service_charge' },
            { data: 'govt_fee', name: 'govt_fee' },
            { data: 'pf_amount', name: 'pf_amount' },
            { data: 'bank_service_charge', name: 'bank_service_charge' },
            { data: 'bank_service_charge_vat', name: 'bank_service_charge_vat' },
            { data: 'commission_loc_user', name: 'commission_loc_user' },
            { data: 'commission_non_loc_user', name: 'commission_non_loc_user' },
            { data: 'returnable_to', name: 'returnable_to' },
            { data: 'returnable_amt', name: 'returnable_amt' },
            { data: 'split_govt_fee_acc', name: 'split_govt_fee_acc' },
            { data: 'split_govt_fee_amt', name: 'split_govt_fee_amt' },
            { data: 'extra_srv_chg', name: 'extra_srv_chg' },
        ])
    })
</script>
@endpush