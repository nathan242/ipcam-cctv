<?php
    require_once '../include/inc.main.php';
    $pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('ADMIN SETTINGS', '/admin'), array('PHPINFO', $_SERVER['REQUEST_URI']));
    $topbar = true;
    require '../include/header.php';
    phpinfo();
    require '../include/footer.php';
?>
