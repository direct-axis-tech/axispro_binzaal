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

use Illuminate\Support\Facades\Auth;

define("FA_LOGOUT_PHP_FILE","");

$page_security = 'SA_OPEN';
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");

// If this is a logout request, Short circuit it.
// We were previously handling this in access/logout.php file,
// Now we don't need to proceed any further
Auth::logout();

session_destroy();
unset($_SESSION['wa_current_user']);

app('activityLogger')
    ->info(
        "{user} Logged out",
        ["user" => $_SESSION['wa_current_user']->username]
    );

header("Location: $path_to_root/../login.php");