<?php
    require_once '../include/inc.main.php';
    
    $pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('ADMIN SETTINGS', '/admin/'));
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
    
    /*
    <p><a class="btn btn-default" href="/cctv.php">&lt&ltBACK</a></p>
    <p><a class="btn btn-primary" href="configmgr.php">CONFIGURATION MANAGER</a></p>
    <p><a class="btn btn-primary" href="users.php">USER MANAGER</a></p>
    <p><a class="btn btn-primary" href="phpinfo.php">PHP INFORMATION</a></p>
     * 
     */

    require '../include/footer.php';
