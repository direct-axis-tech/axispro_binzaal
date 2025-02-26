<?php

error_reporting(0);

$path_to_root = "..";

include_once($path_to_root . "/config_db.php");
include_once($path_to_root . "/config.php");
include_once($path_to_root . "/AutoFetchApi/API_Functions.php");

$db_info = $db_connections[0];
$conn = mysqli_connect(
    $db_info['host'],
    $db_info['dbuser'],
    $db_info['dbpassword'],
    $db_info['dbname'],
    $db_info['port']
);
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : "";
$api = new API_Functions();

if (empty($method)) {
    echo json_encode([
        'status' => 'FAIL',
        'msg' => 'PARAM_METHOD_EMPTY'
    ]);
    exit();
}

if (method_exists($api, $method)) {
    $api->$method($conn);
} else {
    echo json_encode([
        'status' => 'FAIL',
        'msg' => 'METHOD_NOT_FOUND'
    ]);
    exit();
}


function HttpOriginCheck($allowed)
{
    $origin = "";

    if (array_key_exists('HTTP_ORIGIN', $_SERVER)) {
        $origin = $_SERVER['HTTP_ORIGIN'];
    } else if (array_key_exists('HTTP_REFERER', $_SERVER)) {
        $origin = $_SERVER['HTTP_REFERER'];
    } else {
        $origin = $_SERVER['REMOTE_ADDR'];
    }

    if (isset($origin) && in_array($origin, $allowed))
        return true;

    return false;
}

