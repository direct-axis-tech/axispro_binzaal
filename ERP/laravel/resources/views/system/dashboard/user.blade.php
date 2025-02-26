@php
    use App\Permissions as P;
@endphp

@extends('layout.app')

@section('title', 'Dashboard')

@section('page')
    <!--begin:ContentContainer-->
    <div class="container-fluid" id="system-user-dashboard">
        @if ($user->hasPermission(P::SA_DSH_FIND_INV))
        <div class="card mb-5" id="find-invoice-card">
            <div class="card-header">
                <div class="card-title">{{ __('Find invoice') }}</div>
            </div>
            <div class="card-body">
                <div class="form-group mb-5" data-parsley-form-group>
                    <input
                        type="text"
                        required
                        name="reference"
                        class="form-control"
                        placeholder="{{ __('Enter invoice number') }}">
                </div>
                <div class="form-group">
                    <button type="button" data-method="print"  class="btn btn-facebook">{{ __('Print Invoice') }}</button>
                    <button type="button" data-method="edit"  class="btn btn-primary">{{ __('Update Transaction ID') }}</button>
                </div>
            </div>
        </div>
        @endif

        @if ($user->hasPermission(P::SA_DSH_TODAYS_INV))
        <div class="card mb-5" id="todays-invoices-card">
            <div class="card-header">
                <div class="card-title">{{ __("Today's invoices") }}</div>
            </div>
            <div class="card-body">
                <table
                    class="table table-striped table-row-bordered g-3 text-nowrap thead-strong"
                    data-control="dataTable">
                    <thead>
                        <th>{{__('Reference')}}</th>
                        <th>{{__('Token Number')}}</th>
                        <th>{{__('Customer Name')}}</th>
                        <th>{{__('Display Customer')}}</th>
                        <th>{{__('Amount')}}</th>
                        <th>{{__('Payment Status')}}</th>
                        <th>{{__('Payment Method')}}</th>
                        <th>{{__('Employee')}}</th>
                        <th>{{__('Transaction Status')}}</th>
                    </thead>
                </table>
            </div>
        </div>
        @endif
            @if ($user->hasPermission(P::SA_DSH_TODAYS_REC))

                <div class="card mb-5" id="todays-receipts-card">
                    <div class="card-header">
                        <div class="card-title">{{ __("Today's Receipts") }}</div>
                    </div>
                    <div class="card-body">
                        <table
                            class="table table-striped table-row-bordered g-3 text-nowrap thead-strong"
                            data-control="dataTable">
                            <thead>
                            <th>{{__('Reference')}}</th>
                            <th>{{__('Customer Name')}}</th>
                            <th>{{__('Amount')}}</th>
                            <th>{{__('Employee')}}</th>
                            </thead>
                        </table>
                    </div>
                </div>
            @endif

        @if ($user->hasAnyPermission([
            P::SA_DSH_TRANS,
            P::SA_DSH_TRANS_ACC,
            P::SA_DSH_BNK_AC,
            P::SA_DSH_COLL_BD,
            P::SA_DHS_CUST_BAL,
            P::SA_DHS_DEP_SALES,
            P::SA_DSH_DEP_SALES_MNTH
        ]))
        <div class="row mt-10 mb-5" id="date-filter">
            <div class="col-auto">
                <div class="form-group form-group-sm row">
                    <label class="col-auto col-form-label col-form-label-sm">{{ __('Date') }}:</label>
                    <div class="col-auto">
                        <input
                            type="text"
                            name="date"
                            id="date"
                            class="form-control form-control-sm"
                            placeholder="Select date"
                            data-date-format="{{ getBSDatepickerDateFormat() }}"
                            value="{{ $date }}" >
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-primary">Get Report</button>
            </div>
        </div>
        @else
            <input style='display:none' name="date" id="date" data-date-format="{{ getBSDatepickerDateFormat() }}" value="{{ $date }}" >
        @endif

        @if ($user->hasPermission(P::SA_DSH_TRANS))
        <div class="card mb-5" id="category-group-wise-daily-sales-card">
            <div class="card-header">
                <div class="card-title d-block">
                    <span class="text-nowrap">Today Transactions</span>&nbsp;
                    <span class="text-nowrap" lang="ar">معاملات اليوم</span>
                </div>
            </div>
            <div class="card-body">
                <table
                    class="table table-striped table-row-bordered g-3 text-nowrap thead-strong tfoot-strong text-end"
                    data-control="dataTable">
                    <thead>
                        <tr>
                            <th>
                                Department<br><span lang="ar">الادارة</span>
                            </th>
                            <th>
                                No. of Trans.<br><span lang="ar">عدد المعاملات</span>
                            </th>
                            <th>
                                Gov. Fees<br><span lang="ar">المصاريف الحكومية</span>
                            </th>
                            <th>
                                Service Charge<br><span lang="ar">قيمة خدمات المركز</span>
                            </th>
                            <th>
                                Credit Facility<br><span lang="ar">دفع أجل</span>
                            </th>
                            <th>
                                Discount<br><span lang="ar">خصم</span>
                            </th>
                            <th>
                                VAT<br><span lang="ar">الضريبة</span>
                            </th>
                            <th>
                                Total Collection<br><span lang="ar">اجمالي المبلغ المتحصلة</span>
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        @endif

        @if ($user->hasPermission(P::SA_DSH_TRANS_ACC))
        <div class="card mb-5" id="category-group-wise-monthly-sales-card">
            <div class="card-header">
                <div class="card-title d-block">
                    <span class="text-nowrap">Accumulated Transactions - {{ $monthName }}</span>&nbsp;
                    <span class="text-nowrap" lang="ar">اجمالي المعاملات</span>
                </div>
            </div>
            <div class="card-body">
                <table
                    class="table table-striped table-row-bordered g-3 text-nowrap thead-strong tfoot-strong text-end"
                    data-control="dataTable">
                    <thead>
                        <tr>
                            <th>
                                Department<br><span lang="ar">الادارة</span>
                            </th>
                            <th>
                                No. of Trans.<br><span lang="ar">عدد المعاملات</span>
                            </th>
                            <th>
                                Service Charge<br><span lang="ar">قيمة خدمات المركز</span>
                            </th>
                            <th>
                                Total Collection<br><span lang="ar">اجمالي المبلغ المتحصلة</span>
                            </th>
                            <th>
                                Credit Facility<br><span lang="ar">دفع أجل</span>
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        @endif

        @if ($user->hasPermission(P::SA_DSH_BNK_AC))
        <div class="card mb-5" id="bank-transactions-card">
            <div class="card-header">
                <div class="card-title d-block">
                    <span class="text-nowrap">Bank Accounts</span> &nbsp;
                    <span class="text-nowrap" lang="ar">حسابات البنوك</span>
                </div>
            </div>
            <div class="card-body">
                <table
                    class="table table-striped table-row-bordered g-3 text-nowrap thead-strong tfoot-strong text-end"
                    data-control="dataTable">
                    <thead>
                        <tr>
                            <th>Account Name<br><span lang="ar">اسماء الحسابات</span></th>
                            <th>Today Opening Balance<br><span lang="ar">الرصيد الافتتاحي اليوم</span></th>
                            <th>Today Deposits<br><span lang="ar">الايداعات اليوم</span></th>
                            <th>Today Transactions<br><span lang="ar">معاملات اليوم</span></th>
                            <th>Available Balance<br><span lang="ar">الرصيد المتوفر</span></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        @endif

        @if ($user->hasPermission(P::SA_DHS_DEP_SALES))
        <div class="card mb-5" id="department-wise-daily-sales-card">
            <div class="card-header">
                <div class="card-title d-block">
                    <span class="text-nowrap">Today's Sales (Department Wise)</span>&nbsp;
                    <span class="text-nowrap" lang="ar">مبيعات اليوم (صافي دخل الإدارات)</span><br>
                    <small class="text-muted">{{ $date }}</small>
                </div>
            </div>
            <div class="card-body">
                <table
                    class="table table-striped table-row-bordered g-3 text-nowrap thead-strong tfoot-strong text-end"
                    data-control="dataTable">
                    <thead>
                        <tr>
                            <th>Department  <br><span lang="ar">الادارة</span></th>
                            <th>No. of Trans.<br><span lang="ar">عدد المعاملات</span></th>
                            <th>Invoice Total<br><span lang="ar">إجمالي الفاتورة</span></th>
                            <th>Credit Invoices<br><span lang="ar">فواتير الائتمان</span></th>
                            <th>Discount<br><span lang="ar">خصم</span></th>
                            <th>VAT<br><span lang="ar">الضريبة</span></th>
                            <th>Gov. Fees <br><span lang="ar">المصاريف الحكومية</span></th>
                            <th>Center's Benefits<br><span lang="ar">فوائد المركز</span></th>
                            <th>Employee Commission<br><span lang="ar">عمولة الموظف</span></th>
                            <th>Net - Center's Benefits<br><span lang="ar">صافي - فائدة المركز</span></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        @endif

        @if ($user->hasPermission(P::SA_DSH_DEP_SALES_MNTH))
        <div class="card mb-5" id="department-wise-monthly-sales-card">
            <div class="card-header">
                <div class="card-title d-block">
                    <span class="text-nowrap">Monthly Sales (Department Wise)</span>&nbsp;
                    <span class="text-nowrap" lang="ar">مبيعات الشهرية (صافي دخل الإدارات)</span><br>
                    <span class="text-small text-muted">{{ $monthName }}</span>
                </div>
            </div>
            <div class="card-body">
                <table
                    class="table table-striped table-row-bordered g-3 text-nowrap thead-strong tfoot-strong text-end"
                    data-control="dataTable">
                    <thead>
                        <tr>
                            <th>Department  <br><span lang="ar">الادارة</span></th>
                            <th>No. of Trans.<br><span lang="ar">عدد المعاملات</span></th>
                            <th>Invoice Total<br><span lang="ar">إجمالي الفاتورة</span></th>
                            <th>Credit Invoices<br><span lang="ar">فواتير الائتمان</span></th>
                            <th>Discount<br><span lang="ar">خصم</span></th>
                            <th>VAT<br><span lang="ar">الضريبة</span></th>
                            <th>Gov. Fees <br><span lang="ar">المصاريف الحكومية</span></th>
                            <th>Center's Benefits<br><span lang="ar">فوائد المركز</span></th>
                            <th>Employee Commission<br><span lang="ar">عمولة الموظف</span></th>
                            <th>Other Expense<br><span lang="ar">مصروف العامة</span></th>
                            <th>Esitimated Expense<br><span lang="ar">المصاريف العامة المقدرة</span></th>
                            <th>Net - Center's Benefits<br><span lang="ar">صافي - فائدة المركز</span></th>
                            <th>Estimated<br>Net - Center's Benefits<br><span lang="ar">المقدرة الصافية - فائدة المركز</span></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        @endif

        @if ($user->hasPermission(P::SA_DHS_CUST_BAL))
        <div class="card mb-5" id="customer-balances-card">
            <div class="card-header">
                <div class="card-title d-block">
                    <span class="text-nowrap">Customer Balances</span>&nbsp;
                    <span class="text-nowrap" lang="ar">أرصدة العملاء</span>
                </div>
            </div>
            <div class="card-body">
                <table
                    class="table table-striped table-row-bordered g-3 text-nowrap thead-strong tfoot-strong text-end"
                    data-control="dataTable">
                    <thead>
                        <tr>
                            <th>Customer Name<br><span lang="ar">اسم العميل</span></th>
                            <th>Salesman<br><span lang="ar">بائع</span></th>
                            <th>Last Invoice Date<br><span lang="ar">تاريخ الفاتورة الأخيرة</span></th>
                            <th>Last Payment Date<br><span lang="ar">تاريخ الدفع الأخير</span></th>
                            <th>Today's Opening Balance<br><span lang="ar">الرصيد الافتتاحي اليوم</span></th>
                            <th>Today's Transactions<br><span lang="ar">معاملات اليوم</span></th>
                            <th>Today's Payment<br><span lang="ar">مدفوعات اليوم</span></th>
                            <th>Balance<br><span lang="ar">الرصيد</span></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        @endif

        <div class="row g-3">
            <div class="col-lg-6">
                @if ($user->hasPermission(P::SA_DSH_COLL_BD))
                <div class="card mb-5" id="daily-collection-breakdown-card">
                    <div class="card-header">
                        <div class="card-title d-block">
                            <span class="text-nowrap">Today Collection Breakdown</span>&nbsp;
                            <span class="text-nowrap" lang="ar">تفاصيل التحصيل اليوم</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <table
                            class="table table-striped table-row-bordered g-3 text-nowrap thead-strong tfoot-strong text-end"
                            data-control="dataTable">
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    <!--end:ContentContainer-->
@endsection
