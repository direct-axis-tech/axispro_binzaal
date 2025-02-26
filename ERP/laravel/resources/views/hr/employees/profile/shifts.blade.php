@extends('hr.employees.profile.base')

@section('title', 'Profile - Shifts')

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
                            id="shift_from_range"
                            class="input-group input-daterange"
                            data-control='bsDatepicker'
                            data-date-keep-empty-values="true"
                            data-date-clear-btn="true">
                            <input
                                type="text" 
                                name="shift_from" 
                                id="shift_from"
                                class="form-control"
                                autocomplete="off"
                                value="{{ $inputs['shift_from'] }}"
                                placeholder="Shift From">
                            <div class="input-group-text input-group-addon px-4 rounded-0 border-left-0 border-right-0">to</div>
                            <input
                                type="text" 
                                name="shift_till" 
                                id="shift_till"
                                class="form-control"
                                autocomplete="off"
                                value="{{ $inputs['shift_till'] }}"
                                placeholder="Shift Till">
                        </div>
                    </div>
                </div>
            </form>
            <hr class="my-10">
            <table class="table table-striped text-center" id="shifts-table">
                <thead>
                    <tr>
                        <th class="fw-bold text-muted">{{ 'Date' }}</th>
                        <th class="fw-bold text-muted">{{ 'Code' }}</th>
                        <th class="fw-bold text-muted">{{ 'Timing' }}</th>
                        <th class="fw-bold text-muted">{{ 'Assignor' }}</th>
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
            const dataTable = $('#shifts-table').DataTable({
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
                searching: false,
                ordering: true,
                rowId: 'custom_id',
                columns: [
                    {
                        data: 'date',
                        render: {
                            display: data => moment(data).format(dateFormat)
                        },
                    },
                    {
                        data: 'custom_shift_code',
                        render: {
                            display: data => (data == 'OFF' ? `<span class="text-danger">${data}</span>` : data)
                        },
                    },
                    {
                        data: 'custom_shift_timing',
                    },
                    {
                        data: 'formatted_shift_assignor_name',
                        render: {
                            display: data => (data == 'System' ? `<span class="text-muted">${data}</span>` : data)
                        },
                    },
                ]
            })

            $('#submitBtn').on('click', () => {
                dataTable.ajax.reload();
            })
        });
    </script>
@endpush