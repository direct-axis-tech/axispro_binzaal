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

use App\Models\System\User;

$page_security = 'SA_USERS';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");

page(trans($help_context = "Users"));

$canAssignLimitedRolesOnly = user_check_access('SA_LIMITEDROLEASSIGN');

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/admin/db/users_db.inc");
include_once($path_to_root . "/hrm/db/employees_db.php");

simple_page_mode(true);
//-------------------------------------------------------------------------------------------------

function can_process($new, $selected_id, $canAssignLimitedRolesOnly)
{
    if (!isset($_POST['user_type']) || !in_array($_POST['user_type'], array_keys($GLOBALS['user_types']))) {
        display_error(trans("Please select the user's type"));
        set_focus('user_type');
        return false;
    }

    if ($_POST['user_type'] == UT_EMPLOYEE) {
        if (empty($_POST['employee_id'])) {
            display_error(trans("Please select an employee"));
            set_focus('employee_id');
            return false;
        }

        $_user = getUserByEmployee($_POST['employee_id'], true);
        if ($_user && $_user['id'] != $selected_id) {
            display_error(trans("The selected employee is already assigned to another user"));
            set_focus('employee_id');
            return false;
        }
    }

    if (strlen($_POST['user_id']) < 4) {
        display_error(trans("The user login entered must be at least 4 characters long."));
        set_focus('user_id');
        return false;
    }
    
    if (pref('company.max_user_limit', 1) < (User::where('inactive', 0)->count() + intval($new))) {
        display_error(trans("Maximum users limit reached. Please contact the provider"));
        set_focus('user_id');
        return false;
    }

    if (!$new && ($_POST['new-password'] != "")) {
        if (strlen($_POST['new-password']) < 4) {
            display_error(trans("The password entered must be at least 4 characters long."));
            set_focus('new-password');
            return false;
        }

        if (strstr($_POST['new-password'], $_POST['user_id']) != false) {
            display_error(trans("The password cannot contain the user login."));
            set_focus('new-password');
            return false;
        }
    }

    $role_id = get_post('role_id');
    if (empty($role_id) || empty($userRole = get_security_role($role_id))) {
        display_error(trans("Please select the access level for the user"));
        set_focus('role_id');
        return false;
    }

    if ($canAssignLimitedRolesOnly) {
        $authUserRole = get_security_role($_SESSION['wa_current_user']->access);

        if ($userRole['level'] >= $authUserRole['level'] && $userRole['id'] != $authUserRole['id']) {
            display_error(trans("Your are not authorized to assign a higher level role than yourself"));
            set_focus('role_id');
            return false;
        }
    }

    if (empty($_POST['dflt_dimension_id'])) {
        display_error(trans('Please select the center in which the user belongs to'));
        set_focus('dflt_dimension_id');
        return false;
    }

    return true;
}

//-------------------------------------------------------------------------------------------------

