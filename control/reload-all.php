<?php
require_once(dirname(__FILE__).'/config.php');

$database->query("select `id`, `name` from devices");
$result = $database->result;
foreach ($result as $row) {
    $config = new config($database, $row['id']);
    if (file_exists($config->config_data['pid_directory'].'/'.$row['name'].'.pid')) {
        echo "Reloading ".$row['name']."\n";
        exec('kill `cat '.$config->config_data['pid_directory'].'/'.$row['name'].'.pid`');
    }
}
?>
