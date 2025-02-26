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

use Illuminate\Support\Facades\Auth;

define('VARLIB_PATH', $path_to_root . '/tmp');
define('VARLOG_PATH', $path_to_root . '/tmp');

//============================================================================
if (!isset($path_to_root)) {
    $path_to_root = ".";
}

// Prevent register_globals vulnerability
if (isset($_GET['path_to_root']) || isset($_POST['path_to_root']))
    die("Restricted access");
    
require_once $path_to_root . '/laravel/utils/bootstrap_session.php';

$SysPrefs = new sys_prefs();
$Session_manager = new SessionManager();
$Session_manager->sessionStart();

$_SESSION['SysPrefs'] = &$SysPrefs;

app(\App\Amc::class)->enforceValidity();

// set to reasonable values if not set in config file (pre-2.3.12 installations)
if ((!isset($SysPrefs->login_delay)) || ($SysPrefs->login_delay < 0))
    $SysPrefs->login_delay = 10;

if ((!isset($SysPrefs->login_max_attempts)) || ($SysPrefs->login_max_attempts < 0))
    $SysPrefs->login_max_attempts = 3;

if ($SysPrefs->go_debug > 0)
	$cur_error_level = -1;
else
	$cur_error_level = E_USER_WARNING|E_USER_ERROR|E_USER_NOTICE;

error_reporting($cur_error_level);
ini_set("display_errors", "On");

if ($SysPrefs->error_logfile != '') {
    ini_set("error_log", $SysPrefs->error_logfile);
    ini_set("ignore_repeated_errors", "On");
    ini_set("log_errors", "On");
}

/*
 * Uncomment the setting below when using FA on shared hosting
 * to avoid unexpeced session timeouts.
 * Make sure this directory exists and is writable!
 */
// ini_set('session.save_path', VARLIB_PATH.'/');

hook_session_start(@$_POST["company_login_name"]);

// this is to fix the "back-do-you-want-to-refresh" issue - thanx PHPFreaks
header("Cache-control: private");

get_text_init();

// Page Initialisation
if (!isset($_SESSION['wa_current_user']) || !$_SESSION['wa_current_user']->logged_in()
    || !isset($_SESSION['language']) || !method_exists($_SESSION['language'], 'set_language')) {
    $l = array_search_value($dflt_lang, $installed_languages, 'code');
    $_SESSION['language'] = new language($l['name'], $l['code'], $l['encoding'],
        (isset($l['rtl']) && $l['rtl'] === true) ? 'rtl' : 'ltr');
}

$_SESSION['language']->set_language($_SESSION['language']->code);


// Ajax communication object
$Ajax = new Ajax();

// js/php validation rules container
$Validate = array();
// bindings for editors
$Editors = array();
// page help. Currently help for function keys.
$Pagehelp = array();

$Refs = new references();

// intercept all output to destroy it in case of ajax call
register_shutdown_function('end_flush');
ob_start('output_html', 0);

if (!isset($_SESSION["wa_current_user"]))
    $_SESSION["wa_current_user"] = new current_user();

html_cleanup($_GET);
html_cleanup($_POST);
html_cleanup($_REQUEST);
html_cleanup($_SERVER);

/*
 * If FA_LOGOUT_PHP_FILE is defined it means this is a logout reqeust,
 * so there is no need to check the login status of the user.
 * we can directly logout whichever user's session is this.
 */
