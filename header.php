<?php
$path_to_root = "ERP";
include_once("./ERP/includes/session.inc"); 
include_once("./ERP/includes/date_functions.inc");?>
<!DOCTYPE html>

<html lang="en"<?= $_SESSION['wa_current_user']->prefs->user_language == 'AR' ? ' dir="rtl"' : ''?>>

<!-- begin::Head -->

<?php include "head.php";?>

<!-- end::Head -->

<!-- begin::Body -->
<body
    id="kt_body"
    style="--kt-menubar-height:50px;--kt-menubar-height-tablet-and-mobile:50px"
    class="kt-page-content-white kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-page--loading header-fixed header-tablet-and-mobile-fixed menubar-enabled menubar-fixed">

<?= view('system.amc-notifications')->render() ?>

<!-- begin:: Page -->
<div class="kt-grid kt-grid--hor kt-grid--root">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
        <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper wrapper-custom" id="kt_wrapper">
            <!-- begin::Header -->
            <?= view('layout.header.main')->render() ?>
            <!-- end::Header -->

            <div class="content d-flex flex-column flex-column-fluid">
                <?= view('layout.header._menubar')->render() ?>
                <table class="w-100 tablestyle_noborder">
                    <tr>
                        <td class="text-center p-0">
                            <img
                                id="ajaxmark"
                                style="visibility:hidden"
                                src="<?= $path_to_root."/themes/".user_theme()."/images/ajax-loader.gif" ?>"
                                alt="ajaxmark">
                            <div class="ajax-blocker"></div>
                        </td>
                    </tr>
                </table>
