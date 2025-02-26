<?php

$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/db/items_db.inc");

$canAccess = [
    "OWN" => user_check_access('SA_CUSTWISEALLREP'),
    "ALL" => user_check_access('SA_CUSTWISEOWNREP')
];

$page_security = in_array(true, $canAccess, true) ? 'SA_ALLOW' : 'SA_DENIED';


ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css"/>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans($help_context = "Sales Summary Report"));

$filters = get_valid_user_inputs();

if (!user_check_access('SA_CUSTWISEALLREP')) {
    $filters['person_id'] = auth()->user()->id;
}

$err_string = "Something went wrong! Report could not be fetched.";
$persons = db_query(
    "SELECT id, `real_name` FROM 0_users",
    $err_string
)->fetch_all(MYSQLI_ASSOC);
$report = get_customer_wise_sales_report(...(array_values($filters)));
$customers = db_query(
    "SELECT debtor_no, debtor_ref, `name` FROM 0_debtors_master",
    $err_string
)->fetch_all(MYSQLI_ASSOC);
$departments = get_dimensions()->fetch_all(MYSQLI_ASSOC);
$stocks = db_query(
    "SELECT stock_id, `description` FROM 0_stock_master",
    $err_string
)->fetch_all(MYSQLI_ASSOC);
$categories = db_query(
    "SELECT category_id, `description` FROM 0_stock_category",
    $err_string
)->fetch_all(MYSQLI_ASSOC);

$numberCols = [
    'trans_count' => 'Transactions',
    'inv_total' => 'Invoice Total',
    'service_chg' => 'Service Chg',
    'gov_fee' => 'Govt. Fee',
    'discount' => 'Discount',
    'user_commission' => 'Emp. Comm.',
    'customer_commission' => 'Cust. Comm',
    'salesman_commission' => 'Salesman Comm.',
    'benefits' => 'Benefits'
];
$totals = array_fill_keys(array_keys($numberCols), 0);
foreach($report as $row) {
    foreach ($numberCols as $k => $v) {
        $totals[$k] += $row[$k];
    }
}
$numberColsWithPermission = Arr::except($numberCols, 'trans_count');

$colspan = 2;
$is_summerised_by_month
    = $is_summerised_by_category
    = $is_summerised_by_person
    = $is_summerised_by_department
    = $is_summerised_by_date
    = $is_summerised_by_service
    = false;
if (isset($report[0]) && isset($report[0]['year'])) {
    $is_summerised_by_month = true;
    $colspan += 2;
}
if (isset($report[0]) && isset($report[0]['category'])) {
    $is_summerised_by_category = true;
    $colspan++;
}
if (isset($report[0]) && isset($report[0]['user'])) {
    $is_summerised_by_person = true;
    $colspan++;
}
if (isset($report[0]) && isset($report[0]['department_name'])) {
    $is_summerised_by_department = true;
    $colspan++;
}
if (isset($report[0]) && isset($report[0]['tran_date'])) {
    $is_summerised_by_date = true;
    $colspan++;
}
if (isset($report[0]) && isset($report[0]['service_name'])) {
    $is_summerised_by_service = true;
    $colspan++;
}

$is_summerised_by_customer = true;
if (isset($report[0]) && !isset($report[0]['name'])) {
    $is_summerised_by_customer = false;
    $colspan -= 2;
}

$user_date_format = getDateFormatInNativeFormat();
$first_day_of_this_month = (new DateTime())->modify("first day of this month")->format($user_date_format);
$last_day_of_this_month  = (new DateTime())->modify("last day of this month")->format($user_date_format);

/**
 * Retrieves the validated user inputs
 */
