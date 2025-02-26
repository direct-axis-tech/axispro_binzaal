<?php

/**
 * The output_callback handler which will intercept all outputs sent to the browser
 * 
 * Primarily used to intercept output when is Ajax request
 * 
 * @param string $text The output buffer
 * 
 * @return string The new output buffer
 */
function output_html($text) {
    global $before_box, $Ajax, $messages;
    // Fatal errors are not send to error_handler,
    // so we must check the output
    if ($text && preg_match('/\bFatal error(<.*?>)?:(.*)/i', $text, $m)) {
        $Ajax->aCommands = array();  // Don't update page via ajax on errors
        $text = preg_replace('/\bFatal error(<.*?>)?:(.*)/i', '', $text);
        $messages[] = array(E_ERROR, $m[2], null, null);
    }
    $Ajax->run();
    return in_ajax() ? fmt_errors() : ($before_box . fmt_errors() . $text);
}

/** Unsets all the session data and destroy the session */
function kill_login() {
    session_unset();
    session_destroy();
}

/** Sends the appropriate response when a login attempt is failed */
function login_fail() {
    global $path_to_root;

    $_SESSION['login_failed'] = true;


    if (
        isset($_GET['login_method'])
        && $_GET['login_method'] == 'AJAX'
    ) {
        echo json_encode([
            "success" => false,
            "reason" => "The username or password is incorrect"
        ]);
        exit();
    }

    // header("HTTP/1.1 401 Authorization Required");
	// echo "<center><br><br><font size='5' color='red'><b>" . _("Incorrect Password") . "<b></font><br><br>";
	// echo "<b>" . _("The user and password combination is not valid for the system.") . "<b><br><br>";
	
	// echo _("If you are not an authorized user, please contact your system administrator to obtain an account to enable you to use the system.");
	// echo "<br><a href='$path_to_root/index.php'>" . _("Try again") . "</a>";
  	// echo "</center>";
	// kill_login();
	// die();
    header("Location: $path_to_root/index.php");

    die();
}

/** Sends the appropriate response when a password reset attempt is failed */
function password_reset_fail() {
    global $path_to_root;

    echo "<center><br><br><font size='5' color='red'><b>" . trans("Incorrect Email") . "<b></font><br><br>";
    echo "<b>" . trans("The email address does not exist in the system, or is used by more than one user.") . "<b><br><br>";

    echo trans("Plase try again or contact your system administrator to obtain new password.");
    echo "<br><a href='$path_to_root/index.php?reset=1'>" . trans("Try again") . "</a>";
    echo "</center>";

    kill_login();
    die();
}

/** Sends the appropriate response when a password reset attempt succeed */
function password_reset_success() {
    global $path_to_root;

    echo "<center><br><br><font size='5' color='green'><b>" . trans("New password sent") . "<b></font><br><br>";
    echo "<b>" . trans("A new password has been sent to your mailbox.") . "<b><br><br>";

    echo "<br><a href='$path_to_root/index.php'>" . trans("Login here") . "</a>";
    echo "</center>";

    kill_login();
    die();
}

/**
 * Validates the number of failed login attempts of the user
 * 
 * @return bool 
 * 'true' if attempts per IP address exceed the configured maximum attempts and
 * the time when the user can retry for the next attempt is not yet reached.
 * 'false' other wise
 */
function check_faillog() {
    global $SysPrefs, $login_faillog;

    $user = $_SESSION["wa_current_user"]->user;

    if (@$SysPrefs->login_delay && (@$login_faillog[$user][getCurrentClientIP()] >= @$SysPrefs->login_max_attempts) && (time() < $login_faillog[$user]['last'] + $SysPrefs->login_delay))
        return true;

    return false;
}

/**
 * Invalidates the cache if php caching is active to ensure the file is re-read on next request
 * 
 * @param string $filename The file name for which the cache needs to be invalidated.
 * 
 * @return void
 */
function cache_invalidate($filename) {
    // OpCode extension
    if (function_exists('opcache_invalidate'))
        opcache_invalidate($filename);
}

