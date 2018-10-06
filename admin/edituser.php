<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/include/main.php');

function fail($code) {
    include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';
    if ($code == 1) {
        echo '<p>ERROR: Missing parameters.</p>';
    } elseif ($code == 2) {
        echo '<p>ERROR: Database update failed.</p>';
    } elseif ($code == 3) {
        echo '<p>ERROR: No password set.</p>';
    }
    include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
    exit;
}

if (isset($_POST['id'])) {
	$id = $_POST['id'];
        if (isset($_POST['delete'])) {
            if (!$db->prepared_query('DELETE FROM `users` WHERE `id`=?', array('i'), array($id))) {
                fail(2);
            } else {
                header('Location: '.$_SERVER['HTTP_REFERER']);
                exit();
            }
        }
	if (isset($_POST['username']) && isset($_POST['password'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
		$enabled = (isset($_POST['enabled'])) ? $_POST['enabled'] : 0;
		
		if ($password != '') {
			$password = hash('sha256',$password);
			if ($id == "new") {
				if (!$db->prepared_query('INSERT INTO `users` (`username`, `password`, `enabled`) VALUES (?, ?, ?)', array('s','s','i'), array($username,$password,$enabled))) {
					fail(2);
				} else {
					header('Location: users.php');
					exit;
				}
			} else {
				if (!$db->prepared_query('UPDATE `users` SET `username`=?, `password`=?, `enabled`=? WHERE `id`=?', array('s','s','i','i'), array($username,$password,$enabled,$id))) {
					fail(2);
				} else {
					header('Location: users.php');
					exit;
				}
			}
		} else {
			if ($id == "new") { fail(3); }
			if (!$db->prepared_query('UPDATE `users` SET `username`=?, `enabled`=? WHERE `id`=?', array('s','i','i'), array($username,$enabled,$id))) {
				fail(2);
			} else {
				header('Location: users.php');
				exit;
			}
		}
	}
}

if (isset($_GET['id']) && $db->prepared_query('select id, username, password, enabled from users where id=?', array('i'), array($_GET['id'])) && $db->result) {
	$id = $db->result[0]['id'];
	$username = $db->result[0]['username'];
	$enabled = $db->result[0]['enabled'];
} elseif (isset($_GET['new'])) {
	$id = "new";
	$username = "[NEW USER]";
	$enabled = 1;
} else {
	header('Location: users.php');
	exit;
}

$pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('ADMIN SETTINGS', '/admin/'), array('USER MANAGER', '/admin/users.php'), array('EDIT USER: '.$username, $_SERVER['REQUEST_URI']));
$topbar = true;
include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';

echo '<p><a class="btn btn-default" href="users.php">&lt&ltBACK</a></p>';

echo '<p>Leave password blank to keep existing password</p>';
echo '<form action="" method="post">';
echo '<input type="hidden" name="id" value="'.$id.'">';
echo '<table border="1">';
echo '<tr><td>Username:</td><td><input type="text" name="username" value="'.$username.'" autocomplete="off"></td></tr>';
echo '<tr><td>Password:</td><td><input type="password" name="password" value=""></td></tr>';
echo '<tr><td>Enabled:</td>';
if ($enabled == 1) {
	echo '<td><input type="checkbox" value="1" name="enabled" checked></td>';
} else {
	echo '<td><input type="checkbox" value="1" name="enabled"></td>';
}
echo '</tr>';
echo '<tr><td colspan="2"><input class="btn btn-success" style="width: 100%;" type="submit" value="SAVE"></td></tr>';
echo '</table></form>';

include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
?>
