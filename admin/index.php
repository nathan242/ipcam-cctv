<?php
    require_once '../include/inc.main.php';
    
    $pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('ADMIN SETTINGS', $_SERVER['REQUEST_URI']));
    $topbar = true;
    require '../include/header.php';
    
    echo '<p>';
    gui::button('CONFIGURATION MANAGER', 'configmgr.php');
    echo '</p>';
    
    echo '<p>';
    gui::button('USER MANAGER', 'users.php');
    echo '</p>';
    
    echo '<p>';
    gui::button('PHP INFORMATION', 'phpinfo.php');
    echo '</p>';

    require '../include/footer.php';
