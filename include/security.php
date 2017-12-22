<?php
if(!isset($_SESSION['loginuser'])) {
	session_destroy();
	header('Location: /index.php');
	exit;
}
$loginuser = $_SESSION['loginuser'];
$database->prepared_query('select `enabled` from users where `username`= ?', array('s'), array($loginuser));
if (count($database->result) == 0) {
	session_destroy();
	header('Location: /index.php');
	exit;
} elseif ($database->result[0]['enabled'] == 0) {
		session_destroy();
		header('Location: /index.php');
		exit;
}
?>
