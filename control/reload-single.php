<?php
require_once(dirname(__FILE__).'/config.php');

$db->prepared_query('select `id` from `devices` where `name`=?', array('s'), array($argv[1]));
if (isset($db->result[0])) { $config = new config($db, $db->result[0]['id']); } else { $config = new config($db); }

if (file_exists($config->config_data['pid_directory'].'/'.$argv[1].'.pid')) {
    echo "Reloading ".$argv[1]."\n";
    exec('kill `cat '.$config->config_data['pid_directory'].'/'.$argv[1].'.pid`');
} else {
    echo "Camera not running: ".$argv[1]."\n";
}
?>
