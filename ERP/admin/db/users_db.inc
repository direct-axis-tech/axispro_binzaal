<?php
/**********************************************************************
    Direct Axis Technology L.L.C.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/

function add_user($user_id, $real_name, $password, $phone, $email, $role_id, 
	$language, $profile, $rep_popup, $pos,$is_local,$cashier_account,$user_type,$employee_id)
{
	$sql = (
        "INSERT INTO ".TB_PREF."users (
            user_id,
            real_name,
            password,
            phone,
            email,
            role_id,
            language,
            pos,
            print_profile,
            rep_popup,
            is_local,
            cashier_account,
            type,
            employee_id,
            created_by
        ) VALUES (
            ".db_escape($user_id).", 
		    ".db_escape($real_name).",
            ".db_escape($password) .",
            ".db_escape($phone).",
            ".db_escape($email).",
            ".db_escape($role_id).",
            ".db_escape($language).",
            ".db_escape($pos).",
            ".db_escape($profile).",
		    ".db_escape($rep_popup).",
		    ".db_escape($is_local).",
		    ".db_escape($cashier_account).",
            ".db_escape($user_type).",
            ".db_escape($employee_id,true).",
            ". user_id() ."
        )"
    );

	$mysqli_result = db_query($sql, "could not add user for $user_id");
    return $mysqli_result;
}

//-----------------------------------------------------------------------------------------------

function update_user_password($id, $user_id, $password)
{
	$password = app('hash')->make($password);
	$sql = "UPDATE ".TB_PREF."users SET password=".db_escape($password) . ",
		user_id = ".db_escape($user_id). " WHERE id=".db_escape($id);

	return db_query($sql, "could not update user password for $user_id");
}

//-----------------------------------------------------------------------------------------------

function update_user($id, $user_id, $real_name, $phone, $email, $role_id, 
	$language, $profile, $rep_popup, $pos)
{
	$sql = "UPDATE ".TB_PREF."users SET real_name=".db_escape($real_name).
	", phone=".db_escape($phone).",
		email=".db_escape($email).",
		role_id=".db_escape($role_id).",
		language=".db_escape($language).",
		print_profile=".db_escape($profile).",
		rep_popup=".db_escape($rep_popup).",
		pos=".db_escape($pos).",
		user_id = " . db_escape($user_id) . "
        updated_by = " . user_id() . "
        WHERE id=" . db_escape($id);
	$mysqli_result = db_query($sql, "could not update user for $user_id");

    app('activityLogger')
        ->info(
            "Updated user {user_id}",
            ["user_id" => $id, "role_id" => $role_id]
        );

    return $mysqli_result;
}

//-----------------------------------------------------------------------------------------------

function update_user_prefs($id, $prefs)
{
    $context = ["user_id" => $id];
    if (isset($prefs['role_id'])) {
        $context['role_id'] = $prefs['role_id'];
    }

	$sql = "UPDATE ".TB_PREF."users SET ";
	foreach($prefs as $name => $value) {
		$prefs[$name] = $name.'='. db_escape($value);
	}
    $prefs['updated_by'] = "updated_by = " . user_id();
	$sql .= implode(',', $prefs) . " WHERE id=".db_escape($id);

	$mysqli_result = db_query($sql, "could not update user display prefs for $id");

    if (isset($context['role_id'])) {
        app('activityLogger')
        ->info("Updated user {user_id}", $context);
    }

    return $mysqli_result;
}

//-----------------------------------------------------------------------------------------------


function get_users($all=false, $dimension_ids = null)
{
	$sql = (
        "SELECT
            u.*,
            CONCAT(
                u.user_id,
                IF(
                    u.real_name = ' ',
                    '',
                    CONCAT(' - ', u.real_name)
                )
            ) formatted_name,
            r.role,
            e.name employee_name,
            e.emp_ref employee_ref,
			CASE 
				WHEN u.type = ". UT_EMPLOYEE ." THEN 'Employee User'
				WHEN u.type = ". UT_NORMAL ." THEN 'Normal User'
				ELSE ' -- '
			END AS user_type
        FROM 0_users u
        INNER JOIN 0_security_roles r ON u.role_id = r.id
        LEFT JOIN 0_employees e ON e.id = u.employee_id
        WHERE 1=1"
    );
	if (!$all) {
        $sql .= " AND !u.inactive";
    }

	if(!empty($dimension_ids)) {
        if (is_array($dimension_ids)) {
            $dimension_ids = implode(",", $dimension_ids);
        }

		$sql .= " AND u.dflt_dimension_id IN ({$dimension_ids}) ";
	}

    $sql.=" order by u.user_id";
 
	 return db_query($sql, "could not get users");


}

//-----------------------------------------------------------------------------------------------

function get_user($id)
{
	$sql = "SELECT * FROM ".TB_PREF."users WHERE id=".db_escape($id);

	$result = db_query($sql, "could not get user $id");

	return db_fetch($result);
}

function get_entered_by_user($trans_no,$type) {

    $sql = "SELECT audit.trans_no, audit.type, usr.user_id,usr.real_name, audit.stamp AS created_at
        FROM 0_audit_trail audit 
        LEFT JOIN 0_users usr ON usr.id=audit.user 
        WHERE audit.`trans_no` =$trans_no AND audit.`type` = $type
        ORDER BY audit.id ASC
        LIMIT 1";

    $result = db_query($sql);

    return db_fetch($result);

}

//-----------------------------------------------------------------------------------------------

/** 
 * Retrieves the user by the employee ID
 * 
 * @param $employee_id
 * @param $search_inactive
 * 
 * @return array|null
 */
