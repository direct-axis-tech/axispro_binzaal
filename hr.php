<?php

use App\Permissions as P;
?>
<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- Begin: Manage Section-->
            <div class="w-100 p-3">
                <div class="kt-subheader kt-subheader-custom   kt-grid__item">
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('MANAGE') ?></h3>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?= createMenuTile(
                        [
                            P::HRM_VIEWTIMESHEET_OWN,
                            P::HRM_VIEWTIMESHEET_DEP,
                            P::HRM_VIEWTIMESHEET_ALL
                        ],
                        trans('Attendance Sheet'),
                        trans('Attendance Sheet'),
                        getRoute('timesheet'),
                        'fa fa-calendar-alt'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_PAYROLL,
                        trans('Payroll'),
                        trans('Process payroll'),
                        getRoute('process_payroll'),
                        'fa fa-money-check-alt'
                    ) ?>

                    <?= createMenuTile(
                        [
                            P::HRM_ADDSHIFT_DEP,
                            P::HRM_ADDSHIFT_ALL
                        ],
                        trans('Shifts'),
                        trans("Assign/Update Employee's Shifts"),
                        getRoute('shifts'),
                        'fa fa-user-clock'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_ADD_EMPLOYEE,
                        trans('New Employee'),
                        trans("Add New Employee"),
                        getRoute('add_employee'),
                        'fa fa-user-plus'
                    ) ?>
                    
                    <?= createMenuTile(
                        P::HRM_EDIT_EMPLOYEE,
                        trans('Modify Employee'),
                        trans("Modify Basic Employee Details"),
                        getRoute('edit_employee'),
                        'fa fa-user-edit'
                    ) ?>
                    
                    <?= createMenuTile(
                        [
                            P::HRM_ADD_EMP_LEAVE,
                            P::HRM_ADD_EMP_LEAVE_DEP,
                            P::HRM_ADD_EMP_LEAVE_ALL
                        ],
                        trans('Add|Request Leave'),
                        trans("Add|Request an employee's leave"),
                        getRoute('add_employee_leaves'),
                        'fa fa-notes-medical'
                    ) ?>
                    
                    <?= createMenuTile(
                        P::HRM_EMP_SALARY,
                        trans('Increment/Decrement Employee Salary'),
                        trans("Increment or Decrement an Employee's Salary"),
                        getRoute('add_emp_salary_update'),
                        'fa fa-hand-holding-usd'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_JOB_UPDATE,
                        trans('Add Employee Job Update'),
                        trans("Update employee's job details"),
                        getRoute('add_emp_job_update'),
                        'fa fa-diagnoses'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_ADD_EMP_CANCELATION,
                        trans('Employee Cancelation'),
                        trans('Add Employee Cancelation'),
                        getRoute('add_emp_cancelation'),
                        'fa fa-user-times'
                    ) ?>
                    
                    <?= createMenuTile(
                        P::HRM_HOLD_EMP_SALARY,
                        trans('Hold Salary'),
                        trans('Hold Employee Salary'),
                        getRoute('hold_emp_salary'),
                        'fa fa-money-check-alt'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_HOLDED_EMP_SALARY,
                        trans('Release Holded Salary'),
                        trans('View Holded Employee Salary'),
                        getRoute('holded_emp_salary'),
                        'fa fa-money-check-alt'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_UPLOAD_DOC,
                        trans('Upload Employee Document'),
                        trans('Upload Employee Document'),
                        route('employeeDocument.upload'),
                        'fa fa-cloud-upload-alt'
                    ) ?>

                    <?= createMenuTile(
                        [
                            P::HRM_DOC_RELEASE_REQ,
                            P::HRM_DOC_RELEASE_REQ_ALL
                        ],
                        trans('Passport Release Request'),
                        trans("Request for release of passport"),
                        route('hr.docReleaseRequest.create'),
                        'fa-solid fa-passport'
                    ) ?>
                    <?= createMenuTile(
                        P::HRM_BULK_EMPLOYEE_UPLOAD,
                        trans('Bulk Employee Upload'),
                        trans("Upload employees in bulk from excel/csv"),
                        route('bulkEmployeeUpload.index'), // Use the new route name here
                        'fa fa-users'
                    ) ?>
                    
                    <?= createMenuTile(
                        P::HRM_MANAGE_LEAVE_ADJUSTMENT,
                        trans('Leave Adjustment'),
                        trans('Leave Adjustment'),
                        getRoute('leave_adjustment'),
                        'fa fa-notes-medical'
                    ) ?>

                    <?= createMenuTile(
                        [
                            P::HRM_TIMEOUT_REQUEST,
                            P::HRM_TIMEOUT_REQUEST_ALL
                        ],
                        trans('Personal Timeouts'),
                        trans("Request For Employee Personal Timeouts"),
                        getRoute('employee_personal_timeout'),
                        'fa-history'
                    ) ?>

                    <?= createMenuTile(
                        [
                            P::HRM_MANAGE_DEDUCTION,
                            P::HRM_MANAGE_REWARDS,
                            P::HRM_MANAGE_DEDUCTION_ADMIN,
                            P::HRM_MANAGE_REWARDS_ADMIN
                        ],
                        trans('Deduction / Rewards'),
                        trans('Manage Employees Deduction / Rewards'),
                        route('empRewardsDeductions.index'),
                        'fa fa-users-cog'
                    ) ?>

                    <?= createMenuTile(
                        [
                            P::HRM_MANAGE_GENERAL_REQUEST,
                            P::HRM_MANAGE_GENERAL_REQUEST_ALL,
                        ],
                        trans('General Request'),
                        trans('Apply General Requests'),
                        route('general-requests.index'),
                        'fa fa-headset'
                    ) ?>
                </div>
            </div>
            <!-- End: Manage Section-->

            <!-- Begin: Inquiry Section-->
            <div class="w-100 p-3">
                <div class="kt-subheader kt-subheader-custom   kt-grid__item">
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('Inquiry') ?></h3>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?= createMenuTile(
                        [
                            P::HRM_VIEWATDMETRICS_OWN,
                            P::HRM_VIEWATDMETRICS_DEP,
                            P::HRM_VIEWATDMETRICS_ALL
                        ],
                        trans('Attendance Metrics Analysis'),
                        trans("Late coming | Early leaving | Overtime"),
                        getRoute('attendance_metrics'),
                        'fa fa-tasks'
                    ) ?>

                    <?= createMenuTile(
                        [
                            P::HRM_VIEWEMPLOYEES,
                            P::HRM_VIEWEMPLOYEES_DEP,
                            P::HRM_VIEWEMPLOYEES_ALL
                        ],
                        trans('View Employees'),
                        trans('View All Employees'),
                        route('employees.index'),
                        'fa fa-users'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_VIEWPAYSLIP,
                        trans('Payslip'),
                        trans('View/Print Employee Payslip'),
                        getRoute('view_payslip'),
                        'fa fa-money-check-alt'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_VIEW_END_OF_SERVICE,
                        trans('End of Service'),
                        trans('View End of Service Report'),
                        getRoute('end_of_service_report'),
                        'fa fa-address-book'
                    ) ?>
                    
                    <?= createMenuTile(
                        P::HRM_SALARY_CERTIFICATE,
                        trans('Salary Certificate'),
                        trans('For opening Bank account'),
                        getRoute('salary_certificate'),
                        'fa-credit-card'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_SALARY_TRANSFER_LETTER,
                        trans('Salary Transfer Letter'),
                        trans('Salary Transfer Letter'),
                        getRoute('salary_transfer_letter'),
                        'fa-credit-card'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_EMPLOYEE_DOCUMENT_VIEW,
                        trans('Employee Document View'),
                        trans('Employee Document View'),
                        getRoute('employee_document_view'),
                        'fa-credit-card'
                    ) ?>

                    <?= createMenuTile(
                        [
                            P::HRM_EMPLOYEE_SHIFT_VIEW_OWN,
                            P::HRM_EMPLOYEE_SHIFT_VIEW_DEP,
                            P::HRM_EMPLOYEE_SHIFT_VIEW_ALL
                        ],
                        trans('Employee Shift Report'),
                        trans('Employee Shift Report'),
                        route('shiftReport'),
                        'fa-calendar-alt'
                    ) ?>

                    <?= createMenuTile(
                        [
                            P::HRM_MANAGE_DOC,
                            P::HRM_MANAGE_DOC_OWN
                        ],
                        trans('Manage Employee Documents'),
                        trans('Manage Employee Document'),
                        route('employeeDocument.manage'),
                        'fa fa-money-check-alt'
                    ) ?>

                    <?= createMenuTile(
                        [
                            P::HRM_EMPLOYEE_LEAVE_REPORT,
                            P::HRM_EMPLOYEE_LEAVE_REPORT_DEP,
                            P::HRM_EMPLOYEE_LEAVE_REPORT_OWN
                        ],
                        trans('Leave Report'),
                        trans('Employee Leave Report'),
                        route('leave_report.index'),
                        'fa-tasks'
                    ) ?>

                    <?= createMenuTile(
                        [
                            P::HRM_EMP_DEDUCTION_REWARD,
                            P::HRM_EMP_DEDUCTION_REWARD_OWN
                        ],
                        trans('Deduction / Rewards'),
                        trans('Issued Deduction / Rewards'),
                        route('emp_reward_deductions.list'),
                        'fa-credit-card'
                    ) ?>

                    <?= createMenuTile(
                        [
                            P::HRM_EMP_LEAVE_DETAIL_REPORT,
                            P::HRM_EMP_LEAVE_DETAIL_REPORT_OWN,
                            P::HRM_EMP_LEAVE_DETAIL_REPORT_DEP
                        ],
                        trans('Leave Detail Report'),
                        trans('Employees Leave Detail Report'),
                        route('leave_report.detail'),
                        'fa-tasks'
                    ) ?>

                </div>
            </div>
            <!-- End: Inquiry Section-->

            <!-- Begin: Configuration Section-->
            <div class="w-100 p-3">
                <div class="kt-subheader kt-subheader-custom   kt-grid__item">
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('Masters') ?></h3>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?= createMenuTile(
                        P::HRM_MANAGE_SHIFT,
                        trans('Add/Manage Shift'),
                        trans("Add or manage shifts"),
                        route('shifts.index'),
                        'fa fa-business-time'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_MANAGE_DESIGNATION,
                        trans('Designation'),
                        trans('Designation'),
                        route('designations.index'),
                        'fas fa-address-card'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_MANAGE_DEPARTMENT,
                        trans('Department'),
                        trans('Department'),
                        route('departments.index'),
                        'fas fa-sitemap'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_MANAGE_COMPANY,
                        trans('Company'),
                        trans('Company'),
                        route('companies.index'),
                        'fas fa-suitcase'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_MANAGE_PAY_ELEMENTS,
                        trans('Pay Elements'),
                        trans('Pay Elements'),
                        route('payElements.index'),
                        'fa-credit-card'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_MANAGE_HOLIDAY,
                        trans('Public Holidays'),
                        trans('Public Holidays'),
                        route('holidays.index'),
                        'fa-calendar-alt'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_MANAGE_LEAVE_CARRY_FORWARD,
                        trans('Leave Carry Forward'),
                        trans('Set Leave Carry Forward Limit'),
                        route('leaveCarryForward.index'),
                        'fa-hourglass-start'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_MANAGE_PENSION_CONFIG,
                        trans('GPSSA Configuration'),
                        trans('Employees Pension Configuration'),
                        route('employeePensionConfig.index'),
                        'fa-medal'
                    ) ?>

                    <?= createMenuTile(
                        P::HRM_MANAGE_GENERAL_REQUEST_TYPE,
                        trans('General Request Types'),
                        trans('Manage General Request Types'),
                        route('general-request-types.index'),
                        'fa-map-signs'
                    ) ?>

                </div>
            </div>
            <!-- End: Configuration Section-->

        </div>
    </div>
</div>