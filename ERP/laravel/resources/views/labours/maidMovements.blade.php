@extends('layout.app')

@section('title', 'Maid Movement Report')

@section('page')
<div class="container-fluid">
    <h1 class="my-10">Maid Movements</h1>

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
                    <label for="type">Maid</label>
                    <select name="maid_id" id="maid_id" class="form-select" data-control="select2">
                        <option value="">-- select --</option>
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
                    data-url="{{ url(route('labour.reports.maidMovements.export')) }}"
                    data-export="xlsx"
                    class="btn btn-primary btn-sm-block mx-2">
                    Excel
                </button>

                <button
                    type="button"
                    data-url="{{ url(route('labour.reports.maidMovements.export')) }}"
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
                    id="maidmovementTable"
                    data-control="dataTable"
                    data-url="{{ url(route('api.dataTable.maidMovements')) }}"
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
        initializeLaboursSelect2('#maid_id');
        const dateFormat = '{{ dateformat('momentJs') }}';
        const form = document.querySelector('#filterForm');
        let formData = new FormData(form);
        const tableSelector = '#maidmovementTable';
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
            order: [[ 0, 'desc' ]],
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
                    data: 'trans_id',
                    title: '#',
                    visible: false,
                },
                {
                    data: 'type_name',
                    title: 'Transaction Type',
                },
                {
                    data: 'reference',
                    title: 'Reference',
                    className: 'text-center'
                },
                {
                    data: 'contract_ref',
                    title: 'Contract Ref',
                },
                {
                    data: 'tran_date',
                    title: 'Transaction Date',
                    render: {
                        display: data => moment(data).format(dateFormat)
                    },
                    responsivePriority: 0
                },
                {
                    data: 'maid_name',
                    title: 'Maid',
                },
                {
                    data: 'counter_party_name',
                    title: 'Customer/Supplier',
                },
                {
                    data: 'status',
                    title: 'Status',
                }
            ]
        });
    })
</script>
@endpush