if (
    ($Mode == 'ADD_ITEM' || $Mode == 'UPDATE_ITEM')
    && check_csrf_token()
    && can_process($Mode == 'ADD_ITEM', $selected_id, $canAssignLimitedRolesOnly)
) {
    $permitted_cats = implode(",", $_POST['permitted_categories'] ?? []);
    $allowed_dims = implode(",", $_POST['allowed_dims'] ?? []);

    $employee = getEmployee(($_POST['employee_id'] ?? -1));
    if (!$employee) {
        $employee = [
            "empname" => $_POST['real_name'],
            "email"   => '',
            "mobile_phone" => ''
        ];
    } else {
        $employee = [
            "empname" => !empty($employee['preferred_name']) ? $employee['preferred_name'] : $employee['name'],
            "email"   => $employee['email'],
            "mobile_phone" => $employee['mobile_no']
        ];
    }

    $common_updates = get_post(array(
        'print_profile',
        'rep_popup' => 0,
        'language',
        'govt_credit_account',
        'cash_handover_dr_act',
        'dflt_dimension_id',
        'user_language',
        'purch_req_send_to_level_one' => 0,
        'purch_req_send_to_level_two' => 0,
        'ip_restriction',
        'webuser_id',
        'imm_webuser_id',
        'flow_group_id'
    ));
    $common_updates['permitted_categories']        = $permitted_cats;
    $common_updates['allowed_dims']                = $allowed_dims;

    if ($selected_id != -1) {
        $updates = get_post(array(
            'role_id',
            'pos',
            'is_local',
            'cashier_account'
        ));
        $updates['real_name']   = $employee['empname'];
        $updates['phone']       = $employee['mobile_phone'];
        $updates['email']       = $employee['email'];

        if (User::whereId($selected_id)->value('type') == UT_NORMAL) {
            $updates['type']        = $_POST['user_type'];
            $updates['employee_id'] = $_POST['employee_id'] ?? null;
        }

        update_user_prefs($selected_id, array_merge($common_updates, $updates));

        if ($_POST['new-password'] != "")
            update_user_password($selected_id, $_POST['user_id'], $_POST['new-password']);

        display_notification_centered(trans("User has been updated."));
    } else {
        add_user(
            $_POST['user_id'],
            $employee['empname'],
            app('hash')->make($_POST['new-password']),
            $employee['mobile_phone'],
            $employee['email'],
            $_POST['role_id'],
            $_POST['language'],
            $_POST['print_profile'],
            check_value('rep_popup'),
            $_POST['pos'],
            check_value('is_local'),
            $_POST['cashier_account'],
            $_POST['user_type'],
            $_POST['employee_id'] ?? null
        );
        $id = db_insert_id();
        // use current user display preferences as start point for new user
        $dflt_prefs = $_SESSION['wa_current_user']->prefs->get_all();

        update_user_prefs($id, array_merge($dflt_prefs, $common_updates));

        app('activityLogger')
            ->info(
                "Created user {user_id}",
                [
                    "user_id" => $id,
                    "role_id" => $_POST['role_id']
                ]
            );

        /*-----------------------------END--------------------------*/

        display_notification_centered(trans("A new user has been added."));
    }
    $Mode = 'RESET';
}

//-------------------------------------------------------------------------------------------------

if ($Mode == 'Delete' && check_csrf_token()) {
    $cancel_delete = 0;
    if (key_in_foreign_table($selected_id, 'audit_trail', 'user')) {
        $cancel_delete = 1;
        display_error(trans("Cannot delete this user because entries are associated with this user."));
    }
    if ($cancel_delete == 0) {
        delete_user($selected_id);
        display_notification_centered(trans("User has been deleted."));
    } //end if Delete group
    $Mode = 'RESET';
}

//-------------------------------------------------------------------------------------------------
if ($Mode == 'RESET') {
    $selected_id = -1;
    $sav = get_post('show_inactive', null);
    unset($_POST);    // clean all input fields
    $_POST['show_inactive'] = $sav;
}
 
    
 
 
$result = get_users(check_value('show_inactive'), $_POST['cost_center'] ?? '');
 
 
start_form();

$is_collapsed = !(
    list_updated('cost_center')
    || list_updated('role_id')
    || list_updated('user_type')
    || $Mode == 'Edit'
    || $Mode == 'ADD_ITEM'
);
$collapse_id = 'add-new-user';

br();

dimensions_list_cells(trans('Choose Cost Center :'),'cost_center',null,true,'--All--','','',false);
//dimensions_list_row(trans("Choose Cost Center :"), 'cost_center', null,'---All---',false);

//dimensions_list_row(trans("Select an Employee")." :", 'cost_center', null,  trans("---ALL---"), true, null, true, false,fasle);
list_updated('cost_center');       
submit_cells('RefreshUsers', trans("Search"), '', trans('Refresh Users'), 'default');
$Ajax->activate('_page_body');

echo '<div class="float-right">';
collapse_control('Add/Update User', $collapse_id, $is_collapsed);
echo '</div>';

br(2);

/**------------------------------------------*/

start_collapsible_div($collapse_id, $is_collapsed);

start_table(TABLESTYLE2);