/**
 * Logs or clears the login attempts log based on the result of the login attempt
 * 
 * Simple brute force attack detection is performed before connection to company database is open.
 * Therefore access counters have to be stored in file.
 * Login attempts counter is created for every new user IP, which partialy prevent DOS attacks.
 * 
 * @param string $login The username for which the login attempt is being made
 * @param bool $result Whether the login attempt succeeded or not
 * 
 * @return void
 */
function write_login_filelog($login, $result)
{
    global $login_faillog, $SysPrefs, $path_to_root;

    $user = $_SESSION["wa_current_user"]->user;

    $ip = getCurrentClientIP();

    if (!isset($login_faillog[$user][$ip]) || $result) // init or reset on successfull login
        $login_faillog[$user] = array($ip => 0, 'last' => '');

    if (!$result) {
        $login_faillog[$user][$ip]++;
        if ($login_faillog[$user][$ip] < @$SysPrefs->login_max_attempts) {
            app('activityLogger')
                ->notice(
                    "Failed login attempt on {account}",
                    ["account" => $login]
                );
        } else {
            /*
             * Restarts the conunter.
             * Comment out to restart counter only after successfull login.
             */
            // $login_faillog[$user][$ip] = 0;
            error_log(sprintf(trans("Brute force attack on account '%s' detected. Access for non-logged users temporarily blocked."), $login));
            app('activityLogger')
                ->warning(
                    "Brute force attack on {account} detected",
                    [
                        "account" => $login,
                        "attempt" => $login_faillog[$user][$ip],
                    ]
                );
        }
        $login_faillog[$user]['last'] = time();
    }

    $msg = "<?php\n";
    $msg .= "/*\n";
    $msg .= "Login attempts info.\n";
    $msg .= "*/\n";
    $msg .= "\$login_faillog = " . var_export($login_faillog, true) . ";\n";

    $filename = VARLIB_PATH . "/faillog.php";

    if ((!file_exists($filename) && is_writable(VARLIB_PATH)) || is_writable($filename)) {
        file_put_contents($filename, $msg);
        cache_invalidate($filename);
    }
}


/**
 * Authorises the user to view the page currently being reqeusted
 * 
 * Checks if the user is authorised to view the current page and the
 * database connections are OK before the user can proceed.
 * 
 * Note: This terminates the request if not authorised or database is not OK by sending the appropriate response.
 * 
 * @param string $page_security The key for the security area to be validated
 * 
 * @return void
 */
function check_page_security($page_security)
{
    global $SysPrefs;

    $msg = '';

    if (!$_SESSION["wa_current_user"]->check_user_access()) {
        // notification after upgrade from pre-2.2 version
        $msg = $_SESSION["wa_current_user"]->old_db ?
            trans("Security settings have not been defined for your user account.")
            . "<br>" . trans("Please contact your system administrator.")
            : trans("Please remove \$security_groups and \$security_headings arrays from config.php file!");
    } elseif (!$SysPrefs->db_ok && !$_SESSION["wa_current_user"]->can_access('SA_SOFTWAREUPGRADE')) {
        $msg = trans('Access to application has been blocked until database upgrade is completed by system administrator.');
    }

    if ($msg) {
        display_error($msg);
        end_page(@$_REQUEST['popup']);
        kill_login();
        exit;
    }

    if (!$_SESSION["wa_current_user"]->can_access_page($page_security)) {

        echo "<center><br><br><br><b>";
        echo trans("The security settings on your account do not permit you to access this function");
        echo "</b>";
        echo "<br><br><br><br></center>";
        end_page(@$_REQUEST['popup']);
        exit;
    }
    if (!$SysPrefs->db_ok
        && !in_array($page_security, array('SA_SOFTWAREUPGRADE', 'SA_OPEN', 'SA_BACKUP'))) {
        display_error(trans('System is blocked after source upgrade until database is updated on System/Software Upgrade page'));
        end_page();
        exit;
    }

}

/**
 * Sets the page security level depeding on GET request parameter OR the provided value
 * 
 * Note: The default page_security should be set to the default value as a fallback before calling this function
 * 
 * @param int|string $key 
 * If the key is set in trans array then the value at this key in trans array is set as the security level
 * 
 * @param array $trans 
 * The array containing the security_area indexed using the probable keys
 * 
 * @param array $gtrans The array containing the security_area indexed using probable get parameters.
 * If any of the key in this arrray is set in the _GET global variable,
 * then the curresponding security level provided at that key is set as the page's security level.
 * This has the higher priority over the trans array.
 * 
 * @return void
 */
	
