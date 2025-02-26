<!--begin::Menu-->
<div class="menu-sub menu-sub-dropdown w-350px w-lg-375px">
	<!--begin::Heading-->
    <div class="d-flex flex-column bgi-no-repeat rounded-top" style="background-image:url('{{ media('misc/pattern-1.jpg') }}')">
        <!--begin::Title-->
        <h3 class="text-white fw-bold px-9 mt-8 mb-8">
            Notifications
        </h3>
        <!--end::Title-->

        <!--begin::Tabs-->
        <ul class="nav nav-line-tabs nav-line-tabs-2x nav-stretch fw-bold px-9 d-none">
            <li class="nav-item">
                <a class="nav-link text-white opacity-75 opacity-state-100 pb-4 active" data-bs-toggle="tab" href="#alerts">{{ __('Alerts') }}</a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white opacity-75 opacity-state-100 pb-4" data-bs-toggle="tab" href="#requests">{{ __('Requests') }}</a>
            </li>
        </ul>
        <!--end::Tabs-->
    </div>
	<!--end::Heading-->

    <!--begin::Tab content-->
    <div class="tab-content">
        <!--begin::Tab panel-->
        <div class="tab-pane fade show active" id="alerts" role="tabpanel">
            <!--begin::Wrapper-->
            <div class="d-flex flex-column px-9">
                <!--begin::Section-->
                <div class="pt-10 pb-0">
                    <!--begin::Title-->
                    <h3 class="text-dark text-center fw-bolder">
                        {{ __('All Caught Up!') }}
                    </h3>
                    <!--end::Title-->
                </div>
                <!--end::Section-->

                <!--begin::Illustration-->
                <img class="mh-200px" alt="metronic" src="{{ illustration('1.png') }}"/>
                <!--end::Illustration-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Tab panel-->

        <!--begin::Tab panel-->
        <div class="tab-pane fade" id="requests" role="tabpanel">
            <!--begin::Wrapper-->
            <div class="d-flex flex-column px-9">
                <!--begin::Section-->
                <div class="pt-10 pb-0">
                    <!--begin::Title-->
                    <h3 class="text-dark text-center fw-bolder">
                        {{ __('All Caught Up!') }}
                    </h3>
                    <!--end::Title-->
                </div>
                <!--end::Section-->

                <!--begin::Illustration-->
                <img class="mh-200px" alt="metronic" src="{{ illustration('2.png') }}"/>
                <!--end::Illustration-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Tab panel-->

    </div>
    <!--end::Tab content-->
</div>
<!--end::Menu-->
