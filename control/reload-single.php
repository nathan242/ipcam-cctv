<?php
require_once(dirname(__FILE__).'/config.php');

$database->prepared_query('select `id` from `devices` where `name`=?', array('s'), array($argv[1]));
if (isset($database->result[0])) { $config = new config($database, $database->result[0]['id']); } else { $config = new config($database); }

if (file_exists($config->config_data['pid_directory'].'/'.$argv[1].'.pid')) {
    echo "Reloading ".$argv[1]."\n";
    exec('kill `cat '.$config->config_data['pid_directory'].'/'.$argv[1].'.pid`');
} else {
    echo "Camera not running: ".$argv[1]."\n";
}
?>
