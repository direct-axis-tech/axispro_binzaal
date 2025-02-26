<?php

use App\Models\System\AccessRole;
use App\Models\System\User;
use Illuminate\Support\Facades\Auth;

PHP_SAPI === 'cli' or die('not allowed');

const CONSOLE_SESSION = true;

if (!isset($GLOBALS['path_to_root'])) {
    $GLOBALS['path_to_root'] = '.';
}

if (realpath($GLOBALS['path_to_root']) != realpath(__DIR__.'/..')) {
    die('Directory not supported. Please cd to the directory where the file recedes');
}

define('VARLIB_PATH', $GLOBALS['path_to_root'] . '/tmp');
define('VARLOG_PATH', $GLOBALS['path_to_root'] . '/tmp');

require_once __DIR__ . '/../laravel/utils/bootstrap_session.php';

function set_language() {
    get_text_init();
    global $dflt_lang, $installed_languages;

    $l = array_search_value($dflt_lang, $installed_languages, 'code');
    $_SESSION['language'] = new language(
        $l['name'],
        $l['code'],
        $l['encoding'],
        (isset($l['rtl']) && $l['rtl'] === true) ? 'rtl' : 'ltr'
    );
    $_SESSION['language']->set_language($_SESSION['language']->code);
}

function shutdown() {
    session_destroy();
}

register_shutdown_function('shutdown');
session_start();

$GLOBALS['Ajax'] = new Ajax();
$GLOBALS['SysPrefs'] = new sys_prefs();
$GLOBALS['Refs'] = new references();
set_language();
set_global_connection();

LaravelHelpers::injectFAConnectionToLaravel();

$_SESSION['wa_current_user'] = new current_user();
$_SESSION['wa_current_user']->user = User::SYSTEM_USER;
Auth::login(User::make(['id' => User::SYSTEM_USER]));
authUser()->setRelation('role', AccessRole::make());
$_SESSION['wa_current_user']->set_user_data(authUser());
