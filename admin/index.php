<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/include/main.php');
$pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('ADMIN SETTINGS', '/admin/'));
$topbar = true;
include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';
?>
<p><a class="btn btn-default" href="/cctv.php">&lt&ltBACK</a></p>
<p><a class="btn btn-primary" href="configmgr.php">CONFIGURATION MANAGER</a></p>
<p><a class="btn btn-primary" href="users.php">USER MANAGER</a></p>
<p><a class="btn btn-primary" href="phpinfo.php">PHP INFORMATION</a></p>
<?php
include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
?>
