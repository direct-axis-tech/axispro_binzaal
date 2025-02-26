<?php

$path_to_root = "../..";
$page_security = 'SA_CASH_HANDOVER_INQ';

require_once $path_to_root . "/includes/session.inc";

$inputs = getValidatedInputs();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    returnJsonResponse($inputs);
}

ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css"/>

<link href="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/parsley/parsley.css" rel="stylesheet" type="text/css"/>
<style>
    #daterange_picker.is-valid .form-control,
    #daterange_picker.is-valid .input-group-addon,
    .form-group.is-valid .validate {
        border-color: #28a745;
    }
  
    #daterange_picker.is-invalid .form-control,
    #daterange_picker.is-invalid .input-group-addon,
    .form-group.is-invalid .validate {
        border-color: #dc3545;
    }

    .select2-container--default .select2-selection--single  button.select2-selection__clear {
        box-shadow: none;
        height: calc(1.5rem + 1.3rem);
        font-size: 1.5rem;
        color: #74788d;
        padding-right: 0.5rem;
        padding-top: 0.65rem;
        padding-bottom: 0.65rem;
    }

    .dataTables_length {
        float: left;
    }

    #cash_handover_report_table th {
        font-weight: bold;
    }

    .table-custom td, .table-custom th {
        padding: 0.3rem 0.75rem;
    }

    button {
        box-shadow: none;
    }
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans('Cash Handover Report'), false, false, "", "", false, '', true);
$dimensions = get_dimensions()->fetch_all(MYSQLI_ASSOC);
$users = get_users()->fetch_all(MYSQLI_ASSOC);
?>

