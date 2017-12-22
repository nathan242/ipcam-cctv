<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/include/main.php';
switch ($_GET['action']) {
    case "startall":
        exec('php -f '.$_SERVER['DOCUMENT_ROOT'].'/control/start-all.php');
        break;
    case "stopall":
        exec('php -f '.$_SERVER['DOCUMENT_ROOT'].'/control/stop-all.php');
        break;
    case "reloadall":
        exec('php -f '.$_SERVER['DOCUMENT_ROOT'].'/control/reload-all.php');
        break;
    case "start":
        exec('php -f '.$_SERVER['DOCUMENT_ROOT'].'/control/start-single.php '.$_GET['camera']);
        break;
    case "stop":
        exec('php -f '.$_SERVER['DOCUMENT_ROOT'].'/control/stop-single.php '.$_GET['camera']);
        break;
    case "reload":
        exec('php -f '.$_SERVER['DOCUMENT_ROOT'].'/control/reload-single.php '.$_GET['camera']);
        break;
}
if (isset($_SERVER['HTTP_REFERER'])) {
    //echo '<html><meta HTTP-EQUIV="REFRESH" content="0; url='.$_SERVER['HTTP_REFERER'].'"></html>';
    header('Location: '.$_SERVER['HTTP_REFERER']);
	exit;
}
?>
