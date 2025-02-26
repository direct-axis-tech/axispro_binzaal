<?php
    use App\Permissions as P;

    $user = auth()->user();

    $menu = [
        [
            'title'         => __('DASHBOARD'),
            'path'          => url('/dashboard'),
            'iconClass'     => 'la la-2x la-dashboard',
            'isAccessible'  => true,
            'isActive'      => is_active_menu('dashboard')
        ],
        [
            'title'         => __('SALES'),
            'path'          => erp_url('/?application=sales'),
            'iconClass'     => 'la la-2x la-shopping-basket',
            'isAccessible'  => $user->hasPermission(P::HEAD_MENU_SALES),
            'isActive'      => is_active_menu('sales')
        ],
        [
            'title'         => __('DOMESTIC WORKERS'),
            'path'          => erp_url('/?application=labour'),
            'iconClass'     => 'la la-2x la-users',
            'isAccessible'  => $user->hasPermission(P::HEAD_MENU_LABOUR),
            'isActive'      => is_active_menu('labour')
        ],
        [
            'title'         => __('PURCHASE'),
            'path'          => erp_url('/?application=purchase'),
            'iconClass'     => 'la la-2x la-shopping-basket',
            'isAccessible'  => $user->hasPermission(P::HEAD_MENU_PURCHASE),
            'isActive'      => is_active_menu('purchase')
        ],
        [
            'title'         => __('FIXED ASSETS'),
            'path'          => erp_url('/?application=fixed_assets'),
            'iconClass'     => 'la la-2x la-shopping-basket',
            'isAccessible'  => $user->hasPermission(P::HEAD_MENU_ASSET),
            'isActive'      => is_active_menu('fixed_assets')
        ],
        [
            'title'         => __('FINANCE'),
            'path'          => erp_url('/?application=finance'),
            'iconClass'     => 'la la-2x la-chart-line',
            'isAccessible'  => $user->hasPermission(P::HEAD_MENU_FINANCE),
            'isActive'      => is_active_menu('finance')
        ],
        [
            'title'         => __('HRM'),
            'path'          => erp_url('/?application=hr'),
            'iconClass'     => 'la la-2x la-user-tie',
            'isAccessible'  => $user->hasPermission(P::HEAD_MENU_HR),
            'isActive'      => is_active_menu('hrm')
        ],
        [
            'title'         => __('REPORTS'),
            'path'          => erp_url('/?application=reports'),
            'iconClass'     => 'la la-2x la-files-o',
            'isAccessible'  => $user->hasPermission(P::HEAD_MENU_REPORT),
            'isActive'      => is_active_menu('reports')
        ],
        [
            'title'         => __('SYSTEM'),
            'path'          => erp_url('/?application=settings'),
            'iconClass'     => 'la la-2x la-cog',
            'isAccessible'  => $user->hasPermission(P::HEAD_MENU_SETTINGS),
            'isActive'      => is_active_menu('system')
        ],
    ];
?>
<!--begin::Menu wrapper-->
<div class="menubar-menu align-items-stretch"
     data-kt-drawer="true"
     data-kt-drawer-name="menubar-menu"
     data-kt-drawer-activate="{default: true, xl: false}"
     data-kt-drawer-overlay="true"
     data-kt-drawer-width="{default:'200px', '300px': '250px'}"
     data-kt-drawer-direction="start"
     data-kt-drawer-toggle="#kt_header_menu_mobile_toggle"
     data-kt-swapper="true"
     data-kt-swapper-mode="prepend"
     data-kt-swapper-parent="{default: '#kt_body', xl: '#kt_header_nav'}"
>
    <!--begin::Menu-->
    <div class="menu menu-column menu-xl-row menu-state-bg-primary menu-title-light menu-arrow-gray-400 fw-bold my-5 my-xl-0 align-items-stretch"
         id="kt_header_menu"
         data-kt-menu="true"
    >
        @foreach ($menu as $item)
            @if ($item['isAccessible'])
            <div class="menu-item border-start border-end">
                <a class="menu-link py-3 px-10 {{ $item['isActive'] ? 'active' : '' }}" href="{{ $item['path'] }}">
                    <span class="menu-icon {{ $item['iconClass'] }}"></span>
                    <span class="menu-title">{{ $item['title'] }}</span>
                </a>
            </div>
            @endif
        @endforeach
    </div>
    <!--end::Menu-->
</div>
<!--end::Menu wrapper-->
