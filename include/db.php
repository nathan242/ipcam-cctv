<?php
if ($_SERVER['DOCUMENT_ROOT'] == '') {
    require_once dirname(__FILE__).'/mdb.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'].'/include/mdb.php';
}

// DB config
$dbhost = 'localhost';
$dbuser = 'ipcam';
$dbpass = 'Ipcam_1988';
$dbdb = 'ipcam';

$database = new mdb($dbhost, $dbuser, $dbpass, $dbdb);
//if (!$database->connect()) { echo "FATAL: Cannot connect to DB!\n"; exit(); }

//$database->debug_print = 1;

?>
