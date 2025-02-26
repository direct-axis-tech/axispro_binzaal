<?php

use App\Models\EntityGroup;
use App\Models\EntityGroupCategory;
use Illuminate\Support\Facades\Storage;

$path_to_root = "../..";
$page_security = 'HRM_EDIT_EMPLOYEE'; 

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/hrm/db/shifts_db.php";
require_once $path_to_root . "/hrm/db/employees_db.php";
require_once $path_to_root . "/hrm/db/emp_jobs_db.php";
require_once $path_to_root . "/hrm/db/banks_db.php";
require_once $path_to_root . "/hrm/db/countries_db.php";
require_once $path_to_root . "/hrm/helpers/editEmployeeHelpers.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    EditEmployeeHelper::handleUpdateEmployeeRequest();
}

$editingEmployeeId = (!empty($_GET['id']) && preg_match('/^\d{1,15}$/', $_GET['id'])) ? $_GET['id'] : null;
$employee = $editingEmployeeId ? getEmployee($editingEmployeeId) : [];
$week_offs = json_decode($employee['week_offs'] ?? "[]", true);
$supervisor_ids = json_decode($employee['supervisor_id'] ?? "[]", true);

$weekdays = [
    ["abbr" => "Mon", "name" => "Monday"],
    ["abbr" => "Tue", "name" => "Tuesday"],
    ["abbr" => "Wed", "name" => "Wednesday"],
    ["abbr" => "Thu", "name" => "Thursday"],
    ["abbr" => "Fri", "name" => "Friday"],
    ["abbr" => "Sat", "name" => "Saturday"],
    ["abbr" => "Sun", "name" => "Sunday"]
];

$bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$maritalStatuses = [
    "S" => "Single",
    "M" => "Married",
    "W" => "Widowed",
    "D" => "Divorced"
];
$paymentModes = [
    "C" => "Cash",
    "B" => "WPS Transfer"
];

$homeCountry = $GLOBALS['SysPrefs']->prefs['home_country'];
$flowGroups = EntityGroup::whereCategory(EntityGroupCategory::WORK_FLOW_RELATED)->get();
ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/parsley/parsley.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.css" rel="stylesheet" type="text/css"/>
<style>
    .is-valid .form-control,
    .is-valid .custom-select,
    .is-valid .validate {
        border-color: #28a745;
    }

    .is-invalid .form-control,
    .is-invalid .custom-select,
    .is-invalid .validate {
        border-color: #dc3545;
    }

    .btn-action {
        font-size: 1.5rem;
    }

    img { max-width: 200px; max-height: 200px; }
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans('Update Employee'), false, false, '', '', false, '', true); ?>

