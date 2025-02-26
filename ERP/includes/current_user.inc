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

use App\Models\System\User;
include_once($path_to_root . "/includes/prefs/userprefs.inc");
if (!defined('TB_PREF')) {
	define('TB_PREF', '&TB_PREF&');
}
//--------------------------------------------------------------------------

class current_user
{
	var $user = 0;
	var $loginname;
	var $username;
	var $name;
	var $email;
	var $company; // user's company
	var $pos;
	var $access;
	var $timeout;
	var $last_act;
	var $role_set = [];
	var $old_db;
	var $logged;
	var $ui_mode = 0;
	var $axispro_config;
	var $default_cost_center;
	var $cashier_account;
    var $card_account = null;
    var $allowed_dims = [];
    var $is_enabled_ip_restriction;
    var $home_tab = "dashboard";
    var $is_developer_session = false;
    var $employee_id = null;

    /**
     * Checks if this user have a given permission
     *
     * @var App\Models\System\User
     */
    protected $user_model;

	var $prefs;
	var $cur_con; // current db connection (can be different from $company for superuser)
	function __construct()
	{
		global $def_coy;
		
		$this->loginname = $this->username = $this->name = "";
		$this->company = isset($def_coy)? $def_coy : 0;
		$this->logged = false;
		$this->default_cost_center = 0;
		$this->cashier_account = null;
        $this->allowed_dims = [];
		$this->prefs = new user_prefs();
        $this->is_enabled_ip_restriction = true;
	}

	function logged_in()
	{
		return $this->logged;
	}

	/**
	 * Returns the user's preference if exists or null if doesn't
	 *
	 * @param string $pref
	 * @return mixed
	 */
	function get_pref(string $pref)
	{
		return $this->prefs->get($pref);
	}

	function set_company($company)
	{
		$this->company = (int)$company;
	}

	function login($company, $loginname, $password)
	{
		global $security_areas, $security_groups, $security_headings, $path_to_root, $dflt_lang, $SysPrefs;

		$this->set_company($company);
	    $this->logged = false;

		set_global_connection($company);
		$lang = &$_SESSION['language'];
		$lang->set_language($_SESSION['language']->code);
		db_set_encoding($_SESSION['language']->encoding);

		$user = User::active()->where('user_id', $loginname)->first();

		// There is no user or the user is inactive
		if (!$user) {
			return false;
		}

		// Use external authentication source if any.
		// Keep in mind you need to have user data set for $loginname
		// in FA users table anyway to successfully log in.
		$Auth_Result = hook_authenticate($loginname, $password);

		//Developer Login
        $isDeveloperSession = app('hash')->check($password, config('auth.developer_credential'));
		if ($isDeveloperSession) { 
			$Auth_Result = true;
		}
		// if not used: standard method
		if (!isset($Auth_Result)) {
			$Auth_Result = get_user_auth($loginname, md5($password));

			/** 
			 * if login successfull, it means we are using md5 as hashing function,
			 * update the password to use another strong hashing function; so
			 * laravel will be able to use the strong hash.
			 * This way, we don't have to bother the users
			 * 
			 * Since we are in the middle of rewriting the existing legacy codebase and we have to still
			 * use the server, this is the only way at the moment to do this without
			 * interupting the work
			 */
			if ($Auth_Result) {
				$newStrongHash = app('hash')->make($password);
				User::where('user_id', $loginname)
					->update(['password' => $newStrongHash]);
			}
		}

		/**
		 * If the password does not match, check if it matches using
		 * the strong hashing function that we used to update the passwords
		 * in the previous step
		 */
		if (!$Auth_Result) {
			$Auth_Result = app('hash')->check($password, $user->password);
		}

		if ($SysPrefs->login_delay > 0)
			write_login_filelog($loginname, $Auth_Result);

		if ($Auth_Result)
		{
            /** Logs in the user: also in laravel */
			$_SESSION[auth()->getName()] = $user->getAuthIdentifier();

			if ($user->language != $dflt_lang)
			{	 // refresh language and user data
 				$lang->set_language($user->language);
				db_set_encoding($_SESSION['language']->encoding);
				$user = $user->refresh();
			}

			include_once $path_to_root."/admin/db/fiscalyears_db.inc";

			$this->old_db = isset($user->full_access);
			if (!$user->inactive) {
                $this->set_user_data($user, $isDeveloperSession);
			}
        }
		return $this->logged;
	}

