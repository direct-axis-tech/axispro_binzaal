<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Direct Axis Technology L.L.C') | Axispro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="An ERP solution custom built for UAE Govt. Transaction centers like Tasheel, Amer & more" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="bs-date-format" content="{{ dateformat('bsDatepicker') }}">
    <meta name="moment-date-format" content="{{ getDateFormatForMomentJs() }}">
    <meta name="base-url" content="{{ config('app.url') }}">
    <link rel="shortcut icon" href="{{ asset('media/logos/favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700"/>
    <link rel="stylesheet" href="{{ asset(mix('plugins/global/plugins.bundle.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('plugins/global/plugins-custom.bundle.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/style.bundle.css')) }}">
    @stack('styles')
</head>
<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed menubar-enabled menubar-fixed" style="--kt-menubar-height:50px;--kt-menubar-height-tablet-and-mobile:50px">
    @yield('content')

    <div id="busy-box"></div>
    <script src="{{ asset(mix('plugins/global/plugins.bundle.js')) }}"></script>
    <script src="{{ asset(mix('js/core.bundle.js')) }}"></script>
    <script src="{{ asset(mix('js/scripts.bundle.js')) }}"></script>
    @stack('scripts')
</body>
</html>