if (!defined('FA_LOGOUT_PHP_FILE')) {

    login_timeout();

    if (!$_SESSION["wa_current_user"]->old_db && file_exists($path_to_root . '/company/' . user_company() . '/installed_extensions.php')) {
        include($path_to_root . '/company/' . user_company() . '/installed_extensions.php');
    }

    install_hooks();

    if (
        !Auth::check()
        || isset($_GET['login_method'])
    ) {
        // Checks if this is a password reset request.
        if (@$SysPrefs->allow_password_reset && !$SysPrefs->allow_demo_mode
            && (isset($_GET['reset']) || isset($_POST['email_entry_field']))) {
            if (!isset($_POST["email_entry_field"])) {
                include($path_to_root . "/access/password_reset.php");
                exit();
            } else {
                if (isset($_POST["company_login_nickname"]) && !isset($_POST["company_login_name"])) {
                    for ($i = 0; $i < count($db_connections); $i++) {
                        if ($db_connections[$i]["name"] == $_POST["company_login_nickname"]) {
                            $_POST["company_login_name"] = $i;
                            unset($_POST["company_login_nickname"]);
                            break 1; // cannot pass variables to break from PHP v5.4 onwards
                        }
                    }
                }
                $_succeed = isset($db_connections[$_POST["company_login_name"]]) &&
                    $_SESSION["wa_current_user"]->reset_password($_POST["company_login_name"],
                        $_POST["email_entry_field"]);
                if ($_succeed) {
                    password_reset_success();
                }

                password_reset_fail();
            }
        }

        // Show login screen
        if (empty($_POST["user_name_entry_field"])) {
            /*
             * If the user is not logged in and the username field is not set
             * save his current url and redirect the user to the login page.
             * 
             * In case of Ajax requests strip ajax marker from uri, to force synchronous page reload
             */
            $_SESSION['timeout'] = array(
                'uri' => preg_replace('/JsHttpRequest=(?:(\d+)-)?([^&]+)/s', '', html_specials_encode($_SERVER['REQUEST_URI'])),
                'post' => $_POST
            );


            if (isAjax()) {
                echo json_encode(['status' => 'LOGIN_TIME_OUT', 'msg' => 'Login timeout, Please login again']);
                exit();
            }

            if (in_ajax()) {
                $Ajax->redirect($_SESSION['timeout']['uri']);
                exit;
            }

            header("Location: $path_to_root/../login.php");
            exit();
        } else {

            # Verify captcha
            /* $post_data = http_build_query(
                 array(
                     'secret' => '6LdDGmIUAAAAANU4uiLM-fPytmJ4E_pMD7eZ_tJI',
                     'response' => $_POST['g-recaptcha-response'],
                     'remoteip' => $_SERVER['REMOTE_ADDR']
                 )
             );
             $opts = array('http' =>
                 array(
                     'method'  => 'POST',
                     'header'  => 'Content-type: application/x-www-form-urlencoded',
                     'content' => $post_data
                 )
             );
             $context  = stream_context_create($opts);
             $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
             $result = json_decode($response);
             if (!$result->success) {
                 $_SESSION['captcha_error'] = 1;
                 login_fail();
             } */

             /*
              * Check if this is a bruteforce attack and prevent the login attempt
              * Since now we are using custom login we need to perform this check here.
              * otherwise the check was being performed in the login.php
              */
            if (check_faillog()) {
                kill_login();
                echo json_encode([
                    "success" => false,
                    "reason" => 'Too many failed login attempts. Please wait a while or try later.'
                ]);
                exit();
            }

            if (isset($_POST["company_login_nickname"]) && !isset($_POST["company_login_name"])) {
                for ($i = 0; $i < count($db_connections); $i++) {
                    if ($db_connections[$i]["name"] == $_POST["company_login_nickname"]) {
                        $_POST["company_login_name"] = $i;
                        unset($_POST["company_login_nickname"]);
                        break 1; // cannot pass variables to break from PHP v5.4 onwards
                    }
                }
            }
            // logs in the user
            $succeed = isset($db_connections[$_POST["company_login_name"]]) &&
                $_SESSION["wa_current_user"]->login($_POST["company_login_name"],
                    $_POST["user_name_entry_field"], $_POST["password"]);

            // validates if the user is allowed to access the application from outside the organisation
            if (
                $SysPrefs->prefs['ip_restriction']
                && $_SESSION["wa_current_user"]->is_enabled_ip_restriction
            ) {
                $address = \IPLib\Factory::parseAddressString(getCurrentClientIP());

                // skip the loopback ip addresses like ::1, 127.0.0.1 etc they are always allowed.
                if ($address->getRangeType() !== \IPLib\Range\Type::T_LOOPBACK) {
                    $isAllowed = false;
                    foreach (explode(',', $SysPrefs->prefs['org_ip']) as $ipRange) {
                        $ipRange = \IPLib\Factory::parseRangeString(trim($ipRange));
                        if ($ipRange->contains($address)) {
                            $isAllowed = true;
                            break;
                        }
                    }
                    if (!$isAllowed) {
                        kill_login();
                        echo json_encode([
                            "success" => false,
                            "reason" => 'You are not allowed to access the system from outside the organisation'
                        ]);
                        exit();
                    }
                }
            }

            if ($succeed) {
                /*
                 * If in here, It means the login request was successfull.
                 * So regenerate the session ID to avoid any session fixation attack
                 */
                $Session_manager->regenerateSession();

                app('activityLogger')
                    ->info(
                        "{user} Logged in",
                        [ "user" => $_SESSION['wa_current_user']->username]
                    );

                $_SESSION['isInDeveloperSession'] = intval($_SESSION["wa_current_user"]->is_developer_session);
                $authUser = $_SESSION['wa_current_user']->get_user_model();
                $redirectTo = $authUser->is_fixed_home || empty(session()->previousUrl())
                    ? erp_url($authUser->home_url)
                    : session()->previousUrl();

                echo json_encode([
                    "success" => true,
                    "rdr_to"  => $redirectTo
                ]);
                exit();
            } else {
                // Incorrect password
                login_fail();
            }

            $lang = &$_SESSION['language'];
            $lang->set_language($_SESSION['language']->code);
        }
    } else {
        // if already logged-in, initialise the database connections and configurations
        set_global_connection();

        if (db_fixed())
            db_set_encoding($_SESSION['language']->encoding);

        $SysPrefs->refresh();

        if ($SysPrefs->refreshPermissionsOnEachRequest()){
            $_SESSION['wa_current_user']->refreshPermissions();
        }
    }

    if (!isset($_SESSION["App"])) {
        $_SESSION["App"] = new front_accounting();
        $_SESSION["App"]->init();
    }

    LaravelHelpers::injectFAConnectionToLaravel();
}

// POST vars cleanup needed for direct reuse.
// We quote all values later with db_escape() before db update.
$_POST = strip_quotes($_POST);


global $SysPrefs;
if (isset($SysPrefs->maintenance_flag) && $SysPrefs->maintenance_flag) {
    header("Location: $path_to_root/maintenance_mode.php");
    exit;
}

if (in_ajax() && app(\App\Amc::class)->shouldShowExpiryMsg()) {
    $GLOBALS['Ajax']->addScript(true, "\nsetTimeout(Amc.showExpiryReminder, 50);");
}