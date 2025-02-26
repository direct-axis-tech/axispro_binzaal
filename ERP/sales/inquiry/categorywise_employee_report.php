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
/**********************************************************************
 * Page for searching item list and select it to item selection
 * in pages that have the item dropdown lists.
 * Author: bogeyman2007 from Discussion Forum. Modified by Joe Hunt
 ***********************************************************************/

$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/db/items_db.inc");

$js="";
$total_count=[];
$total_commission=[];


if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}
 
$canAccess['OWN']   = user_check_access('SA_EMPANALYTIC');
$canAccess['DEP']   = user_check_access('SA_EMPANALYTICDEP');
$canAccess['ALL']   = user_check_access('SA_EMPANALYTICALL');

$page_security = in_array(true, $canAccess, true) ? 'SA_ALLOW' : 'SA_DENIED';
$inputs = getInputs();

if (isset($_POST['exportToExcel'])) {
    exportReport($inputs, $canAccess);
    exit();
}

page(trans($help_context = trans("Employee-Category-Sales")), false, false, "", $js);

if (get_post("search") || list_updated('dimension_id')) {
    $Ajax->activate("item_tbl");
}

start_form(false, false, $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']); {
    start_table(TABLESTYLE_NOBORDER); {
        start_row(); {
            date_cells(
                trans("From"),
                'filter_date',
                trans('Filter by Date'),
                true,
                0,
                0,
                0,
                null,
                false
            );
            date_cells(
                trans("Till"),
                'filter_date_to',
                trans('Filter by Date'),
                true,
                0,
                0,
                0,
                null,
                false
            );

            users_list_cells2(
                trans("Select a User: "),
                'filter_user',
                null,
                false,
                '--all user--'
            );

           
            if ($canAccess['ALL'] || $canAccess['DEP']) {
                dimensions_list_cells(
                    "Cost Center",
                    'dimension_id',
                    null,
                    true,
                    'All'
                );
            }
            check_cells(trans("Show only Locals"), 'show_locals');
            submit_cells("search", trans("Search"), "", trans("Search items"), "default");
            submit_cells('exportToExcel', trans("EXPORT"), '', "Export to EXCEL", "process");
        } end_row();
    } end_table();
} end_form();

if (abs( (strtotime($inputs['till']) - strtotime($inputs['from'])) / 60 / 60 / 24 ) > 31) {
    return display_error("Date period should not exceed by 31 days");
}
$result = getReport($inputs, $canAccess);

div_start('item_tbl'); {
    start_table(TABLESTYLE); {
        $k = 0;
        $numberHeaders = array_merge(array_column($result['categories'], 'description'), ['Total']);
        $categoryIds = array_keys($result['categories']);
        $decimal = $inputs['type'] == 'count' ? 0 : user_price_dec();
        $formatCell = function ($value) use ($decimal) {
            label_cell(number_format2($value, $decimal), 'nowrap align="right"');
            
        };

        start_row();
        foreach(['User ID', 'Employee ID', 'Employee Name'] as $lbl) {
            labelheader_cell($lbl, 'rowspan="2"');
        }
        foreach($numberHeaders as $header) {
            labelheader_cell($header, 'colspan="2"');
        }
        end_row();
        start_row();
        foreach($numberHeaders as $header) {
            labelheader_cell('Count');
            labelheader_cell('Commission');
        }
        end_row();

        foreach ($result['reports'] as $rep) {
        
            alt_table_row_color($k);
            label_cell($rep['username']." - ".$rep['name']);
            label_cell($rep['employee_ref']);
            label_cell($rep['employee_name'], 'class="text-nowrap"');
            foreach ($categoryIds as $categoryId) {
                $formatCell($rep[$categoryId]['countByCategoryPerUser']);
                $formatCell($rep[$categoryId]['commissionByCategoryPerUser']);
            }
            $formatCell($rep['total']['countByCategoryPerUser']);
            $formatCell($rep['total']['commissionByCategoryPerUser']);
            end_row();
        }

        if($canAccess['DEP'] || $canAccess['ALL']) {
            alt_table_row_color($k);
            echo '<td colspan="3" class="text-center">Total</td>';
            foreach ($categoryIds as $categoryId) {
                $formatCell($result['totals'][$categoryId]['countByCategoryPerUser']);
                $formatCell($result['totals'][$categoryId]['commissionByCategoryPerUser']);
            }
            $formatCell($result['totals']['total']['countByCategoryPerUser']);
            $formatCell($result['totals']['total']['commissionByCategoryPerUser']);
            end_row();
        }
    } end_table();
} div_end();

