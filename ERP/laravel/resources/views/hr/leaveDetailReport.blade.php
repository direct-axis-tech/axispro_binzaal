@extends('layout.app')

@section('title', 'Employees Leave Detail Report')

@section('page')

<style>
    #leave-detail-report-table th[colspan] {
        min-width: 70px;
    }

    #leave-detail-report-table tbody td {
        line-height: 30px;
    }

    #leave-detail-report-table th, 
    #leave-detail-report-table td {
        border: 1px solid #ddd;
    }

    #leave-detail-report-table {
        border-collapse: collapse;
    }

    #leave-detail-report-table thead tr:first-child th:first-child,
    #leave-detail-report-table tbody tr td:first-child {
        left: 0px;
        background-color: white;
    }

    #leave-detail-report-table thead tr th,
    #leave-detail-report-table tbody tr td:first-child {
        position: sticky;
    }

    #leave-detail-report-table tbody tr td:first-child {
        z-index: 1;
    }

    #leave-detail-report-table thead tr th {
        z-index: 2;
    }

    #leave-detail-report-table thead tr:first-child th:first-child {
        z-index: 3;
    }

    #leave-detail-report-table thead tr:nth-child(1) th {
        /* background-color: white; */
        top: 0px;
    }

    #leave-detail-report-table thead tr:nth-child(2) th {
        /* Line Height + Top Padding + Bottom Padding + Top & Bottom Border Width of Top Row */
        top: calc(1.5rem + 0.75rem + 0.75rem + 2px);
    }

    /* #leave-detail-report-table thead tr:nth-child(3) th {
        /* 2 x (Line Height + Top Padding + Bottom Padding + Top & Bottom Border Width of Top Row) * /
        top: calc(2 * (1.5rem + 0.75rem + 0.75rem + 2px));
    } */

    .table-wrapper .table-responsive {
        max-height: 600px;
        overflow: auto; 
    }
</style>


