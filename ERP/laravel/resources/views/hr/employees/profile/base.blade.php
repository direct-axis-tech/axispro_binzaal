@php
    $nav = array(
        array('title' => 'Personal', 'route' => 'employeeProfile.personal'),
        array('title' => 'Job & Pay', 'route' => 'employeeProfile.jobAndPay'),
        array('title' => 'Leaves', 'route' => 'employeeProfile.leaves'),
        array('title' => 'Documents', 'route' => 'employeeProfile.documents'),
        array('title' => 'Shifts', 'route' => 'employeeProfile.shifts'),
        array('title' => 'Attendances', 'route' => 'employeeProfile.attendances'),
        array('title' => 'Punchings', 'route' => 'employeeProfile.punchings'),
        array('title' => 'Payslip', 'route' => 'employeeProfile.payslip'),
        // array('title' => 'Security', 'route' => ''),
    );
@endphp


@extends('layout.app')

@section('title', 'Profile')

@section('page')
<div class="d-flex flex-column flex-column-fluid align-items-center">
    <div class="d-flex flex-column">
        <!--begin::Navbar-->
        <div class="card mb-5 mb-xl-10">
            <div class="card-body pt-9 pb-0">
                <!--begin::Details-->
                <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                    <!--begin: Pic-->
                    <div class="me-7 mb-4">
                        <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                            @if (!empty($employee->profile_photo))
                                <img src="{{ url(Storage::url($employee->profile_photo)) }}" alt="image"/>
                            @else
                                <img src="{{ $employee->avatar_url }}" alt="image"/>
                            @endif
                            <div class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-white h-20px w-20px"></div>
                        </div>
                    </div>
                    <!--end::Pic-->

                    <!--begin::Info-->
                    <div class="flex-grow-1">
                        <!--begin::Title-->
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                            <!--begin::User-->
                            <div class="d-flex flex-column">
                                <!--begin::Name-->
                                <div class="d-flex align-items-center mb-2">
                                    <a href="#" class="text-gray-800 text-hover-primary fs-2 fw-bolder me-1">{{ $employee->name }}</a>
                                    <a href="#">
                                        {!! get_svg_icon("icons/duotune/general/gen026.svg", "svg-icon-1 svg-icon-primary") !!}
                                    </a>
                                </div>
                                <!--end::Name-->

                                <!--begin::Info-->
                                <div class="d-flex flex-wrap fw-bold fs-6 mb-4 pe-2">
                                    <a href="#" class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                        {!! get_svg_icon("icons/duotune/communication/com005.svg", "svg-icon-4 me-1") !!}
                                        {{ $employee->emp_ref }}
                                    </a>
                                    <a href="#" class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                        {!! get_svg_icon("icons/duotune/communication/com006.svg", "svg-icon-4 me-1") !!}
                                        {{ $employee->currentJob->designation->name }}
                                    </a>
                                    <a href="#" class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                        {!! get_svg_icon("icons/duotune/electronics/elc002.svg", "svg-icon-4 me-1") !!}
                                        {{ $employee->mobile_no }}
                                    </a>
                                    <a href="#" class="d-flex align-items-center text-gray-400 text-hover-primary mb-2">
                                        {!! get_svg_icon("icons/duotune/communication/com011.svg", "svg-icon-4 me-1") !!}
                                        {{ $employee->email }}
                                    </a>
                                </div>
                                <!--end::Info-->
                            </div>
                            <!--end::User-->
                        </div>
                        <!--end::Title-->

                        <!--begin::Stats-->
                        <div class="d-flex flex-wrap flex-stack">
                            <!--begin::Wrapper-->
                            <div class="d-flex flex-column flex-grow-1 pe-8">
                                <!--begin::Stats-->
                                <div class="d-flex flex-wrap">
                                    <!--begin::Stat-->
                                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                        <!--begin::Number-->
                                        <div class="d-flex align-items-center">
                                            {!! get_svg_icon("icons/duotune/arrows/arr066.svg", "svg-icon-3 svg-icon-success me-2") !!}
                                            <div class="fs-2 fw-bolder" data-kt-countup="true" data-kt-countup-duration="1" data-kt-countup-value="{{ $employee->salary }}" data-kt-countup-prefix="AED ">0</div>
                                        </div>
                                        <!--end::Number-->

                                        <!--begin::Label-->
                                        <div class="fw-bold fs-6 text-gray-400">{{ __('Earnings') }}</div>
                                        <!--end::Label-->
                                    </div>
                                    <!--end::Stat-->
                                </div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Stats-->
                    </div>
                    <!--end::Info-->
                </div>
                <!--end::Details-->

                <!--begin::Navs-->
                <div class="d-flex overflow-auto h-55px">
                    <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bolder flex-nowrap">
                        @foreach($nav as $tab)
                            <!--begin::Nav item-->
                            <li class="nav-item">
                                <a
                                    class="{{  class_names([
                                        'nav-link text-active-primary me-6',
                                        'active' => request()->path() === ltrim(route($tab['route'], ['employee' => $employee->id], false), '/')
                                    ]) }}"
                                    href="{{ route($tab['route'], ['employee' => $employee->id]) }}">
                                    {{ $tab['title'] }}
                                </a>
                            </li>
                            <!--end::Nav item-->
                        @endforeach
                    </ul>
                </div>
                <!--begin::Navs-->
            </div>
        </div>
        <!--end::Navbar-->

        @yield('slot')
    </div>
</div>
@endsection