end_page();

// PHP Helper Functions
/**
 * Get the report form the database
 *
 * @param array $filters
 * @param array $canAccess
 * @return array
 */
function getReport($filters, $canAccess) {
    $where = "usr.inactive = 0"
        . " AND trans.`type` IN ('".ST_SALESINVOICE."','".ST_CUSTCREDIT."')"
        . " AND (trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount) <> 0"
        . " AND trans.tran_date >= '{$filters['from']}'"
        . " AND trans.tran_date <= '{$filters['till']}'";

    if (!$canAccess['ALL']) {
        if (!$canAccess['DEP']) {
            $where .= " AND usr.id = " . user_id();
        } else {
            $allowedDims = implode(",", $_SESSION['wa_current_user']->allowed_dims);
            $where .= " AND (trans.dimension_id IN ({$allowedDims}))";
        }
    }

    if (!empty($filters['filter_user'])) {
        $userId = db_escape($filters['filter_user']);
        $where .= " AND (usr.id = {$userId})";
    }

    if (!empty($filters['dimension_id'])) {
        $dimensionId = db_escape($filters['dimension_id']);
        $where .= " AND (trans.dimension_id = {$dimensionId})";
    }

    if ($filters['locals_only']) {
        $where .= " AND usr.is_local = 1";
    }

    $users = collect(get_users(false)->fetch_all(MYSQLI_ASSOC))->keyBy('id');

    $factor = "if(trans.`type` = '".ST_CUSTCREDIT."', -1, 1)";
    $sql = (
        "SELECT
            usr.id user_id,
            usr.user_id username,
            usr.real_name name,
            trans.tran_date `date`,
            usr.dflt_dimension_id dim_id,
            stock.category_id,
            SUM({$factor} * detail.quantity) as `count`,
            SUM({$factor} * (detail.user_commission + detail.cust_comm_emp_share) * detail.quantity) as `gross_commission`,
            SUM({$factor} * detail.user_commission * detail.quantity) as `commission`
        FROM `0_debtor_trans` trans
        LEFT JOIN `0_debtor_trans_details` detail ON
            detail.debtor_trans_type = trans.`type`
            AND detail.debtor_trans_no = trans.trans_no
        LEFT JOIN `0_stock_master` stock ON
            stock.stock_id = detail.stock_id
        LEFT JOIN `0_users` usr ON
            usr.id = COALESCE(detail.transaction_id_updated_by, detail.created_by)
        WHERE {$where}
        GROUP BY usr.id, trans.tran_date, stock.category_id"
        
    );

    $mysqliResult = db_query($sql, "Could not retrieve report");
    $summery = [
        "countByDatePerUser" => [],
        "countByCategoryPerUser" => [],
        "commissionByCategoryPerUser" => []
    ];
    $commissionCol = pref('axispro.show_comm_total_in_reports', 0) ? 'gross_commission' : 'commission';
    $_report = [];
    $_users = [];
    while ($r = db_fetch_assoc($mysqliResult)) {
        $_report
            [$r['dim_id']]
            [$r['user_id']]
            [] = $r;
        $_users[$r['user_id']] = true;

        // summarize count by category per user
        if (!isset(
            $summery
                ['countByCategoryPerUser']
                [$r['category_id']]
                [$r['user_id']]
            )
        ) {
            $summery
                ['countByCategoryPerUser']
                [$r['category_id']]
                [$r['user_id']] = 0;
        }
        $summery
            ['countByCategoryPerUser']
            [$r['category_id']]
            [$r['user_id']] += $r['count'];

        // summarize commission by category per user
        if (!isset(
            $summery
                ['commissionByCategoryPerUser']
                [$r['category_id']]
                [$r['user_id']]
            )
        ) {
            $summery
                ['commissionByCategoryPerUser']
                [$r['category_id']]
                [$r['user_id']] = 0;
        }
        $summery
            ['commissionByCategoryPerUser']
            [$r['category_id']]
            [$r['user_id']] += $r[$commissionCol];
    }

    $categories = get_item_categories_keyed_by_id(true);
    $categories = array_intersect_key($categories, $summery['countByCategoryPerUser']);

    // Build the Report
    $keys = ['countByCategoryPerUser','commissionByCategoryPerUser'];
    $reports = [];
    $users = $users->intersectByKeys($_users);
    foreach ($users as $userId => $user) {
        $report = [
            "username" => $user['user_id'],
            "name" => $user['real_name'],
            "employee_ref" => $user['employee_ref'],
            "employee_name" => $user['employee_name'],
            "total" => [
                "countByCategoryPerUser" => 0,
                "commissionByCategoryPerUser" => 0
            ]
        ];
        foreach ($categories as $categoryId => $_) {
            $report[$categoryId]['countByCategoryPerUser'] = $summery['countByCategoryPerUser'][$categoryId][$userId] ?? 0;
            $report['total']['countByCategoryPerUser'] += $report[$categoryId]['countByCategoryPerUser'];

            $report[$categoryId]['commissionByCategoryPerUser'] = $summery['commissionByCategoryPerUser'][$categoryId][$userId] ?? 0;
            $report['total']['commissionByCategoryPerUser'] += $report[$categoryId]['commissionByCategoryPerUser'];
        }
        $reports[] = $report;
    }

    $totals = [
        'total' => [
            "countByCategoryPerUser" => 0,
            "commissionByCategoryPerUser" => 0
        ]
    ];
    foreach ($categories as $categoryId => $category) {
        $totals[$categoryId]['countByCategoryPerUser'] = array_sum($summery['countByCategoryPerUser'][$categoryId]);
        $totals['total']['countByCategoryPerUser'] += $totals[$categoryId]['countByCategoryPerUser'];
        
        $totals[$categoryId]['commissionByCategoryPerUser'] = array_sum($summery['commissionByCategoryPerUser'][$categoryId]);
        $totals['total']['commissionByCategoryPerUser'] += $totals[$categoryId]['commissionByCategoryPerUser'];
    }

    return compact('categories', 'reports', 'totals');
}

