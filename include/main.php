<?php
// This will run at the beggining of every page

// Start a session
session_start();

// Include database functions
require_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';

// Run security checks
require_once $_SERVER['DOCUMENT_ROOT'].'/include/security.php';
?>
