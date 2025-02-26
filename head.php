<head>
    <base href="">
    <meta charset="utf-8"/>
    <meta name="pragma" content="no-cache" />
    <title>AxisPro | Dashboard</title>
    <meta name="description" content="Latest updates and statistic charts">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="Content-Type" content="text/html" charset="utf-8"/>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="bs-date-format" content="<?= dateformat('bsDatepicker') ?>">
    <meta name="moment-date-format" content="<?= getDateFormatForMomentJs() ?>">

    <!--begin::Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Asap+Condensed:500">

    <link rel="stylesheet" href="<?= asset(mix('plugins/global/plugins.bundle.css')) ?>">
    <link rel="stylesheet" href="<?= asset(mix('plugins/global/plugins-custom.bundle.css')) ?>">
    <link href="./assets/css/style.bundle.css?id=v1.0.1" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="<?= asset(mix('css/fa-overrides.css')) ?>">
    <!--end:: Vendor Plugins for custom pages -->

    <link rel="shortcut icon" href="./assets/media/logos/favicon.ico"/>

    <?php 
        if (isset($GLOBALS['__HEAD__']) && is_array($GLOBALS['__HEAD__'])) {
            foreach ($GLOBALS['__HEAD__'] as $val) {
                echo $val;
            }
        }
    ?>
    <style>

        .hidden_elem {
            display: none; !important;
        }


        .error_note {
            color: red !important;
        }

        .kt-iconbox {
            border: 1px solid #e0dbd6;


            -webkit-box-shadow: 0 10px 6px -6px #777 !important;
            -moz-box-shadow: 0 10px 6px -6px #777 !important;
            box-shadow: 0 8px 5px -6px #777 !important;

        }

        /*Disable Animation*/
        .kt-iconbox--animate-slow:after,.kt-iconbox--animate-fast:after {

            width: initial !important;
            height: initial !important;
            bottom: initial !important;
            left: initial !important;

        }



        #rep-form {
            margin-top: 10px;
        }

        .bigdrop {
            width: 600px !important;
        }

        #kt_header_brand {
            margin-left: 20px !important;
        }

        .kt-header .kt-header__bottom {
            margin-top: 0 !important;
        }

        .kt-header__brand-logo-default {
            width: 150px;
        }

        .kt-header-mobile__logo img {
            width: 75px;
        }

        .kt-portlet .kt-portlet__body {
            padding: 0 !important;
        }

        .kt-iconbox .kt-iconbox__body .kt-iconbox__desc .kt-iconbox__content {
            font-size: 11px !important;
            color: black;
        }

        .kt-subheader-custom .kt-container {
            padding: 0 !important;
        }

        #kt_header .kt-container  {

            padding: 0 !important;

        }

        .kt-iconbox .kt-iconbox__body .kt-iconbox__desc .kt-iconbox__title {
            font-size: 18px !important;
        }

        .kt-menu__link-text {
            color: #fff !important;

            font-family: Sans-Serif !important;
            font-size: 12px !important;
            font-weight: bold !important;

        }

        #kt_header_menu_wrapper {
            background: #1a2226;
        }

        .kt-menu__link {
            background: none !important;
        }

        #kt_header_menu {
            margin: 0 auto;
            position: relative;
        }

        .kt-menu__item--here {
            /*background: #c38f21;*/
            background: #009688;
        }

        .kt-menu__item {
            border-right: 1px solid #fff !important;
        }

        .kt-menu__item:first-child {
            border-left: 1px solid #fff !important;
        }







        .kt-iconbox {

            cursor: pointer !important;


        }
        .marquee-on-hover {
            -moz-animation: marquee 5s linear infinite;
            -webkit-animation: marquee 5s linear infinite;
            animation: marquee 5s linear infinite;

        }

        .marquee-on-hover-left-to-right {
            -moz-animation: marquee-left-to-right 5s linear infinite;
            -webkit-animation: marquee-left-to-right 5s linear infinite;
            animation: marquee-left-to-right 5s linear infinite;

        }

        .kt-iconbox__title {
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;

        }

        .kt-iconbox__desc {
            overflow: hidden;
        }


        @-moz-keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        @-webkit-keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        @keyframes marquee {
            0% {
                -moz-transform: translateX(100%);
                -webkit-transform: translateX(100%);
                transform: translateX(100%) }
            100% {
                -moz-transform: translateX(-100%);
                -webkit-transform: translateX(-100%);
                transform: translateX(-100%); }
        }




        @-moz-keyframes marquee-left-to-right {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        @-webkit-keyframes marquee-left-to-right {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        @keyframes marquee-left-to-right {
            0% {
                -moz-transform: translateX(-100%);
                -webkit-transform: translateX(-100%);
                transform: translateX(-100%) }
            100% {
                -moz-transform: translateX(100%);
                -webkit-transform: translateX(100%);
                transform: translateX(100%); }
        }




        html[dir="rtl"] .kt-iconbox__desc {
            text-align:right !important;
        }

        .kt-iconbox {
            padding: 8px !important;
        }

        .kt-iconbox .kt-iconbox__body .kt-iconbox__desc .kt-iconbox__title {
            font-size: 15px !important;
        }

        .flexbox {
            display: flex !important;
            justify-content:  space-between !important;
            flex-wrap: wrap !important;
        }

        .kt-container.kt-grid__item.kt-grid__item--fluid.kt-grid.kt-grid--hor.kt-grid--stretch {
            width: 100% !important;}

        #ajaxmark {
            z-index: 2000;
            padding: 5px;
            background: rgba(247, 241, 241, 0.75);
            -webkit-box-shadow: 3px 0px 19px 2000px rgb(247 241 241 / 75%);
            -moz-box-shadow: 3px 0px 19px 2000px rgba(247, 241, 241, 0.75);
            box-shadow: 3px 0px 19px 2000px rgb(247 241 241 / 75%);
            position: fixed;
            top: 35%;
            left: 53%;
            border-radius: 50px;
            filter: alpha(opacity=60);
            font-weight: bold;
        }

    </style>


</head>