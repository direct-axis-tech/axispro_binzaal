<?php

require __DIR__ . '/../ERP/laravel/bootstrap/autoload.php';

define('DT_FORMAT', 'Y-m-d H:i:s');
date_default_timezone_set('Asia/Dubai');
$db_config  = getConnectionConfigs();
$GLOBALS['mysqli'] = $mysqli = new mysqli(...$db_config);

if ($mysqli->connect_errno) {
    writeLog("Could not connect to Database");
    exit();
}

$location_id = $_GET['loc'] ?? '1';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (!preg_match('/^[0-9]+$/', $location_id)) {
        echo "Invalid location id recieved";
        _exit('Invalid location id recieved from client');
    }

    $last_recorded_id = $mysqli->query("SELECT ref_id FROM `0_empl_punchinouts` WHERE loc = '{$location_id}' ORDER BY id DESC LIMIT 1");
    if ($last_recorded_id) {
        $last_recorded_id = $last_recorded_id->fetch_assoc();
        echo ($last_recorded_id ? $last_recorded_id['ref_id'] : '0'); 
    } else {
        echo 'Could Not retrieve the last recorded id';
    }
    _exit();
} else {
    $secret_key = include(__DIR__ . DIRECTORY_SEPARATOR . 'secret_key.php');
    if ($_POST['key'] != $secret_key) {
        echo "Security token mismatch";
        _exit("Request received from untrusted source");
    }
    $data = json_decode($_POST['data'], true);
    if (empty($data)) {
        _exit("Could not parse the JSON data");
    }

    /** Skip the duplicates if our basis for the punching is time instead of id */
    // $last_records = $mysqli->query(
    //     "SELECT empid, `loc`, authdatetime
    //     FROM `0_empl_punchinouts`
    //     WHERE
    //         loc = '{$location_id}'
    //         AND authdatetime = (SELECT authdatetime FROM `0_empl_punchinouts` WHERE loc = '{$location_id}' ORDER BY authdatetime DESC LIMIT 1)"
    // );
    // if (!$last_records) {
    //     _exit("Could not fetch the last record");
    // }
    // $last_records = $last_records->fetch_all(MYSQLI_ASSOC);
    // if (!empty($last_records)) {
    //     $last_authdatetime = reset($last_records)['authdatetime'];
    //     foreach ($data as $index => $row) {
    //         $authdatetime = (new DateTime($row['authdatetime']))->format(DT_FORMAT);
    //         if ($authdatetime == $last_authdatetime) {
    //             if (!empty(array_filter($last_records, function ($rec) use ($row) {
    //                 return (
    //                     $row['empid'] == $rec['empid']
    //                     && ($row['loc'] ?? '1') == $rec['loc']
    //                 );
    //             }))) {
    //                 unset($data[$index]);
    //             }
    //             continue;
    //         }
    //         break;
    //     }
    // }
    
    /** reorder the keys */
    $data = array_values($data);
    $totalRecords = count($data);
    $recordsPerBatch = 1000;
    $batches = ceil($totalRecords / $recordsPerBatch);
    
    if (false === $mysqli->query("START TRANSACTION")) {
        writeLog("Transaction could not be started");
    }
    for($i = 0; $i < $batches; $i++) {
        $current_offset = $i * $recordsPerBatch;
        $current_batch_limit = $current_offset + $recordsPerBatch;

        $values = [];
        for ($j = $current_offset; ($j < $totalRecords && $j < $current_batch_limit); $j++) {
            $row = [
                quote($mysqli->real_escape_string($data[$j]['id'])),
                quote($mysqli->real_escape_string($data[$j]['loc'] ?? '1')),
                quote($mysqli->real_escape_string($data[$j]['empid'])),
                quote($mysqli->real_escape_string($data[$j]['authdatetime'])),
                quote($mysqli->real_escape_string($data[$j]['authdate'])),
                quote($mysqli->real_escape_string($data[$j]['authtime'])),
                quote($mysqli->real_escape_string($data[$j]['status'])),
                quote($mysqli->real_escape_string($data[$j]['devicename'])),
                quote($mysqli->real_escape_string($data[$j]['deviceserialno'])),
                quote($mysqli->real_escape_string($data[$j]['person'])),
                quote($mysqli->real_escape_string($data[$j]['cardno']))
            ];
            $value = '(' . implode(",", $row) . ')';
            $values[] = $value;
        }
        $values = implode(",\n", $values);
        $sql = "INSERT IGNORE INTO 0_empl_punchinouts (ref_id, loc, empid, authdatetime, authdate, authtime, `status`, devicename, deviceserialno, person, cardno)VALUES\n{$values}";
        if (false === $mysqli->query($sql)) {
            _exit("Could not insert batch['{$i}']: {$mysqli->error}");
        }
    }
    if (false === $mysqli->query("COMMIT")) {
        writeLog("Transaction could not be completed");
    }
    $memUsage = (memory_get_peak_usage(true) / 1024) / 1024;
    writeLog("Execution completed - Memmory peak usage: {$memUsage} Mb");
}


/**
 * Reads the database connection information from config_db.php
 */
function getConnectionConfigs() {
    $factory = new Dotenv\Environment\DotenvFactory([
        new Dotenv\Environment\Adapter\ArrayAdapter(),
    ]);
    
    $configs = Dotenv\Dotenv::create(__DIR__ . '/../ERP/laravel', null, $factory)->load();

    $config = [
        $configs['DB_HOST'],
        $configs['DB_USERNAME'],
        $configs['DB_PASSWORD'],
        $configs['DB_DATABASE'],
        $configs['DB_PORT'],
    ];

    return $config;
}

/** 
 * Write to a log file
 */
function writeLog($msg) {
    $now = date(DT_FORMAT);
    $date = date('Y-m-').(['01', '10'][date('d')[0]] ?? '20');
    $log = fopen( dirname(__DIR__) . "/ERP/laravel/storage/logs/sync-attendance-{$date}.log", 'a');
    if ($log) {
        fwrite($log, "[{$now}] {$msg}\n");
        fclose($log);
    }
}

function _exit($msg = null) {
    if ($msg != null) {
        writeLog($msg);
    }
    $GLOBALS['mysqli']->close();
    exit();
}