/**
 * Export the report to excel
 *
 * @param array $filters
 * @param array $canAccess
 * @return array
 */
function exportReport($filters, $canAccess) {
    global $path_to_root;

    include_once($path_to_root . "/reporting/includes/excel_report.inc");
    $keys = ['countByCategoryPerUser','commissionByCategoryPerUser'];
    $result = getReport($filters, $canAccess);
    $categoryIds = array_keys($result['categories']);
    $columns = [];
    $columns[] = [
        "key" => "username",
        "title" => "User ID",
        "width" => 50,
        "align" => 'left'
    ];
    $columns[] = [
        "key" => "employee_ref",
        "title" => "Employee ID",
        "width" => 50,
        "align" => 'left'
    ];
    $columns[] = [
        "key" => "employee_name",
        "title" => "Employee Name",
        "width" => 50,
        "align" => 'left'
    ];
    foreach ($result['categories'] as $categoryId => $category) {
        $columns[] = [
            "key" => $categoryId."count",
            "title" => "count".$category['description'],
            "width" => 35,
            "align" => 'right'
        ];
        $columns[] = [
            "key" => $categoryId."commission",
            "title" => "commission".$category['description'],
            "width" => 35,
            "align" => 'right'
        ];
        
    }
    $columns[] = [
        "key" => "totalcount",
        "title" => "Total_count",
        "width" => 50,
        "align" => 'left'
    ];
    $columns[] = [
        "key" => "totalcommission",
        "title" => "Total_commission",
        "width" => 50,
        "align" => 'left'
    ];

    $page = 'A3';
    $orientation = 'L';
    $decimal = $filters['type'] == 'count' ? 0 : user_price_dec();
    $params = [
        "",
        ['text' => _('Period'), 'from' => sql2date($filters['from']), 'to' => sql2date($filters['till'])],
        ['text' => _('Type'), 'from' => ucfirst($filters['type']), 'to' => ''],
    ];
    if (!empty($filters['dimension_id'])) {
        $params[] = [
            'text' => 'Department',
            'from' => get_dimension($filters['dimension_id'])['name'],
            'to' => ''
        ];
    }

    $colInfo = new ColumnInfo($columns, $page, $orientation);

    $rep = new FrontReport(
        _('Employee Category Sales Report'),
        "EmployeeCategorySalesReport",
        $page,
        9,
        $orientation
    );
    $colPoints = $colInfo->cols();
    $colPoints2 = [$colPoints[$colInfo->x1("$categoryId.'count'")]];
    foreach($categoryIds as $categoryId) {
       $colPoints2[] = $colPoints[$colInfo->x2("$categoryId.'commission'")];
    }
    $colPoints2[] = $colPoints[$colInfo->x2('totalcommission')];

    foreach($result['categories'] as $categoryId => $category){
        $headers2[]= [trans($category['description'])];
    }
    // $l=sizeof($headers2);
    // $headers2[$l]="Total";
    // $rep->NewLine();
    $aligns2 = 'right';

    $rep->Font();
    $rep->Info(
        $params,
        $colInfo->cols(),
        $colInfo->headers(),
        $colInfo->aligns(),
        $colPoints2,$headers2,$aligns2
    );
    $rep->NewPage();
       

    foreach ($result['reports'] as $report) {
        $rep->TextCol(
            $colInfo->x1('username'),
            $colInfo->x2('username'),
            $report['username']
        );

        foreach ($categoryIds as $categoryId) {
            $rep->AmountCol(
                $colInfo->x1($categoryId."count"),
                $colInfo->x2($categoryId."count"),
                $report[$categoryId]["countByCategoryPerUser"],
                $decimal
            );
            $rep->AmountCol(
                $colInfo->x1($categoryId."commission"),
                $colInfo->x2($categoryId."commission"),
                $report[$categoryId]["commissionByCategoryPerUser"],
                $decimal
            );
        }

        $rep->AmountCol(
            $colInfo->x1("totalcount"),
            $colInfo->x2("totalcount"),
            $report['total']['countByCategoryPerUser'],
            $decimal
        );

        $rep->AmountCol(
            $colInfo->x1("totalcommission"),
            $colInfo->x2("totalcommission"),
            $report['total']['commissionByCategoryPerUser'],
            $decimal
        );
        $rep->NewLine();
    }

    if($canAccess['DEP'] || $canAccess['ALL']){
        $rep->TextCol(
            $colInfo->x1('username'),
            $colInfo->x2('username'),
            'Total'
        );
 
        foreach ($categoryIds as $categoryId) {
            $rep->AmountCol(
                $colInfo->x1($categoryId."count"),
                $colInfo->x2($categoryId."count"),
                $result['totals'][$categoryId]['countByCategoryPerUser'],
                $decimal
            );
            $rep->AmountCol(
                $colInfo->x1($categoryId."commission"),
                $colInfo->x2($categoryId."commission"),
                $result['totals'][$categoryId]['commissionByCategoryPerUser'],
                $decimal
            );
        }
        $rep->AmountCol(
            $colInfo->x1('totalcount'),
            $colInfo->x2('totalcount'),
            $result['totals']['total']['countByCategoryPerUser'],
            $decimal
        );
        $rep->AmountCol(
            $colInfo->x1("totalcommission"),
            $colInfo->x2("totalcommission"),
            $result['totals']['total']['commissionByCategoryPerUser'],
            $decimal
        );
        $rep->NewLine();
    }

    $rep->End();
}

/**
 * Retrieves the validated inputs from the POST Variable
 *
 * @return void
 */
function getInputs() {
    $inputs = [
        'type' => 'count',
        'dimension_id' => null,
        'from' => date(DB_DATE_FORMAT),
        'till' => date(DB_DATE_FORMAT),
        'locals_only' => intval(!empty($_POST['show_locals']))
    ];



    if (!empty($_POST['filter_user']) ) {
        $inputs['filter_user'] = $_POST['filter_user'];
    }

    if (!empty($_POST['dimension_id']) && is_numeric($_POST['dimension_id'])) {
        $inputs['dimension_id'] = $_POST['dimension_id'];
    }

    if (!empty($_POST['filter_date'])) {
        $inputs['from'] = date2sql($_POST['filter_date']) ?: $inputs['from'];
    }

    if (!empty($_POST['filter_date_to'])) {
        $inputs['till'] = date2sql($_POST['filter_date_to']) ?: $inputs['till'];
    }

    return $inputs;
}