@extends('hr.employees.profile.base')

@section('title', 'Profile - Punchings')

@section('slot')
    <div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
        <!--begin::Card body-->
        <div class="card-body p-9">
            <form action="" method="POST" id="filter-form">
                <div class="row flex-row-reverse">
                    <div class="col-auto text-end">
                        <button {{ blank($payrolls) ? 'disabled' : '' }}
                            id="printBtn"
                            type="button"
                            value="Print"
                            class="btn btn-primary btn-sm-block mx-2">
                            Print
                        </button>
                    </div>
    
                    <div class="form-group col-lg-4">
                        <div class="row">
                            <label class="col-form-label col-4" for="payroll_id">Payslip of</label>
                            <div class="col-8">
                                <select required class="form-select" name="payroll_id" id="payroll_id">
                                    @foreach ($payrolls as $id => $payroll)
                                        <option value="{{ $id }}"  {{ $id == $selectedPayroll ? 'selected' : '' }}>{{ $payroll->custom_id }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <hr class="my-10">
            <div class="table-responsive mx-auto" style="width: 1000px;">
                @if ($renderedHtml)
                    {!! $renderedHtml !!}
                @endif
            </div>
        </div>
        <!--end::Card body-->
    </div>
@endsection


@push('scripts')
<script>
    route.push('payslip.print', '{{ rawRoute('payslip.print') }}');
    $(function () {
        const form = document.getElementById('filter-form');

        $('#payroll_id').on('change', function () {
            form.submit();
        })

        $('#printBtn').on('click', function () {
            setTimeout(() => {
                url = route('payslip.print', {
                    employee: '{{ $employee->id }}',
                    payroll: $('#payroll_id').val()
                });
                createPopup(url);
            });
        })
    });
</script>
@endpush