function set_page_security($key = null, $trans = array(), $gtrans = array())
{
    global $page_security;

    // first check is this is not start page call
    foreach ($gtrans as $_key => $area)
        if (isset($_GET[$_key])) {
            $page_security = $area;
            return;
        }

    // then check session value
    if (isset($trans[$key])) {
        $page_security = $trans[$key];
        return;
    }
}

/** 
 * Removes the magic quotes from the data recursively
 *
 * @param string|array $data
 * 
 * @return string|array The sanitized data.
 */
function strip_quotes($data)
{
	if(version_compare(phpversion(), '5.4', '<') && get_magic_quotes_gpc()) {
		if(is_array($data)) {
			foreach($data as $k => $v) {
				$data[$k] = strip_quotes($data[$k]);
            }
        } else
            return stripslashes($data);
    }
    return $data;
}

/**
 * Encodes the HTML special characters in the string
 * 
 * Note: htmlspecialchars does not support certain encodings. 
 *       ISO-8859-2 fortunately has the same special characters positions as
 *       ISO-8859-1, so fix is easy. If any other unsupported encoding is used,
 *       add workaround here.
 * 
 * @param string $str
 * 
 * @return string The sanitised string
 */
function html_specials_encode($str)
{
    return htmlspecialchars($str, ENT_QUOTES, $_SESSION['language']->encoding == 'iso-8859-2' ?
        'ISO-8859-1' : $_SESSION['language']->encoding);
}

/**
 * Decodes the HTML special characters in the string
 * 
 * Note: htmlspecialchars does not support certain encodings. 
 *       ISO-8859-2 fortunately has the same special characters positions as
 *       ISO-8859-1, so fix is easy. If any other unsupported encoding is used,
 *       add workaround here.
 * 
 * @param string $str
 * 
 * @return string The sanitized string
 */
function html_specials_decode($str)
{
    return @html_entity_decode($str, ENT_QUOTES, $_SESSION['language']->encoding == 'iso-8859-2' ?
        'ISO-8859-1' : $_SESSION['language']->encoding);
}

/**
 * Cleans up the HTML special charecters from the array recursively
 * 
 * @param array $param
 * 
 * @return void
 */
function html_cleanup(&$parms)
{
    foreach ($parms as $name => $value) {
        if (is_array($value))
            html_cleanup($parms[$name]);
        else
            $parms[$name] = html_specials_encode($value);
    }
    reset($parms); // needed for direct key() usage later throughout the sources
}

/**
 * Validates the user's activeness
 * 
 * Logs out the user if is inactive for more than the allowed timout limit - 
 * or, update the last active time for the user.
 */
function login_timeout()
{
    // skip timeout on logout page
    if ($_SESSION["wa_current_user"]->logged) {
        $tout = $_SESSION["wa_current_user"]->timeout;
        if ($tout && (time() > $_SESSION["wa_current_user"]->last_act + (int)$tout)) {
            $_SESSION["wa_current_user"]->logged = false;
        }
        $_SESSION["wa_current_user"]->last_act = time();
    }
}

/**
 * Get real IPv4 address of the client
 * @return string
 */
function getCurrentClientIP() {
    if (getenv('HTTP_CLIENT_IP')) {
        $ipAddress = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ipAddress = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ipAddress = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ipAddress = getenv('HTTP_FORWARDED_FOR');
    } elseif (getenv('HTTP_FORWARDED')) {
        $ipAddress = getenv('HTTP_FORWARDED');
    } elseif (getenv('REMOTE_ADDR')) {
        $ipAddress = getenv('REMOTE_ADDR');
    } else {
        $ipAddress = null;
    }

    return $ipAddress;
}

/**
 * Build the url from the associative array
 * 
 * The reverse function for the PHP's native parse_url
 * @param array $parsed_url The associative array containing different segments of the the URL 
 * @return string
 */
function makeUrl($parsed_url) {
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return "$scheme$user$pass$host$port$path$query$fragment";
}