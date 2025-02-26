@extends('reports.managementReport.base')

@section('report')
    <h1 class="mb-10">Invoice List</h1>
    <form action="" id="filterForm">
        @csrf
        <div class="row g-5">
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="reference">Reference No</label>
                    <input type="text" class="form-control" id="reference" name="reference">
                </div>
            </div>
            
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="trans_date_range">Date</label>
                    <div class="input-group input-daterange" id="trans_date_range">
                        <input
                            type="text"
                            name="voided_from"
                            id="voided_from"
                            data-control="bsDatepicker"
                            value="{{ $inputs['voided_from'] }}"
                            class="form-control">
                        <div class="input-group-text">to</div>
                        <input
                            type="text"
                            name="voided_till"
                            data-control="bsDatepicker"
                            id="voided_till"
                            value="{{ $inputs['voided_till'] }}"
                            class="form-control">
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="voided_by">Voided By</label>
                    <select data-control="select2" name="voided_by" id="voided_by" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($users as $u)
                        <option {{ 
                            class_names(['selected' => $u->id == $inputs['voided_by']])
                        }} value="{{ $u->id }}">{{ $u->user_id }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
                    
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="transaction_type">Type</label>
                    <select name="transaction_type" id="transaction_type" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($transactionTypes as $k => $v)
                        <option {{ 
                            class_names(['selected' => $k == $inputs['transaction_type']])
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
                    data-url="{{ url(route('reports.sales.exportVoidedTransactions')) }}"
                    data-export="xlsx"
                    class="btn btn-primary btn-sm-block mx-2">Excel</button>
                <button
                    type="button"
                    data-url="{{ url(route('reports.sales.exportVoidedTransactions')) }}"
                    data-export="pdf"
                    class="btn btn-primary btn-sm-block mx-2">PDF</button>
            </div>
        </div>
    </form>
    <div class="card mt-5">
        <div class="card-body">
            <div class="w-100 table-responsive" id="dataTableWrapper">
                <table data-control="dataTable"
                    data-url="{{ url(route('api.dataTable.voidedTransactions')) }}"
                    class="table table-striped table-sm table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Voided Date</th>
                            <th>Transaction Date</th>
                            <th>Voided By</th>
                            <th>Transaction By</th>
                            <th>Memo</th>
                            <th>Type</th>
                            <th>Amount</th>
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
            { data: 'reference', name: 'reference' },
            { data: 'voided_date', name: 'voided_date' },
            { data: 'trans_date', name: 'trans_date' },
            { data: 'voided_by', name: 'voided_by' },
            { data: 'transacted_by', name: 'transacted_by' },
            { data: 'memo_', name: 'memo_' },
            { data: 'type', name: 'type' },
            { data: 'amount', name: 'amount' },
        ])
    })
</script>
@endpush