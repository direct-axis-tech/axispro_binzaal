<?php

$page_security = "SA_CUSTPAYMREP";
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

ob_start(); ?>

<!-- Head block: Start -->
<link href="../../../assets/plugins/general/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
<link href="../../../assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css"/>
<link href="../../../assets/plugins/general/datatables/Buttons-1.6.5/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css"/>

<style>
    #cust-bal-rep-tbl_length {
        margin-right: 0.5rem;
        margin-left: 0.5rem;
    }
    #cust-bal-rep-tbl_length select {
        width: unset;
    }
    select.custom-select-sm {
        height: calc(1.5em + 1rem + 2px) !important;
        padding: 0.5rem 1rem !important;
        font-size: 0.875rem !important;
        line-height: 1.5 !important;
    }
</style>
<!-- Head block: End -->

<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans($help_context = "Customer balance report"));

// validate customer id
if(
    empty($_GET['cust_id']) 
    || !preg_match('/^[1-9][0-9]*$/', $_GET['cust_id'])
) {
    $_GET['cust_id'] = '';
}

// validate date from
if(
    empty($_GET['dt_from']) 
    || !($dt = DateTime::createFromFormat('d/m/Y', $_GET['dt_from'])) 
    || $dt->format('d/m/Y') != $_GET['dt_from']
) {
    $_GET['dt_from'] = date('d/m/Y');
}

// validate date to
if(
    empty($_GET['dt_to']) 
    || !($dt = DateTime::createFromFormat('d/m/Y', $_GET['dt_to'])) 
    || $dt->format('d/m/Y') != $_GET['dt_to']
) {
    $_GET['dt_to'] = date('d/m/Y');
}

$show_outstanding_only = empty($_GET['show_outstanding_only']) ? '' : 'checked';
$customers = db_query("SELECT debtor_no id, name FROM 0_debtors_master")->fetch_all(MYSQLI_ASSOC);
$op_bal = get_opening_bal($_GET['cust_id'], $_GET['dt_from']);
$res = get_cust_bal_rep($_GET['cust_id'], $_GET['dt_from'], $_GET['dt_to']);

$offset = 0;
$tot_debit = 0;
$tot_credit = 0;
$rows = [];
$grand_tot = $op_bal;
for($count = 0; $r = db_fetch_assoc($res); $count++) {
    $r['debit'] = round2(floatval($r['debit']), 2);
    $r['credit'] = round2(floatval($r['credit']), 2);
    $r['amount'] = round2(floatval($r['amount']), 2);
    $r['type'] = isset($GLOBALS['systypes_array'][$r['type']]) ? $GLOBALS['systypes_array'][$r['type']] : $r['type'];
    $tot_debit += $r['debit'];
    $tot_credit += $r['credit'];
    if (($grand_tot += $r['amount']) <= 0) {
        $offset = $count;
    }
    $r['balance'] = round2($grand_tot, 2);
    $rows[] = $r;
}
$grand_tot = round2($grand_tot, 2);

// if want to show only outstanding results
if(!empty($show_outstanding_only)){
    /**
     * 1. check if offset is the last index(last el) and is not the only element
     * 2. check if offset is not 0 - ie. we could avoid potentially unwanted slice operation
     */
    if($offset == $count - 1 && $count != 1) {
        $rows = [];
    } else if($offset) {
        $rows = array_slice($rows, $offset);
    }
}

?>

<div class="w-100 p-3">
    <form action="./customer_balance_statement.php">
        <div class="row">
            <div class="col-lg-3">
                <div class="form-group-sm row">
                    <label for="cust_id" class="col-3 col-form-label-sm">Customers:</label>
                    <div class="col-9">
                        <select 
                            class="custom-select custom-select-sm"
                            name="cust_id" 
                            id="cust_id">
                            <option value="">--select--</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>" <?= $customer['id'] == $_GET['cust_id'] ? 'selected' : '' ?>><?= $customer['name'] ?></option>
                           <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group-sm row">
                    <label for="date-range" class="col-3 col-form-label-sm">Date:</label>
                    <div class="col-9">
                        <div id="date-range" class="input-group input-daterange">
                            <input 
                                data-provide="datepicker" 
                                data-date-format="dd/mm/yyyy"
                                data-date-autoclose="true"
                                data-date-end-date="0d"
                                name="dt_from"
                                type="text" 
                                class="form-control form-control-sm" 
                                value="<?= $_GET['dt_from'] ?>">
                            <div class="input-group-addon text-center bg-secondary">-</div>
                            <input 
                                data-provide="datepicker" 
                                data-date-format="dd/mm/yyyy"
                                data-date-end-date="0d"
                                data-date-autoclose="true"
                                name="dt_to"
                                type="text" 
                                class="form-control form-control-sm" 
                                value="<?= $_GET['dt_to'] ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-check">
                    <input <?= $show_outstanding_only ?>
                        class="form-check-input"
                        type="checkbox"
                        name="show_outstanding_only"
                        value="true" 
                        id="show_outstanding_only">
                    <label 
                        for="show_outstanding_only" 
                        class="form-check-label mt-1 ml-3">
                        Show outstanding only
                    </label>
                </div>
            </div>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-success">
                <span class="fa fa-paper-plane mr-2"></span>Search
            </button>
        </div>
    </form>
    <hr>
    <div>
        <table style="width:350px" class="mb-3">
            <tbody>
                <tr>
                    <td>Opening Balance: </td>
                    <td><?= $op_bal ?></td>
                </tr>
                <tr>
                    <td>Tot. Debit: </td>
                    <td><?= $tot_debit ?></td>
                </tr>
                <tr>
                    <td>Tot. Credit: </td>
                    <td><?= $tot_credit ?></td>
                </tr>
                <tr>
                    <td>Balance: </td>
                    <td><?= $grand_tot ?></td>
                </tr>
            </tbody>
        </table>
        <table id="cust-bal-rep-tbl" class="table table-sm table-bordered">
            <thead class="bg-success">
                <tr>
                    <th style="width: 30%">Date</th>
                    <th>Type</th>
                    <th>Ref</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Bal</th>
                    <th>Memo</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($rows as $row): ?>
                <tr>
                    <td><?= $row['tran_date'] ?></td>
                    <td><?= $row['type'] ?></td>
                    <td><?= $row['reference'] ?></td>
                    <td><?= $row['debit'] ?></td>
                    <td><?= $row['credit'] ?></td>
                    <td><?= $row['balance'] ?></td>
                    <td><?= $row['memo_'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php ob_start() ?>
<!-- Foot Block: Start -->
<script src="../../../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="../../../assets/plugins/general/datatables/datatables.min.js" type="text/javascript"></script>
<script src="../../../assets/plugins/general/datatables/Buttons-1.6.5/js/dataTables.buttons.min.js" type="text/javascript"></script>
<script src="../../../assets/plugins/general/datatables/JSZip-2.5.0/jszip.min.js" type="text/javascript"></script>
<script src="../../../assets/plugins/general/datatables/Buttons-1.6.5/js/buttons.html5.min.js" type="text/javascript"></script>
<script>
    $('#cust-bal-rep-tbl').DataTable({
        dom: 'lfBr<"table-responsive"t>ip',
        buttons: [
            'copy', 'csv', 'excel'
        ]
    });
    initializeSelect2('#cust_id');
</script>
<!--  Foot Block: End -->
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();?>