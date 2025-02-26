@extends('layout.base')

@section('content')
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Page-->
        <div class="page d-flex flex-row flex-column-fluid">
            <!--begin::Wrapper-->
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                @include('layout.header.main')

                <!--begin::Content-->
                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    @include('layout.header._menubar')

                    <!--begin::Post-->
                    <div class="post d-flex flex-column-fluid" id="kt_post">
                        @yield('page')
                    </div>
                    <!--end::Post-->
                </div>
                <!--end::Content-->

                @include('layout._footer')
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::Root-->

    @include('system.tasks.show')
    @include('system.amc-notifications')
@endsection