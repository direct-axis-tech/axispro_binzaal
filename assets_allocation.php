<?php
$path_to_root = './ERP';
include_once("ERP/fixed_assets/includes/fixed_assets_db.inc");
require_once("ERP/hrm/db/employees_db.php");
require_once("ERP/hrm/db/departments_db.php");

if (isset($_POST['assignAssets']) && isset($_POST['postData'])) {
    $assignData = json_decode($_POST['postData'], true);
    include("ERP/includes/session.inc");
    allocateAssets($assignData);

    // Convert the response to JSON and echo it
    header('Content-Type: application/json');
    echo json_encode(array('status' => 'success'));
    die;
}

if (isset($_POST['assignId']) && $_POST['assignId'] > 0) {

    include("ERP/includes/session.inc");

    deAllocateAssets();

    // Convert the response to JSON and echo it
    header('Content-Type: application/json');
    echo json_encode(array('status' => 'success'));
    die;
}

if (isset($_POST['search_btn']) && $_POST['item_list'] != null) {
    $assetId = $_POST['item_list'];
} else {
    $assetId = 0;
}

function allocateAssets($assignData)
{
    $user = $_SESSION["wa_current_user"]->user;

    foreach ($assignData as $key => $value) {

        if (check_allocation_status($value['stockId'])) {
            $assign =  array(
                'stock_id'      => db_escape($value['stockId']),
                'assignee'      => db_escape($value['allocateTo']),
                'is_employee'   => db_escape(($value['optgroup'] == 'E') ? 1 : (($value['optgroup'] == 'D') ? 2 : '')),
                'status'        => 1,
                'assigned_date' => quote(date('Y-m-d H:i:s', strtotime($value['allocationDate']))),
                'assigned_by'   => db_escape($user),
                'created_at'    => quote(date('Y-m-d H:i:s')),
            );
            add_asset_allocation($assign);
        }
    }
}

function deAllocateAssets()
{
    $deAllocate = array(
        'stockId'    => db_escape($_POST['stockId']),
        'assignId'   => db_escape($_POST['assignId']),
        'remarks'    => db_escape($_POST['remarks']),
        'returnDate' => quote(date('Y-m-d H:i:s', strtotime($_POST['returnDate']))),
    );
    de_allocate_assets($deAllocate);
}

include "header.php";

$canAccess = [
    "OWN" => 0,
    "DEP" => 0,
    "ALL" => 1
];
$itemList =  get_list_assets_to_allocate(array('assignStatus' => 1))->fetch_all(MYSQLI_ASSOC); // All Items For Serach Form
$assetsList =  get_list_assets_to_allocate(array('itemId' => $assetId, 'assignStatus' => 1))->fetch_all(MYSQLI_ASSOC); // Selected Item For result
$employeeList = getAuthorizedEmployeesKeyedById($canAccess);
$departmentList = getAuthorizedDepartments($canAccess)->fetch_all(MYSQLI_ASSOC);

?>