$_POST['email'] = "";
//$_POST['govt_credit_account'] = "";
$permitted_cats = "";
if ($selected_id != -1) {
    if ($Mode == 'Edit') {
        //editing an existing User
        $myrow = get_user($selected_id);

        $_POST['id'] = $myrow["id"];
        $_POST['user_id'] = $myrow["user_id"];
        $_POST['real_name'] = $myrow["real_name"];
        $_POST['phone'] = $myrow["phone"];
        $_POST['email'] = $myrow["email"];
        $_POST['role_id'] = $myrow["role_id"];
        $_POST['language'] = $myrow["language"];
        $_POST['print_profile'] = $myrow["print_profile"];
        $_POST['rep_popup'] = $myrow["rep_popup"];
        $_POST['pos'] = $myrow["pos"];
        $_POST['is_local'] = $myrow["is_local"];
        $_POST['cash_handover_dr_act'] = $myrow['cash_handover_dr_act'];
        $_POST['employee_id'] = $myrow['employee_id'];
        $_POST['user_type'] = $myrow['type'];
        $_POST['cashier_account'] = $myrow['cashier_account'];
        $_POST['govt_credit_account'] = $myrow["govt_credit_account"];
        $_POST['permitted_categories'] = explode(",", $myrow['permitted_categories']);
        $_POST['allowed_dims'] = explode(",", $myrow['allowed_dims']);

        $_POST['user_language'] = $myrow["user_language"];
        $_POST['dflt_dimension_id'] = $myrow["dflt_dimension_id"];
        // $_POST['purch_req_send_to'] = $myrow["purch_req_send_to"];

        $_POST['purch_req_send_to_level_one'] = $myrow["purch_req_send_to_level_one"];
        $_POST['purch_req_send_to_level_two'] = $myrow["purch_req_send_to_level_two"];

        $_POST['ip_restriction'] = $myrow["ip_restriction"];
        $_POST['webuser_id']     = $myrow["webuser_id"];
        $_POST['imm_webuser_id']     = $myrow["imm_webuser_id"];
        $_POST['flow_group_id']  = $myrow['flow_group_id'];

    }
    hidden('selected_id', $selected_id);
    hidden('user_id');

    start_row();
    label_row(trans("User login:"), $_POST['user_id']);
} else { //end of if $selected_id only do the else when a new record is being entered

    text_row(trans("User Login:*"), "user_id", null, 22, 20);
    $_POST['language'] = user_language();
    $_POST['print_profile'] = user_print_profile();
    $_POST['rep_popup'] = user_rep_popup();
    $_POST['pos'] = user_pos();
    $_POST['cashier_account'] = '';
}

$_POST['new-password'] = "";
password_row(trans($selected_id != -1 ? "Password:" : "Password:*"), 'new-password', $_POST['new-password'], 'autocomplete="new-password"');

if ($selected_id != -1) {
    label_row("", trans("Note: Leave empty to keep current."));
}

if ($selected_id != -1 && $Mode == 'Edit' && $_POST['user_type'] != UT_NORMAL) {
    hidden('user_type');
    hidden('employee_id');
    label_row(trans('User type:'), $GLOBALS['user_types'][$_POST['user_type']]);
    $_POST['user_type'] == UT_EMPLOYEE && label_row(trans('Employee:'), $myrow['real_name']);
} else {
    array_selector_row(
        'User type:*',
        'user_type',
        null,
        array_merge(
            [0 => "-- select --"],
            $GLOBALS['user_types']
        ),
        ['select_submit' => true]
    );
    
    if ($_POST['user_type'] == UT_EMPLOYEE) {
        employees_list_rows(trans('Employee:*'), 'employee_id', null, false, true, null, null, false, true);
    }
}

$_POST['user_type'] == UT_EMPLOYEE
    ? hidden('real_name')
    : text_row_ex(trans("Full Name").":", 'real_name', 29, 50);

if(list_updated('user_type')) {
    $Ajax->activate('employee_id');
    $Ajax->activate('real_name');
}

if (list_updated('role_id')) {
    $role_permitted_categories = explode(',', get_security_role(get_post('role_id'))['permitted_categories']);

    if (!empty($role_permitted_categories)) {
        $_POST['permitted_categories'] = $role_permitted_categories;
        $Ajax->activate('permitted_categories');
    }
}

// text_row_ex(trans("Telephone No.:"), 'phone', 30);

// email_row_ex(trans("Email Address:"), 'email', 50);

text_row_ex(trans("Webuser ID:"), 'webuser_id', 30);
text_row_ex(trans("Immig Webuser ID:"), 'imm_webuser_id', 30);

array_selector_row('Enable IP Restriction', 'ip_restriction', null, ["No", "Yes"], null);

$authUserRole = get_security_role($_SESSION['wa_current_user']->access);
$userRole = get_security_role(get_post('role_id')) ?: $authUserRole;
if (
    $canAssignLimitedRolesOnly
    && $userRole['level'] >= $authUserRole['level']
    && $userRole['id'] != $authUserRole['id']
) {
    hidden('role_id');
} else {
    security_roles_list_row(trans("Access Level:"), 'role_id', null, false, true, false, $canAssignLimitedRolesOnly);
}

flow_groups_list_row(trans("Workflow Group"), 'flow_group_id');

