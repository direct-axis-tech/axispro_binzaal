@extends('hr.employees.profile.base')

@section('title', 'Profile - Punchings')

@section('slot')
    <div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
        <!--begin::Card body-->
        <div class="card-body p-9">
            <form action="" method="POST" id="filter-form">
                <div class="row flex-row-reverse">
                    <div class="col-auto text-end">
                        <button id="submitBtn" type="button" class="btn btn-primary btn-sm-block mx-2">Submit</button>
                    </div>
    
                    <div class="form-group col-auto">
                        <div 
                            id="punch_from_range"
                            class="input-group input-daterange"
                            data-control='bsDatepicker'
                            data-date-keep-empty-values="true"
                            data-date-clear-btn="true">
                            <input
                                type="text" 
                                name="punch_from" 
                                id="punch_from"
                                class="form-control"
                                autocomplete="off"
                                value="{{ $inputs['punch_from'] }}"
                                placeholder="Attendance From">
                            <div class="input-group-text input-group-addon px-4 rounded-0 border-left-0 border-right-0">to</div>
                            <input
                                type="text" 
                                name="punch_till" 
                                id="punch_till"
                                class="form-control"
                                autocomplete="off"
                                value="{{ $inputs['punch_till'] }}"
                                placeholder="Attendance Till">
                        </div>
                    </div>
                </div>
            </form>
            <hr class="my-10">
            <table class="table table-striped text-nowrap" id="punchings-table">
                <thead>
                    <tr>
                        <th class="fw-bold text-muted">{{ 'Name in Machine' }}</th>
                        <th class="fw-bold text-muted text-center">{{ 'Date' }}</th>
                        <th class="fw-bold text-muted text-center">{{ 'Time' }}</th>
                        <th class="fw-bold text-muted">{{ 'Status in Machine' }}</th>
                    </tr>
                </thead>
            </table>
        </div>
        <!--end::Card body-->
    </div>
@endsection


@push('scripts')
    <script>
        $(function () {
            const form = document.getElementById('filter-form');
            const dateFormat = '{{ dateformat('momentJs') }}';
            const dataTable = $('#punchings-table').DataTable({
                ajax: ajaxRequest({
                    url: form.action,
                    method: 'post',
                    processData: false,
                    contentType: false,
                    data: function (data) {
                        return buildURLQuery(data, null, new FormData(form))
                    },
                    eject: true,
                }),
                processing: true,
                serverSide: true,
                paging: false,
                ordering: true,
                order: [[1, 'desc'], [2, 'desc']],
                rowId: 'custom_id',
                columns: [
                    {
                        data: 'person',
                        width: '300px',
                        class: 'mw-300px'
                    },
                    {
                        data: 'authdate',
                        class: 'text-center',
                        render: {
                            display: data => moment(data).format(dateFormat)
                        },
                    },
                    {
                        data: 'authtime',
                        class: 'text-center',
                        render: {
                            display: (data, type, row) => moment(row.authdatetime).format('hh:mm a')
                        },
                    },
                    {data: 'status'}
                ]
            })

            $('#submitBtn').on('click', () => {
                dataTable.ajax.reload();
            })
        });
    </script>
@endpush