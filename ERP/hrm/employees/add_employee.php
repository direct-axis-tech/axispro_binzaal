<?php

use App\Models\Hr\EmployeePensionConfig;
use App\Models\EntityGroup;
use App\Models\EntityGroupCategory;
use App\Models\Hr\Company;

$path_to_root = "../..";
$page_security = 'HRM_ADD_EMPLOYEE'; 

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/db/shifts_db.php";
require_once $path_to_root . "/hrm/db/departments_db.php";
require_once $path_to_root . "/hrm/db/pay_elements_db.php";
require_once $path_to_root . "/hrm/db/employees_db.php";
require_once $path_to_root . "/hrm/db/banks_db.php";
require_once $path_to_root . "/hrm/db/countries_db.php";
require_once $path_to_root . "/hrm/db/designations_db.php";
require_once $path_to_root . "/hrm/db/emp_jobs_db.php";
require_once $path_to_root . "/hrm/db/emp_salaries_db.php";
require_once $path_to_root . "/hrm/db/emp_salary_details_db.php";
require_once $path_to_root . "/hrm/helpers/addEmployeeHelpers.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    AddEmployeeHelper::handleAddEmployeeRequest();
}

$weekdays = [
    ["abbr" => "Mon", "name" => "Monday"],
    ["abbr" => "Tue", "name" => "Tuesday"],
    ["abbr" => "Wed", "name" => "Wednesday"],
    ["abbr" => "Thu", "name" => "Thursday"],
    ["abbr" => "Fri", "name" => "Friday"],
    ["abbr" => "Sat", "name" => "Saturday"],
    ["abbr" => "Sun", "name" => "Sunday"]
];

$defaultWeekends = array_map(
    function ($weekend) use ($weekdays) {
        return $weekdays[$weekend - 1]["abbr"] ?? '';
    },
    explode(",", $GLOBALS['SysPrefs']->prefs['weekends'])
);
$companies = Company::all()->sortBy('name');
$visaCompanies = $companies->where('mol_id', '!=', '');
$defaultWorkHours = $GLOBALS['SysPrefs']->prefs['standard_workhours'];
$homeCountry = $GLOBALS['SysPrefs']->prefs['home_country'];
$flowGroups = EntityGroup::whereCategory(EntityGroupCategory::WORK_FLOW_RELATED)->get();
$pensionConfigs = EmployeePensionConfig::active()->get();

ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/parsley/parsley.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/hrm/css/add_employee.css" rel="stylesheet" type="text/css"/>
<style>
    img { max-width: 200px; max-height: 200px; }
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans('Add New Employee'), false, false, '', '', false, '', true); ?>

