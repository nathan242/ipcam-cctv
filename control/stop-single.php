<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/../include/camera.php');

$db->prepared_query('select `id` from `devices` where `name`=?', array('s'), array($argv[1]));
if (isset($db->result[0])) { $config = new config($db, $db->result[0]['id']); } else { $config = new config($db); }

if (file_exists($config->config_data['pid_directory'].'/'.$argv[1].'.pid')) {
    echo "Stopping ".$argv[1]."\n";
    exec('touch '.$config->config_data['pid_directory'].'/'.$argv[1].'.norespawn');
    exec('kill `cat '.$config->config_data['pid_directory'].'/'.$argv[1].'.pid`');
} elseif (file_exists($config->config_data['pid_directory'].'/'.$argv[1].'.sleep')) {
    echo "Stopping ".$argv[1]."\n";
    if (file_exists($config->config_data['pid_directory'].'/'.$argv[1].'.sleeppid')) {
        exec('kill `cat '.$config->config_data['pid_directory'].'/'.$argv[1].'.sleeppid`');
	@unlink($config->config_data['pid_directory'].'/'.$argv[1].'.sleeppid');
	@unlink($config->config_data['pid_directory'].'/'.$argv[1].'.sleep');
	@unlink($config->config_data['pid_directory'].'/'.$argv[1].'.norespawn');
        $device = new camera($db, $config->config_data, false, $argv[1]);
        if (!$device->device_data) { echo "No Device Data! [".$argv[1]."]\n"; exit(); }
	$device->log_add('Device stopped.', 0);
    } else {
        exec('touch '.$config->config_data['pid_directory'].'/'.$argv[1].'.norespawn');
    }
} else {
    echo "Camera not running: ".$argv[1]."\n";
}
?>
