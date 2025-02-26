@extends('hr.employees.profile.base')

@section('slot')
<div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
    <!--begin::Card header-->
    <div class="card-header cursor-pointer">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bolder m-0">{{ __('Pay') }}</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--begin::Card header-->

    <!--begin::Card body-->
    <div class="card-body p-9">
        <div class="row mb-10 mt-n3">
            <!--begin::Label-->
            <label class="col-auto fs-4 fw-bold text-muted">{{ __('Mode of Pay') }}</label>
            <!--end::Label-->

            <!--begin::Col-->
            <div class="col-auto">
                <span class="fw-bolder fs-3 text-danger">{{ 'B' == $employee->mode_of_pay ? 'Bank' : 'Cash' }}</span>
            </div>
            <!--end::Col-->
        </div>
        <h3 class="fw-bolder fs-4 text-grey-400 text-hover-primary mb-1">{{ __('Bank Account') }}</h3>
        <hr class="mt-1">
        @if ($employee->bank)
            <!--begin::Row-->
            <div class="row mb-7">
                <div class="col-auto">
                    <div class="card border card-strech-lg">
                        <div class="card-body p-3">
                            <h4 class="fs-4 fw-bolder text-info">{{ $employee->bank->name }}</h4>
                            @if (!empty($employee->branch_name))
                            <span class="fs-2 text-grey-400">{{ $employee->branch_name }}</span>
                            @endif
                            <!--begin::Row-->
                            <div class="row">
                                <!--begin::Label-->
                                <label class="col-lg-4 fw-bold text-muted">{{ __('Routing No') }}</label>
                                <!--end::Label-->

                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <span class="fw-bolder fs-6 text-dark">{{ $employee->bank->routing_no }}</span>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->

                            <!--begin::Row-->
                            <div class="row">
                                <!--begin::Label-->
                                <label class="col-lg-4 fw-bold text-muted">{{ __('IBAN No') }}</label>
                                <!--end::Label-->

                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <span class="fw-bolder fs-6 text-dark">{{ $employee->iban_no }}</span>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Row-->
        @else
            <span class="d-flex flex-center text-muted">Not Configured</span>
        @endif
    </div>
    <!--end::Card body-->
</div>

<div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
    <!--begin::Card header-->
    <div class="card-header cursor-pointer">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bolder m-0">{{ __('Job') }}</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--begin::Card header-->

    <!--begin::Card body-->
    <div class="card-body p-9">
        <!--begin::Row-->
        <div class="row mb-7">
            <div class="col-lg-6">
                <div class="card border card-strech-lg">
                    <div class="card-body p-3">
                        <span class="fs-4 fw-bolder d-block text-grey-800">{{ $employee->currentJob->designation->name }}</span>
                        <!--begin::Row-->
                        <div class="row">
                            <!--begin::Label-->
                            <label class="col-lg-4 fw-bold text-muted">{{ __('Department') }}</label>
                            <!--end::Label-->

                            <!--begin::Col-->
                            <div class="col-lg-8">
                                <span class="fw-bolder fs-6 text-muted">{{ $employee->currentJob->department->name }}</span>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Row-->

                        <!--begin::Row-->
                        <div class="row">
                            <!--begin::Label-->
                            <span class="col fw-bolder text-primary text-end">
                                {{ $employee->currentJob->commence_from }} - Present
                            </span>
                            <!--end::Label-->

                        </div>
                        <!--end::Row-->
                    </div>
                </div>
            </div>
        </div>
        <!--end::Row-->
    </div>
    <!--end::Card body-->
</div>
@endsection