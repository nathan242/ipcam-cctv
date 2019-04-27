<?php
    // Main include file

    // Start a session if we are not running from CLI
    $is_cli = php_sapi_name() === 'cli';
    if (!$is_cli) {
        session_start();
    }
    
    // Class autoloader
    spl_autoload_register(function ($class) {
        require 'class.'.$class.'.php';
    });

    // DB config
    $dbhost = 'localhost';
    $dbuser = 'ipcam';
    $dbpass = 'Ipcam_1988';
    $dbdb = 'ipcam';

    $db = new mdb($dbhost, $dbuser, $dbpass, $dbdb);
    //$db->debug_print = true;

    // Check user
    if (!$is_cli && (!isset($page_no_login) || $page_no_login === false)) {
        user::check_logged_in($db);
    }
