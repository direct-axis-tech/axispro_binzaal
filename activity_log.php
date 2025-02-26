<?php

$path_to_root = "ERP";

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/includes/ui.inc";
require_once $path_to_root . "/inventory/includes/db/items_db.inc";

ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css"/>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

$page_security = 'SA_USERACTIVITY';

page(trans("User Activity Log"));

$err_string = "Something went wrong! Report could not be fetched.";
$limit = 20;
[$filters, $offset] = get_valid_user_inputs($limit);
["result" => $logs, "total" => $total_count] = getActivityLog($filters, $offset, $limit);
$users = db_query(
    "SELECT id, CONCAT(user_id, if(real_name != ' ', CONCAT(' - ', real_name), '')) `name` FROM 0_users",
    $err_string
)->fetch_all(MYSQLI_ASSOC);

/**
 * Gets the user's activity
 * 
 * Todo: Implement all major activities
 * 
 * Currently Implemented:
 *     1. User login
 *     2. User logout
 *     3. User login attempt failure
 *     4. User account bruteforce detection
 *     5. User environment change
 * 
 * @param array $filters The filters for the activity log
 * @param int   $after   The number of records after the report should be returned
 * @param int   $take    The number of records to fetch in one report. Specify null to retieve all
 * 
 * @return array
 */
function getActivityLog($filters, $after = 0, $take = 50) {
    $buildWhere = function($from, $till, $user_id) {
        $where = ["l.channel = 'UserActivity'"];
        if ($from) {
            $where[] = "l.timestamp >= '$from 00:00:00'";
        }
        if ($till) {
            $where[] = "l.timestamp <= '$till 23:59:59'";
        }
        if ($user_id) {
            $where[] = "l.user = $user_id";
        }
        return implode(" AND ", $where);
    };
    $errStrng = "Something went wrong! Please contact the administrator";

    $sql = (
        "SELECT
            l.`timestamp`,
            u.user_id,
            l.ip,
            l.`message`
        FROM
            `0_logs` l
        LEFT JOIN `0_users` u ON
            u.id = l.user
        WHERE {$buildWhere(...array_values($filters))}
        ORDER BY l.`timestamp` DESC, l.id DESC"
    );

    $total_count = db_query("SELECT COUNT(*) FROM ($sql) AS t", $errStrng)->fetch_row()[0];

    if ($take) {
        $sql .= " LIMIT {$take}";

        if ($after) {
            $sql .= " OFFSET {$after}";
        }
    }

    return [
        "result" => db_query($sql, $errStrng),
        "total"  => $total_count
    ];
}

/**
 * Retrieves the validated user inputs
 * 
 * @param int $limit The number of record to take from the database per query
 * @return array
 */
function get_valid_user_inputs($limit) {
    $filters = [
        "from"                  => null,
        "till"                  => null,
        "user_id"               => null
    ];
    $offset = 0;
    $dt_from = $dt_till = false;

    $userDateFormat = getDateFormatInNativeFormat();
    if (
        isset($_GET['from'])
        && ($dt_from = DateTime::createFromFormat($userDateFormat, $_GET['from']))
        && $dt_from->format($userDateFormat) == $_GET['from']
    ) {
        $filters['from'] = $dt_from->format(DB_DATE_FORMAT);
    } else {
        $filters['from'] = date(DB_DATE_FORMAT);
    }

    if (
        isset($_GET['till'])
        && ($dt_till = DateTime::createFromFormat($userDateFormat, $_GET['till']))
        && $dt_till->format($userDateFormat) == $_GET['till']
    ) {
        $filters['till'] = $dt_till->format(DB_DATE_FORMAT);
    } else {
        $filters['till'] = date(DB_DATE_FORMAT);
    }

    if ($dt_from && $dt_till && $dt_till < $dt_from){
        $_from  = $filters['from'];
        $filters['from'] = $filters['till'];
        $filters['till'] = $_from;
    }

    if (
        isset($_GET['user_id'])
        && preg_match('/^[1-9][0-9]{0,15}$/', $_GET['user_id']) === 1
    ) {
        $filters['user_id'] = $_GET['user_id'];
    }

    if (
        isset($_GET['page'])
        && preg_match('/^[1-9][0-9]{0,15}$/', $_GET['page']) === 1
    ) {
        $offset = ($_GET['page'] * $limit) - $limit;
    }

    return [$filters, $offset];
} ?>
<div class="w-100 p-3 font-weight-normal">
    <h1 class="h3 px-3 mb-5">User Activity Log</h1>
    <div class="card rounded">
        <div class="card-body">
            <form action="" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group form-group-sm row">
                            <label for="daterange" class="col-3 col-form-label">Date: </label>
                            <div class="col-9">
                                <div 
                                    id="daterange"
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
                                        value="<?= sql2date($filters['from']) ?>">
                                    <div class="input-group-text input-group-addon px-4">to</div>
                                    <input 
                                        type="text" 
                                        name="till" 
                                        id="till"
                                        class="form-control"
                                        placeholder="--select date--"
                                        value="<?= sql2date($filters['till']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group form-group-sm row">
                            <label for="user_id" class="col-3 col-form-label">User: </label>
                            <div class="col-9">
                                <select
                                    class="form-control"
                                    name="user_id"
                                    id="user_id">
                                    <option value="">-- select --</option>
                                    <?php foreach($users as $user): ?>
                                    <option 
                                        value="<?= $user['id'] ?>"
                                        <?= ($user['id'] == $filters['user_id']) ? 'selected' : ''; ?>>
                                        <?= $user['name'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-instagram flaticon-search-magnifier-interface-symbol">
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card p-3 mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table id="user-activity-table" class="table table-sm table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="font-weight-bold">Time</th>
                            <th class="font-weight-bold">IP Address</th>
                            <th class="font-weight-bold">User</th>
                            <th class="font-weight-bold">Desc</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($row = db_fetch_assoc($logs)): ?>
                        <tr>
                            <td><?= $row['timestamp'] ?></td>
                            <td><?= $row['ip'] ?></td>
                            <td><?= $row['user_id'] ?></td>
                            <td><?= $row['message'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <div id="pg-link"><?= AxisPro::paginate($total_count, $limit) ?></div>
            </div>
        </div>
    </div>
</div>
<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function() {
        $('#user_id').select2();
    })
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();?>