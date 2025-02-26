<?php

$page_security = "SA_SALESANALYTIC";
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/db/items_db.inc");

if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}

// 
ob_start(); ?>

<!-- Head block: Start -->
<link href="../../../assets/plugins/general/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
<link href="../../../assets/plugins/general/datatables/Buttons-1.6.5/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css"/>

<style>
    #service-wise-rep-tbl_length {
        margin-right: 0.5rem;
        margin-left: 0.5rem;
    }
    #service-wise-rep-tbl_length select {
        width: unset;
    }
</style>
<!-- Head block: End -->

<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans($help_context = "Service wise sales inquiry"), false, false, "", $js);

$filters = [
    'cost_center' => isset($_GET['cost_center']) ?  $_GET['cost_center'] : null
];

$rows = get_service_wise_report($filters);
$costCenters = get_dimensions()->fetch_all(MYSQLI_ASSOC);
?>

 <div class="w-100 p-3">
    <form action="./service_wise_inquiry.php">
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group row">
                    <label class="col-4 col-form-label">
                        Center: 
                    </label>
                    <div class="col-8">
                        <select 
                            class="custom-select custom-select-sm kt-select2 ap-select2"
                            name="cost_center" 
                            id="cost_center">
                            <option value="">--select--</option>
                            <?php foreach ($costCenters as $costCenter): ?>
                            <option value="<?= $costCenter['id'] ?>"><?= $costCenter['name'] ?></option>
                           <?php endforeach; ?>
                        </select>
                    </div>
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
        <table id="service-wise-rep-tbl" class="table table-sm table-bordered">
            <thead class="bg-success">
                <tr>
                    <th style="width: 30%">Name</th>
                    <th>Category</th>
                    <th>Center</th>
                    <th>Month</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($rows as $row): ?>
                <tr>
                    <td><?= $row['item_name'] ?></td>
                    <td><?= $row['category'] ?></td>
                    <td><?= $row['cost_center'] ?></td>
                    <td><?= $row['trans_month'] ?></td>
                    <td><?= $row['quantity'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php ob_start() ?>
<!-- Foot Block: Start -->
<script src="../../../assets/plugins/general/datatables/datatables.min.js" type="text/javascript"></script>
<script src="../../../assets/plugins/general/datatables/Buttons-1.6.5/js/dataTables.buttons.min.js" type="text/javascript"></script>
<script src="../../../assets/plugins/general/datatables/JSZip-2.5.0/jszip.min.js" type="text/javascript"></script>
<script src="../../../assets/plugins/general/datatables/Buttons-1.6.5/js/buttons.html5.min.js" type="text/javascript"></script>
<script>
    $('#service-wise-rep-tbl').DataTable({
        dom: 'lfBr<"table-responsive"t>ip',
        buttons: [
            'copy', 'csv', 'excel'
        ]
    });
</script>
<!--  Foot Block: End -->
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();?>