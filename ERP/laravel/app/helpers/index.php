<?php

$current_file = basename(__FILE__);
$files = glob(__DIR__."/*.php");

foreach ($files as $file) {
    if (basename($file) !== $current_file) {
        // Do something with the file
        include_once $file;
    }
}