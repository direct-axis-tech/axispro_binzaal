<!--begin::Header-->
<div class="header align-items-stretch">
	<!--begin::Container-->
	<div class="container-fluid d-flex align-items-stretch justify-content-between px-xl-6 px-sm-5">
        <!--begin::Header menu toggle-->
        <div class="d-flex align-items-center d-xl-none me-2 ms-n3" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Show header menu">
            <div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px" id="kt_header_menu_mobile_toggle">
                {!! get_svg_icon("icons/duotune/abstract/abs015.svg", "svg-icon-2x mt-1") !!}
            </div>
        </div>
        <!--end::Header menu toggle-->

        <!--begin::Logo-->
        <div class="d-flex align-items-center flex-grow-1 flex-xl-grow-0 me-xl-15">
            <a href="{{ url('dashboard') }}">
                <img alt="Logo" src="{{ media('logos/logo-1.svg') }}" class="h-35px"/>
            </a>
        </div>
        <!--end::Logo-->

		<!--begin::Wrapper-->
		<div class="d-flex align-items-stretch justify-content-between flex-grow-0 w-100 overflow-hidden flex-xl-grow-1">
			<!--begin::Title-->
            <div class="d-flex flex-stack page-title">
                <!--begin::Separator-->
                <span class="h-20px border-gray-200 border-start mx-4"></span>
                <!--end::Separator-->

                @include('layout.header._title')
            </div>
			<!--end::Title-->

            @if (app(\App\Amc::class)->shouldShowWarningBanner())
            <div class="d-flex flex-stack min-w-100px px-5">
                <div class="amc-expiry-marquee">
                    <div class="marquee-container">
                        <span class="marquee-item">{!! app(\App\Amc::class)->getWarningBannerMsg() !!}</span>
                    </div>
                </div>
            </div>
            @endif

			<!--begin::Toolbar-->
	        <div class="d-flex align-items-stretch flex-shrink-0">
                @include('layout.header._toolbar')
			</div>
			<!--end::Toolbar-->
		</div>
		<!--end::Wrapper-->
	</div>
	<!--end::Container-->
</div>
<!--end::Header-->
