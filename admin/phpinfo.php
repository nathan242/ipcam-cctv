<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/include/main.php';
$pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('ADMIN SETTINGS', '/admin'), array('PHPINFO', $_SERVER['REQUEST_URI']));
$topbar = true;
include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';
echo '<p><a class="btn btn-default" href="index.php">&lt&ltBACK</a></p>';
phpinfo();
include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
?>