<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">
            <div class="kt-container  kt-grid__item kt-grid__item--fluid">
                <div class="row">
                    <div class="kt-portlet">
                        <div class="kt-portlet__head">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title">
                                    <?= trans('Assets Allocation & Deallocation') ?>
                                </h3>
                            </div>
                        </div>

                        <div style="padding: 10px">
                            <form id="filter_form" name="filter_form" method="POST">
                                <div class="form-group row">
                                    <div class="col-lg-2">
                                        <label class=""><?= trans('Item') ?>:</label>
                                        <select name="item_list" class="form-control">
                                            <option value="">--Select--</option>
                                            <?php foreach ($itemList as $key => $value) { ?>
                                                <option value="<?php echo $value['stock_id']; ?>" <?php echo ($assetId == $value['stock_id']) ? 'selected' : ''; ?>><?php echo $value['stock_id'] . ' - ' . $value['stock_name']; ?> </option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="col-lg-1">
                                        <label class="">&nbsp</label>
                                        <button type="submit" id="search_btn" name="search_btn" class="form-control btn btn-sm btn-primary">
                                            Search
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="text-right">
                            <button type="button" name="allocate_multiple" class="btn btn-success" id="allocate_multiple" data-toggle="modal" data-target="#allocateModal" style="margin: 10px 70px;">Multiple Allocation</button>
                        </div>

                        <div class="table-responsive" style="padding: 7px 7px 7px 7px;">
                            <table class="table table-bordered" id="service_req_list_table">
                                <thead>
                                    <th><input type="checkbox" name="check-all" class="check-all" value="1"> </th>
                                    <th><?= trans('Sl No') ?></th>
                                    <th><?= trans('Item Code') ?></th>
                                    <th><?= trans('Item') ?></th>
                                    <th><?= trans('Category') ?></th>
                                    <th><?= trans('Allocate To') ?></th>
                                    <th>Actions</th>
                                </thead>
                                <tbody id="tbody">
                                    <?php
                                    $sl = 1;
                                    foreach ($assetsList as $key => $value) { ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="check-row" class="check-row" value="1" stock-id="<?php echo $value['stock_id']; ?>" <?php echo ($value['assign_id']) ? 'disabled' : ''; ?>>
                                            </td>
                                            <td><?php echo $sl++; ?></td>
                                            <td>
                                                <a href="javascript:void(0);" onclick="window.open('assets_allocation_history.php?stockId=<?php echo $value['stock_id']; ?>', 'AssetAllocationHistory', 'width=800,height=600');">
                                                    <b><?php echo $value['stock_id']; ?></b>
                                                </a>
                                            </td>
                                            <td><?php echo $value['stock_name']; ?></td>
                                            <td><?php echo $value['category']; ?></td>
                                            <td>
                                                <select name="allocate_to" class="form-control allocate_to" <?php echo ($value['assign_id']) ? 'disabled' : ''; ?>>
                                                    <option value="">--Select--</option>
                                                    <optgroup label="Departments" groupId="D">
                                                        <?php foreach ($departmentList as $departId => $department) { ?>
                                                            <option value="<?php echo $department['id']; ?>" <?php echo ($value['assignee'] == $department['id'] && $value['is_employee'] == 2) ? 'selected' : ''; ?>><?php echo $department['name']; ?></option>
                                                        <?php } ?>
                                                    </optgroup>

                                                    <optgroup label="Employees" groupId="E">
                                                        <?php foreach ($employeeList as $empId => $employees) { ?>
                                                            <option value="<?php echo $employees['id']; ?>" <?php echo ($value['assignee'] == $employees['id'] && $value['is_employee'] == 1) ? 'selected' : ''; ?>><?php echo $employees['name']; ?></option>
                                                        <?php } ?>
                                                    </optgroup>
                                                </select>
                                            </td>
                                            <td>
                                                <?php if ($value['assign_id']) { ?>
                                                    <button type="button" class="btn btn-warning deallocate" data-toggle="modal" data-target="#deallocateModal" data-stock-id="<?php echo $value['stock_id']; ?>" data-assign-id="<?php echo $value['assign_id']; ?> ">Deallocate</button>
                                                <?php } else { ?>
                                                    <button type="button" name="single_allocate" class="btn btn-primary single_allocate" data-toggle="modal" data-target="#allocateModal">Allocate</button>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
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

<!-- Allocate Modal -->
<div class="modal fade" id="allocateModal" tabindex="-1" role="dialog" aria-labelledby="allocateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allocateModalLabel">Allocate Asset</h5>
                <button type="button" class="close closeAllocateModal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="allocationDate">Allocation Date</label>
                    <input type="date" class="form-control" id="allocationDate" name="allocationDate">
                </div>
                <input type="hidden" name="assetId" id="assetId" value="">
                <input type="hidden" name="assignId" id="assignId" value="">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary closeAllocateModal" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="allocateSubmit">Submit</button>
            </div>
        </div>
    </div>
</div>


<!-- Deallocate Modal -->
<div class="modal fade" id="deallocateModal" tabindex="-1" role="dialog" aria-labelledby="deallocateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deallocateModalLabel">Deallocate Asset</h5>
                <button type="button" class="close closeDeallocateModal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="returnDate">Return Date</label>
                    <input type="date" class="form-control" id="returnDate" name="returnDate">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary closeDeallocateModal" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="deallocateSubmit">Submit</button>
            </div>
        </div>
    </div>
</div>



<?php include "footer.php"; ?>

<script type="text/javascript">

    $(document).ready(function() {
        // Add a click event handler to the "check-all" checkbox
        $('.check-all').click(function() {
            // Get the state of the "check-all" checkbox
            const isChecked = $(this).prop('checked');

            // Set the state of all "check-row" checkboxes to match the "check-all" checkbox
            $('.check-row:not(:disabled)').prop('checked', isChecked);
        });
    });

    // Add a click event handler to the "single_allocate" button
    $('.single_allocate').click(function() {

        $('.check-row').closest('tr').css('background-color', '');
        $('.check-row').closest('tr').find('.error-message').remove();

        // Get the closest row to the clicked button
        const row = $(this).closest('tr');
        const stockId = row.find('.check-row').attr('stock-id');
        const allocateTo = row.find('.allocate_to').val();
        const optgroup = row.find('select.allocate_to option:selected').closest('optgroup').attr('groupId');

        // Check if the selected option is empty or undefined
        if (!allocateTo) {
            // Add a red border and an error message to the row
            row.css('background-color', '#FFCCBA');
            // You can display an error message next to the select element
            row.find('.allocate_to').after('<span class="error-message" style="color: #D63301">Please select a employee or department to allocate.</span>');
            return; // Exit the function if the row is invalid
        }

        // Set the default value of "allocationDate"
        $('#allocationDate').val(dateFormat());
        $('#allocateModal').modal('show'); // Open the modal

        // Create a promise to wait for the button click on modal
        const allocateButtonClickPromise = new Promise((resolve, reject) => {
            // Add an event listener to the allocateSubmit button from modal
            $('#allocateSubmit').on('click', function() {
                resolve(); // Resolve the promise
            });

            $('#allocateModal').on('hidden.bs.modal', function() {
                reject('User canceled the operation');
            });

        });

        allocateButtonClickPromise
            .then(() => {

                var allocationDate = $('#allocationDate').val();

                // Check if the allocationDate is empty
                if (allocationDate === '') {
                    toastr.error('Please select a allocation date.');
                    return; // Exit the function if the allocationDate is empty
                }

                // Initialize an array to store the selected row's data
                const selectedRow = [{
                    stockId,
                    allocateTo,
                    optgroup,
                    allocationDate
                }];

                //Send an AJAX request to post the selected row's data
                $.ajax({
                    type: 'POST',
                    url: 'assets_allocation.php',
                    data: {
                        assignAssets: true,
                        postData: JSON.stringify(selectedRow)
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            toastr.success("Successfully Allocated");
                        }
                        location.reload();
                    },
                    error: function(error) {
                        toastr.error('Something went wrong!');
                    }
                });
            })
            .catch((reason) => {
                console.log(reason);
            });

        $('#allocateModal').modal('hide'); // Close the modal

    });

    // Add a click event handler to the "Allocate Multiple" button
    $('#allocate_multiple').click(function() {

        // Initialize an array to store the selected rows' data
        const selectedRows = [];
        let isValid = true;
        $('.check-row').closest('tr').css('background-color', '');
        $('.check-row').closest('tr').find('.error-message').remove();

        // Check if at least one checkbox is checked
        if ($('.check-row:checked').length === 0) {
            toastr.error('Please select at least one item to allocate.');
            return; // Exit the function if the nothing is selected
        }

        // Loop through all the checked rows
        $('.check-row:checked').each(function() {

            const stockId = $(this).attr('stock-id');
            const allocateTo = $(this).closest('tr').find('.allocate_to').val();
            const optgroup = $(this).closest('tr').find('select.allocate_to option:selected').closest('optgroup').attr('groupId');

            // Check if the selected option is empty or undefined
            if (!allocateTo) {
                // Add a red border and an error message to the row
                $(this).closest('tr').css('background-color', '#FFCCBA');
                // You can display an error message next to the select element
                $(this).closest('tr').find('.allocate_to').after('<span class="error-message" style="color: #D63301">Please select a employee or department to allocate.</span>');
                // Set validation status to false
                isValid = false;
            }

            // Push the selected row data to the array
            selectedRows.push({
                stockId,
                allocateTo,
                optgroup
            });
        });

        if (!isValid) {
            return; // Exit the function if the any row is invalid
        }

        // Set the default value of "allocationDate"
        $('#allocationDate').val(dateFormat());
        $('#allocateModal').modal('show'); // Open the modal

        // Create a promise to wait for the button click on modal
        const allocateButtonClickPromise = new Promise((resolve, reject) => {
            // Add an event listener to the allocateSubmit button from modal
            $('#allocateSubmit').on('click', function() {
                resolve(); // Resolve the promise
            });

            $('#allocateModal').on('hidden.bs.modal', function() {
                reject('User canceled the operation');
            });

        });

        allocateButtonClickPromise
            .then(() => {

                const allocationDate = $('#allocationDate').val();

                // Check if the allocationDate is empty
                if (allocationDate === '') {
                    toastr.error('Please select a allocation date.');
                    return; // Exit the function if the allocationDate is empty
                }

                selectedRows.forEach((row) => {
                    row.allocationDate = allocationDate;
                });

                // Send an AJAX request to post the selected rows' data
                $.ajax({
                    type: 'POST',
                    url: 'assets_allocation.php', // Replace with the appropriate URL
                    data: {
                        assignAssets: true,
                        postData: JSON.stringify(selectedRows)
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            toastr.success("Successfully Allocated");
                        }
                        location.reload();
                    },
                    error: function(error) {
                        toastr.error('Something went wrong!');
                    }
                });

            })
            .catch((reason) => {
                console.log(reason);
            });

        $('#allocateModal').modal('hide'); // Close the modal

    });

    // Add a click event handler to the "Deallocate" button
    $('.deallocate').click(function() {

        const stockId  = $(this).data('stock-id');
        const assignId = $(this).data('assign-id');

        // Set the default value of "returnDate"
        $('#returnDate').val(dateFormat());
        $('#deallocateModal').modal('show'); // Open the modal

        // Create a promise to wait for the button click on modal
        const allocateButtonClickPromise = new Promise((resolve, reject) => {
            // Add an event listener to the allocateSubmit button from modal
            $('#deallocateSubmit').on('click', function() {
                resolve(); // Resolve the promise
            });

            $('#deallocateModal').on('hidden.bs.modal', function() {
                reject('User canceled the operation');
            });

        });

        allocateButtonClickPromise
            .then(() => {

                // Get the values from the modal inputs
                const remarks    = $('#remarks').val();
                const returnDate = $('#returnDate').val();

                // Check if the returnDate is empty
                if (returnDate === '') {
                    toastr.error('Please select a return date.');
                    return; // Exit the function if the returnDate is empty
                }

                // Perform the AJAX request to deallocate the asset
                $.ajax({
                    type: 'POST',
                    url: 'assets_allocation.php',
                    data: {
                        stockId: stockId,
                        assignId: assignId,
                        remarks: remarks,
                        returnDate: returnDate
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            toastr.success("Successfully Deallocated");
                        }
                        location.reload();
                    },
                    error: function(error) {
                        toastr.error('Something went wrong!');
                    }
                });

            })
            .catch((reason) => {
                console.log(reason);
            });

        $('#deallocateModal').modal('hide'); // Close the modal

    });


    function dateFormat() {
        // Set the default value as the current date
        const currentDate = new Date();
        const year = currentDate.getFullYear();
        const month = (currentDate.getMonth() + 1).toString().padStart(2, '0'); // Adding 1 because January is 0-based
        const day = currentDate.getDate().toString().padStart(2, '0');
        const formattedDate = `${year}-${month}-${day}`;
        return formattedDate;
    }

    // // Add a click event handler to the close button for modal
    $('.closeAllocateModal, .closeDeallocateModal').click(function() {
        $('.modal').modal('hide');
    });

    // Add an event listener to the modal's "hidden.bs.modal" event
    $('#allocateModal').on('hidden.bs.modal', function () {
        // Clear all input fields in the modal
        $('#allocationDate').val('');
    });

    // Add a click event handler to the close button with id "closeDeallocateModal"
    $('.closeDeallocateModal').click(function() {
        $('#deallocateModal').modal('hide');
    });

    // Add an event listener to the modal's "hidden.bs.modal" event
    $('#deallocateModal').on('hidden.bs.modal', function () {
        // Clear all input fields in the modal
        $('#remarks').val('');
        $('#returnDate').val('');
    });

</script>