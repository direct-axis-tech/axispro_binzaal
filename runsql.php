<?php
$sql = file_get_contents('changed.sql');
include_once("./ERP/config_db.php");
$connection = $db_connections[0];
$mysqli = new mysqli($connection["host"], $connection["dbuser"], $connection["dbpassword"], $connection["dbname"]);

/* execute multi query */
// $mysqli->multi_query($sql);
if($mysqli->multi_query($sql)){
	echo "Success";
}else{
	echo "Error";
}
?>