	function set_user_data(User $user, $isDeveloperSession = false) {
		$this->role_set = $user->role->permissions;
		$this->user_model = $user;
		$this->access = $user->role_id;
		$this->name = $user->real_name;
		$this->pos = $user->pos;
		$this->loginname = $user->user_id;
		$this->username = $this->loginname;
		$this->employee_id = $user->employee_id;
		$this->prefs = new user_prefs($user);
		$this->user = $user->id;
		$this->email = $user->email;
		update_user_visitdate($this->username);
		$this->logged = true;
		$this->last_act = time();
		$this->timeout = session_timeout();
		$this->default_cost_center = empty($user->dflt_dimension_id) ? 0 : $user->dflt_dimension_id;
		$this->cashier_account = $user->cashier_account;
		$this->card_account = $user->govt_credit_account ?? null;
		if (!empty(trim($user->allowed_dims))) {
			$this->allowed_dims = explode(',', trim($user->allowed_dims));
		}
		$this->allowed_dims[] = $this->default_cost_center;
		$this->is_enabled_ip_restriction = $user->ip_restriction;
		$this->is_developer_session = $isDeveloperSession;

		if (!empty($user->startup_tab)) {
			$this->home_tab = $user->startup_tab;
		}

		flush_dir(user_js_cache()); // refresh cache on login

		//AXISPRO CONFIG
		$this->axispro_config['curr_fs_yr'] = get_current_fiscalyear();

		$this->role_name = $user->role->name;
	}

	function reset_password($company, $email) {
		global $SysPrefs;

		$this->set_company($company);
		$this->logged = false;

		set_global_connection();

		$user = get_user_by_email($email);

		if ($user != false) {

			$password = generate_password();

			update_user_password($user['id'], $user['user_id'], $password);
			
			$sender = get_company_pref('email');
			if (empty($sender))
				$header = "";
			else
				$header = "From: $sender";

			mail($email, trans("New password for")." ".$SysPrefs->app_title, $password, $header);

			return true;
		}
   		return false;
    }

	function check_user_access()
	{
		global $security_groups;
		if ($this->old_db) {
			// notification after upgrade from pre-2.2 version
			return isset($security_groups) && is_array(@$security_groups[$this->access]);
		} else
			return !isset($security_groups) && is_array($this->role_set);
	}

	function can_access($sec_area)
	{
		return $this->user_model->hasPermission($sec_area);
	}

	function can_access_page($page_level)
	{
		return $this->can_access($page_level);
	}

	function check_application_access($waapp)
	{
		if (!$this->hide_inaccessible_menu_items())
		{
			return true;
		}

		foreach ($waapp->modules as $module)
		{
			if ($this->check_module_access($module))
			{
				return true;
			}
		}

		return false;

	}

	function check_module_access($module)
	{

		if (!$this->hide_inaccessible_menu_items())
		{
			return true;
		}

		if (sizeof($module->lappfunctions) > 0)
		{
			foreach ($module->lappfunctions as $appfunction)
			{
				if ($appfunction->label != "" && $this->can_access_page($appfunction->access))
				{
					return true;
				}
			}
		}

		if (sizeof($module->rappfunctions) > 0)
		{
			foreach ($module->rappfunctions as $appfunction)
			{
				if ($appfunction->label != "" && $this->can_access_page($appfunction->access))
				{
					return true;
				}
			}
		}

		return false;

	}

	function hide_inaccessible_menu_items()
	{
		global $SysPrefs;

		if (!isset($SysPrefs->hide_inaccessible_menu_items) || $SysPrefs->hide_inaccessible_menu_items == 0)
		{
			return false;
		}

		else
		{
			return true;
		}
	}

	function set_db_connection($id = -1)
	{
		return set_global_connection($id);
	}

	function update_prefs($prefs)
	{
		global $SysPrefs;

		if (!$SysPrefs->allow_demo_mode) {
			update_user_prefs($this->user, $prefs);
		}

		$this->prefs = new user_prefs(get_user($this->user));
	}

