@extends('layout.app')

@section('title', 'Employees Leave Report')

@section('page')

<style>
    td, th {
        vertical-align: middle !important;
        text-align: center;
    }

</style>

<div class="container-fluid">
    <div>
        <h1 class="d-inline-block my-10">Leave Report</h1>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form id="leaveReportForm" method="GET">
                <div class="row mb-7">
                    <div class="col-md-2">
                        <label for="employee_id" class="form-label">Employee:</label>
                        <select name="employee_id[]" class="form-control" id="employee_id" data-control="select2" data-placeholder=" -- Select Employee -- " multiple >
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" {{ (isset($userInputs['employee_id']) && in_array($employee->id, $userInputs['employee_id'])) ? 'selected' : '' }} >{{ $employee->formatted_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="leave_type" class="form-label">Leave Type:</label>
                        <select name="leave_type[]" class="form-control" id="leave_type" data-control="select2" data-placeholder=" -- Select Leave Type -- " multiple>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ (isset($userInputs['leave_type']) && in_array($type->id, $userInputs['leave_type'])) ? 'selected' : '' }} >{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="leave_category" class="form-label">Leave Category:</label>
                        <select name="leave_category[]" class="form-control" id="leave_category" data-control="select2" data-placeholder=" -- Select Leave Type -- " multiple>
                            @foreach ($leaveCategory as $categoryId => $category)
                                <option value="{{ $categoryId }}" {{ (isset($userInputs['leave_category']) && in_array($categoryId, $userInputs['leave_category'])) ? 'selected' : '' }} >{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="trans_type" class="form-label">Transaction Type:</label>
                        <select name="trans_type[]" class="form-control" id="trans_type" data-control="select2" data-placeholder=" -- Select Leave Type -- " multiple>
                            @foreach ($transactionType as $trans => $transType)
                                <option value="{{ $trans }}" {{ (isset($userInputs['trans_type']) && in_array($trans, $userInputs['trans_type'])) ? 'selected' : '' }} >{{ $transType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="leave_status" class="form-label">Leave Status:</label>
                        <select name="leave_status[]" class="form-control" id="leave_status" data-control="select2" data-placeholder=" -- Select Leave Type -- " multiple>
                            @foreach ($leaveStatus as $statusId => $status)
                                <option value="{{ $statusId }}" {{ (isset($userInputs['leave_status']) && in_array($statusId, $userInputs['leave_status'])) ? 'selected' : '' }} >{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="form-group col-md-6 col-lg-3 col-xl-2">
                        <label for="from_date" class="form-label">From Date:</label>
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
                                value="{{ (isset($userInputs['leave_from_start']) && $userInputs['leave_from_start'] != '') ? date(dateformat(), strtotime($userInputs['leave_from_start'])) : '' }}">
                            <div class="input-group-text input-group-addon px-4 rounded-0 border-left-0 border-right-0">to</div>
                            <input
                                type="text" 
                                name="leave_from_end" 
                                id="leave_from_end"
                                class="form-control"
                                autocomplete="off"
                                value="{{ (isset($userInputs['leave_from_end']) && $userInputs['leave_from_end'] != '') ? date(dateformat(), strtotime($userInputs['leave_from_end'])) : '' }}"
                                placeholder="d-MMM-yyyy">
                        </div>
                    </div>

                    <div class="form-group col-md-6 col-lg-3 col-xl-2">
                        <label for="to_date" class="form-label">To Date:</label>
                        <div 
                            id="leave_till_range"
                            data-control='bsDatepicker'
                            class="input-group input-daterange"
                            data-date-keep-empty-values="true"
                            data-date-clear-btn="true">
                            <input
                                type="text" 
                                name="leave_till_start" 
                                id="leave_till_start"
                                class="form-control"
                                autocomplete="off"
                                placeholder="d-MMM-yyyy"
                                value="{{ (isset($userInputs['leave_till_start']) && $userInputs['leave_till_start'] != '') ? date(dateformat(), strtotime($userInputs['leave_till_start'])) : '' }}">
                            <div class="input-group-text input-group-addon px-4 rounded-0 border-left-0 border-right-0">to</div>
                            <input
                                type="text" 
                                name="leave_till_end" 
                                id="leave_till_end"
                                class="form-control"
                                autocomplete="off"
                                value="{{ (isset($userInputs['leave_till_end']) && $userInputs['leave_till_end'] != '') ? date(dateformat(), strtotime($userInputs['leave_till_end'])) : '' }}"
                                placeholder="d-MMM-yyyy">
                        </div>
                    </div>

                    <div class="form-group col-md-6 col-lg-3 col-xl-2">
                        <label for="to_date" class="form-label">Requested Date:</label>
                        <div 
                            id="leave_requested_range"
                            data-control='bsDatepicker'
                            class="input-group input-daterange"
                            data-date-keep-empty-values="true"
                            data-date-clear-btn="true">
                            <input
                                type="text" 
                                name="leave_requested_start" 
                                id="leave_requested_start"
                                class="form-control"
                                autocomplete="off"
                                placeholder="d-MMM-yyyy"
                                value="{{ (isset($userInputs['leave_requested_start']) && $userInputs['leave_requested_start'] != '') ? date(dateformat(), strtotime($userInputs['leave_requested_start'])) : '' }}">
                            <div class="input-group-text input-group-addon px-4 rounded-0 border-left-0 border-right-0">to</div>
                            <input
                                type="text" 
                                name="leave_requested_end" 
                                id="leave_requested_end"
                                class="form-control"
                                autocomplete="off"
                                value="{{ (isset($userInputs['leave_requested_end']) && $userInputs['leave_requested_end'] != '') ? date(dateformat(), strtotime($userInputs['leave_requested_end'])) : '' }}"
                                placeholder="d-MMM-yyyy">
                        </div>
                    </div>

                    <div class="col-md-3 d-flex justify-content-center mt-8">
                        <button type="submit" class="btn btn-primary m-2">Filter</button>
                        <button type="button" data-export="xlsx" class="btn btn-primary m-2">Excel</button>
                        <button type="button" data-export="pdf" class="btn btn-primary m-2">PDF</button>
                        <button type="reset" class="btn btn-secondary m-2">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-10">
        <div class="card-body">
            <div class="w-100 table-responsive">
                <table id="leave-report-table" class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Employee Name</th>
                            <th>Leave Type</th>
                            <th>Leave Category</th>
                            <th>Type</th>
                            <th>No Of Days</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Requested On</th>
                            <th>Remarks</th>
                            <th>Leave Status</th>
                            <th>Approved By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultList as $key => $value) 
                            <tr>
                                <td class="text-center align-middle">{{ $resultList->firstItem() + $loop->index }}</td>
                                <td class="text-center align-middle">{{ $value->name }}</td>
                                <td class="text-center align-middle">{{ $value->leave_type }}</td>
                                <td class="text-center align-middle">{{ $value->category }}</td>
                                <td class="text-center align-middle">{{ $value->transaction_type }}</td>
                                <td class="text-center align-middle">{{ $value->days }}</td>
                                <td class="text-center align-middle">{{ $value->formatted_from_date }}</td>
                                <td class="text-center align-middle">{{ $value->formatted_till_date }}</td>
                                <td class="text-center align-middle">{{ $value->formatted_requested_on }}</td>
                                <td class="align-middle">
                                    <textarea class="form-control form-control-plaintext" cols="30" rows="3" readonly >{{ $value->memo }}</textarea>
                                </td>
                                <td class="text-center align-middle">{{ $value->leave_status }}</td>
                                <td class="text-center align-middle">{{ $value->approved_by }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-10">
                <p> Page {{ $resultList->currentPage() }} of {{ $resultList->lastPage() }} <br></p>
                <div class="pagination d-flex justify-around">
                    {{ $resultList->links() }}
                </div>
            </div>
        </div>
    </div>
    
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        $('#employee_id, #leave_type, #leave_category, #trans_type, #leave_status').select2();

        $('#leaveReportForm').on('reset', function() {
            setTimeout(() => {
                $('#employee_id option, #leave_type option, #leave_category option, #trans_type option, #leave_status option').removeAttr('selected');
                $('#employee_id, #leave_type, #leave_category, #trans_type, #leave_status').val('').trigger('change');
                $("input[data-control='bsDatepicker'], [data-control='bsDatepicker'] input").val('').trigger('change');
            })
        });  
        
        $('#leave-report-table').DataTable({
            paging: false, 
            info: false
        });

        route.push('leave_report.export', '{{ rawRoute('leave_report.export') }}');

        $('button[data-export]').on('click', function(e) {
            const form   = document.querySelector('#leaveReportForm');
            let formData = new FormData(form);
            const actionType = $(this).data('export');
            formData.append('actionType', actionType);

            ajaxRequest({
                url: '{{ route('leave_report.export') }}',
                contentType: false,
                processData: false,
                method: 'POST',
                data: formData
            }).done(resp => {
                if (resp && resp.redirect_to) {
                    window.location = resp.redirect_to;
                } else {
                    defaultErrorHandler();
                }
            }).fail(defaultErrorHandler);
        })

    });
</script>
@endpush