<div id="_content" class="row mx-5 bg-white border rounded text-dark">
    <!-- MultiStep Form -->
    <div class="border rounded mx-auto mb-4 col-lg-9 p-4">
        <form
            action=""
            method="POST"
            id="reg-form"
            data-parsley-validate>
            <!-- progressbar -->
            <ul id="progressbar">
                <li class="active">Personal Details</li>
                <li>ID Details</li>
                <li>Payment Details</li>
                <li>Job Details</li>
                <li>Salary Details</li>
            </ul>
            <div class="row stages">
                <div data-stage="0" class="stage col-lg-8 offset-lg-2">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title text-dark">Personal Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label for="profile_photo">
                                    <img id="image_preview" src="http://dummyimage.com/400x200/f5f5f5/000.jpg&text=Click+here+to+upload+your+image" alt="Select Employee Photo" />
                                </label>
                                <input name="profile_photo" id="profile_photo" type="file" accept="image/*" style="display: none;"  />
                            </div>
                            <div class="form-group row required">
                                <label for="emp_ref" class="col-form-label col-sm-3">Employee ID:</label>
                                <div class="col-auto">
                                    <input
                                        type="text"
                                        required
                                        data-parsley-remote="<?= erp_url('/ERP/API/hub.php?method=isEmployeeIdUnique') ?>"
                                        data-parsley-remote-message="This ID is already taken"
                                        data-parsley-trigger-after-failure="change"
                                        data-parsley-group="stage-0"
                                        data-parsley-type="alphanum"
                                        class="form-control"
                                        name="emp[emp_ref]"
                                        id="emp_ref"
                                        placeholder="001">
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="machine_id" class="col-form-label col-sm-3">Attendance ID:</label>
                                <div class="col-auto">
                                    <input
                                        required
                                        data-parsley-remote="<?= erp_url('/ERP/API/hub.php?method=isMachineIdUnique') ?>"
                                        data-parsley-remote-message="This ID is already taken"
                                        data-parsley-trigger-after-failure="change"
                                        data-parsley-group="stage-0"
                                        data-parsley-type="alphanum"
                                        type="text"
                                        class="form-control"
                                        name="emp[machine_id]"
                                        id="machine_id"
                                        placeholder="M001">
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="name" class="col-form-label col-sm-3">Full Name:</label>
                                <div class="col-sm-9 col-md-9 col-lg-6">
                                    <input
                                        type="text"
                                        required
                                        minlength="3"
                                        data-parsley-pattern="[a-zA-Z\ ]+"
                                        data-parsley-pattern-message="Only letters and space are allowed"
                                        data-parsley-group="stage-0"
                                        class="form-control"
                                        name="emp[name]"
                                        id="name"
                                        placeholder="Fulan AlFulani">
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="preferred_name" class="col-form-label col-sm-3">Preferred Name:</label>
                                <div class="col-sm-9 col-md-9 col-lg-6">
                                    <input
                                        type="text"
                                        required
                                        minlength="3"
                                        data-parsley-pattern="[a-zA-Z\ ]+"
                                        data-parsley-pattern-message="Only letters and space are allowed"
                                        data-parsley-group="stage-0"
                                        class="form-control"
                                        name="emp[preferred_name]"
                                        id="preferred_name"
                                        placeholder="Fulan">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="ar_name" class="col-form-label col-sm-3">Full Name (Arabic):</label>
                                <div class="col-sm-9 col-md-9 col-lg-6">
                                    <input
                                        type="text"
                                        minlength="3"
                                        data-parsley-group="stage-0"
                                        class="form-control"
                                        name="emp[ar_name]"
                                        id="ar_name"
                                        dir="rtl"
                                        placeholder="فلان الفلاني">
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="nationality" class="col-form-label col-sm-3">Nationality:</label>
                                <div class="col-sm-9">
                                    <select
                                        required
                                        data-home-country="<?= $homeCountry ?>"
                                        data-selection-css-class="validate"
                                        data-parsley-group="stage-0"
                                        name="emp[nationality]"
                                        id="nationality"
                                        class="custom-select">
                                        <option value="">-- Select Nationality --</option>
                                        <?php foreach (getCountriesKeyedByCode() as $code => $country): ?>
                                        <option value="<?= $code ?>"><?= $country['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label class="col-form-label col-sm-3">Gender:</label>
                                <div class="col-sm-9">
                                    <div class="form-check form-check-inline">
                                        <input
                                            required
                                            data-parsley-group="stage-0"
                                            class="form-check-input"
                                            type="radio"
                                            name="emp[gender]"
                                            id="gender-male"
                                            value="M">
                                        <label class="form-check-label" for="gender-male">Male</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input
                                            required
                                            data-parsley-group="stage-0"
                                            class="form-check-input"
                                            type="radio"
                                            name="emp[gender]"
                                            id="gender-female"
                                            value="F">
                                        <label class="form-check-label" for="gender-female">Female</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="date_of_birth" class="col-form-label col-sm-3">Date of Birth:</label>
                                <div class="col-auto">
                                    <input
                                        type="text"
                                        required
                                        data-parsley-trigger-after-failure="change"
                                        data-parsley-group="stage-0"
                                        class="form-control"
                                        name="emp[date_of_birth]"
                                        id="date_of_birth"
                                        data-provide="datepicker"
                                        data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                        data-date-autoclose="true"
                                        placeholder="<?= getDateFormatForBSDatepicker() ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="blood_group" class="col-form-label col-sm-3">Blood Group:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <select
                                        data-parsley-group="stage-0"
                                        class="custom-select"
                                        name="emp[blood_group]"
                                        id="blood_group">
                                        <option value="">-- Select Blood Group --</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="marital_status" class="col-form-label col-sm-3">Marital Status:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <select
                                        class="custom-select"
                                        data-parsley-group="stage-0"
                                        name="emp[marital_status]"
                                        id="marital_status">
                                        <option value="">-- Rather Not Say --</option>
                                        <option value="S">Single</option>
                                        <option value="M">Married</option>
                                        <option value="W">Widowed</option>
                                        <option value="D">Divorced</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="email" class="col-form-label col-sm-3">Email ID:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <input
                                        required
                                        data-parsley-group="stage-0"
                                        type="email"
                                        class="form-control"
                                        name="emp[email]"
                                        id="email"
                                        placeholder="fulan@company.org">
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="mobile_no" class="col-form-label col-sm-3">Mobile Number:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">+971</span>
                                        </div>
                                        <input
                                            type="number"
                                            data-parsley-group="stage-0"
                                            class="form-control"
                                            required
                                            data-parsley-pattern="(5[024568]|[1234679])\d{7}"
                                            data-parsley-pattern-message="This is not a valid UAE number"
                                            name="emp[mobile_no]"
                                            id="mobile_no"
                                            placeholder="50XXXX123">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div data-stage="1" class="stage col-lg-8 offset-lg-2">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title text-dark">ID Details</h2>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label for="passport_no" class="col-form-label col-sm-3">Passport Number:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <input
                                        type="text"
                                        data-parsley-required-if-not-select="nationality,<?= $homeCountry ?>"
                                        data-parsley-group="stage-1"
                                        data-parsley-type="alphanum"
                                        class="form-control"
                                        name="emp[passport_no]"
                                        id="passport_no"
                                        placeholder="AXXXXX123">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="emirates_id" class="col-form-label col-sm-3">Emirates ID:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <input
                                        type="text"
                                        class="form-control"
                                        name="emp[emirates_id]"
                                        id="emirates_id"
                                        data-parsley-group="stage-1"
                                        data-parsley-pattern="784-\d{4}-\d{7}-\d"
                                        data-parsley-pattern-message="This is not a valid emirates id"
                                        placeholder="784-1997-XXXXXXX-X">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="file_no" class="col-form-label col-sm-3">File Number:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <input
                                        type="text"
                                        class="form-control"
                                        name="emp[file_no]"
                                        id="file_no"
                                        data-parsley-group="stage-1"
                                        data-parsley-pattern="[1-7]0[12]/\d{4}/\d/?\d+"
                                        data-parsley-pattern-message="This is not a valid file number"
                                        placeholder="201/2020/XXXXXXX">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="labour_card_no" class="col-form-label col-sm-3">Labour Card No.:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <input
                                        type="text"
                                        data-parsley-group="stage-1"
                                        minlength="5"
                                        data-parsley-pattern="\d+"
                                        data-parsley-pattern-message="This is not a valid labour card number"
                                        class="form-control"
                                        name="emp[labour_card_no]"
                                        id="labour_card_no"
                                        placeholder="87XXXXX8">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="uid_no" class="col-form-label col-sm-3">Unified Number (UID No.):</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <input
                                        type="text"
                                        data-parsley-group="stage-1"
                                        minlength="5"
                                        data-parsley-pattern="\d+"
                                        data-parsley-pattern-message="This is not a valid unified number (UID number)"
                                        class="form-control"
                                        name="emp[uid_no]"
                                        id="uid_no"
                                        placeholder="12XXXXXX9">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div data-stage="2" class="stage col-lg-8 offset-lg-2">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title text-dark">Payment Details</h2>
                        </div>
                        <div class="card-body">
                            <div class="form-group row required">
                                <label for="mode_of_pay" class="col-form-label col-sm-3">Mode of Pay:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <select
                                        required
                                        data-parsley-group="stage-2"
                                        class="custom-select"
                                        name="emp[mode_of_pay]"
                                        id="mode_of_pay">
                                        <option value="">-- Select Mode Of Pay --</option>
                                        <option value="C">Cash</option>
                                        <option value="B">WPS Transfer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="bank_id" class="col-form-label col-sm-3">Bank:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <select
                                        data-parsley-required-if-select="mode_of_pay,B"
                                        data-parsley-group="stage-2"
                                        data-selection-css-class="validate"
                                        class="custom-select"
                                        name="emp[bank_id]"
                                        id="bank_id">
                                        <option value="">-- Select Bank --</option>
                                        <?php foreach(getBanksKeyedById() as $id => $bank): ?>
                                        <option value="<?= $id ?>"><?= $bank['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="branch_name" class="col-form-label col-sm-3">Branch Name:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <input
                                        type="text"
                                        class="form-control"
                                        name="emp[branch_name]"
                                        id="branch_name"
                                        data-parsley-pattern-message="This is not a valid Branch name"
                                        data-parsley-required-if-select="mode_of_pay,B"
                                        data-parsley-group="stage-2"
                                        placeholder="Sheikh Zayed Rd, Dubai">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="iban_no" class="col-form-label col-sm-3">IBAN Number:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <input
                                        type="text"
                                        class="form-control"
                                        name="emp[iban_no]"
                                        id="iban_no"
                                        data-parsley-pattern="(AE\d{21}|\d{23})"
                                        data-parsley-pattern-message="This is not a valid IBAN number"
                                        data-parsley-required-if-select="mode_of_pay,B"
                                        data-parsley-group="stage-2"
                                        placeholder="AEXXXXXXXXXXXXXXXXXXX21">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="personal_id_no" class="col-form-label col-sm-3">Personal ID Number:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <input
                                        data-parsley-pattern="\d{14}"
                                        data-parsley-pattern-message="This is not a valid Person ID number"
                                        data-parsley-required-if-select="mode_of_pay,B"
                                        data-parsley-group="stage-2"
                                        type="text"
                                        class="form-control"
                                        name="emp[personal_id_no]"
                                        id="personal_id_no"
                                        placeholder="00XXXXXXXXXX14">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div data-stage="3" class="stage col-lg-8 offset-lg-2">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title text-dark">Job Details</h2>
                        </div>
                        <div class="card-body">
                            <div class="form-group row required">
                                <label for="working_company_id" class="col-form-label col-sm-3">Working Company:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <select
                                        required
                                        data-parsley-group="stage-3"
                                        class="custom-select"
                                        name="job[working_company_id]"
                                        id="working_company_id">
                                        <option value="">-- Select Working Company --</option>
                                        <?php foreach ($companies as $wCompany): ?>
                                        <option value="<?= $wCompany->id ?>"><?= $wCompany->name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="visa_company_id" class="col-form-label col-sm-3">Visa Company:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <select
                                        required
                                        data-parsley-group="stage-3"
                                        class="custom-select"
                                        name="job[visa_company_id]"
                                        id="visa_company_id">
                                        <option value="">-- Select Visa Company --</option>
                                        <?php foreach ($visaCompanies as $vCompany): ?>
                                        <option value="<?= $vCompany->id ?>"><?= $vCompany->name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="department_id" class="col-form-label col-sm-3">Department:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <select
                                        required
                                        data-parsley-group="stage-3"
                                        class="custom-select"
                                        name="job[department_id]"
                                        id="department_id">
                                        <option value="">-- Select Department --</option>
                                        <?php foreach (getDepartmentsKeyedById() as $id => $department): ?>
                                        <option value="<?= $id ?>"><?= $department['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="designation_id" class="col-form-label col-sm-3">Designation:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <select
                                        class="custom-select"
                                        required
                                        data-parsley-group="stage-3"
                                        name="job[designation_id]"
                                        id="designation_id">
                                        <option value="">-- Select Designation --</option>
                                        <?php foreach (getDesignationsKeyedById() as $id => $designation): ?>
                                        <option value="<?= $id ?>"><?= $designation['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="commence_from" class="col-form-label col-sm-3">Commence From:</label>
                                <div class="col-auto">
                                    <input
                                        type="text"
                                        required
                                        data-parsley-group="stage-3"
                                        data-parsley-trigger-after-failure="change"
                                        class="form-control"
                                        name="job[commence_from]"
                                        id="commence_from"
                                        data-provide="datepicker"
                                        data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                        data-date-autoclose="true"
                                        data-date-today-highlight="true"
                                        placeholder="<?= getDateFormatForBSDatepicker() ?>">
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="week_offs" class="col-form-label col-sm-3">Weekly Offs:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <select
                                        required
                                        data-parsley-group="stage-3"
                                        class="custom-select"
                                        name="job[week_offs][]"
                                        id="week_offs"
                                        multiple>
                                        <?php foreach ($weekdays as $day): ?>
                                        <option <?= in_array($day['abbr'], $defaultWeekends) ? 'selected' : '' ?> 
                                            value="<?= $day['abbr'] ?>"><?= $day['name'] ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="work_hours" class="col-form-label col-sm-3">Work Hours:</label>
                                <div class="col-auto">
                                    <input
                                        type="number"
                                        required
                                        data-parsley-group="stage-3"
                                        class="form-control"
                                        name="job[work_hours]"
                                        id="work_hours"
                                        value="<?= $defaultWorkHours ?>">
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="attendance_type" class="col-form-label col-sm-3">Attendance Type:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <select 
                                        required
                                        data-parsley-group="stage-3"
                                        class="custom-select"
                                        name="job[attendance_type]"
                                        id="attendance_type" >
                                        <option value="">-- Select Attendance Type --</option>
                                        <?php foreach (get_employee_attendance_types() as $typeId => $types): ?>
                                        <option value="<?= $typeId ?>"><?= $types ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="default_shift_id" class="col-form-label col-sm-3">Shift Timing:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <select class="custom-select" name="job[default_shift_id]" id="default_shift_id">
                                        <option value="">-- Select Shift Timing --</option>
                                        <?php foreach (getShiftsKeyedById() as $id => $shift): ?>
                                        <option value="<?= $id ?>"><?= $shift['description'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="supervisor_id" class="col-form-label col-sm-3">Supervisor:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <select class="custom-select" name="job[supervisor_id][]" id="supervisor_id" multiple >
                                        <option value="">-- Select Supervisor --</option>
                                        <?php foreach (getEmployeesKeyedById() as $id => $emp): ?>
                                        <option value="<?= $id ?>"><?= $emp['emp_ref'] . " - " . $emp['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row ">
                                <label for="pension_scheme" class="col-form-label col-sm-3">Pension Scheme:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <select 
                                        data-selection-css-class="validate"
                                        data-parsley-group="stage-3"
                                        class="custom-select"
                                        name="job[pension_scheme]"
                                        id="pension_scheme" >
                                        <option value="">-- Select Pension Scheme --</option>
                                        <?php foreach ($pensionConfigs as $pension => $schemes): ?>
                                            <option value="<?= $schemes['id'] ?>"><?= $schemes['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-9 offset-sm-3 mb-2">
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            name="job[has_commission]"
                                            id="has_commission"
                                            class="form-check-input mt-0"
                                            checked="checked"
                                            value="1">
                                        <label for="has_commission" class="form-check-label pl-2">Has Commission</label>
                                    </div>
                                </div>
                                <div class="col-sm-9 offset-sm-3 mb-2">
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            name="job[has_pension]"
                                            id="has_pension"
                                            class="form-check-input mt-0"
                                            value="1">
                                        <label for="has_pension" class="pl-2 form-check-label">Has Pension</label>
                                    </div>
                                </div>
                                <div class="col-sm-9 offset-sm-3 mb-2">
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            name="job[has_overtime]"
                                            id="has_overtime"
                                            class="form-check-input mt-0"
                                            checked="checked"
                                            value="1">
                                        <label for="has_overtime" class="pl-2 form-check-label">Has Overtime</label>
                                    </div>
                                </div>
                                <div class="col-sm-9 offset-sm-3 mb-2">
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            name="job[require_attendance]"
                                            id="require_attendance"
                                            class="form-check-input mt-0"
                                            checked="checked"
                                            value="1">
                                        <label for="require_attendance" class="pl-2 form-check-label">Require Attendance Validation</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div data-stage="4" class="stage col-lg-8 offset-lg-2">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title text-dark">Salary Details</h2>
                        </div>
                        <div class="card-body">
                        <?php foreach(getPayElementsKeyedById(['is_fixed' => 1]) as $id => $payElem): ?>
                            <div class="form-group row required">
                                <label for="PEL-<?= $id ?>" class="col-form-label col-sm-3"><?= $payElem['name'] ?>:</label>
                                <div class="col-auto">
                                    <input
                                        required
                                        data-parsley-group="stage-4"
                                        data-pay-element="<?= $id ?>"
                                        data-type="<?= $payElem['type'] ?>"
                                        type="number"
                                        class="form-control"
                                        name="salary[<?= $id; ?>]"
                                        id="PEL-<?= $id ?>"
                                        min="0"
                                        value="0">
                                </div>
                            </div>
                        <?php endforeach; ?>
                            <div class="form-group row required">
                                <label for="gross_salary" class="col-form-label col-sm-3">Total Salary:</label>
                                <div class="col-auto">
                                    <input
                                        type="number"
                                        readonly
                                        class="form-control"
                                        name="gross_salary"
                                        id="gross_salary"
                                        data-parsley-group="stage-4"
                                        data-parsley-trigger-after-failure="change"
                                        data-parsley-min-message="Nobody works without salary!"
                                        required
                                        min="1"
                                        value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="control-area" class="w-100 text-center mt-4">
                <button type="button" id="previous" class="btn shadow-none btn-label-dark float-left"><< Previous</button>
                <div class="text-center mt-2">
                    <span class="step active"></span>
                    <span class="step"></span>
                    <span class="step"></span>
                    <span class="step"></span>
                    <span class="step"></span>
                </div>
                <button type="button" id="next" class="btn shadow-none btn-primary float-right">Next >></button>
            </div>
        </form>
    </div>
    <!-- /.MultiStep Form -->
</div>

<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/hrm/js/add_employee.js?id=v1.0.2" type="text/javascript"></script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();