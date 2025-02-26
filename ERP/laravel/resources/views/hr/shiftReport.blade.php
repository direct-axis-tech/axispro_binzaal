@extends('layout.app')

@section('title', 'Shift Report')

@push('styles')
<style>
  #shiftsTable thead th {
    position: sticky;
    background-color: white;
    top: 0;
  }

  #shiftsTable tbody tr:nth-child(odd) td {
    background: rgba(245, 248, 250);
  }

  #shiftsTable tbody tr:nth-child(even) td {
    background: #fff;
  }

  #shiftsTable thead th:nth-child(2) {
    position: sticky;
    left: 0;
    z-index: 2;
  }

  #shiftsTable tbody td:nth-child(2) {
    position: sticky;
    left: 0;
    z-index: 1;
  }
</style>
@endpush

@section('page')
<div class="container">
    <h1 class="my-10">Shift Report</h1>

    <form action="" id="filterForm">
        @csrf
        <div class="row g-5">
            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="working_company_id">Working Company</label>
                    <select
                        id="working_company_id"
                        data-control="select2"
                        data-placeholder="-- All --"
                        name="working_company_id"
                        class="form-control">
                        <option value="">-- select working company --</option>
                        @foreach ($companies as $c)
                        <option value="{{ $c->id }}" {{ $defaultFilters['working_company_id'] == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="employee_ids">Employees</label>
                    <select id="employee_ids"
                        data-control="select2"
                        data-placeholder="-- All --"
                        name="employee_ids[]"
                        class="form-control"
                        multiple>
                        @foreach ($employees as $e)
                        <option
                            data-working-company-id="{{ $e->working_company_id }}"
                            value="{{ $e->id }}">
                            {{ $e->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="shift_ids">Shift Code</label>
                    <select
                        id="shift_ids"
                        data-control="select2"
                        data-placeholder="-- All --"
                        name="shift_ids[]"
                        class="form-control"
                        multiple>
                        <option value="">-- all --</option>
                        <option value="off" data-color="{{ \App\Models\Hr\Shift::OFF_COLOR_CODE }}">Off</option>
                        @foreach ($shifts as $s)
                        <option value="{{ $s->id }}" data-color="{{ $s->color }}">{{ $s->code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-4 col-lg-3">
                <div class="form-group">
                    <label for="shift_date_range">Date</label>
                    <div class="input-group input-daterange" id="shift_date_range">
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

            <div class="col-12 text-end">
                <button
                    id="submitBtn"
                    type="button"
                    class="btn btn-primary btn-sm-block mx-2">
                    Submit
                </button>

                <button
                    type="button"
                    data-url="{{ url(route('exportShiftReport')) }}"
                    data-export="xlsx"
                    class="btn btn-primary btn-sm-block mx-2">
                    Excel
                </button>

                <button
                    type="button"
                    data-url="{{ url(route('exportShiftReport')) }}"
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
                    id="shiftsTable"
                    data-control="dataTable"
                    data-url="{{ url(route('api.dataTable.shiftReport')) }}"
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
        var storage = {
            employees: [],
            shifts: {}
        };
        
        const momentJsDateFormat = '<?= dateformat('momentJs') ?>';
        const form = document.querySelector('#filterForm');  
        const tableSelector = '#shiftsTable';
        const $table = $(tableSelector);
        const employeesElem = document.getElementById('employee_ids');
        const workingCompanyElem = document.getElementById('working_company_id');

        // Read and initialize employees data
        (function () {
            var employees = employeesElem.options;
            for (var i = 0; i < employees.length; i++) {
                var employee = employees[i];
                storage.employees[i] = {
                    id: employee.value,
                    name: employee.text,
                    workingCompanyId: employee.dataset.workingCompanyId
                }
            }
        })();

        // Read and initialize shifts data
        (function () {
            var shifts = document.getElementById('shift_ids').options;
            for (var i = 0; i < shifts.length; i++) {
                var shift = shifts[i];
                storage.shifts[shift.text] = {
                    id: shift.value,
                    name: shift.text,
                    color: shift.dataset.color
                }
            }
        })();

        // When selected working company change regenerate the employees list
        regenerateEmployees();
        $(`#working_company`).on('change', regenerateEmployees);

        // When form is submitted, regenerate the report
        $('#submitBtn').on('click', regenerateReport)

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

        // generate report on load ??
        regenerateReport();

        function regenerateReport() {
            // Make UI busy
            setBusyState();

            // Destroy the existing dataTable
            if ($.fn.DataTable.isDataTable(tableSelector)) {
                $table.DataTable().destroy();
            }
            
            // Reconfigure the columns
            var columns = [
                {
                    data: 'emp_ref',
                    title: 'Emp. #',
                    className: 'text-center'
                },
                {
                    data: 'employee_name',
                    title: 'Emp. Name',
                },
                {
                    data: 'department_name',
                    title: 'Department',
                },
                {
                    data: 'working_company_name',
                    title: 'Company',
                }
            ];

            const from = $("#from").datepicker("getDate");
            const till = $("#till").datepicker("getDate");
            let curr = new Date(from.getTime())
            while (curr <= till) {
                columns.push({
                    data: `shift_code_${moment(curr).format(momentJsDateFormat)}`,
                    title: moment(curr).format('MMM-D ddd'),
                    className: 'px-2',
                    defaultContent: '',
                    render: function (data, type) {
                        if (type !== 'display' || !data) {
                            return data;
                        }

                        let color, fontColor;
                        let styles = ['padding: 5px 15px'];
                        
                        if (color = storage.shifts[data].color) {
                            styles.push('background-color:' + color);
                        }

                        if (fontColor = getTextColor(color)) {
                            styles.push('color:' + fontColor);
                        }

                        return `<span class="d-block text-center" style="${styles.join(';')}">${data}</span>`;
                    }
                });
                curr = moment(curr).add(1, 'days').toDate();
            }

            empty($table[0]);

            $table.DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                dom:
                    "<'row'<'col-sm-12 justify-content-end'f>>" +
                    "<'table-responsive mh-600px'tr>" +
                    "<'row'" +
                    "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'li>" +
                    "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                    ">",
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
                columns: columns
            });

            // Free the ui
            unsetBusyState();
        }

        function regenerateEmployees() {
            var workingCompanyId = workingCompanyElem.value;

            // filter the employees as per the working company
            var filteredEmployees = storage.employees
                .filter(function(employee) {
                    return (!workingCompanyId.length || employee.workingCompanyId === workingCompanyId)
                });
            
            // prepare the dataSource for the select element
            var dataSource = filteredEmployees.map(function(employee) {
                return {
                    id: employee.id,
                    text: employee.name,
                    selected: false
                }
            })

            if ($(employeesElem).hasClass('select2-hidden-accessible')) {
                $(employeesElem).select2('destroy');   
            }

            empty(employeesElem);
            $(employeesElem).select2({data: dataSource})
        }
        
        function formatDate(date) {
            var date = moment(date);
            return date.isValid() ? date.format('') : null;
        }
    });
</script>
@endpush