<div id="_content" class="text-dark">
    <div class="card mx-5">
        <form action="" class="w-100" id="filter_form" method="POST">
            <div class="card mx-4 mb-3">
                <div class="card-body">
                    <div class="row pt-3 align-items-center">
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="dimension_id"><?= trans('Department') ?>:</label>
                                <div>
                                    <select
                                        data-selection-css-class="validate"
                                        class="form-control mw-100"
                                        name="dimension_id" id="dimension_id">
                                        <option value="">-- select department --</option>
                                        <?php foreach($dimensions as $d): ?>
                                        <option value="<?= $d['id'] ?>"><?= $d['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="user_id"><?= trans('User') ?>:</label>
                                <div>
                                    <select
                                        style="line-height: 1.5"
                                        class="form-control mw-100"
                                        name="user_id"
                                        id="user_id"
                                        data-placeholder="-- all users --">
                                        <?php foreach($users as $u): ?>
                                        <option
                                            data-department="<?= $u['dflt_dimension_id'] ?>"
                                            value="<?= $u['id'] ?>"><?= $u['formatted_name'] ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group required">
                                <label for="daterange_picker"><?= trans('Date') ?>:</label>
                                <div 
                                    id="daterange_picker"
                                    class="input-group input-daterange"
                                    data-parsley-max-days="31"
                                    data-parsley-class-handler="#daterange_picker"
                                    data-parsley-validate-if-empty
                                    data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                    data-date-autoclose="true">
                                    <input 
                                        required
                                        type="text" 
                                        name="from" 
                                        id="from"
                                        class="form-control"
                                        autocomplete="off"
                                        placeholder="d-MMM-yyyy"
                                        value="<?= sql2date($inputs['from']) ?>">
                                    <div class="input-group-text input-group-addon px-4 rounded-0 border-left-0 border-right-0">to</div>
                                    <input
                                        required
                                        type="text" 
                                        name="till" 
                                        id="till"
                                        class="form-control"
                                        autocomplete="off"
                                        value="<?= sql2date($inputs['till']) ?>"
                                        placeholder="d-MMM-yyyy">
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" id="proceed" class="btn btn-primary mx-3 shadow-none"><?= trans('Get Report') ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="card mx-4 mb-3">
            <div class="card-body">
                <h3 class="card-title mb-3"><?= trans('Cash Handover Report') ?></h3>
                <div class="table-responsive">
                    <table
                        class="w-100 table-custom table table-bordered table-striped"
                        id="cash_handover_report_table">
                        <!- Will generate dynamically -->
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net/js/jquery.dataTables.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-bs4/js/dataTables.bootstrap4.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-buttons/js/dataTables.buttons.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/jszip/dist/jszip.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-buttons/js/buttons.html5.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-responsive/js/dataTables.responsive.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js" type="text/javascript"></script>

<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script>
$(function () {
    var filterFormElem = document.getElementById('filter_form');
    var elementIds = {
        user: 'user_id',
        department: 'dimension_id',
        dateRange: 'daterange_picker',
        fromDate: 'from',
        tillDate: 'till',
        reportTable: 'cash_handover_report_table'
    }
    var storage = {
        users: [],
        departments: {}
    };
    var numberFormatter = new Intl.NumberFormat('en-US', {maximumFractionDigits: 2});
    var dateFormatter = new Intl.DateTimeFormat('en-US', {weekday: 'short', month: 'short', day: 'numeric'});

    // read and initialise the users data from the dom
    (function() {
        /** @type {HTMLSelectElement} */
        var elem = document.getElementById(elementIds.user);

        var users = elem.options;
        for (var i = 0; i < users.length; i++) {
            var user = users[i];
            storage.users[i] = {
                id: user.value,
                name: user.text,
                department: user.dataset.department
            }
        }
    })();

    // read and initialise the department data from the dom
    (function () {
        /** @type {HTMLSelectElement} */
        var elem = document.getElementById(elementIds.department);

        var departments = elem.options;
        for (var i = 0; i < departments.length; i++) {
            var department = departments[i];
            storage.departments[department.value] = department.text;
        }
    })();

    // filter the employees when there is a change in department.
    (function() {
        /** @type {HTMLSelectElement} */
        var usersElem = document.getElementById(elementIds.user);
        /** @type {HTMLSelectElement} */
        var departmentElem = document.getElementById(elementIds.department);
        
        // initialise the selects
        $(departmentElem).select2()
        regenerateUsers();
        
        // add the change listener
        $('#' + elementIds.department).on('change', regenerateUsers);

        /**
         * Regenerates the users HTMLSelectElement based on the department
         */
        function regenerateUsers() {
            var department = departmentElem.value;
            
            // filter the users as per the department
            var filteredUsers = department.length
                ? storage.users
                    .filter(function(user) { return user.department === department })
                : storage.users;

            // prepare the dataSource for the select element
            var dataSource = [{
                id: '',
                text: '-- select user --',
            }].concat(filteredUsers.map(function(user) {
                return {
                    id: user.id,
                    text: user.name
                }
            }));
            
            if ($(usersElem).hasClass('select2-hidden-accessible')) {
                $(usersElem).select2('destroy');   
            }
            empty(usersElem);
            $(usersElem).select2({
                data: dataSource,
                allowClear: true
            })
        }
    })();

    // initialise the date picker
    $('#' + elementIds.dateRange).datepicker();

    // initialise the dataTable
    var $reportTable = $('#' + elementIds.reportTable).DataTable({
        dom: 'lfBr<"table-responsive"t>ip',
        ajax: requestCashHandoverReport,
        buttons: [
            {
                extend: 'excel',
                exportOptions: {
                    orthogonal: 'filter',
                    stripNewlines: false,
                    stripHtml: false
                }
            }
        ],
        stateSave: true,
        columns: [
            {
                title: 'Department',
                name: 'department_name',
                data: 'department_name'
            },
            {
                title: 'User Name',
                name: 'username',
                data: 'username'
            },
            {
                title: 'Date',
                name: 'date',
                data: null,
                render: {
                    _: 'date',
                    display: function (data, type, row, meta) {
                        return dateFormatter.format(new Date(row.date));
                    }
                }
            },
            {
                title: 'Handovers To',
                name: 'handovers_to',
                data: null,
                orderable: false,
                render: {
                    _: 'handovers_to',
                    display: function (data, type, row, meta) {
                        return '<pre class="m-0">' + row.handovers_to + '</pre>';
                    }
                }
            },
            {
                title: 'Handovers From',
                name: 'handovers_from',
                orderable: false,
                data: null,
                render: {
                    _: 'handovers_from',
                    display: function (data, type, row, meta) {
                        return '<pre class="m-0">' + row.handovers_from + '</pre>';
                    }
                }
            },
            {
                title: 'Collection',
                name: 'collection',
                data: null,
                className: 'text-right',
                render: {
                    _: 'collection',
                    display: function (data, type, row, meta) {
                        return numberFormatter.format(row.collection);
                    }
                }
            },
            {
                title: 'Received',
                name: 'received',
                data: null,
                className: 'text-right',
                render: {
                    _: 'received',
                    display: function (data, type, row, meta) {
                        return numberFormatter.format(row.received);
                    }
                }
            },
            {
                title: 'Cash in Hand',
                name: 'cash_inhand',
                data: null,
                className: 'text-right',
                render: {
                    _: 'cash_inhand',
                    display: function (data, type, row, meta) {
                        return numberFormatter.format(row.cash_inhand);
                    }
                }
            },
            {
                title: 'Handovered',
                name: 'handovered',
                data: null,
                className: 'text-right',
                render: {
                    _: 'handovered',
                    display: function (data, type, row, meta) {
                        return numberFormatter.format(row.handovered);
                    }
                }
            },
            {
                title: 'Balance',
                name: 'balance',
                data: null,
                className: 'text-right',
                render: {
                    _: 'balance',
                    display: function (data, type, row, meta) {
                        return numberFormatter.format(row.balance);
                    }
                }
            },
            
        ],
    });

    // Initialise the main form
    (function() {
        window.Parsley.addValidator('maxDays', {
            messages: {en: 'The date-period cannot exceed 31 days'},
            requirementType: 'integer',
            validate: function(_value, requirement) {
                var fromDate = $('#' + elementIds.fromDate).datepicker('getDate');
                var tillDate = $('#' + elementIds.tillDate).datepicker('getDate');
                return Math.abs(tillDate - fromDate) / (1000 * 60 * 60 * 24) <= requirement;
            }
        });

        var pslyForm = $(filterFormElem).parsley({
            errorClass: 'is-invalid',
            successClass: 'is-valid',
            errorsWrapper: '<ul class="errors-list"></ul>',
            classHandler: field => field.$element.closest('.form-group'),
            errorsContainer: field => field.$element.closest('.form-group'),
            inputs: Parsley.options.inputs + ',[data-parsley-max-days]'
        });

        pslyForm.on('form:submit', function() {
            $reportTable.ajax.reload();
            return false;
        });
    })();

    /**
     * Async cash handover request table
     */
    function requestCashHandoverReport(data, callback, settings) {
        var error = function () {
            toastr.error("Something went wrong! Please try again later");
            callback({"data": []});
        }
        setBusyState();
        $.ajax({
            method: filterFormElem.method,
            url: filterFormElem.action,
            headers: {
                'Accept': 'application/json'
            },
            dataType: 'json',
            data: new FormData(filterFormElem),
            processData: false,
            contentType: false
        }).done(function(res) {
            if (res.status && res.status == 200) {
                callback(res);
            } else {
                error();
            }
        }).fail(error)
        .always(unsetBusyState)
    }
})
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();

// PHP Helper Functions

/**
 * Query the database for the report and return the result
 *
 * @param string $from
 * @param string $till
 * @param int $dimension_id
 * @param int $user_id
 * @return array
 */
function getCashHandoverReport($from, $till, $dimension_id = null, $user_id = null) {
    $where = "gl.amount <> 0"
        . " AND gl.tran_date >= '{$from}'"
        . " AND gl.tran_date <= '{$till}'";
    if (!empty($dimension_id)) {
        $where .= " AND usr.dflt_dimension_id = '{$dimension_id}'";
    }
    if (!empty($user_id)) {
        $where .= " AND usr.id = '{$user_id}'";
    }
    
    $sql = (
        "SELECT
            usr.id user_id,
            usr.user_id username,
            usr.dflt_dimension_id department_id,
            dim.name department_name,
            gl.tran_date `date`,
            IFNULL(SUM(pmt.ov_amount + pmt.ov_gst + pmt.ov_freight + pmt.ov_freight_tax + pmt.ov_discount + pmt.credit_card_charge + pmt.round_of_amount), 0) `collection`,
            SUM(gl.amount) `cash_inhand`,
            IFNULL(handovered_to.tot_handovered, 0) `handovered`,
            SUM(gl.amount) - IFNULL(handovered_to.tot_handovered, 0) `balance`,
            IFNULL(handovered_to.handovers, '') handovers_to,
            IFNULL(handovered_from.handovers, '') handovers_from,
            IFNULL(handovered_from.tot_received, 0) `received`
        FROM `0_users` usr
        LEFT JOIN `0_bank_accounts` bank ON bank.id = usr.cashier_account
        LEFT JOIN `0_dimensions` dim ON usr.dflt_dimension_id = dim.id
        LEFT JOIN `0_gl_trans` gl
            ON gl.created_by = usr.id
            AND gl.account = bank.account_code
        LEFT JOIN `0_debtor_trans` pmt ON
            pmt.`type` = gl.`type`
            AND pmt.trans_no = gl.type_no
            AND pmt.`type` = 12
            AND pmt.payment_method = 'Cash'
            AND (pmt.ov_amount + pmt.ov_gst + pmt.ov_freight + pmt.ov_freight_tax + pmt.ov_discount) <> 0
        LEFT JOIN (
            SELECT
                req_to.cashier_id user_id,
                req_to.trans_date tran_date,
                group_concat(concat(req_to.total_to_pay, '\\t -> ', usr_to.user_id) SEPARATOR '\\n') handovers,
                sum(req_to.cash_in_hand) tot_handovered
            FROM `0_cash_handover_requests` req_to
            LEFT JOIN `0_users` usr_to ON usr_to.id = req_to.approve_rejected_by
            WHERE
                req_to.status = 'APPROVED'
                AND req_to.trans_date >= '{$from}'
                AND req_to.trans_date <= '{$till}'
            GROUP BY req_to.cashier_id, req_to.trans_date
        ) handovered_to ON
            usr.id = handovered_to.user_id
            AND gl.tran_date = handovered_to.tran_date
        LEFT JOIN (
            SELECT
                req_from.approve_rejected_by user_id,
                req_from.handovered_on tran_date,
                group_concat(concat(req_from.total_to_pay, '\\t <- ', usr_from.user_id) SEPARATOR '\\n') handovers,
                sum(req_from.total_to_pay) tot_received
            FROM `0_cash_handover_requests` req_from
            LEFT JOIN `0_users` usr_from ON usr_from.id = req_from.cashier_id
            WHERE
                req_from.status = 'APPROVED'
                AND req_from.handovered_on >= '{$from}'
                AND req_from.handovered_on <= '{$till}'
            GROUP BY req_from.approve_rejected_by, req_from.handovered_on
        ) handovered_from ON
            usr.id = handovered_from.user_id
            AND gl.tran_date = handovered_from.tran_date
        WHERE {$where}
        GROUP BY usr.id, gl.tran_date, handovered_from.handovers, handovered_from.tot_received"
    );

    return db_query($sql, "Could not retrieve cash handover report")->fetch_all(MYSQLI_ASSOC);
}

/**
 * Retrieves the valid filters or default
 *
 * @return array
 */
function getValidatedInputs() {
    $yesterday = (new DateTime())->modify('-1 day')->format(DB_DATE_FORMAT);
    $filters = [
        'from' => $yesterday,
        'till' => $yesterday,
        'dimension_id' => null,
        'user_id' => null
    ];

    $userDateFormat = getDateFormatInNativeFormat();
    if (
        isset($_POST['from'])
        && ($dt_from = DateTime::createFromFormat($userDateFormat, $_POST['from']))
        && $dt_from->format($userDateFormat) == $_POST['from']
    ) {
        $filters['from'] = $dt_from->format(DB_DATE_FORMAT);
    }

    if (
        isset($_POST['till'])
        && ($dt_till = DateTime::createFromFormat($userDateFormat, $_POST['till']))
        && $dt_till->format($userDateFormat) == $_POST['till']
    ) {
        $filters['till'] = $dt_till->format(DB_DATE_FORMAT);
    }

    if (
        isset($_POST['dimension_id'])
        && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['dimension_id']) === 1
    ) {
        $filters['dimension_id'] = $_POST['dimension_id'];
    }

    if (
        isset($_POST['user_id'])
        && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['user_id']) === 1
    ) {
        $filters['user_id'] = $_POST['user_id'];
    }

    return $filters;
}

/**
 * Handles the post request for the data
 *
 * @param array $inputs The valid user inputs
 * @return void
 */
function returnJsonResponse($inputs) {
    if (!user_check_access('SA_CASH_HANDOVER_INQ')) {
        http_response_code(403);
        echo json_encode([
            "status" => 403,
            "message" => "You are not authorized to access this function"
        ]);
        exit();
    }
    
    $report = getCashHandoverReport($inputs['from'], $inputs['till'], $inputs['dimension_id'], $inputs['user_id']);

    echo json_encode([
        "status" => 200,
        "data" => $report
    ]);
    exit();
}