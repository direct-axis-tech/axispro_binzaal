<?php
$path_to_root = './ERP';
include("ERP/includes/session.inc");
include_once("ERP/fixed_assets/includes/fixed_assets_db.inc");

// Check if the 'stockId' parameter is set in the URL
if (isset($_GET['stockId']) && $_GET['stockId'] != null) {
    $stockId = $_GET['stockId'];

    // Fetch asset allocation and deallocation history for the specified stock item
    $searchArray = array(
        'itemId' => $stockId
    );
    $itemHistory = get_list_assets_to_allocate($searchArray)->fetch_all(MYSQLI_ASSOC);;
}

include "header.php";
?>

<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">
            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <div class="kt-portlet__head" style="padding: 10px">
                    <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title">
                            <?= trans('Assets Allocation & Deallocation Details') ?>
                        </h3>
                    </div>
                </div>

                <!-- Asset Details Section -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="kt-portlet">
                            <div class="kt-portlet__head">
                                <div class="kt-portlet__head-label">
                                    <h3 class="kt-portlet__head-title">
                                        <?= trans('Asset Details') ?>
                                    </h3>
                                </div>
                            </div>
                            <div class="kt-portlet__body">
                                <!-- Display asset details here -->
                                <table class="table">
                                    <tr>
                                        <th width="20%"><?= trans('Stock Code') ?></th>
                                        <td><?= $itemHistory[0]['stock_id'] ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= trans('Stock Name') ?></th>
                                        <td><?= $itemHistory[0]['stock_name'] ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= trans('Stock Category') ?></th>
                                        <td><?= $itemHistory[0]['category'] ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Asset Allocation & Deallocation History Table -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="kt-portlet">
                            <div class="kt-portlet__head">
                                <div class="kt-portlet__head-label">
                                    <h3 class="kt-portlet__head-title">
                                        <?= trans('Asset Allocation & Deallocation History') ?>
                                    </h3>
                                </div>
                            </div>
                            <div class="kt-portlet__body">
                                <div class="table-responsive" style="padding: 7px 7px 7px 7px;">
                                    <table class="table table-bordered" id="service_req_list_table">
                                        <thead>
                                            <th><?= trans('Allocated To') ?></th>
                                            <th><?= trans('Allocated Date') ?></th>
                                            <th><?= trans('Allocated By') ?></th>
                                            <th><?= trans('Returned Date') ?></th>
                                            <th><?= trans('Collected By') ?></th>
                                            <th><?= trans('Remarks') ?></th>
                                        </thead>
                                        <tbody id="tbody">
                                            <?php if (!$itemHistory[0]['assign_id']) { ?>
                                                <tr class="text-center">
                                                    <td colspan="5">No History To Shown !</td>
                                                </tr>
                                            <?php } else { ?>
                                                <!-- Loop through $itemHistory and display allocation history data here -->
                                                <?php foreach ($itemHistory as $historyItem) { ?>
                                                    <tr>
                                                        <td><?= $historyItem['allocated_to'] ?></td>
                                                        <td><?= sql2date($historyItem['assigned_date']) ?></td>
                                                        <td><?= $historyItem['assigned_by_name'] ?></td>
                                                        <td>
                                                            <?= ($historyItem['returned_date'] !== '0000-00-00 00:00:00') ? sql2date($historyItem['returned_date']) : '--'; ?>
                                                        </td>
                                                        <td><?= $historyItem['returned_by_name'] ?></td>
                                                        <td><?= $historyItem['remarks'] ?></td>
                                                    </tr>
                                            <?php }
                                            } ?>
                                        </tbody>
                                    </table>
                                    <div id="pg-link"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>