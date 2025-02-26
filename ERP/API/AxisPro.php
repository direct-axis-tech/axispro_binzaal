<?php
/**
 * Class API_Call
 * Created By : Bipin
 */

ini_set('memory_limit', '500M');

include_once "AxisProPagination.php";

class AxisPro
{
    /**
     * Returns a validation error response
     *
     * @param string $message
     * @param integer $code
     * @return void
     */
    public static function ValidationError($message = 'Please check your inputs for errors', $code = 422)
    {
        http_response_code($code);
        echo json_encode([
            'status' => $code,
            'message' => $message
        ]);
        exit();
    }

    /**
     * @param $data
     * @param string $format
     * @return mixed
     * Return HTTP Response
     *
     * TODO : For other formats, now implemented only JSON and ARRAY
     */
    public static function SendResponse($data, $format = 'json')
    {
        //$format = isset($_GET['format']) ? $_GET['format'] : 'json';
        if ($format == 'json') {
            echo json_encode($data);
            exit();
        }

//        dd($data);

        return $data;

    }


    /**
     * @param $total_rows
     * @param int $per_page
     * @return bool|string
     * Generate pagination
     */
    public static function paginate($total_rows,$per_page = 200)
    {

        if (empty($total_rows))
            return false;

        $pagConfig = array(
            'baseURL' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}",
            'totalRows' => $total_rows,
            'perPage' => $per_page
        );
        $pagination = new AxisProPagination($pagConfig);
        return $pagination->createLinks();
    }


    /**
     * @param $table_to_insert
     * @param array $cols
     * @param $files_input - eg: $_FILES['file']
     * @param array $extra_fields - Extra common fields like **foreign key**
     * @param bool $skip_first_row
     * @param bool $skip_last_row
     * @return bool|mysqli_result|resource
     * Custom function for import CSV file directly into tables
     * 28-11-2019
     * Bipin
     */
    public static function import_csv($table_to_insert, $cols = [], $files_input, $extra_fields = [],
                                      $skip_first_row = true, $skip_last_row = true)
    {

        $file_name = $files_input["tmp_name"];
        $file_type = $files_input['type'];

        //Return if file type is not CSV
        if ($file_type != "application/vnd.ms-excel")
            return false;

        if ($files_input["size"] > 0) {

            $file = fopen($file_name, "r");
            $fp = file($file_name);
            $total_rows = count($fp);
            $batch_array = [];
            $i = 0;

            while (($row = fgetcsv($file, 10000, ",")) !== FALSE) {

                //Skip first row, eg: Headers
                if ($i == 0 && $skip_first_row) {
                    $i++;
                    continue;
                };

                //Skip last row, eg: Totals
                if ($i + 1 == $total_rows && $skip_last_row) {
                    $i++;
                    continue;
                };

                $i++;

                //Preparing batch arrays
                $pre_batch = [];
                for ($index = 0; $index < count($cols); $index++) {

                    $field = $cols[$index];
                    $field_value = db_escape($row[$index]);

                    if (is_array($field)) {

                        //Checking field type
                        //If field type is amount then, removing the commas from numeric string
                        if (isset($field['type'])) {

                            if ($field['type'] == 'amount') {
                                $field_value = rm_comma($row[$index]);
                            }

                            if ($field['type'] == 'date') {

                                $from_fmt = $field['format'];
                                $to_fmt = 'Y-m-d';

                                $date = DateTime::createFromFormat($from_fmt, trim($field_value,"'"));
                                $field_value = db_escape($date->format($to_fmt));
                            }

                            $field = $field['field'];
                        }
                    }

                    $pre_batch[$field] = $field_value;
                }

                //Handling additional cols
                //Eg: Foreign key
                if (!empty($extra_fields)) {

                    foreach ($extra_fields as $key => $val)
                        $pre_batch[$key] = $val;

                }
                $batch_array[] = $pre_batch;
            }

            return db_insert_batch($table_to_insert, $batch_array);

        }

        return false;

    }


    /**
     * Catch the exceptions and write them in to log
     * @param $e
     * @return array
     */
    public static function catchException($e)
    {
        $code = $e->getCode();
        $message = explode(':', $e->getMessage());
        Log::warning($code . "->" . $e->getMessage() . "(" . $e->getFile() . ":" . $e->getLine() . ")");
        if ($code < 100 || $code > 599)
            $code = 500;

        return array('msg' => trim($message[1]), 'code' => $code);
    }



    public static function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }


    /** Random number for barcode
     * @param int $length
     * @param null $check_table
     * @param null $field
     * @return string
     */
    public static function GenerateBarCode($length = 12, $check_table = null, $field = null)
    {
        $barcode = '';

        for ($i = 0; $i < $length; $i++) {
            $barcode .= mt_rand(0, 9);
        }

        $sql = "SELECT * FROM $check_table WHERE $field = $barcode";
        $result = db_query($sql, "can't retrieve child trans");

        if (db_num_rows($result) > 1) {
            self::GenerateBarCode($length, $check_table, $field);
        }
        return $barcode;

    }



}