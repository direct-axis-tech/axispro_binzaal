@extends('hr.employees.profile.base')

@section('slot')
<div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
    <!--begin::Card body-->
    <div class="card-body p-9">
        <table class="table table-striped text-center text-nowrap" id="leave-summary-table">
            <thead>
                <tr>
                    <th class="fw-bold text-muted">{{ 'Type' }}</th>
                    <th class="fw-bold text-muted">{{ 'Taken' }}</th>
                    <th class="fw-bold text-muted">{{ 'Remaining' }}</th>
                    <th class="fw-bold text-muted">{{ '' }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($leaveTypes as $leaveType)
                @php
                    $leaveDetails = HRPolicyHelpers::getLeaveBalance($employee->id, $leaveType->id, $employee->date_of_join);    
                @endphp
                <tr>
                    <td data-col="leaveTypeName" class="fw-bold text-muted">{{ $leaveType->name }}</td>
                    <td class="fs-6 text-dark">{{ ($taken = data_get($leaveDetails, 'takenLeaves')) <= 0 ? '--' : $taken }}</td>
                    <td class="fs-6 text-dark">{{
                        $leaveType->id == \App\Models\Hr\LeaveType::ANNUAL
                            ? data_get($leaveDetails, 'balanceLeaves')
                            : '--'
                    }}</td>
                    <td>
                        <button
                            data-show-details-of="{{ $leaveType->id }}"
                            type="button"
                            class="btn btn-text-primary px-3 py-0 fs-3"> 
                            <span class="fa fa-eye" title="View Details"></span>
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Leave details modal -->
<div class="modal fade" id="leaveDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h2 class="modal-title">Leave Details: <span id="leaveTypeName"></span></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-5">
                <table id="leaveDetailsTable" class="table table-striped text-center text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th class="text-muted">Requested On</th>
                            <th class="text-muted">Category</th>
                            <th class="text-muted">Type</th>
                            <th class="text-muted">No Of Days</th>
                            <th class="text-muted">From Date</th>
                            <th class="text-muted">Till Date</th>
                            <th class="text-start text-muted w-300px">Memo</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    route.push('employeeProfile.leave.details', '{{ rawRoute('employeeProfile.leave.details') }}');

    const modalNode = document.getElementById('leaveDetailsModal');
    const leaveTypeNameNode = document.getElementById('leaveTypeName'); 
    const modal = new bootstrap.Modal(modalNode);
    let showDetailsOfLeveType = null;
    let dataTable = null;

    $('[data-show-details-of]').on('click', function () {
        showDetailsOfLeveType = this.dataset.showDetailsOf;
        leaveTypeNameNode.textContent = this.closest('tr').querySelector('[data-col="leaveTypeName"]').textContent;
        modal.show();
    })

    // Initialise the data table
    modalNode.addEventListener('shown.bs.modal', function (ev) {
        dataTable = $('#leaveDetailsTable').DataTable({
            ajax: ajaxRequest({
                url: route('employeeProfile.leave.details', {
                    employee: '{{ $employee->id }}',
                    leaveType: showDetailsOfLeveType
                }),
                method: 'post',
                eject: true,
            }),
            processing: true,
            serverSide: true,
            rowId: 'id',
            columns: [
                {
                    data: 'formatted_requested_on',
                    render: {
                        display: data => (data || '--')
                    }
                },
                {data: 'category'},
                {data: 'transaction_type'},
                {data: 'days'},
                {
                    data: 'formatted_from_date',
                    render: {
                        display: data => (data || '--')
                    }
                },
                {
                    data: 'formatted_till_date',
                    render: {
                        display: data => (data || '--')
                    }
                },
                {
                    data: 'memo',
                    class: 'text-start text-wrap'
                },
            ]
        })
    })

    // Destroy the data table instance
    modalNode.addEventListener('hidden.bs.modal', function (ev) {
        showDetailsOfLeveType = null;
        leaveTypeNameNode.textContent = '';
        dataTable.destroy();
        let tableNode = document.getElementById('leaveDetailsTable');
        for (let i = 0; i < tableNode.tBodies.length; i++) {
            tableNode.removeChild(tableNode.tBodies[i]);
        }
    })
})
</script>
@endpush