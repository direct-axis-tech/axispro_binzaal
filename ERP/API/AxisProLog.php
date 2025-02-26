<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 12/7/2019
 * Time: 4:27 PM
 * Created By : Bipin
 */


$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/taxes/tax_calc.inc");
include_once($path_to_root . "/admin/db/shipping_db.inc");
include_once($path_to_root . "/themes/daxis/kvcodes.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/admin/db/company_db.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");

include_once("AxisPro.php");

ini_set('memory_limit', '500M');

class AxisProLog
{

    /**
     * @param $id
     * @param string $format
     * @return mixed
     * read a log
     */
    public static function read($id, $format = 'json')
    {
        try {
            $sql = "SELECT * FROM 0_activity_log WHERE id = $id";
            $result = db_query($sql);
            $return_result = db_fetch_assoc($result);
            return AxisPro::SendResponse($return_result, $format);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @param $query
     * @return array
     */
    public static function logQuery($query)
    {
        try {

            self::analyseQuery($query);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @param $query
     * @return array
     */
    public static function analyseQuery($query)
    {
        try {

            $query_type = self::queryType($query);

            $tables_involved = [];
            if(preg_match_all('/((FROM|JOIN) `(.*)`)/', $query, $matches)) {
                $tables_involved = array_unique($matches[3]);
            }

            return [
                'type' => $query_type,
                'tables' => $tables_involved,
            ];



        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @param $query
     * @return array|bool
     */
    public static function queryType($query)
    {

        try {

            $_TRIM_MASK_WITH_PAREN = "( \t\n\r\0\x0B";

            return strtoupper(
                substr(
                    ltrim($query, $_TRIM_MASK_WITH_PAREN), 0, 6
                )
            );

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

}