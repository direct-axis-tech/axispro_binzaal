@extends('layout.app')

@section('title', 'Public Holidays')

@section('page')
<div class="container">
    <div>
        <h1 class="d-inline-block my-10">Manage Public Holidays</h1>
        <button type="button" data-bs-toggle="modal" data-bs-target="#addHolidayModal" class="btn btn-primary float-end my-10">
            <span class="fa fa-plus mx-2"></span> Add New Public Holiday
        </button>
    </div>
    <div class="card mt-10">
        <div class="card-body">
            <div class="w-100 table-responsive">
                <table id="holidays-table" class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Holiday Name</th>
                            <th>Number Of Days</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th></th>
                        </tr>
                    <thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for adding a holiday -->
    <div class="modal fade" id="addHolidayModal" tabindex="-1" aria-labelledby="holidayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="holidayModalLabel">Add Holiday</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="addHolidayForm" method="POST">
                    <div class="modal-body p-5 bg-light">
                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="name">Holiday Name:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    class="form-control"
                                    required>
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="num_of_days">Number of Days:</label>
                            <div class="col-sm-9">
                                <input
                                    type="number"
                                    name="num_of_days"
                                    id="num_of_days"
                                    class="form-control"
                                    min="1"
                                    value="1"
                                    required>
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="start_date">Starting From:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    data-parsley-trigger-after-failure="change"
                                    data-parsley-date="{{ dateformat('momentJs') }}"
                                    data-control="bsDatepicker"
                                    data-dateformat="{{ dateformat('bsDatepicker') }}"
                                    data-date-today-btn="linked"
                                    name="start_date"
                                    id="start_date"
                                    class="form-control"
                                    value="{{ date(dateformat()) }}"
                                    required>
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="end_date">Ending On:</label>
                            <div class="col-sm-9">
                                <!-- Calculate ending date based on the start date and number of holidays -->
                                <input
                                    required
                                    type="text"
                                    name="end_date"
                                    id="end_date"
                                    class="form-control"
                                    placeholder="{{ dateformat('momentJs') }}"
                                    value="{{ date(dateformat()) }}"
                                    readonly
                                >
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <input type="hidden" name="holiday_id" class="holiday_id" id="holiday_id" value="" >
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="addHolidayBtn" class="btn btn-primary">Add Holiday</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
@push('scripts')

<script>
    $(document).ready(function() {
        route.push('holidays.store', '{{ rawRoute('holidays.store') }}');
        route.push('holidays.update', '{{ rawRoute('holidays.update') }}');
        route.push('holidays.destroy', '{{ rawRoute('holidays.destroy') }}');
        const dateFormat = '{{ dateformat('momentJs') }}';

        var table = $('#holidays-table').DataTable({
            ajax: ajaxRequest({
                url: '{{ route('api.dataTable.holidays') }}',
                method: 'post',
                eject: true,
            }),
            processing: true,
            serverSide: true,
            paging: true,
            ordering: true,
            rowId: 'id',
            columns: [
                {
                    data: 'name',
                    title: 'Holiday Name',
                    class: 'text-nowrap',
                },
                {
                    data: 'num_of_days',
                    title: 'Number Of Days',
                    class: 'text-nowrap',
                },
                {
                    data: 'formatted_start_date',
                    title: 'Start Date',
                    class: 'text-nowrap'
                },
                {
                    data: 'formatted_end_date',
                    title: 'End Date',
                    class: 'text-nowrap',
                },
                {
                    data: null,
                    defaultContent: '',
                    title: '',
                    width: '20px',
                    searchable: false,
                    orderable: false,
                    render: function (data) {
                        var actions = `<span data-action="edit" title="Edit" class="text-warning mx-1 fa fs-1 p-2 cursor-pointer fa-pencil-alt"></span>`;
                        if (!parseInt(data.is_used)) {
                            actions += `<span data-action="delete" title="Delete" class="text-accent mx-1 fa fs-1 p-2 cursor-pointer fa-trash"></span>`;
                        }
                        return actions;
                    },
                },
            ],
        });

        // Add event listeners to calculate the end date on input changes
        $("#start_date, #num_of_days").on("change", function () {
            let endDate = moment($("#start_date").datepicker('getDate'));
            const numOfDays = parseInt($("#num_of_days").val(), 10) || 0;
            
            endDate = endDate.isValid()
                ? endDate.add(numOfDays ? numOfDays - 1 : 0, 'days').format(dateFormat)
                : '';

            $("#end_date").val(endDate).trigger('change');
        });

        const parsleyForm = $('#addHolidayForm').parsley();
        parsleyForm.on('form:submit', function() {
            Swal.fire({
                target: document.getElementById('addHolidayModal'),
                title: '!! SHIFT UPDATE !!',
                text: `Shifts have already been assigned to employees on these dates. Would you like to update all their shifts to 'OFF' as well?`,
                icon: 'warning',
                showDenyButton: true,
                confirmButtonText:"Update shifts also",
                denyButtonText:"Proceed without updating shift",
                confirmButtonColor: '#3085d6',
                denyButtonColor: '#d33',
            }).then(function (result) {
                submitForm(+(result.isConfirmed));
            })

            return false;
        });

        function submitForm(updateShifts) {
            const data = parsleyForm.$element.serializeArray()
                .reduce((acc, ob) => {
                    acc[ob.name] = ob.value;
                    return acc;
                }, {});

            data._method = data.holiday_id ? 'PATCH' : 'POST';
            data.update_shifts = updateShifts;
            ajaxRequest({
                method: "POST",
                url: data.holiday_id
                    ? route('holidays.update', { holiday: data.holiday_id })
                    : route('holidays.store'),
                data: data
            }).done(function(response) {
                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: response.message
                });

                $('#addHolidayModal').modal('hide');
                table.ajax.reload();
            }).fail(defaultErrorHandler);
        }

        // Handle Holiday Update
        $('#holidays-table').on('click', 'span[data-action="edit"]', function() {
            var data = table.row($(this).closest('tr')).data();

            (['name', 'num_of_days']).forEach(k => {
                parsleyForm.element.elements[k].value = data[k];
            });
            
            $(parsleyForm.element.elements['start_date'])
                .datepicker('setDate', new Date(data['start_date']))
                .trigger('change');
            
            $('#holiday_id').val(data.id);
            $('#holidayModalLabel').text('Edit Holiday');
            $('#addHolidayBtn').text('Update');
            $('#addHolidayModal').modal('show');
        });

        // Handle Holiday Delete
        $('#holidays-table').on('click', 'span[data-action="delete"]', function() {
            var data = table.row($(this).closest('tr')).data();
            
            Swal.fire({
                title: 'Are you sure?',
                html: `Your are about to delete this holiday: '${data.name}'. <br>This process cannot be reversed!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (!result.value) {
                    return;
                }

                ajaxRequest({
                    method: "POST",
                    url: route('holidays.destroy', { holiday: data.id }),
                    data: {
                        _method: 'DELETE'
                    }
                }).done(function (response) {
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: response.message,
                    });
                    table.ajax.reload();
                }).fail(defaultErrorHandler);
            })
        });

        $('#addHolidayModal').on('hidden.bs.modal', function () {
            $('#addHolidayForm')[0].reset();
        });

        parsleyForm.$element.on('reset', function () {
            parsleyForm.reset();
            $('#holiday_id').val('');
            $('#holidayModalLabel').text('Add Holiday'); 
            $('#addHolidayBtn').text('Add Holiday'); 
        })
    });
</script>

@endpush