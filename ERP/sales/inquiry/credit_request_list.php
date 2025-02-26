<?php
/**********************************************************************
 * Direct Axis Technology L.L.C.
 * Released under the terms of the GNU General Public License, GPL,
 * as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/
$page_security = 'SA_SALESALLOC';
$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();
page(trans($help_context = "Additional Credit Amount Requests"), false, false, "", $js);


if (get_post('RefreshInquiry')) {
    $Ajax->activate('_trans_tbl_span');
}


if (isset($_GET['customer_id'])) {
    $_POST['customer_id'] = $_GET['customer_id'];
}

if (isset($_GET['user_id'])) {
    $_POST['user_id'] = $_GET['user_id'];
}


//------------------------------------------------------------------------------------------------

if (!isset($_POST['customer_id']))
    $_POST['customer_id'] = null;

if (!isset($_POST['user_id']))
    $_POST['user_id'] = null;




start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();
customer_list_cells(trans("Select a customer: "), 'customer_id', $_POST['customer_id'], true);
users_list_cells(trans("Select a user: "), 'user_id', $_POST['user_id'], true);

end_row();
start_row();

request_status_cell("STATUS","status");

submit_cells('RefreshInquiry', trans("Search"), '', trans('Refresh Inquiry'), 'default');

set_global_customer($_POST['customer_id']);

end_row();
end_table();
//------------------------------------------------------------------------------------------------


function systype_name($dummy, $type)
{
    global $systypes_array;

    return $systypes_array[$type];
}

function check_redeemed($row)
{
    return false;
}

function fmt_format_inv($row)
{

}

function approval_link($row) {

    $id = $row['id'];

    if($row['status'] != "PENDING") {
        return "<button type='button' style='background: #838a89' disabled>".$row['status']."</button>";
    }

    return "<button type='button' data-id='$id' class='approve_btn'>Approve</button>
            <button type='button' data-id='$id' class='reject_btn' style='background-color: orangered'>Reject</button>";

}


function request_status_cell($label,$name,$selected_id=null)
{
    echo "<td>$label</td>
            <td>" . array_selector(
            $name, $selected_id,
            [
                "PENDING" => "PENDING",
                "ACCEPTED"=>"ACCEPTED",
                "REJECTED"=>"REJECTED"
            ]
        ) . "</td>";

}

function get_all_credit_requests($date,$status="PENDING",$customer_id=null) {

    $where = "";

    if(!empty($customer_id)) {
        $where .= " AND a.customer_id=$customer_id ";
    }

    $sql = "SELECT b.name,a.description,c.user_id,a.id,a.status  FROM 0_credit_requests a 
            LEFT JOIN 0_debtors_master b ON a.customer_id=b.debtor_no 
            LEFT JOIN 0_users c ON c.id=a.requested_by 
            WHERE DATE(a.req_date) = ".db_escape($date)." AND status=".db_escape($status)." $where ORDER BY a.id DESC";
    return $sql ;
}


//------------------------------------------------------------------------------------------------

$customer_id = get_post('customer_id');
$user_id = get_post('user_id');
$status = get_post('status');

$date = date2sql(Today());
$sql = get_all_credit_requests($date,$status,$customer_id);

//display_error($sql);

//------------------------------------------------------------------------------------------------
$cols = array(
    trans("Customer") => array('align' => 'center'),
//    trans("Requested Amount") => array('align' => 'center'),
    trans("Description") => array( 'align' => 'center'),
    trans("Requested By") => array('align' => 'center'),
    array('insert' => true, 'fun' => 'approval_link')
);


$table =& new_db_pager('trans_tbl', $sql, $cols);

$table->set_marker('check_redeemed', null);

$table->width = "80%";

display_db_pager($table);

end_form();


end_page();

?>


<!--Request for aditional credit  - Modal Form-->
<div id="request_approval_model" class="modal modal-sm">
    <!-- Modal content -->
    <div class="modal-content" style="width: 30%">
        <div class="modal-header">
            <span class="close close_approval_model">&times;</span>
            <p id="modal_title">Request Approval</p>

        </div>
        <div class="modal-body">
            <input type="hidden" id="req_id">
            <input type="hidden" id="req_action">
            <table class="tablestyle" cellpadding="2" cellspacing="0">
                <thead>
                <tbody>
                <tr id="approval_amount_tr" style="display: none">
                    <td>Approved Amount</td>
                    <td><input type="number" id="approved_amount"></td>
                </tr>

                <tr>
                    <td>Note</td>
                    <td><textarea id="action_description" rows="6" cols="200"></textarea></td>
                </tr>

                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" id="btn_req_approve">Approve</button>
        </div>
    </div>
</div>

<script>




    //FOR REQUESY MODAL
    var approval_modal = document.getElementById('request_approval_model');


    // Get the <span> element that closes the modal
    var approval_span = document.getElementsByClassName("close_approval_model")[0];


    // When the user clicks on <span> (x), close the modal
    approval_span.onclick = function () {
        approval_modal.style.display = "none";
    };


    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {
        if (event.target == approval_span) {
            approval_modal.style.display = "none";
        }
    };



    $(document).on("click", ".approve_btn", function () {

        approval_modal.style.display = "block";
        var request_id = $(this).data('id');
        $("#req_id").val(request_id);
        $("#req_action").val("ACCEPTED");
        $("#btn_req_approve").html("ACCEPT");
        $("#modal_title").html("Request Approval");

    });

    $(document).on("click", ".reject_btn", function () {

        approval_modal.style.display = "block";
        var request_id = $(this).data('id');
        $("#req_id").val(request_id);
        $("#approved_amount").val(0);
        $("#approval_amount_tr").hide();
        $("#req_action").val("REJECTED");
        $("#btn_req_approve").html("REJECT");
        $("#modal_title").html("Request Rejection");

    });


    $(document).on("click", "#btn_req_approve", function () {

        var action_description = $("#action_description").val();
        var request_id = $("#req_id").val();
        var approved_amount = $("#approved_amount").val();
        var request_action = $("#req_action").val();

        $("#ajaxmark").attr("visibility", "visible");
        $.ajax({
            url: "../credit_requests_ajax.php?take_action=1",
            type: "post",
            dataType: 'json',
            data: {
                request_id: request_id,
                amount: approved_amount,
                action_description: action_description,
                action: request_action
            },
            success: function (response) {
                $("#ajaxmark").attr("visibility", "hidden");
                alert(response.msg);
                approval_modal.style.display = "none";
                window.location.reload();
            },
            error: function (xhr) {
            }
        });

    });

</script>

<style>

    /* The Modal (background) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 9999; /* Sit on top */
        padding-top: 100px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0, 0, 0); /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
    }

    /* Modal Content */
    .modal-content {
        position: relative;
        background-color: #fefefe;
        margin: auto;
        padding: 0;
        border: 1px solid #888;
        width: 50%;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        -webkit-animation-name: animatetop;
        -webkit-animation-duration: 0.4s;
        animation-name: animatetop;
        animation-duration: 0.4s
    }

    /* Add Animation */
    @-webkit-keyframes animatetop {
        from {
            top: -300px;
            opacity: 0
        }
        to {
            top: 0;
            opacity: 1
        }
    }

    @keyframes animatetop {
        from {
            top: -300px;
            opacity: 0
        }
        to {
            top: 0;
            opacity: 1
        }
    }

    /* The Close Button */
    .close {
        color: white;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }

    .modal-header {
        padding: 1px 14px;
        background-color: #009688;
        color: white;
    }

    .modal-body {
        padding: 2px 16px;
    }

    .modal-footer {
        padding: 2px 16px;
        background-color: #009688;
        color: white;
        text-align: right;
    }

    #btn_req_approve {
        background: #585858 !important;
        box-shadow: none !important;
        padding: 0px 20px !important;
        margin: 2px;
    }

</style>


