<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/include/main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/include/camera.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config.php');

$db->prepared_query('select `id` from `devices` where `name`=?', array('s'), array($_GET['camera']));
if (isset($db->result[0])) { $config = new config($db, $db->result[0]['id']); } else { $config = new config($db); }

$device = new camera($db, $config->config_data, false, $_GET['camera']);

$pagetitle = 'Log for '.$device->device_data['name'];
include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';

$data = $device->log_get();

if ($data) {
    echo '<table border="1">
        <tr>
            <th>ID</th>
            <th>TIMESTAMP</th>
            <th>EVENT</th>
            <th>STATUS</th>
        </tr>';

    foreach ($data as $row) {
        echo '<tr>';
        echo '<td>'.$row['id'].'</td>';
        echo '<td>'.$row['timestamp'].'</td>';
        echo '<td>'.$row['event'].'</td>';
        echo '<td>'.$row['status'].'</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<p>No log data.</p>';
}

include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
?>