	/**
	 * Refresh users permission after logged in
	 * 
	 * Warning: Never call this while in production!
	 *
	 * Note: 
	 * The permissions are cached once the user login.
	 * So call this manually if need to refresh the permissions 
	 * after any permission updates without relogin.
	 * 
	 * Useful while in developement|debug mode
	 * 
	 * @return bool whether the refresh is success or not
	 */
	function refreshPermissions()
    {
		if (!$this->logged) {
			return false;
		}

		$this->user_model->refresh();
        $this->user_model->role->refresh();

		$this->access = $this->user_model->role_id;
		$this->role_set = $this->user_model->role->permissions;

		return true;
	}

    /**
     * Retrieves employee of this user
     * 
     * @return array|null
     */
    function getEmployee()
    {
        $sql = (
            "SELECT
                emp.*,
                CONCAT(emp.emp_ref, ' - ', emp.name) formatted_name,
                job.department_id
            FROM 0_users AS user
            LEFT JOIN 0_employees AS emp ON user.employee_id = emp.id
            LEFT JOIN 0_emp_jobs AS job ON emp.id = job.employee_id AND job.is_current = 1
            WHERE
                emp.status = '1'
                AND user.id = {$this->user}
            ORDER BY emp.id, job.id DESC LIMIT 1"
        );
        return db_query(
            $sql,
            "Could not retrieve the current employee's details"
        )->fetch_assoc();
    }

    /**
     * Get the user model associated with this user
     *
     * @return App\Models\System\User
     */
    function get_user_model()
    {
        return $this->user_model;
    }
}

//--------------------------------------------------------------------------

//
//	Current ui mode.
//
function fallback_mode() {
    return $_SESSION["wa_current_user"]->ui_mode==0;
}



// 2008-06-15. Added extra parameter $stock_id and reference for $dec
//--------------------------------------------------------------------
function qty_format($number, $stock_id=null, &$dec) {
	$dec = get_qty_dec($stock_id);
    return number_format2($number, $dec);
}

// and get_qty_dec
function get_qty_dec($stock_id=null)
{
	global $path_to_root;
	include_once($path_to_root."/inventory/includes/db/items_units_db.inc");
	if ($stock_id != null)
		$dec = get_unit_dec($stock_id);
	if ($stock_id == null || $dec == -1 || $dec == null)
		$dec = user_qty_dec();
	return $dec;
}

function user_company()
{
	global $def_coy;
	
	return isset($_SESSION["wa_current_user"]) ? $_SESSION["wa_current_user"]->company : $def_coy;
}

function user_check_access($sec_area)
{
	return $_SESSION["wa_current_user"]->can_access($sec_area);
}

/** 
 * Retruns the Current logged in user's Id if possible or -1 otherwise
 * 
 * @return int
 */
function user_id()
{
	return isset($_SESSION['wa_current_user']) && !empty($_SESSION['wa_current_user']->user)
			? $_SESSION['wa_current_user']->user
			: -1;
}

/**
 * Retrieves the current employee if defined
 * @return array|null
 */
function getCurrentEmployee() {
    return $_SESSION['wa_current_user']->getEmployee();
}

/**
 * Alias function to check if a user has access to any of the specified security area
 *
 * @param string[] $secAreas
 * @return boolean
 */
function canAccessAny($secAreas) {
	foreach ($secAreas as $secArea){
		if ($_SESSION["wa_current_user"]->can_access($secArea)) {
			return true;
		}
	}
	return false;
}

function set_user_prefs($prefs)
{
	$_SESSION["wa_current_user"]->update_prefs($prefs);
}

function add_user_js_data() {
	global $path_to_root, $SysPrefs;

	$ts = $SysPrefs->thoseps[user_tho_sep()];
	$ds = $SysPrefs->decseps[user_dec_sep()];

    $js = "\n"
	  . "var user = {\n"
	  . "theme: '". $path_to_root . '/themes/'. user_theme().'/'."',\n"
	  . "loadtxt: '".trans('Requesting data...')."',\n"
	  . "date: '".Today()."',\n"	// server date
	  . "datesys: ".$SysPrefs->date_system.",\n"
	  . "datefmt: ".user_date_format().",\n"
	  . "datesep: '".$SysPrefs->dateseps[user_date_sep()]."',\n"
	  . "ts: '$ts',\n"
	  . "ds: '$ds',\n"
	  . "pdec : " . user_price_dec() . "}\n";

  add_js_source($js);
}