bank_accounts_list_row(trans("Cashier Account"), 'cashier_account', null, false, '-- Not Applicable --');

bank_accounts_list_row(trans("E-Dirham Card / Govt. Credit A/C"), 'govt_credit_account', null, false, '-- select --');

gl_all_accounts_list_row(trans("Cash Handover DR A/C"), 'cash_handover_dr_act', null, false, false, "-- select --");

//languages_list_row(trans("Language:"), 'language', null);

hidden('language', $_POST['language']);

// $options = array('select_submit' => true, 'disabled' => null, 'id' => 'user_language');
// $select_opt = array(
//     "EN" => "ENGLISH",
//     "AR" => "ARABIC"
// );
//echo '<tr><td class="label">User Language </td><td>' . array_selector('user_language', $_POST['user_language'], $select_opt, $options) . '</td> </tr>';


dimensions_list_row(trans('Default Cost Center:*'), 'dflt_dimension_id', null, true, '-No Applicable-');

dimensions_list_row(trans('Allowed Cost Centers:'), 'allowed_dims', null, false, null, false, 1, false, true);

users_list_rows2(trans('Level One - Purchase Request Send To'). ":", 'purch_req_send_to_level_one',
    null, false, '-- select --', null, '0');

users_list_rows2(trans('Level Two - Purchase Request Send To') . ":", 'purch_req_send_to_level_two',
    null, false, '-- select --', null, '0');

pos_list_row(trans("User's POS") . ':', 'pos', null);

print_profiles_list_row(trans("Printing profile") . ':', 'print_profile', null,
    trans('Browser printing support'));

check_row(trans("Use popup window for reports:"), 'rep_popup', $_POST['rep_popup'],
    false, trans('Set this option to on if your browser directly supports pdf files'));

start_row();    
label_cell("Local Nationality ? :", "style='line-height: 1.5; vertical-align: top'");
label_cell(
    checkbox(null, 'is_local', null, false, trans('Set this option if the user is a local nationality'))
    . '<br>' . trans("Note: Commission will only be disbursed to employees if it has been enabled."),
    'style="line-height: 1.5;"'
);
end_row();

stock_categories_list_row(trans("Permitted Categories for invoicing:"),
    'permitted_categories', null, false, false, false, true);

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_collapsible_div();

/**------------------------------------------*/

br();

start_table(TABLESTYLE);
$th = array(
        trans("User login"), 
        trans("Full Name"), 
        trans("Phone"),
        trans("E-mail"), 
        trans("Last Visit"), 
        trans("Access Level"), 
        trans("User Type"), 
        "", ""
    );

inactive_control_column($th);
table_header($th);

$k = 0; //row colour counter

while ($myrow = db_fetch($result)) {

    alt_table_row_color($k);

	$time_format = (user_date_format() == 0 ? "h:i a" : "H:i");
	$last_visit_date = sql2date($myrow["last_visit_date"]). " " . 
		date($time_format, strtotime($myrow["last_visit_date"]));

    /*The security_headings array is defined in config.php */
    $not_me = strcasecmp($myrow["user_id"], $_SESSION["wa_current_user"]->username);

    label_cell($myrow["user_id"]);
    label_cell($myrow["real_name"]);
    label_cell($myrow["phone"]);
    email_cell($myrow["email"]);
    label_cell($last_visit_date, "nowrap");
    label_cell($myrow["role"]);
    label_cell($myrow["user_type"]);

    if ($not_me)
        inactive_control_cell($myrow["id"], $myrow["inactive"], 'users', 'id');
    elseif (check_value('show_inactive'))
        label_cell('');

    edit_button_cell("Edit" . $myrow["id"], trans("Edit"));
    if ($not_me)
        delete_button_cell("Delete" . $myrow["id"], trans("Delete"));
    else
        label_cell('');
    end_row();

} //END WHILE LIST LOOP

inactive_control_row($th);
end_table(1);

end_form();
end_page();

?>
<style>
    #permitted_categories {
        height: 143px !important;
    }

    .tablestyle2 td:first-child {
        text-align: right;
    }
</style>

<script>

    // alert(1)
    $(document).ready(function () {

        var cats = '<?php $permitted_cats ?>';

        var psdy = 123;

        console.log(cats);

        // var permitted_categories_select=$("select[name='permitted_categories[]']").select2();
        // permitted_categories_select.val(["1", "4"]);
        // permitted_categories_select.trigger("change");
    });

</script>