function getUserByEmployee($employee_id, $search_inactive = false) {
    $sql = "SELECT * FROM `0_users` WHERE employee_id = ".db_escape($employee_id);
    if (!$search_inactive) {
        $sql .= " AND NOT inactive";
    }

    return db_query($sql, "Could not retrieve the user")->fetch_assoc();
}

//-----------------------------------------------------------------------------------------------

function get_user_by_login($user_id)
{
	$sql = "SELECT * FROM ".TB_PREF."users WHERE user_id=".db_escape($user_id);

	$result = db_query($sql, "could not get user $user_id");

	return db_fetch($result);
}

//-----------------------------------------------------------------------------------------------

function get_user_by_email($email)
{
	$sql = "SELECT * FROM ".TB_PREF."users WHERE email=".db_escape($email);

	$result = db_query($sql, "could not get user for email $email");

	if (db_num_rows($result) != 1)
		return false;

	return db_fetch($result);
}

//-----------------------------------------------------------------------------------------------

function delete_user($id)
{
	$sql="DELETE FROM ".TB_PREF."users WHERE id=".db_escape($id);

	db_query($sql, "could not delete user $id");
}

//-----------------------------------------------------------------------------------------------

function get_user_auth($user_id, $password)
{

	$sql = "SELECT * FROM ".TB_PREF."users WHERE user_id = ".db_escape($user_id)." AND"
		." password=".db_escape($password);

	return db_num_rows(db_query($sql, "could not get validate user login for $user_id")) != 0;
}

//-----------------------------------------------------------------------------------------------

function update_user_visitdate($user_id)
{
	$sql = "UPDATE ".TB_PREF."users SET last_visit_date='". date("Y-m-d H:i:s") ."'
		WHERE user_id=".db_escape($user_id);

	db_query($sql, "could not update last visit date for user $user_id");
}

//-----------------------------------------------------------------------------------------------
function check_user_activity($id) 
{
	$sql = "SELECT COUNT(*) FROM ".TB_PREF."audit_trail WHERE audit_trail.user="
		. db_escape($id);
	$result = db_query($sql,"Cant check user activity");
	$ret = db_fetch($result);

	return $ret[0];
}

//-----------------------------------------------------------------------------------------------
function show_users_online()
{
	global $db, $GetText, $SysPrefs;

	if (!isset($SysPrefs->show_users_online) || $SysPrefs->show_users_online == 0 || !defined('TB_PREF') || 
		!isset($GetText) || !isset($db))
		return "";
	$result = db_query("SHOW TABLES LIKE '".TB_PREF."useronline'");	
	if (db_num_rows($result) == 1)
	{
		$timeoutseconds = 120;

		$timestamp=time();
		$timeout=$timestamp-$timeoutseconds;
		/*
		This will find out if user is from behind proxy server. 
		In that case, the script would count them all as 1 user.
		This function tryes to get real IP address.
		*/
		if (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED'];
		}
		elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_FORWARDED_FOR'];
		}
		elseif (isset($_SERVER['HTTP_FORWARDED'])) {
			$ip = $_SERVER['HTTP_FORWARDED'];
		}
		else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		// Add user to database
		db_query("INSERT INTO ".TB_PREF."useronline (timestamp, ip, file) VALUES ('". $timestamp ."',". db_escape($ip) .",". db_escape($_SERVER['PHP_SELF']) .")");
		//Remove users that were not online within $timeoutseconds.
		db_query("DELETE FROM ".TB_PREF."useronline WHERE timestamp<". $timeout);

		// Select online users
		$result = db_query("SELECT DISTINCT ip FROM ".TB_PREF."useronline");
		$users = db_num_rows($result);
	}
	else
		$users = 1;
	return "$users ".($users == 1 ? trans("user online") : trans("users online"));

}
