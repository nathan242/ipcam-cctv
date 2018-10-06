<?php
    require_once(dirname(__FILE__).'/../control/config.php');
    require_once(dirname(__FILE__).'/../include/config.php');

    $global = new config($db);
    $config = config::config_dump($db);

    if (file_exists($global->config_data['lock_directory']."/remove-old-data.lock")) { exit; }

    touch($global->config_data['lock_directory']."/remove-old-data.lock");

    foreach ($config as $cnf) {
        if (isset($cnf['recording_directory']) || isset($cnf['log_directory']) || isset($cnf['recording_device_limit'])) {
            if (!isset($cnf['recording_directory'])) { $cnf['recording_directory'] = $global->config_data['recording_directory']; }
            if (!isset($cnf['log_directory'])) { $cnf['log_directory'] = $global->config_data['log_directory']; }
            if (!isset($cnf['recording_device_limit'])) { $cnf['recording_device_limit'] = $global->config_data['recording_device_limit']; }
            $used = shell_exec("df ".$cnf['recording_directory']." | tr -s ' ' | cut -d ' ' -f 5 | sed s/%// | grep ^[0-9]");
            if (!preg_match('/^[0-9]{1,3}$/', $used)) { echo "ERROR: Disk space command returned invalid value!\n"; exit(1); }
            if ($used > $cnf['recording_device_limit']) {
                $file = shell_exec("ls -1t ".$cnf['recording_directory']."/ | tail -n1");
                $file = preg_replace('/\n/', '', $file);
                if ($file == '') { continue; }
                unlink($cnf['recording_directory']."/".$file);
                echo "Removed old file: ".$file."\n";
                $logfile = preg_replace('/\.[^\.]*$/', '.log', $file);
                unlink($cnf['log_directory']."/".$logfile);
                echo "Removed old logfile: ".$logfile."\n";
            }
        }
    }

    unlink($global->config_data['lock_directory']."/remove-old-data.lock");
?>