function get_valid_user_inputs() {
    $filters = [
        "from"                  => null,
        "till"                  => null,
        "customer_id"           => null,
        "department_id"         => null,
        "category_id"           => null,
        "person_id"             => null,
        "summerise_by_months"   => false,
        "summerise_by_category" => false,
        "summerise_by_person"   => false,
        "summerise_by_customer" => true,
        "summerise_by_department" => false,
        "summerise_by_date"     => false,
        "summerise_by_service"  => false,
        "stock_id"              => null,
    ];

    $userDateFormat = getDateFormatInNativeFormat();
    if (
        isset($_GET['from'])
        && ($dt_from = DateTime::createFromFormat($userDateFormat, $_GET['from']))
        && $dt_from->format($userDateFormat) == $_GET['from']
    ) {
        $filters['from'] = $dt_from->format(DB_DATE_FORMAT);
    }

    if (
        isset($_GET['till'])
        && ($dt_till = DateTime::createFromFormat($userDateFormat, $_GET['till']))
        && $dt_till->format($userDateFormat) == $_GET['till']
    ) {
        $filters['till'] = $dt_till->format(DB_DATE_FORMAT);
    }

    if (isset($dt_till) && isset($dt_from) && $dt_till < $dt_from){
        $_from  = $filters['from'];
        $filters['from'] = $filters['till'];
        $filters['till'] = $_from;
    }

    if (!empty($_GET['stock_id'])) {
        $filters['stock_id'] = $_GET['stock_id'];
    }

    if (
        isset($_GET['customer_id'])
        && preg_match('/^[1-9][0-9]{0,15}$/', $_GET['customer_id']) === 1
    ) {
        $filters['customer_id'] = $_GET['customer_id'];
    }

    if (
        isset($_GET['department_id'])
        && preg_match('/^[1-9][0-9]{0,15}$/', $_GET['department_id']) === 1
    ) {
        $filters['department_id'] = $_GET['department_id'];
    }

    if (
        isset($_GET['category_id'])
        && preg_match('/^[1-9][0-9]{0,15}$/', $_GET['category_id']) === 1
    ) {
        $filters['category_id'] = $_GET['category_id'];
    }
    if (
        isset($_GET['person_id'])
        && preg_match('/^[1-9][0-9]{0,15}$/', $_GET['person_id']) === 1
    ) {
        $filters['person_id'] = $_GET['person_id'];
    }

    if (isset($_GET['summerise_by_months'])) {
        $filters['summerise_by_months'] = true;
    }

    if (isset($_GET['summerise_by_category'])) {
        $filters['summerise_by_category'] = true;
    }

    if (isset($_GET['summerise_by_person'])) {
        $filters['summerise_by_person'] = true;
    }
    
    if (isset($_GET['summerise_by_department'])) {
        $filters['summerise_by_department'] = true;
    }
    
    if (isset($_GET['summerise_by_date'])) {
        $filters['summerise_by_date'] = true;
    }
    
    if (isset($_GET['summerise_by_service'])) {
        $filters['summerise_by_service'] = true;
    }
    
    if (!empty(array_intersect_key($_GET, $filters)) && !isset($_GET['summerise_by_customer'])) {
        $filters['summerise_by_customer'] = false;
    }

    return $filters;
} ?>
<div class="w-100 p-3 font-weight-normal">
    <h1 class="h3 px-3 mb-5">Sales Summary Report</h1>
    <div class="card rounded">
        <div class="card-body">
            <form action="./customer_wise_sales.php">
                <div class="row">
                    <div class="col-lg-2">
                        <div class="form-group row">
                            <label class="col-4  col-form-label">
                                Customer: 
                            </label>
                            <div class="col-6">
                                <select 
                                    class="select-2"
                                    name="customer_id" 
                                    id="customer_id">
                                    <option value="">-- All Customers --</option>
                                    <?php foreach ($customers as $c): ?>
                                    <option 
                                        value="<?= $c['debtor_no'] ?>" 
                                        <?php if($filters['customer_id'] == $c['debtor_no']): ?>
                                        selected
                                        <?php endif; ?>>
                                        <?= "{$c['debtor_ref']} - {$c['name']}" ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                        <div class="col-lg-3">
                            <div class="form-group row">
                                <label class="col-2 col-form-label">
                                    User:
                                </label>
                                <div class="col-6">
                                    <select
                                            class="select-2"
                                            name="person_id"
                                            id="person_id">
                                        <?php if (user_check_access('SA_CUSTWISEALLREP')): ?>
                                            <option value="">-- All User --</option>
                                        <?php endif; ?>
                                        <?php foreach ($persons as $c): ?>
                                            <option
                                                    value="<?= $c['id'] ?>"
                                                <?php if($filters['person_id'] == $c['id']): ?>
                                                    selected
                                                <?php endif; ?>>
                                                <?= $c['real_name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <div class="col-lg-2">
                        <div class="form-group row">
                            <label class="col-3 col-form-label">
                                Category:
                            </label>
                            <div class="col-6">
                                <select 
                                    class="select-2"
                                    name="category_id" 
                                    id="category_id">
                                    <option value="">-- All Categories --</option>
                                    <?php foreach ($categories as $c): ?>
                                    <option 
                                        value="<?= $c['category_id'] ?>" 
                                        <?php if($filters['category_id'] == $c['category_id']): ?>
                                        selected
                                        <?php endif; ?>>
                                        <?= $c['description'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group row">
                            <label class="col-3 col-form-label">
                                Service:
                            </label>
                            <div class="col-6">
                                <select 
                                    class="select-2"
                                    name="stock_id" 
                                    id="stock_id">
                                    <option value="">-- All Service --</option>
                                    <?php foreach ($stocks as $s): ?>
                                    <option 
                                        value="<?= $s['stock_id'] ?>" 
                                        <?php if($filters['stock_id'] == $s['stock_id']): ?>
                                        selected
                                        <?php endif; ?>>
                                        <?= $s['description'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="class-lg-2">
                        <div class="form-group form-group-sm row">
                            <label for="daterange" class="col-2 col-form-label">Date: </label>
                            <div class="col-6">
                                <div 
                                    class="input-group input-daterange" 
                                    data-provide="datepicker"
                                    data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                    data-date-autoclose="true"
                                    data-date-today-btn="linked"
                                    data-date-today-highlight="true">
                                    <input 
                                        type="text" 
                                        name="from" 
                                        id="from"
                                        class="form-control"
                                        placeholder="--select date--"
                                        value="<?= $filters['from'] ? sql2date($filters['from']) : $first_day_of_this_month ?>">
                                    <div class="input-group-text input-group-addon px-4">to</div>
                                    <input 
                                        type="text" 
                                        name="till" 
                                        id="till"
                                        class="form-control"
                                        placeholder="--select date--"
                                        value="<?= $filters['till'] ? sql2date($filters['till']) : $last_day_of_this_month ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group row">
                            <label class="col-4 col-form-label">
                                Department: 
                            </label>
                            <div class="col-6">
                                <select 
                                    class="select-2"
                                    name="department_id" 
                                    id="department_id">
                                    <option value="">-- All Departments --</option>
                                    <?php foreach ($departments as $dept): ?>
                                    <option 
                                        value="<?= $dept['id'] ?>" 
                                        <?php if($filters['department_id'] == $dept['id']): ?>
                                        selected
                                        <?php endif; ?>>
                                        <?= $dept['name'] ?>
                                    </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-check form-check-inline">
                            <input
                                name="summerise_by_customer"
                                id="summerise_by_customer"
                                class="form-check-input"
                                type="checkbox"
                                <?php if($filters['summerise_by_customer']): ?>
                                checked
                                <?php endif ?>
                                value="1">
                            <label class="form-check-label" for="summerise_by_customer">Summerise by Customer</label>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-check form-check-inline">
                            <input
                                name="summerise_by_months"
                                id="summerise_by_months"
                                class="form-check-input"
                                type="checkbox"
                                <?php if($filters['summerise_by_months']): ?>
                                checked
                                <?php endif ?>
                                value="1">
                            <label class="form-check-label" for="summerise_by_months">Summerise by Month</label>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-check form-check-inline">
                            <input
                                name="summerise_by_date"
                                id="summerise_by_date"
                                class="form-check-input"
                                type="checkbox"
                                <?php if($filters['summerise_by_date']): ?>
                                checked
                                <?php endif ?>
                                value="1">
                            <label class="form-check-label" for="summerise_by_date">Summerise by Date</label>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-check form-check-inline">
                            <input
                                name="summerise_by_department"
                                id="summerise_by_department"
                                class="form-check-input"
                                type="checkbox"
                                <?php if($filters['summerise_by_department']): ?>
                                checked
                                <?php endif ?>
                                value="1">
                            <label class="form-check-label" for="summerise_by_department">Summerise by Department</label>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-check form-check-inline">
                            <input
                                name="summerise_by_category"
                                id="summerise_by_category"
                                class="form-check-input"
                                <?php if($filters['summerise_by_category']): ?>
                                checked
                                <?php endif ?>
                                type="checkbox"
                                value="1">
                            <label class="form-check-label" for="summerise_by_category">Summerise by Category</label>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-check form-check-inline">
                            <input
                                name="summerise_by_service"
                                id="summerise_by_service"
                                class="form-check-input"
                                <?php if($filters['summerise_by_service']): ?>
                                checked
                                <?php endif ?>
                                type="checkbox"
                                value="1">
                            <label class="form-check-label" for="summerise_by_service">Summerise by Service</label>
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="form-check form-check-inline">
                            <input
                                    name="summerise_by_person"
                                    id="summerise_by_person"
                                    class="form-check-input"
                                <?php if($filters['summerise_by_person']): ?>
                                    checked
                                <?php endif ?>
                                    type="checkbox"
                                    value="1">
                            <label class="form-check-label" for="summerise_by_person">Summerise by Person</label>
                        </div>
                    </div>

                </div>
                <div class="text-center">
                    <button type="submit" class="py-2 rounded flaticon-search-magnifier-interface-symbol">
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="card p-3 mt-3">
        <div class="card-body">
            <table id="customer-wise-rep-tbl" class="table table-sm table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <?php if ($is_summerised_by_customer): ?>
                        <th class="font-weight-bold">Ref</th>
                        <th class="font-weight-bold">Name</th>
                        <?php endif; ?>
                        <?php if ($is_summerised_by_department): ?>
                        <th class="font-weight-bold">Department</th>
                        <?php endif; ?>
                        <?php if ($is_summerised_by_category): ?>
                        <th class="font-weight-bold">Category</th>
                        <?php endif; ?>
                        <?php if ($is_summerised_by_service): ?>
                        <th class="font-weight-bold">Service</th>
                        <?php endif; ?>
                        <?php if ($is_summerised_by_person): ?>
                        <th class="font-weight-bold">User</th>
                        <?php endif; ?>
                        <?php if ($is_summerised_by_date): ?>
                        <th class="font-weight-bold">Date</th>
                        <?php endif; ?>
                        <?php if ($is_summerised_by_month): ?>
                        <th class="font-weight-bold">Year</th>
                        <th class="font-weight-bold">Month</th>
                        <?php endif; ?>
                        <th class="font-weight-bold">Transactions</th>
                        <?php if(user_check_access('SA_CUSTWISEREPPARTICULARS')):
                            foreach ($numberColsWithPermission as $key => $value):
                            echo "<th class=\"font-weight-bold\">$value</th>";
                            endforeach;
                        endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($report as $row): ?>
                    <tr>
                        <?php if ($is_summerised_by_customer): ?>
                        <td><?= $row['debtor_ref'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <?php endif; ?>
                        <?php if ($is_summerised_by_department): ?>
                        <td><?= $row['department_name'] ?></td>
                        <?php endif; ?>
                        <?php if ($is_summerised_by_category): ?>
                        <td><?= $row['category'] ?></td>
                        <?php endif; ?>
                        <?php if ($is_summerised_by_service): ?>
                        <td><?= $row['service_name'] ?></td>
                        <?php endif; ?>
                        <?php if ($is_summerised_by_person): ?>
                        <td><?= $row['user'] ?></td>
                        <?php endif; ?>
                        <?php if ($is_summerised_by_date): ?>
                        <td><?= $row['tran_date'] ?></td>
                        <?php endif; ?>
                        <?php if ($is_summerised_by_month): ?>
                        <td><?= $row['year'] ?></td>
                        <td><?= $row['month'] ?></td>
                        <?php endif; ?>
                        <td><?= number_format2($row['trans_count']) ?></td>
                        <?php if(user_check_access('SA_CUSTWISEREPPARTICULARS')):
                            foreach ($numberColsWithPermission as $key => $value):
                            echo "<td>".price_format($row[$key])."</td>";
                            endforeach;
                        endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <?php if ($colspan): ?>
                        <td colspan="<?= $colspan ?>" class="font-weight-bold">Total</td>
                        <?php endif; ?>
                        <td class="font-weight-bold"><?= number_format2($totals['trans_count']) ?></td>
                        <?php if(user_check_access('SA_CUSTWISEREPPARTICULARS')):
                            foreach ($numberColsWithPermission as $key => $value):
                            echo '<td class="font-weight-bold">'.price_format($totals[$key]).'</td>';
                            endforeach;
                        endif; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net/js/jquery.dataTables.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-bs4/js/dataTables.bootstrap4.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-responsive/js/dataTables.responsive.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/datatables/Buttons-1.6.5/js/dataTables.buttons.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/datatables/JSZip-2.5.0/jszip.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/datatables/Buttons-1.6.5/js/buttons.html5.min.js" type="text/javascript"></script>
<script>
    $('#customer-wise-rep-tbl').DataTable({
        dom: 'lfBr<"table-responsive"t>ip',
        buttons: [
            'copy', 'csv', 'excel'
        ]
    });
    $('.select-2').select2();
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();?>