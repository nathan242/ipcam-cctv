<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/include/main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config.php');

if (isset($_GET['camera']) && $_GET['camera'] != '*') {
    $database->prepared_query('select `id` from `devices` where `name`=?', array('s'), array($_GET['camera']));
    if (isset($database->result[0])) { $config = new config($database, $database->result[0]['id']); } else { $config = new config($database); }
} else {
    $config = new config($database);
}

if (isset($_GET['video']) && file_exists($config->config_data['recording_directory'].'/'.basename($_GET['video']))) {
	if (isset($_GET['stream'])) {
                session_write_close();
		function video_out(&$video, $size) {
			while ($size > 4096) {
				echo fread($video, 4096);
				$size = $size-4096;
			}
			echo fread($video, $size);
		}
		foreach (getallheaders() as $name => $value) {
			if ($name == "Range") {
				$data = preg_split('/=/', $value);
				if (count($data) != 2) { continue; }
				$data = $data[1];
				$data = preg_split('/-/', $data);
				$range = array('', '');
				$range[0] = (isset($data[0])) ? $data[0] : '';
				$range[1] = (isset($data[1])) ? $data[1] : '';
			}
		}
		$video = fopen($config->config_data['recording_directory'].'/'.basename($_GET['video']), 'r');
		$videosize = filesize($config->config_data['recording_directory'].'/'.basename($_GET['video']));
		if (isset($range[0]) && $range[0] != '') {
			if ($range[1] == '') { $range[1] = $videosize-1; }
			header('HTTP/1.1 206 Partial Content');
			header('Accept-Ranges: bytes');
			//header('Content-Length: '.$range[1]-$range[0]);
			header('Content-Range: bytes '.$range[0].'-'.$range[1].'/'.$videosize);
			header('Content-Type: '.$config->config_data['recording_mime']);
			fseek($video, $range[0]);
			//echo fread($video, $range[1]-$range[0]);
			video_out($video, $range[1]-$range[0]);
		} else {
			//echo fread($video, $videosize);
			video_out($video, $videosize);
		}
		exit;
	}
	$videofile = basename($_GET['video']);
	echo '<html><title>RECORDING VIDEO VIEWER: '.$videofile.'</title>';
	$pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('RECORDING VIDEO VIEWER', '/recording-video.php'.((isset($_GET['camera']) && !empty($_GET['camera'])) ? '?camera='.$_GET['camera'] : '')), array('RECORDING VIDEO VIEWER: '.$videofile, '/recording-video.php?camera='.((isset($_GET['camera']) && !empty($_GET['camera'])) ? $_GET['camera'] : '*').'&video='.$videofile));
	$topbar = true;
	include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';
	echo '<p><a class="btn btn-default" href="/recording-video.php';
	if (isset($_GET['camera']) && !empty($_GET['camera'])) {
		echo '?camera='.$_GET['camera'];
	}
	echo '">&lt&ltBACK</a></p>';
	
	echo '<video width="800" height="600" controls>';
	echo '<source src="recording-video.php?video='.$_GET['video'].((isset($_GET['camera'])) ? '&camera='.$_GET['camera'] : '').'&stream=1" type="'.$config->config_data['recording_mime'].'">';
	echo 'NOT SUPPORTED';
	echo '</video>';

} else {
	$pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('RECORDING VIDEO VIEWER', $_SERVER['REQUEST_URI']));
	$topbar = true;
	include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';
	echo '<p><a class="btn btn-default" href="/cctv.php">&lt&ltBACK</a></p>';
	
	$database->query("select `name` from devices");
	if (isset($database->result[0])) {
		echo '<p><form action="" method="get"><select name="camera" onchange="this.form.submit()"><option';
		if (!isset($_GET['camera']) || empty($_GET['camera'])) {
			echo ' selected';
		}
		echo '>*</option>';
		foreach ($database->result as $row) {
			echo '<option';
			if (isset($_GET['camera']) && $_GET['camera'] == $row['name']) {
				echo ' selected';
			}
			echo '>'.$row['name'].'</option>';
		}
		echo '</select></form></p>';
	}
	
	$videofiles = scandir($config->config_data['recording_directory'],1);
	//print_r($logfiles);
	
	foreach ($videofiles as $videofile) {
		if ($videofile != '.' && $videofile != '..') {
			if (isset($_GET['camera']) && !empty($_GET['camera']) && $_GET['camera'] != '*') {
				if (strpos($videofile, $_GET['camera']) === 0 && $videofile[strlen($_GET['camera'])] == '.') {
					echo '<p><a href="recording-video.php?video='.$videofile;
					echo '&camera='.$_GET['camera'];
					echo '">'.$videofile.'</a></p>';
				}
			} else {
				echo '<p><a href="recording-video.php?video='.$videofile;
				echo '">'.$videofile.'</a></p>';
			}
		}
	}
}

include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
?>