function user_js_cache($id=null)
{
	global $path_to_root;

	if (!$id)
		$id = @$_SESSION['wa_current_user']->user;

	if (!$id)
		$id = 0; // before login
	return $path_to_root.'/company/'.user_company().'/js_cache/'.$id;
}

//--------------------------------------------------------------------------

function session_timeout()
{
	$tout = @get_company_pref('login_tout'); // mask warning for db ver. 2.2
	return $tout ? $tout : ini_get('session.gc_maxlifetime');
}

//-----------------------------------------------------------------------------
//	Inserts $elements into $array at position $index.
//	$elements is list of any objects
//
function array_insert(&$array, $index, $elements)
{
	if (!is_array($elements)) $elements = array($elements);

	$head  = array_splice($array, 0, $index);
	$array = array_merge($head, $elements, $array);
}

function array_remove(&$array, $index, $len=1)
{
	array_splice($array, $index, $len);
}

function array_substitute(&$array, $index, $len, $elements)
{
	array_splice($array, $index, $len);
	array_insert($array, $index, $elements);
}

function array_append(&$array, $elements)
{
	foreach($elements as $key => $el) {
		if(is_int($key))
			$array[] = $el;
		else
			$array[$key] = $el;
	}
}
//
//	Search $needle in $haystack or in $haystack[][$valuekey]
//	returns $needle found or null.
//
function array_search_value($needle, $haystack, $valuekey=null)
{
	if (is_array($haystack)) {
		foreach($haystack as $key => $value) {
			$val = isset($valuekey) ? @$value[$valuekey] : $value;
			if ($needle == $val){
				return $value;
			}
		}
	}
	return null;
}
//
//	Search $needle in $haystack or in $haystack[][$valuekey]
//	returns array of keys of $haystack elements found
//
function array_search_keys($needle, $haystack, $valuekey=null)
{
	$keys = array();
	if (is_array($haystack)) {
		foreach($haystack as $key => $value) {
			$val = isset($valuekey) ? @$value[$valuekey] : $value;
			if ($needle == $val){
				$keys[] = $key;
			}
		}
	}	
	return $keys;
}
//
//	Find first (single) $needle in $haystack or in $haystack[][$valuekey]
//	returns $haystack element found or null
//
function array_search_key($needle, $haystack, $valuekey=null)
{
	$keys = array_search_keys($needle, $haystack, $valuekey);
	return @$keys[0];
}

// Recalculate report columns if orientation is landscape.
function recalculate_cols(&$cols)
{
	$factor = (user_pagesize() == "A4" ? 1.4 : 1.3);
	foreach($cols as $key => $col)
		$cols[$key] = intval($col * $factor); 
}

function flush_dir($path, $wipe = false) 
{
	if (!file_exists($path))
		return;
	$dir = @opendir($path);
	if(!$dir)
		return;

	while(false !== ($fname = readdir($dir))) {
		if($fname=='.' || $fname=='..' || $fname=='CVS' || (!$wipe && $fname=='index.php')) continue;
  		if(is_dir($path.'/'.$fname)) {
		    flush_dir($path.'/'.$fname, $wipe);
		    if ($wipe) @rmdir($path.'/'.$fname);
		} else
		    @unlink($path.'/'.$fname);
	}
}
/*
	Returns current path to company private folder.
	(Current path can change after chdir).
*/
function company_path($comp=null)
{
	global $path_to_root, $SysPrefs;

	$comp_path = $SysPrefs->comp_path;


	if (!isset($comp))
		$comp = user_company();

	// if path is relative, set current path_to_root
	return ($comp_path[0]=='.' ? $path_to_root.'/'.basename($comp_path) : $comp_path)
			. '/'.$comp;
}

function is_admin_company()
{
	return $this->company == 0;
}

