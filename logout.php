<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/include/main.php';

//echo '<html><title>Logging out...</title>';
//echo '<p>Logging out...</p>';

session_destroy();

//echo '<meta HTTP-EQUIV="REFRESH" content="0; url=/index.php">';
header('Location: /index.php');

//echo '</html>';
?>