<div class="container-fluid">
    <div>
        <h1 class="d-inline-block my-10">Leave Detail Report</h1>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form id="leaveDetailReportForm" method="GET">
                <div class="row mb-3">
                    <div class="col-md-6 col-lg-3 col-xl-2">
                        <div class="form-group">
                            <label for="w_company_id" class="form-label">Company:</label>
                            <select name="w_company_id[]" class="form-control" id="w_company_id" data-control="select2" data-placeholder=" -- Select Company -- " multiple >
                                <option value=""> -- Select Company -- </option>
                                @foreach ($wCompanies as $company)
                                    <option value="{{ $company->id }}" {{ (isset($userInputs['w_company_id']) && in_array($company->id, (array)$userInputs['w_company_id'])) ? 'selected' : '' }} >{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 col-xl-2">
                        <div class="form-group">
                            <label for="department_id" class="form-label">Department:</label>
                            <select name="department_id[]" class="form-control" id="department_id" data-control="select2" data-placeholder=" -- Select Department -- " multiple >
                                <option value=""> -- Select Department -- </option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" {{ (isset($userInputs['department_id']) && in_array($department->id, (array)$userInputs['department_id'])) ? 'selected' : '' }} >{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 col-xl-2">
                        <div class="form-group">
                            <label for="employee_id" class="form-label">Employee:</label>
                            <select name="employee_id[]" class="form-control" id="employee_id" data-control="select2" data-placeholder=" -- Select Employee -- " multiple >
                                <option value=""> -- Select Employee -- </option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ (isset($userInputs['employee_id']) && in_array($employee->id, (array)$userInputs['employee_id'])) ? 'selected' : '' }} >{{ $employee->formatted_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 col-xl-2">
                        <div class="form-group">
                            <label for="leave_type" class="form-label">Leave Type:</label>
                            <select name="leave_type[]" class="form-control" id="leave_type" data-control="select2" data-placeholder=" -- Select Leave Type -- " multiple >
                                <option value=""> -- Select Leave Type -- </option>
                                @foreach ($allLeaveTypes as $type)
                                    <option value="{{ $type->id }}" {{ (isset($userInputs['leave_type']) && in_array($type->id, (array)$userInputs['leave_type'])) ? 'selected' : '' }} >{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-4 col-xl-3">
                        <div class="form-group">
                            <label for="from_date" class="form-label">Date Period:</label>
                            <div
                                id="leave_from_range"
                                class="input-group input-daterange"
                                data-control='bsDatepicker'
                                data-date-keep-empty-values="true"
                                data-date-clear-btn="true">
                                <input
                                    type="text"
                                    name="leave_from_start"
                                    id="leave_from_start"
                                    class="form-control"
                                    autocomplete="off"
                                    placeholder="d-MMM-yyyy"
                                    value="{{ (isset($userInputs['leave_from_start']) && $userInputs['leave_from_start'] != '') ? date(dateformat(), strtotime($userInputs['leave_from_start'])) : date(dateformat(), strtotime('first day of January this year')) }}">
                                <div class="input-group-text input-group-addon px-4 rounded-0 border-left-0 border-right-0">to</div>
                                <input
                                    type="text"
                                    name="leave_from_end"
                                    id="leave_from_end"
                                    class="form-control"
                                    autocomplete="off"
                                    value="{{ (isset($userInputs['leave_from_end']) && $userInputs['leave_from_end'] != '') ? date(dateformat(), strtotime($userInputs['leave_from_end'])) : date(dateformat()) }}"
                                    placeholder="d-MMM-yyyy">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-6 text-center">
                        <button type="submit" class="btn btn-primary m-2">Filter</button>
                        <button type="reset" class="btn btn-secondary m-2">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-10">
        <div class="card-body">
            <div class="w-100 table-wrapper">
                <table id="leave-detail-report-table" class="table text-nowrap text-center">
                    <thead>
                        <tr>
                            <th rowspan="2" style="vertical-align: middle;" class="ps-3">Employee</th>
                            @foreach ($leaveTypes as $types)
                                <th colspan="{{ count($leaveAttr) }}" style="background-color: {{ $types->color }}">{{ $types->desc }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($leaveTypes as $types)
                                @foreach($leaveAttr as $attr)
                                    <th style="background-color: {{ $types->color }}">{{ $attr }}</th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resultList as $list)
                            <tr>
                                <td class="text-start ps-3">{{ $list['employee_name'] }}</td>

                                @foreach ($leaveTypes as $types)
                                    @foreach($leaveAttr as $attr)
                                        <td style="background-color: {{ $types->color }}" >{{ $list[$types->id][$attr] ?? 0 }}</td>
                                    @endforeach
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
</div>

@endsection

@push('scripts').
<script>
    $(document).ready(function() {

        $('#employee_id, #leave_type').select2();
        const employees = @json($employees); 
        regenerateEmployees();

        $('#leaveDetailReportForm').on('reset', function() {
            setTimeout(() => {
                $('#w_company_id, #department_id, #employee_id, #leave_type').val([]).trigger('change');
                $("input[data-control='bsDatepicker'], [data-control='bsDatepicker'] input").val('').trigger('change');
            })
        });

        if ($('#leave-detail-report-table').length) {
            $('#leave-detail-report-table').DataTable({
                paging: false,
                info: false,
            });
        }

        $('#w_company_id, #department_id').on('change', function() {
            regenerateEmployees();
        });

        function regenerateEmployees()
        {
            var workingCompanyIds = $('#w_company_id').val();
            var departmentIds     = $('#department_id').val();
            var filteredEmployees = employees.filter(function(employee) {
                var isCompanyMatch    = (workingCompanyIds.length === 0) || (workingCompanyIds.includes(String(employee.working_company_id)));
                var isDepartmentMatch = (departmentIds.length === 0) || (departmentIds.includes(String(employee.department_id)));
                return isCompanyMatch && isDepartmentMatch;
            });

            $('#employee_id').empty();

            if (filteredEmployees.length > 0) {
                $.each(filteredEmployees, function(index, employee) {
                    $('#employee_id').append(new Option(employee.formatted_name, employee.id));
                });
            } else {
                $('#employee_id').append(new Option('-- No Employees Available --', ''));
            }

            $('#employee_id').trigger('change');
        }
    });
</script>
@endpush




