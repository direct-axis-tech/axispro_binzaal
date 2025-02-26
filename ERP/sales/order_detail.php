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

use App\Exceptions\BusinessLogicException;

$path_to_root = "..";
$page_security = 'SA_SALES_LINE_VIEWs';

require_once __DIR__ . "/../includes/session.inc";
require_once __DIR__ . "/../includes/data_checks.inc";
require_once __DIR__ . "/../sales/includes/sales_ui.inc";
require_once __DIR__ . "/../sales/includes/sales_db.inc";
require_once __DIR__ . "/../sales/includes/ui/sales_order_ui.inc";
require_once __DIR__ . "/../API/AxisPro.php";

global $Refs, $Ajax;

// Handle the requests
$request = request();
switch ($request->query('action')) {
    case 'complete':
        if (!user_check_access('SA_SALESDELIVERY')) {
            return AxisPro::ValidationError('The security settings on your account do not permit you to access this function', 403);
        }

        try {
            complete_transaction($request);
    
            http_response_code(204);
            echo json_encode([
                'status' => 204,
                'message' => 'Transaction completed successfully'
            ]);
            exit();
        }
        catch (BusinessLogicException $e) {
            return AxisPro::ValidationError($e->getMessage(), 422);
        }
        exit();
    
    default:
        return AxisPro::ValidationError('Bad Request', 400);
        exit();
}
