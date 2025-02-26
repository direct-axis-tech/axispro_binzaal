<?php

define('DT_FORMAT', 'Y-m-d H:i:s');
date_default_timezone_set('Asia/Dubai');
set_error_handler('error_handler');
set_exception_handler('exception_handler');

/*
 |------------------------------------------------------------------
 | For automatic update of cacert.pem schedule one of the following
 |------------------------------------------------------------------
 | A suitable curl command line to only download it when it has changed:
 |  curl -k --etag-compare C:\curl\bin\etag.txt --etag-save C:\curl\bin\etag.txt https://curl.se/ca/cacert.pem -o C:\curl\bin\curl-ca-bundle.crt
 */

$location_id = '1';
$config = include __DIR__.'/config.php';

$curlOpts = [
    CURLOPT_URL => $config['apiEndpoint']. "?loc={$location_id}",
    CURLOPT_RETURNTRANSFER     => true,
    CURLOPT_CAINFO => $config['caCertificateInfo'],
    CURLOPT_CAPATH => $config['caCertificatePath']
];

curl_setopt_array(($handle = curl_init()), $curlOpts);
if (($response = curl_exec($handle)) === false) {
    writeLog("Curl error: " . curl_error($handle));
    return 1;
}
curl_close($handle);

if (!preg_match('/^\d+$/', ($lastSyncedId = $response))) {
    writeLog("Server returned unsupported data:" . $response);
    return 1;
}

if ($lastSyncedId < ($validId = 0)) {
    $lastSyncedId = $validDate;
}

$conn = sqlsrv_connect(
    $config['connection']['serverName'],
    [
        "Database" => $config['connection']['database'],
        "UID" => $config['connection']['userId'],
        "PWD" => $config['connection']['password'],
        "ReturnDatesAsStrings" => true
    ]
);

if (!($conn)) {
    writeLog("Connection could not be established");
    writeLog(print_r(sqlsrv_errors(), true));
    return 1;
}

$sql = (
    "SELECT
        Attd.[Id] as [id],
        '{$location_id}' as [loc],
        Attd.[BadgeNumber] as [empid],
        CONVERT(VARCHAR(23), Attd.[VerifyTime], 120) as [authdatetime],
        CONVERT(VARCHAR(10), Attd.[VerifyTime], 23) as [authdate],
        CONVERT(time, Attd.[VerifyTime], 108) as [authtime],
        Attd.[VerifyType] as [devicename],
        Attd.[Status] as [status],
        Attd.[DeviceSerialNumber] as [deviceserialno],
        Emp.[NAME] as [person],
        Emp.[CardNo] as [cardno]
    FROM [{$config['connection']['database']}].[dbo].[{$config['connection']['table']}] as Attd
    LEFT JOIN [{$config['connection']['database']}].[dbo].[Employee] as Emp ON
        Emp.[BadgeNumber] = Attd.[BadgeNumber]
    WHERE Attd.[Id] > '{$lastSyncedId}'
    ORDER BY Attd.[Id]"
);

if (false === ($result = sqlsrv_query($conn, $sql))) {
    writeLog("MSSQL Error: " . json_encode(sqlsrv_errors() ?? [], JSON_PRETTY_PRINT));
    return 1;
}

$data = [];
while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $data[] = $row;
}

if (!empty($data)) {
    $totalRecords = count($data);
    $recordsPerBatch = 10000;
    $batches = ceil($totalRecords / $recordsPerBatch);

    for($i = 0; $i < $batches; $i++) {
        $execTime = microtime(true);
        $current_offset = $i * $recordsPerBatch;
        $current_batch_limit = $current_offset + $recordsPerBatch;

        $_data = [];
        for ($j = $current_offset; ($j < $totalRecords && $j < $current_batch_limit); $j++) {
            $_data[] = $data[$j];
        }

        $curlOpts[CURLOPT_POST] = true;
        $curlOpts[CURLOPT_POSTFIELDS] = [
            "data" => json_encode($_data),
            "key"  => $config['secretKey']
        ];
        curl_setopt_array(($handle = curl_init()), $curlOpts);
        $response = curl_exec($handle);
        $execTime = round((microtime(true)) - $execTime, 3);
        if ($response === false) {
            writeLog('Curl error: ' . curl_error($handle));
        } else {
            writeLog("Execution completed in '{$execTime}' seconds");
        }
        curl_close($handle);
    }
} else {
    writeLog("Data is empty");
}

return 0;

function writeLog($msg) {
    $now = date(DT_FORMAT);
    $log = fopen(__DIR__ . DIRECTORY_SEPARATOR . date('Y_m_d_') .'sync.log', 'a');
    if ($log) {
        fwrite($log, "[{$now}] {$msg}\r\n");
        fclose($log);
    }
}

function exception_handler($exception) {
    writeLog(sprintf(
        "Unhandled exception [%s]: %s at %s: %d.",
        $exception->getCode(),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    ));
}

function error_handler($errNo, $errStr, $file, $line) {
    writeLog(sprintf(
        "Unhandled Error [%s]: %s at %s: %d.",
        $errNo,
        $errStr,
        $file,
        $line
    ));
}