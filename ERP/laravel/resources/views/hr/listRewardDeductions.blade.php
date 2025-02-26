@extends('layout.app')

@section('title', 'Deduction / Rewards')

@section('page')

<div class="container-fluid">
    <div>
        <h1 class="d-inline-block my-10">Issued Employees Deduction / Rewards</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="deduction-reward-form" method="GET">
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label for="employee_id" class="form-label">Employee:</label>
                        <select name="employee_id" class="form-control" id="employee_id" data-control="select2" data-placeholder=" -- Select Employee -- ">
                            <option value=""> -- Select Employee -- </option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" {{ (isset($userInputs['employee_id']) &&  $userInputs['employee_id'] == $employee->id) ? 'selected' : '' }} >{{ $employee->formatted_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="element_type" class="form-label">Type:</label>
                        <select name="element_type" class="form-control" id="element_type" data-control="select2" data-placeholder=" -- Select Type -- ">
                            <option value=""> -- Select Type -- </option>
                            @foreach ($elementTypes as $key => $type) 
                                <option value="{{ $key }}" {{ (isset($userInputs['element_type']) &&  $userInputs['element_type'] == $key) ? 'selected' : '' }} >{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="sub_element" class="form-label">Sub Element:</label>
                        <select name="sub_element" class="form-control" id="sub_element" data-control="select2" data-placeholder=" -- Select Sub Element -- ">
                            <option value=""> -- Select Sub Element -- </option>
                            @foreach ($subElements as $sub => $element) 
                                <option value="{{ $element['id'] }}" {{ (isset($userInputs['sub_element']) &&  $userInputs['sub_element'] == $element['id']) ? 'selected' : '' }} >{{ $element['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="effective_date_from" class="form-label">Effective Date From:</label>
                        <input
                            type="text"
                            name="effective_date_from"
                            id="effective_date_from"
                            class="form-control"
                            data-parsley-trigger-after-failure="change"
                            data-parsley-date="{{ dateformat('momentJs') }}"
                            data-control="bsDatepicker"
                            data-dateformat="{{ dateformat('bsDatepicker') }}"
                            data-date-today-btn="linked"
                            value="{{ (isset($userInputs['effective_date_from']) && $userInputs['effective_date_from'] != '') ? date(dateformat(), strtotime($userInputs['effective_date_from'])) : '' }}" >
                    </div>
                    <div class="col-md-2">
                        <label for="effective_date_to" class="form-label">Effective Date To:</label>
                        <input
                            type="text"
                            name="effective_date_to"
                            id="effective_date_to"
                            class="form-control"
                            data-parsley-trigger-after-failure="change"
                            data-parsley-date="{{ dateformat('momentJs') }}"
                            data-control="bsDatepicker"
                            data-dateformat="{{ dateformat('bsDatepicker') }}"
                            data-date-today-btn="linked"
                            value="{{ (isset($userInputs['effective_date_to']) && $userInputs['effective_date_to'] != '') ? date(dateformat(), strtotime($userInputs['effective_date_to'])) : '' }}" >
                    </div>
                    <div class="col-md-2 d-flex justify-content-center mt-8">
                        <button type="submit" class="btn btn-primary m-2">Filter</button>
                        <button type="reset" class="btn btn-secondary m-2">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-10">
        <div class="card-body">
            <div class="w-100 table-responsive">
                <table id="deduction-reward-table" class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong dataTable">
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Element</th>
                            <th>Sub Element</th>
                            <th>Amount</th>
                            <th>Effective Date</th>
                            <th>No. Installments</th>
                            <th>Installment Amount</th>
                            <th>Processed Amount</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resultList  as $key => $list)
                            <tr>
                                <td>{{ $resultList->firstItem() + $loop->index }}</td>
                                <td>{{ $list->employee_name }}</td>
                                <td>{{ $list->type }}</td>
                                <td>{{ $list->pay_element }}</td>
                                <td>{{ $list->sub_element_name }}</td>
                                <td>{{ $list->amount }}</td>
                                <td>{{ $list->effective_date }}</td>
                                <td>{{ $list->number_of_installments }}</td>
                                <td>{{ $list->installment_amount }}</td>
                                <td>{{ $list->processed_amount }}</td>
                                <td>{{ $list->remarks }}</td>
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

        $('#employee_id, #element_type, #sub_element').select2();

        $('#deduction-reward-form').on('reset', function() {
            setTimeout(() => {
                $('#employee_id option, #element_type option, #sub_element option').removeAttr('selected');
                $('#employee_id, #element_type, #sub_element').val('').trigger('change');
                $("input[data-control='bsDatepicker'], [data-control='bsDatepicker'] input").val('').trigger('change');
            })
        });
        
        $('#deduction-reward-table').DataTable({
            paging: false, 
            info: false
        });
        
    });

</script>
@endpush