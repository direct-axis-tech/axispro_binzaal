<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 7/19/2018
 * Time: 10:56 AM
 */
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/prefs/sysprefs.inc");
include_once($path_to_root . "/includes/db/connect_db.inc");

// output headers so that the file is downloaded rather than displayed
header('Content-type: text/csv');
header('Content-Disposition: attachment; filename="unreconciled.csv"');

// do not cache the file
header('Pragma: no-cache');
header('Expires: 0');

// create a file pointer connected to the output stream
$file = fopen('php://output', 'w');

// send the column headers
fputcsv($file, array('Transaction Description', 'Debit', 'Credit'));

$sql = "select * from temp_bank_statements where reconciled=0";
$result = db_query($sql);
if (db_num_rows($result) > 1) {
    $data = [];
    while ($myrow = db_fetch($result)) {
        $array = [$myrow['transaction_'], $myrow['debit'], $myrow['credit']];
        array_push($data, $array);
    }
}

// output each row of the data
foreach ($data as $row)
{
    fputcsv($file, $row);
}

exit();