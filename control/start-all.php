<?php
require_once(dirname(__FILE__).'/config.php');

$database->query("select `id`, `name` from devices");
$result = $database->result;
foreach ($result as $row) {
    $config = new config($database, $row['id']);
    if (!file_exists($config->config_data['pid_directory'].'/'.$row['name'].'.pid') && !file_exists($config->config_data['pid_directory'].'/'.$row['name'].'.sleep')) {
        echo "Starting ".$row['name']."\n";
        exec('php -f '.dirname(__FILE__).'/spawn-vlc.php '.$row['name'].' > /dev/null & ');
    }
}
?>
