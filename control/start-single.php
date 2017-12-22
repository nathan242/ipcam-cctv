<?php
require_once(dirname(__FILE__).'/config.php');

$database->prepared_query('select `id` from `devices` where `name`=?', array('s'), array($argv[1]));
if ($database->result[0]) { $config = new config($database, $database->result[0]['id']); } else { $config = new config($database); }

if (!file_exists($config->config_data['pid_directory'].'/'.$argv[1].'.pid') && !file_exists($config->config_data['pid_directory'].'/'.$argv[1].'.sleep')) {
    echo "Starting ".$argv[1]."\n";
    exec('php -f '.dirname(__FILE__).'/spawn-vlc.php '.$argv[1].' > /dev/null & ');
}
?>
