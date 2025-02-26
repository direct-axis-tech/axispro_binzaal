@extends('hr.employees.profile.base')

@section('title', 'Profile - Attendances')

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
                            id="attendance_from_range"
                            class="input-group input-daterange"
                            data-control='bsDatepicker'
                            data-date-keep-empty-values="true"
                            data-date-clear-btn="true">
                            <input
                                type="text" 
                                name="attendance_from" 
                                id="attendance_from"
                                class="form-control"
                                autocomplete="off"
                                value="{{ $inputs['attendance_from'] }}"
                                placeholder="Attendance From">
                            <div class="input-group-text input-group-addon px-4 rounded-0 border-left-0 border-right-0">to</div>
                            <input
                                type="text" 
                                name="attendance_till" 
                                id="attendance_till"
                                class="form-control"
                                autocomplete="off"
                                value="{{ $inputs['attendance_till'] }}"
                                placeholder="Attendance Till">
                        </div>
                    </div>
                </div>
            </form>
            <hr class="my-10">
            <table class="table table-striped text-center text-nowrap" id="attendances-table">
                <thead>
                    <tr>
                        <th class="fw-bold text-muted">{{ 'Date' }}</th>
                        <th class="fw-bold text-muted">{{ 'Status' }}</th>
                        <th class="fw-bold text-muted">{{ 'Based on Shift' }}</th>
                        <th class="fw-bold text-muted">{{ 'Attd. Timing' }}</th>
                        <th class="fw-bold text-muted">{{ 'Total Duration' }}</th>
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
            const dataTable = $('#attendances-table').DataTable({
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
                        data: 'formatted_attendance_status',
                        render: {
                            display: data => {
                                const classes = {
                                    'Present': 'success',
                                    'Not Present': 'danger',
                                    'Off': 'muted',
                                }

                                return `<span class="text-${classes[data] || 'info'}">${data}</span>`
                            }
                        },
                    },
                    {data: 'based_on_shift_timing'},
                    {data: 'attendance_timing'},
                    {data: 'formatted_total_duration'},
                ]
            })

            $('#submitBtn').on('click', () => {
                dataTable.ajax.reload();
            })
        });
    </script>
@endpush