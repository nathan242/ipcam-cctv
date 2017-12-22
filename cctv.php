<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/include/main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/include/camera.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config.php');

$pagepath = array(array('CCTV CONTROL', $_SERVER['REQUEST_URI']));
$topbar = true;
include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';
?>

<div class="panel panel-primary control-box">
<div class="panel-heading">RECORDING CONTROL</div>
<div class="panel-body">
<a class="btn btn-success" href="control.php?action=startall">START ALL</a>
<a class="btn btn-danger" href="control.php?action=stopall">STOP ALL</a>
<a class="btn btn-warning" href="control.php?action=reloadall">RELOAD ALL</a>
</div>
</div>

<div class="panel panel-primary control-box">
<div class="panel-heading">RECORDING DATA</div>
<div class="panel-body">
<a class="btn btn-primary" href="recording-video.php">VIDEO FILES</a>
<a class="btn btn-primary" href="recording-log.php">RECORDING LOG VIEWER</a>
</div>
</div>

<div class="panel panel-primary control-box">
<div class="panel-heading">FUNCTIONS</div>
<div class="panel-body">
<a class="btn btn-primary" href="/admin/">ADMIN SETTINGS</a>
</div>
</div>

<hr>

<p>DISK USAGE:</p>

<?php

$config = config::get_key_instances($database, 'recording_directory');
if ($config === false) {
    echo '<p>Error getting [recording_directory] configuration key!</p>';
}

echo '<table border="1">';
$headers_shown = false;
foreach ($config as $c) {
    $rec_path = preg_replace('/[^a-zA-Z0-9\-\/]/', '_', $c['value']);
    $dev_ids = ($c['devices'] == '') ? '**DEFAULT**' : $c['devices'];
    exec('df -h '.$rec_path.' | tr -s \' \'', $df);
    $first = 1;
    foreach ($df as $line) {
        $word = explode(' ',$line);
        if ($first == '1') {
            if ($headers_shown === false) {
                echo '<tr><td><b>Device IDs</b></td><td><b>'.$word[0].'</b></td><td><b>'.$word[1].'</b></td><td><b>'.$word[2].'</b></td><td><b>'.$word[3].'</b></td><td><b>'.$word[4].'</b></td><td><b>'.$word[5].'</b></td><td><b>% Used</b></td>';
                $headers_shown = true;
            }
            $first = 0;
        } else {
            echo '<tr><td>'.$dev_ids.'</td><td>'.$word[0].'</td><td>'.$word[1].'</td><td>'.$word[2].'</td><td>'.$word[3].'</td><td>'.$word[4].'</td><td>'.$word[5].'</td>';
            echo '<td><div class="percentbar" style="width: 100px;"><div style="width:'.$word[4].';"></div></div></td></tr>';
        }
    }
    unset($df);
}

echo '</table>';

?>

<hr>

<p>DEVICE LIST:</p>

<p><a class="btn btn-primary" href="/camera-manager.php">EDIT CAMERAS</a></p>

<table class="table table-hover">
    <tr>
        <th>ID</th>
        <th>NAME</th>
        <th>IP ADDRESS</th>
        <th>PROTOCOL</th>
        <th>URL</th>
        <th>USERNAME</th>
        <th>PASSWORD</th>
        <th>STATUS</th>
	<th colspan=7>CONTROLS</th>
    </tr>

<?php
//$result = $db->query("select `id`,`name`,`ip_address`,`protocol`,`url`,`username`,`password` from devices");
$database->query("select `id`,`name`,`ip_address`,`protocol`,`url`,`username`,`password` from devices");
//$highlight = 0;
if (isset($database->result[0])) {
        $result = $database->result;
	foreach ($result as $row) {
    	//echo ($highlight) ? '<tr bgcolor="#CCCCCC">' : '<tr>';
    	//$highlight = ($highlight) ? 0 : 1;
        echo '<tr>';
    	echo '<td>'.$row['id'].'&nbsp</td>';
    	echo '<td>'.$row['name'].'&nbsp</td>';
    	echo '<td>'.$row['ip_address'].'&nbsp</td>';
    	echo '<td>'.$row['protocol'].'&nbsp</td>';
    	echo '<td>'.$row['url'].'&nbsp</td>';
    	echo '<td>'.$row['username'].'&nbsp</td>';
    	echo '<td>'.$row['password'].'&nbsp</td>';

        $config = new config($database, $row['id']);
	$camera = new camera($database, $config->config_data, $row['id']);
	//echo '<pre>'.print_r($camera,1).'</pre>';
	if ($camera->has_pid()) {
		if ($camera->is_running()) {
			echo '<td>RUNNING PID: '.$camera->get_pid().'</td>';
		} else {
			echo '<td>PROCESS DIED PID: '.$camera->get_pid().'</td>';
		}
	} elseif ($camera->has_sleep_pid()) {
		echo '<td>SLEEPING: '.$camera->get_sleep_pid().'</td>';
	} else {
		echo '<td>STOPPED</td>';
	}

    	echo '<td><a class="btn btn-sm btn-success" href="control.php?action=start&camera='.$row['name'].'">START</a></td>';
    	echo '<td><a class="btn btn-sm btn-danger" href="control.php?action=stop&camera='.$row['name'].'">STOP</a></td>';
    	echo '<td><a class="btn btn-sm btn-warning" href="control.php?action=reload&camera='.$row['name'].'">RELOAD</a></td>';
    	echo '<td><a class="btn btn-sm btn-primary" href="javascript:var sfw=window.open(\'liveview.php?camera='.$row['name'].'\', \''.$row['name'].'\', \'height=500, menubar=no, status=no, toolbar=no, width=660, location=no, scrollbars=no\');">LIVE</a></td>';
    	echo '<td><a class="btn btn-sm btn-primary" href="javascript:var sfw=window.open(\'recording-viewer.php?camera='.$row['name'].'\', \''.$row['name'].'\', \'height=500, menubar=no, status=no, toolbar=no, width=660, location=no, scrollbars=yes\');">RECORDINGS</a></td>';
    	echo '<td><a class="btn btn-sm btn-primary" href="javascript:var sfw=window.open(\'viewlog.php?camera='.$row['name'].'\', \''.$row['name'].'\', \'height=700, menubar=no, status=no, toolbar=no, width=800, location=no, scrollbars=yes\');">LOG</a></td>';
    	echo '<td><a class="btn btn-sm btn-primary" href="http://'.$row['ip_address'].'/" target="_blank">WEB</a></td>';
    	echo '</tr>';
	}
}
?>
</table>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
?>
