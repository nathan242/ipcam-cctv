<?php
    require_once(dirname(__FILE__).'/../control/config.php');
    require_once(dirname(__FILE__).'/../include/config.php');

    $global = new config($database);
    $config = config::config_dump($database);

    if (file_exists($global->config_data['lock_directory']."/remove-empty-files.lock")) { exit; }

    touch($global->config_data['lock_directory']."/remove-empty-files.lock");

    foreach ($config as $cnf) {
        if (isset($cnf['recording_directory']) || isset($cnf['log_directory'])) {
            if (!isset($cnf['recording_directory'])) { $cnf['recording_directory'] = $global->config_data['recording_directory']; }
            if (!isset($cnf['log_directory'])) { $cnf['log_directory'] = $global->config_data['log_directory']; }
            $empty = shell_exec("ls -l ".$cnf['recording_directory']."/ | tr -s ' ' | cut -d ' ' -f 5,8,9 | egrep '^[0-9]{1,3}\ ' | cut -d ' ' -f 3");
            $empty = preg_split('/\n/', $empty);
            foreach ($empty as $e) {
                if ($e == "") { continue; }
                $opens = shell_exec("lsof ".$cnf['recording_directory']."/".$e." | wc -l");
                if ($opens == 0) {
                    unlink($cnf['recording_directory']."/".$e);
                    echo "Removed empty file: ".$e."\n";
                    $logfile = preg_replace('/\.[^\.]*$/', '.log', $e);
                    unlink($cnf['log_directory']."/".$logfile);
                    echo "Removed logfile: ".$logfile."\n";
                }
            }
        }
    }

    unlink($global->config_data['lock_directory']."/remove-empty-files.lock");
?>