<div id="_content" class="mx-5 bg-white border rounded text-dark p-3">
    <h1 class="p-4">Modify Basic Employee Details</h1>

    <form
        action=""
        method="GET"
        id="filter-form"
        data-parsley-validate
        class="form-inline px-4">
        <div class="form-group">
            <label for="edit-employee" class="sr-only">Select Employee</label>
            <select name="id" class="custom-select" id="edit-employee">
                <option value="">-- Select Employee --</option>
                <?php foreach (getEmployeesKeyedById() as $empId => $emp): ?>
                <option <?= $editingEmployeeId == $empId ? 'selected' : '' ?>
                    value="<?= $empId ?>"
                    ><?= $emp['formatted_name'] ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary my-1 mx-3 shadow-none">Submit</button>
    </form>

    <?php if (!empty($employee)): ?>
    <hr>
    <form
        action=""
        method="POST"
        id="update-form">
        <div class="row p-3">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title text-dark">Personal Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="profile_photo">
                                <img id="image_preview" src="<?= ($employee['profile_photo'] ?? null) == '' ? '' : url(Storage::url($employee['profile_photo'])) ?>" alt="Select Employee Photo" />
                            </label>
                            <input name="profile_photo" id="profile_photo" type="file" accept="image" style="display: none;" />
                        </div>
                        <div class="form-group row">
                            <label for="emp_ref" class="col-form-label col-sm-3">Employee ID:</label>
                            <div class="col-auto">
                                <input type="hidden" name="emp[id]" value="<?= $editingEmployeeId ?>">
                                <input
                                    type="text"
                                    class="form-control-plaintext"
                                    id="emp_ref"
                                    readonly
                                    value="<?= $employee['emp_ref'] ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="machine_id" class="col-form-label col-sm-3">Attendance ID:</label>
                            <div class="col-auto">
                                <input
                                    type="text"
                                    class="form-control-plaintext"
                                    id="machine_id"
                                    readonly
                                    value="<?= $employee['machine_id'] ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="date_of_join" class="col-form-label col-sm-3">Date of Join:</label>
                            <div class="col-auto">
                                <input
                                    type="text"
                                    class="form-control-plaintext"
                                    id="date_of_join"
                                    readonly
                                    value="<?= sql2date($employee['date_of_join']) ?>">
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
                                    class="form-control"
                                    name="emp[name]"
                                    id="name"
                                    value="<?= $employee['name'] ?>">
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
                                    class="form-control"
                                    name="emp[preferred_name]"
                                    id="preferred_name"
                                    value="<?= $employee['preferred_name'] ?>">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="ar_name" class="col-form-label col-sm-3">Full Name (Arabic):</label>
                            <div class="col-sm-9 col-md-9 col-lg-6">
                                <input
                                    type="text"
                                    required
                                    minlength="3"
                                    class="form-control"
                                    name="emp[ar_name]"
                                    id="ar_name"
                                    dir="rtl"
                                    value="<?= $employee['ar_name'] ?>">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="nationality" class="col-form-label col-sm-3">Nationality:</label>
                            <div class="col-sm-9">
                                <select
                                    required
                                    data-home-country="<?= $homeCountry ?>"
                                    data-selection-css-class="validate"
                                    name="emp[nationality]"
                                    id="nationality"
                                    class="custom-select">
                                    <option value="">-- Select Nationality --</option>
                                    <?php foreach (getCountriesKeyedByCode() as $code => $country): ?>
                                    <option
                                        <?php if ($employee['nationality'] == $code): ?>
                                        selected
                                        <?php endif; ?>
                                        value="<?= $code ?>"
                                        ><?= $country['name'] ?>
                                    </option>
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
                                        class="form-check-input"
                                        type="radio"
                                        name="emp[gender]"
                                        id="gender-male"
                                        value="M"
                                        <?= $employee['gender'] == 'M' ? 'checked' : ''?>>
                                    <label class="form-check-label" for="gender-male">Male</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input
                                        required
                                        class="form-check-input"
                                        type="radio"
                                        name="emp[gender]"
                                        id="gender-female"
                                        value="F"
                                        <?= $employee['gender'] == 'F' ? 'checked' : ''?>>
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
                                    class="form-control"
                                    name="emp[date_of_birth]"
                                    value="<?= sql2date($employee['date_of_birth']) ?>"
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
                                    class="custom-select"
                                    name="emp[blood_group]"
                                    id="blood_group">
                                    <option value="">-- Select Blood Group --</option>
                                    <?php foreach ($bloodGroups as $bloodGroup): ?>
                                    <option <?= $employee['blood_group'] == $bloodGroup ? 'selected' : ''?>
                                        value="<?= $bloodGroup ?>"
                                        ><?= $bloodGroup ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="marital_status" class="col-form-label col-sm-3">Marital Status:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <select
                                    class="custom-select"
                                    name="emp[marital_status]"
                                    id="marital_status">
                                    <option value="">-- Rather Not Say --</option>
                                    <?php foreach ($maritalStatuses as $key => $status): ?>
                                    <option <?= $employee['marital_status'] == $key ? 'selected' : '' ?>
                                        value="<?= $key ?>"
                                        ><?= $status ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="email" class="col-form-label col-sm-3">Email ID:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <input
                                    required
                                    type="email"
                                    class="form-control"
                                    name="emp[email]"
                                    id="email"
                                    value="<?= $employee['email'] ?>">
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
                                        class="form-control"
                                        required
                                        data-parsley-pattern="(5[024568]|[1234679])\d{7}"
                                        data-parsley-pattern-message="This is not a valid UAE number"
                                        name="emp[mobile_no]"
                                        id="mobile_no"
                                        placeholder="50XXXX123"
                                        value="<?= $employee['mobile_no'] ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="card-title text-dark">ID Details</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="passport_no" class="col-form-label col-sm-3">Passport Number:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <input
                                    type="text"
                                    data-parsley-group="nationality"
                                    data-parsley-type="alphanum"
                                    class="form-control"
                                    name="emp[passport_no]"
                                    id="passport_no"
                                    placeholder="AXXXXX123"
                                    value="<?= $employee['passport_no'] ?>">
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
                                    data-parsley-pattern="784-\d{4}-\d{7}-\d"
                                    data-parsley-pattern-message="This is not a valid emirates id"
                                    placeholder="784-1997-XXXXXXX-X"
                                    value="<?= $employee['emirates_id'] ?>">
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
                                    data-parsley-pattern="[1-7]0[12]/\d{4}/\d/?\d+"
                                    data-parsley-pattern-message="This is not a valid file number"
                                    placeholder="201/2020/XXXXXXX"
                                    value="<?= $employee['file_no'] ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="labour_card_no" class="col-form-label col-sm-3">Labour Card No.:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <input
                                    type="text"
                                    minlength="5"
                                    data-parsley-pattern="\d+"
                                    data-parsley-pattern-message="This is not a valid labour card number"
                                    class="form-control"
                                    name="emp[labour_card_no]"
                                    id="labour_card_no"
                                    placeholder="87XXXXX8"
                                    value="<?= $employee['labour_card_no'] ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="uid_no" class="col-form-label col-sm-3">Unified Number (UID No.):</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <input
                                    type="text"
                                    minlength="5"
                                    data-parsley-pattern="\d+"
                                    data-parsley-pattern-message="This is not a valid unified number (UID number)"
                                    class="form-control"
                                    name="emp[uid_no]"
                                    id="uid_no"
                                    placeholder="12XXXXXX9"
                                    value="<?= $employee['uid_no'] ?>">
                            </div>
                        </div>
                    </div>
                </div>
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
                                    class="custom-select"
                                    name="emp[mode_of_pay]"
                                    id="mode_of_pay">
                                    <option value="">-- Select Mode Of Pay --</option>
                                    <?php foreach ($paymentModes as $key => $mode): ?>
                                    <option <?= $employee['mode_of_pay'] == $key ? 'selected' : '' ?>
                                        value="<?= $key ?>"><?= $mode ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="bank_id" class="col-form-label col-sm-3">Bank:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <select
                                    data-parsley-group="mode_of_pay"
                                    data-selection-css-class="validate"
                                    class="custom-select"
                                    name="emp[bank_id]"
                                    id="bank_id">
                                    <option value="">-- Select Bank --</option>
                                    <?php foreach(getBanksKeyedById() as $id => $bank): ?>
                                    <option <?= $employee['bank_id'] == $id ? 'selected' : '' ?>
                                        value="<?= $id ?>"><?= $bank['name'] ?></option>
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
                                    placeholder="Sheikh Zayed Rd, Dubai"
                                    value="<?= $employee['branch_name'] ?>">
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
                                    data-parsley-group="mode_of_pay"
                                    data-parsley-pattern="(AE\d{21}|\d{23})"
                                    data-parsley-pattern-message="This is not a valid IBAN number"
                                    placeholder="AEXXXXXXXXXXXXXXXXXXX21"
                                    value="<?= $employee['iban_no'] ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="personal_id_no" class="col-form-label col-sm-3">Personal ID Number:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <input
                                    data-parsley-pattern="\d{14}"
                                    data-parsley-pattern-message="This is not a valid Person ID number"
                                    type="text"
                                    class="form-control"
                                    data-parsley-group="mode_of_pay"
                                    name="emp[personal_id_no]"
                                    id="personal_id_no"
                                    placeholder="00XXXXXXXXXX14"
                                    value="<?= $employee['personal_id_no'] ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title text-dark">Job Details</h2>
                        <input type="hidden" name="job[id]" id="job_id" value="<?= $employee['job_id'] ?>">
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="working_company_name" class="col-form-label col-sm-3">Working Company:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <input
                                    type="text"
                                    class="form-control-plaintext"
                                    id="working_company_name"
                                    readonly
                                    value="<?= $employee['working_company_name'] ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="visa_company_name" class="col-form-label col-sm-3">Visa Company:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <input
                                    type="text"
                                    class="form-control-plaintext"
                                    id="visa_company_name"
                                    readonly
                                    value="<?= $employee['visa_company_name'] ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="department_name" class="col-form-label col-sm-3">Department:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <input
                                    type="text"
                                    class="form-control-plaintext"
                                    id="department_name"
                                    readonly
                                    value="<?= $employee['department_name'] ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="designation_name" class="col-form-label col-sm-3">Designation:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <input
                                    type="text"
                                    class="form-control-plaintext"
                                    id="designation_name"
                                    readonly
                                    value="<?= $employee['designation_name'] ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="commence_from" class="col-form-label col-sm-3">From:</label>
                            <div class="col-auto">
                                <input
                                    type="text"
                                    class="form-control-plaintext"
                                    id="commence_from"
                                    readonly
                                    value="<?= sql2date($employee['commence_from']) ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="week_offs" class="col-form-label col-sm-3">Weekly Offs:</label>
                            <div class="col-sm-9 col-md-6 col-lg-4">
                                <select
                                    required
                                    class="custom-select"
                                    name="job[week_offs][]"
                                    id="week_offs"
                                    multiple>
                                    <?php foreach ($weekdays as $day): ?>
                                    <option <?= in_array($day['abbr'] , $week_offs) ? 'Selected' : '' ?> value="<?= $day['abbr'] ?>">
                                        <?= $day['name'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="work_hours" class="col-form-label col-sm-3">Work Hours:</label>
                            <div class="col-auto">
                                <input
                                    type="number"
                                    readonly
                                    class="form-control-plaintext"
                                    id="work_hours"
                                    value="<?= $employee['work_hours'] ?>">
                            </div>
                        </div>
                        <div class="form-group row required">
                                <label for="attendance_type" class="col-form-label col-sm-3">Attendance Type:</label>
                                <div class="col-sm-9 col-md-6 col-lg-4">
                                    <select 
                                        required
                                        class="custom-select"
                                        name="job[attendance_type]"
                                        id="attendance_type" >
                                        <option value="">-- Select Attendance Type --</option>
                                        <?php foreach (get_employee_attendance_types() as $typeId => $types): ?>
                                        <option value="<?= $typeId ?>" <?= $employee['attendance_type'] == $typeId ? 'selected' : '' ?> ><?= $types ?></option>
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
                                    <option  <?= $employee['default_shift_id'] == $id ? 'selected' : '' ?>
                                        value="<?= $id ?>"><?= $shift['description'] ?>
                                    </option>
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
                                    <option  <?= in_array($id , $supervisor_ids) ? 'selected' : '' ?>
                                        value="<?= $id ?>"><?= $emp['formatted_name'] ?>
                                    </option>
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
                                        value="1"
                                        <?= $employee['has_commission'] == '1' ? 'checked' : ''?>>
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
                                        value="1"
                                        <?= $employee['has_pension'] == '1' ? 'checked' : ''?>>
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
                                        value="1"
                                        <?= $employee['has_overtime'] == '1' ? 'checked' : '' ?>>
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
                                        value="1"
                                        <?= $employee['require_attendance'] == '1' ? 'checked' : '' ?>>
                                    <label for="require_attendance" class="pl-2 form-check-label">Require Attendance Validation</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div id="control-area" class="col-12 text-center mt-4">
                <button type="button" id="btn-cancel" class="btn shadow-none btn-action btn-label-dark">Cancel</button>
                <button type="submit" id="btn-submit" class="btn shadow-none btn-action btn-primary">Update</button>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/hrm/js/edit_employee.js?id=v1.0.0" type="text/javascript"></script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();