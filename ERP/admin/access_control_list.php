<?php

$page_security = 'SA_ACL_LIST';

$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/API/API_Call.php");

$aclCursor = db_query("SELECT * FROM `0_security_roles`", "Something went wrong");

$acl = [];
while ($role = $aclCursor->fetch_assoc()) {
    $role['areas'] = explode(';', $role['areas']);
    $acl[$role['id']] = $role;
}

$usersCursor = db_query("SELECT * FROM `0_users` u WHERE u.inactive = 0");

$sec_areas = app(App\Permissions::class)->toArray();
$sec_areas = array_map(function($area, $code){
    return [
        "code" => $code,
        "id"   => $area[0],
        "name" => $area[1]
    ];
}, $sec_areas, array_keys($sec_areas));
$sec_areas = array_combine(array_column($sec_areas, 'id'), $sec_areas);
$sec_sects = app(App\PermissionGroups::class)->toArray();

$columns = [
    ["key" => "role_id",            "width" => 10,  "name" => "Role ID",            "align" => "left"],
    ["key" => "role",               "width" => 35,  "name" => "Role",               "align" => "left"],
    ["key" => "permission_code",    "width" => 20,  "name" => "Permission Code",    "align" => "left"],
    ["key" => "permission_id",      "width" => 12,  "name" => "Permission ID",      "align" => "left"],
    ["key" => "permission",         "width" => 50,  "name" => "Permission",         "align" => "left"],
    ["key" => "section_id",         "width" => 12,  "name" => "Section ID",         "align" => "left"],
    ["key" => "section",            "width" => 35,  "name" => "Section",            "align" => "left"],
];

ob_start(); ?>
<link href="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
<link href="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
<link href="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
<style>
    button {
        box-shadow: none;
    }

    .dataTables_length {
        float: left;
    }

    .table-custom td,
    .table-custom th {
        padding: 0.3rem 0.75rem;
    }
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans('Access Control list'), false, false, '', '', false, '', true); ?>

<div id="_content" class="text-dark">
    <div class="card mx-5">
        <div class="card mx-4 mb-3">
            <div class="card-body">
                <h3 class="card-title mb-3"><?= trans('Access Control List') ?></h3>

                <div class="mt-4">
                    <table class="w-100 table text-nowrap table-custom table-bordered table-striped " id="acl_tbl">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>User</th>
                                <th>Role ID</th>
                                <th>Role</th>
                                <th>Permission Code</th>
                                <th>Permission ID</th>
                                <th>Permission</th>
                                <th>Section ID</th>
                                <th>Section</th>
                            </tr>
                            <tr>
                                <th>User ID</th>
                                <th>User</th>
                                <th>Role ID</th>
                                <th>Role</th>
                                <th>Permission Code</th>
                                <th>Permission ID</th>
                                <th>Permission</th>
                                <th>Section ID</th>
                                <th>Section</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($user = $usersCursor->fetch_assoc()) :
                            $role = $acl[$user['role_id']]; 
                            foreach($role['areas'] as $area): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= $user['user_id'] ?></td>
                                <td><?= $role['id'] ?></td>
                                <td><?= $role['role'] ?></td>
                                <td><?= $sec_areas[$area]['code'] ?></td>
                                <td><?= $area ?></td>
                                <td><?= $sec_areas[$area]['name'] ?></td>
                                <td><?= $area&~0xff ?></td>
                                <td><?= $sec_sects[$area&~0xff] ?></td>
                            </tr>
                        <?php endforeach;
                        endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>

<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net/js/jquery.dataTables.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-bs4/js/dataTables.bootstrap4.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-buttons/js/dataTables.buttons.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/jszip/dist/jszip.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-buttons/js/buttons.html5.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-responsive/js/dataTables.responsive.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/custom/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js" type="text/javascript"></script>

<script>
    $(document).ready(function() {
        // Setup - add a text input to each footer cell
        $('#acl_tbl thead tr:eq(1) th').each(function() {
            var title = $(this).text();
            $(this).html('<input type="text" placeholder="Search ' + title + '" class="column_search" />');
        });

        // DataTable
        var table = $('#acl_tbl').DataTable({
            orderCellsTop: true,
            dom: 'lfBr<"table-responsive"t>ip',
            buttons: [
                'copy', 'csv', 'excel'
            ],
            paging: true,
            scrollCollapse: true,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
        });

        // Apply the search
        $('#acl_tbl thead').on('keyup', ".column_search", function() {
            table
                .column($(this).parent().index())
                .search(this.value)
                .draw();
        });
    });
</script>

<?php $GLOBALS['__FOOT__'][] = ob_get_clean();
end_page();