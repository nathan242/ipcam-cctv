<?php
session_start();
function login($username) {
	$_SESSION['loginuser'] = $username;
	header('Location: /cctv.php');
	exit;
}

function fail_login($reason) {
	session_destroy();
        $pagetitle = 'LOGIN FAILED';
        include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';
	switch ($reason) {
		case 0:
			echo '<p>ERROR: Unknown username or password.</p>';
			break;
		case 1:
			echo '<p>ERROR: Account is disabled.</p>';
			break;
	}
			echo '<p><a href="/index.php">BACK TO LOGIN PAGE</a></p>';
			include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
			exit;
}

if(!isset($_SESSION['loginuser']) && (!isset($_POST['username']) || !isset($_POST['password']))) {
	$pagetitle = 'LOGIN';
	include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';
        echo '<div class="panel panel-default control-box">';
        echo '<div class="panel-heading">LOGIN</div>';
	echo '<form action="" method="POST">';
	echo '<table border="1">';
	echo '<tr><td>USERNAME:</td><td><input type="text" name="username" autocomplete="off"></td></tr>';
	echo '<tr><td>PASSWORD:</td><td><input type="password" name="password"></td></tr>';
	echo '<tr><td colspan="2"><input class="btn" type="submit" value="LOGIN" style="width:100%"></td></tr>';
	echo '</table>';
	echo '</form>';
        echo '</div>';
	echo '<div style="position: absolute; bottom: 5px;">';
	echo '<font size="1">VERSION: XX-XX-2017 (DEV)</font>';
	echo '</div>';
	include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
} elseif (!isset($_SESSION['loginuser']) && isset($_POST['username']) && isset($_POST['password'])) {
	// Process login
	require_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
	$username = $_POST['username'];
	$database->prepared_query('select `username`, `password`, `enabled` from users where `username`=?', array('s'), array($username));

	// Check if user exsists
	if (count($database->result) == 0) {
		fail_login(0);
	}

	// Check user password
	$loginpassword = hash('sha256',$_POST['password']);
	if ($database->result[0]['password'] == $loginpassword) {
		if ($database->result[0]['enabled'] == 1) {
			login($database->result[0]['username']);
		} else {
			fail_login(1);
		}
	} else {
		fail_login(0);
	}
} else {
	// Already logged in - Restore page
	header('Location: /cctv.php');
}
?>
