<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/../include/camera.php');

$db->query("select `id`,`name` from devices");
$result = $db->result;
foreach ($result as $row) {
    $config = new config($db, $row['id']);
    if (file_exists($config->config_data['pid_directory'].'/'.$row['name'].'.pid')) {
        echo "Stopping ".$row['name']."\n";
        exec('touch '.$config->config_data['pid_directory'].'/'.$row['name'].'.norespawn');
        exec('kill `cat '.$config->config_data['pid_directory'].'/'.$row['name'].'.pid`');
    } elseif (file_exists($config->config_data['pid_directory'].'/'.$row['name'].'.sleep')) {
        echo "Stopping ".$row['name']."\n";
        if (file_exists($config->config_data['pid_directory'].'/'.$row['name'].'.sleeppid')) {
            exec('kill `cat '.$config->config_data['pid_directory'].'/'.$row['name'].'.sleeppid`');
            @unlink($config->config_data['pid_directory'].'/'.$row['name'].'.sleeppid');
            @unlink($config->config_data['pid_directory'].'/'.$row['name'].'.sleep');
            @unlink($config->config_data['pid_directory'].'/'.$row['name'].'.norespawn');
            $device = new camera($db, $config->config_data, $row['id']);
	    $device->log_add('Device stopped.', 0);
        } else {
            exec('touch '.$config->config_data['pid_directory'].'/'.$row['name'].'.norespawn');
        }
    }
}
?>
