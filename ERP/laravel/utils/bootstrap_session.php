<?php

/**
 * In laravel there are helper functions named trans & today which would cause
 * issues with the already defined function that we use all over.
 * so load this before loading laravel so laravel will not redefine it again
 */

require_once $GLOBALS['path_to_root'] . '/includes/lang/language.inc';

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require_once __DIR__.'/../bootstrap/autoload.php';

// These classes needed to be loaded after autoload but before
// The session is initialized.
require_once $GLOBALS['path_to_root'] . "/sales/includes/cart_class.inc";
require_once $GLOBALS['path_to_root'] . "/purchasing/includes/supp_trans_class.inc";
require_once $GLOBALS['path_to_root'] . "/purchasing/includes/po_class.inc";
require_once $GLOBALS['path_to_root'] . "/includes/ui/allocation_cart.inc";

// Bootstrap laravel
require_once $GLOBALS['path_to_root'] . '/laravel/utils/bootstrap_laravel.php';

// Sets the error handler for anything that follows.
require_once $GLOBALS['path_to_root'] . '/includes/errors.inc';
set_error_handler('error_handler' /*, errtypes */);
set_exception_handler('exception_handler');

require_once $GLOBALS['path_to_root'] . '/laravel/utils/session_helpers.php';
require_once $GLOBALS['path_to_root'] . '/includes/current_user.inc';
require_once $GLOBALS['path_to_root'] . '/frontaccounting.php';
require_once $GLOBALS['path_to_root'] . '/admin/db/security_db.inc';
require_once $GLOBALS['path_to_root'] . '/includes/lang/language.inc';
require_once $GLOBALS['path_to_root'] . '/config_db.php';
require_once $GLOBALS['path_to_root'] . '/includes/ajax.inc';
require_once $GLOBALS['path_to_root'] . '/includes/ui/ui_msgs.inc';
require_once $GLOBALS['path_to_root'] . '/includes/prefs/sysprefs.inc';
require_once $GLOBALS['path_to_root'] . '/includes/hooks.inc';

if (!defined('CONSOLE_SESSION')) {
    require_once $GLOBALS['path_to_root'] . '/../helper.php';
}

// include all extensions hook files.
foreach ($installed_extensions as $ext) {
    if (file_exists($GLOBALS['path_to_root'] . '/' . $ext['path'] . '/hooks.php'))
        include_once ($GLOBALS['path_to_root'] . '/' . $ext['path'] . '/hooks.php');
}

// Optionally include the faillog
if (file_exists(VARLIB_PATH . '/faillog.php')) {
    include_once VARLIB_PATH . '/faillog.php';
}

require_once $GLOBALS['path_to_root'] . '/includes/access_levels.inc';
require_once $GLOBALS['path_to_root'] . '/version.php';
require_once $GLOBALS['path_to_root'] . '/includes/main.inc';
require_once $GLOBALS['path_to_root'] . '/includes/app_entries.inc';