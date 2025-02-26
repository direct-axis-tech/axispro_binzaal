@php
    $itemClass = "ms-1 ms-lg-3";
    $btnClass = "btn btn-icon btn-icon-muted btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px";
    $userAvatarClass = "symbol-30px symbol-md-40px";
    $btnIconClass = "svg-icon-1";
@endphp

<!--begin::Toolbar wrapper-->
<div id="topbar" class="menu d-flex align-items-stretch flex-shrink-0" data-kt-menu="true">

    <!--begin::Notifications-->
    <div
        id="notification-wrapper"
        class="menu-item d-flex notification-wrapper align-items-center {{ $itemClass }}"
        data-kt-menu-trigger="click"
        data-kt-menu-attach="#notification-wrapper"
        data-kt-menu-placement="bottom"
        data-user-id="{{ auth()->user()->id }}">
        <!--begin::Menu- wrapper-->
        <div
            class="{{ $btnClass }} notification-icon menu-link p-0"
            data-unread-notifications="0"
            >
            {!! get_svg_icon("icons/duotune/general/gen007.svg", $btnIconClass) !!}
        </div>
        @include('layout.header._notifications-menu')
        <!--end::Menu wrapper-->
    </div>
    <!--end::Notifications-->

    <!--begin::User Name-->
    <div class="fw-bolder menu-item fs-3 d-flex align-items-center {{ $itemClass }}">
        {{ auth()->user()->name }}
    </div>
    <!--end::User Name-->

    <!--begin::User menu-->
    <div
        class="menu-item d-flex align-items-center {{ $itemClass }}"
        id="kt_header_user_menu_toggle"
        data-kt-menu-trigger="click"
        data-kt-menu-attach="#kt_header_user_menu_toggle"
        data-kt-menu-placement="bottom">
        <!--begin::Menu wrapper-->
        <div class="cursor-pointer border symbol {{ $userAvatarClass }} menu-link p-0">
            <img src="{{ auth()->user()->avatar_url }}" alt="user" class="align-self-start"/>
        </div>
        @include('layout.header._user-menu')
        <!--end::Menu wrapper-->
    </div>
    <!--end::User menu-->
</div>
<!--end::Toolbar wrapper-->