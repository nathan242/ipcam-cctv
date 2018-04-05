<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/include/main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config.php');

$device = $_GET['camera'];

$database->prepared_query('select `id` from `devices` where `name`=?', array('s'), array($device));
if (isset($database->result[0])) { $config = new config($database, $database->result[0]['id']); } else { $config = new config($database); }

$pagetitle = 'Recordings for '.$device;
include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';

echo '<p>Recordings:</p>';

$files = scandir($config->config_data['recording_directory'],1);
foreach ($files as $file) {
    if (strpos($file, $device.'.') !== false) {
        echo '<p>'.$file.' -  SIZE: '.number_format((filesize($config->config_data['recording_directory'].'/'.$file)/1024/1024),2).'MB MODIFIED: '.date("H:i d/m/y",filemtime($config->config_data['recording_directory'].'/'.$file)).'</p>';
    }
}

include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
?>
