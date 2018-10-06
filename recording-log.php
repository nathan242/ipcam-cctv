<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/include/main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config.php');

if (isset($_GET['camera']) && $_GET['camera'] != '*') {
    $db->prepared_query('select `id` from `devices` where `name`=?', array('s'), array($_GET['camera']));
    if (isset($db->result[0])) { $config = new config($db, $db->result[0]['id']); } else { $config = new config($db); }
} else {
    $config = new config($db);
}

if (isset($_GET['log']) && $log = @file_get_contents($config->config_data['log_directory'].'/'.basename($_GET['log']))) {
	$logfile = basename($_GET['log']);
	$pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('RECORDING LOG VIEWER', '/recording-log.php'.((isset($_GET['camera']) && !empty($_GET['camera'])) ? '?camera='.$_GET['camera'] : '')), array('RECORDING LOG VIEWER: '.$logfile, '/recording-log.php?camera='.((isset($_GET['camera']) && !empty($_GET['camera'])) ? $_GET['camera'] : '*').'&log='.$logfile));
	$topbar = true;
	include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';
	echo '<p><a class="btn btn-default" href="/recording-log.php';
	if (isset($_GET['camera']) && !empty($_GET['camera'])) {
		echo '?camera='.$_GET['camera'];
	}
	echo '">&lt&ltBACK</a></p>';
	
	echo '<pre>'.$log.'</pre>';
	
} else {
	$pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('RECORDING LOG VIEWER', $_SERVER['REQUEST_URI']));
	$topbar = true;
	include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';
	echo '<p><a class="btn btn-default" href="/cctv.php">&lt&ltBACK</a></p>';
	
	$db->query("select `name` from devices");
	if (isset($db->result[0])) {
		echo '<p><form action="" method="get"><select name="camera" onchange="this.form.submit()"><option';
		if (!isset($_GET['camera']) || empty($_GET['camera'])) {
			echo ' selected';
		}
		echo '>*</option>';
		foreach ($db->result as $row) {
			echo '<option';
			if (isset($_GET['camera']) && $_GET['camera'] == $row['name']) {
				echo ' selected';
			}
			echo '>'.$row['name'].'</option>';
		}
		echo '</select></form></p>';
	}
	
	$logfiles = scandir($config->config_data['log_directory'],1);
	//print_r($logfiles);
	
	foreach ($logfiles as $logfile) {
		if ($logfile != '.' && $logfile != '..') {
			if (isset($_GET['camera']) && !empty($_GET['camera']) && $_GET['camera'] != '*') {
				if (strpos($logfile, $_GET['camera']) === 0 && $logfile[strlen($_GET['camera'])] == '.') {
					echo '<p><a href="recording-log.php?log='.$logfile;
					echo '&camera='.$_GET['camera'];
					echo '">'.$logfile.'</a></p>';
				}
			} else {
				echo '<p><a href="recording-log.php?log='.$logfile;
				echo '">'.$logfile.'</a></p>';
			}
		}
	}
}

include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
?>
