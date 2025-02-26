<?php

use App\Http\Controllers\Accounting\DimensionController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Hr\DocumentsController;
use App\Http\Controllers\Hr\ProfilesController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\EntityGroupController;
use App\Http\Controllers\EntityGroupMemberController;
use App\Http\Controllers\Hr\AttendanceController;
use App\Http\Controllers\Hr\ShiftController;
use App\Http\Controllers\Sales\AutofetchController;
use App\Http\Controllers\Sales\CustomersController;
use App\Http\Controllers\Sales\TokenController;
use App\Http\Controllers\Sales\InvoiceController;
use App\Http\Controllers\Hr\DesignationController;
use App\Http\Controllers\Hr\EmpDocReleaseRequestsController;
use App\Http\Controllers\Hr\BulkEmployeeUploadController;
use App\Http\Controllers\Sales\Reports as SalesReport;
use App\Http\Controllers\Hr\Reports as HrReport;
use App\Http\Controllers\Labour\Reports as LabourReport;
use App\Http\Controllers\System\DashboardController;
use App\Http\Controllers\System\NotificationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\Vendor\LaravelWebSockets\ShowDashboard;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\Hr\DepartmentController;
use App\Http\Controllers\Hr\PayElementsController;
use App\Http\Controllers\Hr\CompanyController;
use App\Http\Controllers\Hr\GeneralRequestTypeController;
use App\Http\Controllers\Hr\GeneralRequestController;
use App\Http\Controllers\Labour\ContractController;
use App\Http\Controllers\Labour\LabourController;
use App\Http\Controllers\Labour\MaidReturnController;
use App\Http\Controllers\Hr\HolidayController;
use App\Http\Controllers\Hr\LeaveCarryForwardController;
use App\Http\Controllers\Labour\MaidReplacementController;
use App\Http\Controllers\Hr\EmpLeaveController;
use App\Http\Controllers\Hr\EmployeeController;
use App\Http\Controllers\Hr\EmployeePensionConfigController;
use App\Http\Controllers\Hr\PayslipController;
use App\Http\Controllers\Labour\InstallmentController;
use App\Http\Controllers\Sales\SalesOrderDetailsController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\Hr\EmployeeRewardsDeductionsController;
use App\Http\Controllers\System\AmcController;
use BeyondCode\LaravelWebSockets\Dashboard\Http\Middleware\Authorize as AuthorizeDashboard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Protected routes
Route::group(['middleware' => ['auth']], function() {
    Route::group(['prefix' => 'hr/employee/{employee}/profile', 'as' => 'employeeProfile.'], function() {
        Route::get('personal', [ProfilesController::class, 'personal'])->name('personal');
        Route::get('jobAndPay', [ProfilesController::class, 'jobAndPay'])->name('jobAndPay');
        Route::get('leaves', [ProfilesController::class, 'leaves'])->name('leaves');
        Route::post('leaves/{leaveType}/details', [ProfilesController::class, 'leaveDetails'])->name('leave.details');
        Route::get('documents', [ProfilesController::class, 'documents'])->name('documents');
        Route::match(['get', 'post'], 'shifts', [ProfilesController::class, 'shifts'])->name('shifts');
        Route::match(['get', 'post'], 'attendances', [ProfilesController::class, 'attendances'])->name('attendances');
        Route::match(['get', 'post'], 'punchings', [ProfilesController::class, 'punchings'])->name('punchings');
        Route::match(['get', 'post'], 'payslip', [ProfilesController::class, 'payslip'])->name('payslip');
    });

    Route::get('hr/employee/document/upload', [DocumentsController::class, 'upload'])->name('employeeDocument.upload');
    Route::get('hr/employee/document/manage', [DocumentsController::class, 'manage'])->name('employeeDocument.manage');
    Route::get('hr/employee/document/edit/{id}', [DocumentsController::class, 'edit'])->name('employeeDocument.edit');
    Route::post('hr/employee/document', [DocumentsController::class, 'store'])->name('employeeDocument.store');
    Route::put('hr/employee/document/update/{id}', [DocumentsController::class, 'update'])->name('employeeDocument.update');
    Route::delete('hr/employee/document/{id}', [DocumentsController::class, 'destroy'])->name('employeeDocument.destroy');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('userDashboard');

    Route::group(['prefix' => 'system/enity-group', 'as' => 'entityGroup.'], function() {
        Route::get('/', [EntityGroupController::class, 'index'])->name('index');
        Route::post('/', [EntityGroupController::class, 'store'])->name('store');
        Route::delete('/{entityGroup}', [EntityGroupController::class, 'destroy'])->name('destroy');
    });

    Route::get('sales/orders/details/', [SalesOrderDetailsController::class, 'index'])->name('sales.orders.details.index');
    
    Route::group(['prefix' => 'system/enity-group-members', 'as' => 'entityGroupMembers.'], function() {
        Route::get('/', [EntityGroupMemberController::class, 'index'])->name('index');
        Route::post('/', [EntityGroupMemberController::class, 'saveGroupMembers'])->name('update');
    });

    Route::get('/bulk-employee-upload', [BulkEmployeeUploadController::class, 'index'])->name('bulkEmployeeUpload.index');
    Route::post('/bulk-employee-upload', [BulkEmployeeUploadController::class, 'store'])->name('bulkEmployeeUpload.store');

    Route::group(['prefix' => 'system/workflow', 'as' => 'workflow.'], function() {
        Route::get('/', [WorkflowController::class, 'index'])->name('index');
        Route::get('/find', [WorkflowController::class, 'find'])->name('find');
        Route::post('/', [WorkflowController::class, 'store'])->name('store');
        Route::put('/{workflow}', [WorkflowController::class, 'update'])->name('update');
    });

    Route::group(['prefix' => 'system/tasks', 'as' => 'task.'], function() {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('/{transition}', [TaskController::class, 'show'])->name('show');
        Route::get('/view', [TaskController::class, 'view'])->name('view');
        Route::post('/{transition}/action/{action}', [TaskController::class, 'takeAction'])->name('takeAction');
        Route::get('/{task}/comments', [TaskCommentController::class, 'index'])->name('comments.index');
        Route::post('/transitions/{transition}/comments', [TaskCommentController::class, 'store'])->name('comments.store');
    });
 
    Route::group(['prefix' => '/hr'], function() {
        Route::resource('shifts', 'Hr\ShiftController')->except('show', 'create', 'edit');
        Route::resource('designations', 'Hr\DesignationController')->only('index', 'store', 'update', 'destroy');
        Route::resource('departments', 'Hr\DepartmentController')->only('index', 'store', 'update', 'destroy');
        Route::resource('companies', 'Hr\CompanyController')->only('index', 'store', 'update', 'destroy');
        Route::resource('holidays', 'Hr\HolidayController')->only('index', 'store', 'update', 'destroy');

        Route::group(['prefix' => 'employee-pension-config', 'as' => 'employeePensionConfig.'], function () {
            Route::get('/', [EmployeePensionConfigController::class, 'index'])->name('index');
            Route::post('/', [EmployeePensionConfigController::class, 'store'])->name('store');
            Route::match(['put', 'patch'], '/{employeePensionConfig}', [EmployeePensionConfigController::class, 'update'])->name('update');
            Route::delete('/{employeePensionConfig}', [EmployeePensionConfigController::class, 'destroy'])->name('destroy');
        });

        Route::resource('general-request-types', 'Hr\GeneralRequestTypeController')->only('index', 'store', 'update', 'destroy');
        Route::resource('general-requests', 'Hr\GeneralRequestController')->only('index', 'store');

        Route::group(['prefix' => 'leave-carry-forward', 'as' => 'leaveCarryForward.'], function () {
            Route::get('/', [LeaveCarryForwardController::class, 'index'])->name('index');
            Route::post('/', [LeaveCarryForwardController::class, 'store'])->name('store');
            Route::match(['put', 'patch'], '/{leaveCarryForward}', [LeaveCarryForwardController::class, 'update'])->name('update');
            Route::delete('/{leaveCarryForward}', [LeaveCarryForwardController::class, 'destroy'])->name('destroy');
        });

        Route::group(['prefix' => 'employee-rewards-deductions', 'as' => 'empRewardsDeductions.'], function () {
            Route::get('/', [EmployeeRewardsDeductionsController::class, 'index'])->name('index');
            Route::post('/', [EmployeeRewardsDeductionsController::class, 'store'])->name('store');
            Route::match(['put', 'patch'], '/{empRewardDeduction}', [EmployeeRewardsDeductionsController::class, 'update'])->name('update');
            Route::delete('/{empRewardDeduction}', [EmployeeRewardsDeductionsController::class, 'destroy'])->name('destroy');
        });

        Route::get('/issued-deduction-rewards', [EmployeeRewardsDeductionsController::class, 'getIssuedDeductionRewards'])->name('emp_reward_deductions.list');
        Route::get('/pay-elements', [PayElementsController::class, 'index'])->name('payElements.index');
        Route::post('/pay-elements', [PayElementsController::class, 'store'])->name('payElements.store');
        Route::match(['put', 'patch'], '/pay-elements/{payElement}', [PayElementsController::class, 'update'])->name('payElements.update');
        Route::delete('/pay-elements/{payElement}', [PayElementsController::class, 'destroy'])->name('payElements.destroy');

        Route::get('/release-document', [EmpDocReleaseRequestsController::class, 'create'])->name('hr.docReleaseRequest.create');
        Route::post('/release-document', [EmpDocReleaseRequestsController::class, 'store'])->name('hr.docReleaseRequest.store');

        Route::get('/shift-report', [HrReport\Shifts::class, 'index'])->name('shiftReport');
        Route::post('/shift-report/export', [HrReport\Shifts::class, 'export'])->name('exportShiftReport');

        Route::post('/attendance/update', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::get('/leave-report', [EmpLeaveController::class, 'index'])->name('leave_report.index');
        Route::post('/leave-report/export', [EmpLeaveController::class, 'export'])->name('leave_report.export');

        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');

        Route::get('/payrolls/{payroll}/payslip/{employee}/print', [PayslipController::class, 'print'])->name('payslip.print');
        Route::get('/leave-report-detail', [EmpLeaveController::class, 'detailReport'])->name('leave_report.detail');
    });

    Route::group(['prefix' => 'document-types', 'as' => 'documentTypes.'], function() {
        Route::get('/', [DocumentTypeController::class, 'index'])->name('index');
        Route::post('/', [DocumentTypeController::class, 'store'])->name('store');
        Route::put('update/{documentType}', [DocumentTypeController::class, 'update'])->name('update');
        Route::delete('destroy/{documentType}', [DocumentTypeController::class, 'destroy'])->name('destroy');
    });

    Route::get('dimensions/{dimension}', [DimensionController::class, 'find'])->name('dimension.find');

    // LaravelWebsockets routes override
    Route::group([
        'prefix' => config('websockets.path'),
        'middleware' => [AuthorizeDashboard::class]
    ], function() {
        Route::get('/', [ShowDashboard::class, '__invoke']);
    });

    Route::group(['prefix' => 'reports/sales', 'as' => 'reports.sales.'], function() {
        Route::get('/management-report', 'Sales\Reports\ManagementReport')->name('managementReport');

        Route::get('/invoices', [SalesReport\Invoices::class, 'index'])->name('invoices');
        Route::get('/invoice-payments', [SalesReport\InvoicePayments::class, 'index'])->name('invoicesPayments');
        Route::get('/service-transactions', [SalesReport\ServiceTransactions::class, 'index'])->name('serviceTransactions');
        Route::get('/services', [SalesReport\Services::class, 'index'])->name('services');
        Route::get('/voided-transactions', [SalesReport\VoidedTransactions::class, 'index'])->name('voidedTransactions');
        Route::get('/autofetch-transactions', [SalesReport\AutoFetchTransactions::class, 'index'])->name('autoFetchTransactions');
        Route::post('/autofetch-transactions/export', [SalesReport\AutoFetchTransactions::class, 'export'])->name('autoFetchTransactions.export');
        Route::delete('/autofetch-transactions/{AutofetchedTransaction}', [SalesReport\AutoFetchTransactions::class, 'destroy'])->name('autoFetchTransactions.destroy');
        
        Route::post('/invoices/export', [SalesReport\Invoices::class, 'export'])->name('exportInvoices');
        Route::post('/invoice-payments/export', [SalesReport\InvoicePayments::class, 'export'])->name('exportInvoicesPayments');
        Route::post('/services/export', [SalesReport\Services::class, 'export'])->name('exportServices');
        Route::post('/service-transactions/export', [SalesReport\ServiceTransactions::class, 'export'])->name('exportServiceTransactions');
        Route::post('/voided-transactions/export', [SalesReport\VoidedTransactions::class, 'export'])->name('exportVoidedTransactions');
    });

    Route::get('/download/{type}/{file}', [FileController::class, 'download'])->name('file.download')->where('file', '(.*)');
    Route::get('/view/{name}/{file}', [FileController::class, 'view'])->name('file.view')->where('file', '(.*)');

    Route::group(['prefix' => '/labours'], function() {
        Route::resource('agent', 'Labour\AgentController');
        Route::resource('labour','Labour\LabourController');
        Route::resource('contract','Labour\ContractController');
        Route::post('contract/{contract}/deliver-maid', [ContractController::class, 'deliverMaid'])->name('contract.deliverMaid');
        Route::get('contract/{contract}/print', [ContractController::class, 'print'])->name('contract.print');
        Route::get('labour/{labour}/cv', [LabourController::class, 'generateCv'])->name('labour.generateCv');
        Route::post('labour/reference/is-unique', [LabourController::class, 'isReferenceUnique'])->name('labour.reference.isUnique');
        Route::post('labour/check-availability', [LabourController::class, 'checkAvailability'])->name('labour.checkAvailability');
        Route::get('contracts/maid-return', [MaidReturnController::class, 'create'])->name('contract.maidReturn.create');
        Route::post('contracts/maid-return', [MaidReturnController::class, 'store'])->name('contract.maidReturn.store');
        Route::get('contracts/maid-replacement', [MaidReplacementController::class, 'create'])->name('contract.maidReplacement.create');
        Route::post('contracts/maid-replacement', [MaidReplacementController::class, 'store'])->name('contract.maidReplacement.store');
        Route::get('/maid-movements', [LabourReport\MaidMovements::class, 'index'])->name('labour.reports.maidMovements');
        Route::post('/maid-movements/export', [LabourReport\MaidMovements::class, 'export'])->name('labour.reports.maidMovements.export');
        Route::get('contracts/{contract}/installment', [InstallmentController::class, 'create'])->name('contract.installment.create');
        Route::post('contracts/{contract}/installment', [InstallmentController::class, 'store'])->name('contract.installment.store');
        Route::get('contracts/installment/details', [InstallmentController::class, 'details'])->name('contract.installment.details');
        Route::delete('contracts/installment/{installment}', [InstallmentController::class, 'destroy'])->name('contract.installment.destroy');
        Route::get('/installment-enquiry', [LabourReport\Installments::class, 'index'])->name('labour.reports.installmentReport');
        Route::post('/installment-enquiry/export', [LabourReport\Installments::class, 'export'])->name('labour.reports.installmentReport.export');
    });
});

// Protected web-api routes
Route::group(['prefix' => 'api', 'as' => 'api.', 'middleware' => ['auth']], function () {
    // Notification
    Route::get('user/notifications', [NotificationController::class, 'index'])->name('users.notifications');
    Route::get('user/unread-notifications', [NotificationController::class, 'unread'])->name('users.unreadNotifications');
    Route::post('user/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('users.readNotification');
    Route::post('user/notifications/{notification}/unread', [NotificationController::class, 'markAsUnread'])->name('users.unreadNotification');
    Route::post('user/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('users.readAllNotification');
    Route::delete('user/notifications/{notification}', [NotificationController::class, 'destroy'])->name('users.unreadNotification');

    Route::post('reference/{transType}/next', [ReferenceController::class, 'next'])->name('reference.next');
    
    // Shifts
    Route::get('hr/shifts/suggested-colors', [ShiftController::class, 'getSuggestedColors'])->name('shifts.suggestedColors');

    // Customers
    Route::group(['prefix' => 'hr', 'as' => 'hr.'], function() {
        Route::post('/salary-certificate-ref/{employee}', [ReferenceController::class, 'salaryCertificateRef'])->name('getSalaryCertificateReference');
        Route::post('/salary-transfer-letter-ref/{employee}', [ReferenceController::class, 'salaryTransferLetterRef'])->name('getSalaryTransferLetterReference');
    });

    // Customers
    Route::group(['prefix' => 'customers', 'as' => 'customers.'], function() {
        Route::get('/select2', [CustomersController::class, 'select2'])->name('select2');
        Route::get('/{customer}', [CustomersController::class, 'getCustomer'])->name('getCustomer');
        Route::get('/{customer}/commission-payable', [CustomersController::class, 'commissionPayable'])->name('commissionPayable');
    });

    // Labours
    Route::get('labours/select2', [LabourController::class, 'select2'])->name('labours.select2');

    // Contracts Select2
    Route::get('contract/maid-return/select2', [MaidReturnController::class, 'contractsSelect2'])->name('contract.maidReturn.select2');

    // Select2 for made replacement
    Route::get('contract/maid-replacement/contracts-select2', [MaidReplacementController::class, 'contractsSelect2'])->name('contract.maidReplacement.contractsSelect2');

    // Employees
    Route::get('employees/documents/exists', [DocumentsController::class, 'exists'])->name('employees.documents.exists');
    
    // Autofetch
    Route::get('autofetch/{systemId}/pending', [AutofetchController::class, 'pending'])->where(['systemId' => '[\pL\pM\pN_-]+'])->name('autofetch.pending');
    
    // Reception
    Route::post('reception/token', [TokenController::class, 'store'])->name('reception.createToken');

    // Amc Expiry
    Route::get('system/amc/expiry', [AmcController::class, 'getSystemExpiryDetails'])->name('system.amc.expiry');
    Route::post('system/amc/expiry/acknowledge', [AmcController::class, 'acknowledgeSystemExpiry'])->name('system.amc.expiry.acknowledge');

    // Sales
    Route::get('sales/invoice/by-reference/{reference}', [InvoiceController::class, 'findByReference'])->where(['reference' => '.*'])->name('sales.invoice.findByReference');
    Route::get('sales/orders/undelivered-items/select2', [SalesOrderDetailsController::class, 'undeliveredOrderItemsSelect2'])->name('sales.orders.undeliveredItems.select2');

    // Sales Reports
    Route::group(['prefix' => 'sales/reports', 'as' => 'sales.reports.'], function() {
        Route::get('todays-invoices', [SalesReport\Invoices::class, 'getTodaysInvoices'])->name('todaysInvoices');
        Route::get('todays-receipts', [SalesReport\Invoices::class, 'getTodaysReceipts'])->name('todaysReceipts');
        Route::get('category-group-wise-daily-sales', [SalesReport\CategoryGroupWiseReport::class, 'getDailyReport'])->name('categoryGroupWiseDailyReport');
        Route::get('category-group-wise-monthly-sales', [SalesReport\CategoryGroupWiseReport::class, 'getMonthlyReport'])->name('categoryGroupWiseMonthlyReport');
        Route::get('custom/bank-balances', [SalesReport\BankBalanceReportForManagement::class, 'get'])->name('bankBalanceReportForManagement');
        Route::get('department-wise-daily-collection', [SalesReport\DepartmentWiseCollection::class, 'dailyReport'])->name('departmentWiseDailyCollection');
        Route::get('department-wise-monthly-collection', [SalesReport\DepartmentWiseCollection::class, 'monthlyReport'])->name('departmentWiseMonthlyCollection');
        Route::get('customer-balance-inquiry', [SalesReport\CustomerBalanceInquiry::class, 'get'])->name('customerBalanceInquiry');
        Route::get('daily-collection-breakdown', [SalesReport\DailyCollectionBreakdown::class, 'get'])->name('dailyCollectionBreakdown');
    });

    Route::group(['prefix' => 'data-table', 'as' => 'dataTable.'], function() {
        Route::post('/invoices', [SalesReport\Invoices::class, 'dataTable'])->name('invoices');
        Route::post('/invoice-payments', [SalesReport\InvoicePayments::class, 'dataTable'])->name('invoicesPayments');
        Route::post('/services', [SalesReport\Services::class, 'dataTable'])->name('services');
        Route::post('/service-transactions', [SalesReport\ServiceTransactions::class, 'dataTable'])->name('serviceTransactions');
        Route::post('/voided-transactions', [SalesReport\VoidedTransactions::class, 'dataTable'])->name('voidedTransactions');
        Route::post('/autofetch-transactions', [SalesReport\AutoFetchTransactions::class, 'dataTable'])->name('autoFetchTransactions');
        Route::post('/tasks', [TaskController::class, 'dataTable'])->name('tasks');
        Route::post('/shifts', [ShiftController::class, 'dataTable'])->name('shifts');
        Route::post('/shift-report', [HrReport\Shifts::class, 'dataTable'])->name('shiftReport');
        Route::post('/designations', [DesignationController::class, 'dataTable'])->name('designations');
        Route::post('/departments', [DepartmentController::class, 'dataTable'])->name('departments');
        Route::post('/pay-elements', [PayElementsController::class, 'dataTable'])->name('payElements');
        Route::post('/companies', [CompanyController::class, 'dataTable'])->name('companies');
        Route::post('/document-types', [DocumentTypeController::class, 'dataTable'])->name('documentTypes');
        Route::post('/holidays', [HolidayController::class, 'dataTable'])->name('holidays');
        Route::post('/leave_carry_forward', [LeaveCarryForwardController::class, 'dataTable'])->name('leaveCarryForward');
        Route::post('/contracts', [ContractController::class, 'dataTable'])->name('contracts');
        Route::post('/labours', [LabourController::class, 'dataTable'])->name('labours');
        Route::post('/maid-movement-report', [LabourReport\MaidMovements::class, 'dataTable'])->name('maidMovements');
        Route::post('/installment-enquiry-report', [LabourReport\Installments::class, 'dataTable'])->name('installmentReport');
        Route::post('/employee-pension-config', [EmployeePensionConfigController::class, 'dataTable'])->name('employeePensionConfig');
        Route::post('/sales-order-details', [SalesOrderDetailsController::class, 'dataTable'])->name('salesOrderDetails');
        Route::post('/sales-order-details/expenses', [SalesOrderDetailsController::class, 'expensesDataTable'])->name('salesOrderDetails.expenses');
        Route::post('/empRewardsDeductions', [EmployeeRewardsDeductionsController::class, 'dataTable'])->name('empRewardsDeductions');
        Route::post('/general-request-type', [GeneralRequestTypeController::class, 'dataTable'])->name('generalRequestTypes');
        Route::post('/general-requests', [GeneralRequestController::class, 'dataTable'])->name('generalRequests');
    });
});