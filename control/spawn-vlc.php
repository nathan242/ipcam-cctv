<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/../include/camera.php');
$database->keep_connected = 0;

$database->prepared_query('select `id` from `devices` where `name`=?', array('s'), array($argv[1]));
if (isset($database->result[0])) { $config = new config($database, $database->result[0]['id']); } else { $config = new config($database); }

$device = new camera($database, $config->config_data, false, $argv[1]);
if (!$device->device_data) {
    echo "No Device Data! [".$argv[1]."]\n";
    exit();
}

$try = 0;

$device->log_add('Device started.', 0);

while (!file_exists($config->config_data['pid_directory'].'/'.$device->device_data['name'].'.norespawn')) {

    if ($try > 0) {
        if ((time()-$timestamp) < $config->config_data['retry_time_limit']) {
            if ($try > $config->config_data['retry_count']) {
                $device->log_add('Device reload limit hit. Sleeping for '.$config->config_data['sleep_time'].' seconds (time='.(time()-$timestamp).', try='.$try.')', 2);
                $try = 0;
                file_put_contents($config->config_data['pid_directory'].'/'.$device->device_data['name'].'.sleep', $config->config_data['sleep_time']);
                file_put_contents($config->config_data['pid_directory'].'/'.$device->device_data['name'].'.sleeppid', getmypid());
                sleep($config->config_data['sleep_time']);
                unlink($config->config_data['pid_directory'].'/'.$device->device_data['name'].'.sleeppid');
                unlink($config->config_data['pid_directory'].'/'.$device->device_data['name'].'.sleep');
            } else {
                $device->log_add('Device reloaded after '.(time()-$timestamp).' seconds. (try='.$try.')', 1);
            }
        } else {
            $device->log_add('Device reloaded after '.(time()-$timestamp).' seconds.', 0);
            $try = 0;
        }
    }
    $try++;
    $timestamp = time();

    if ($config->config_data['transcode_enable'] == 1) {
        exec($config->config_data['vlc_exec'].' '.$device->full_url().' vlc://quit --sout \'#transcode{vcodec='.$config->config_data['transcode_video_codec'].',vb='.$config->config_data['transcode_bitrate'].',fps='.$config->config_data['transcode_fps'].',scale='.$config->config_data['transcode_scale'].',acodec='.$config->config_data['transcode_audio_codec'].'}:std{access=file,mux='.$config->config_data['recording_file_mux'].',dst="'.$config->config_data['recording_directory'].'/'.$device->device_data['name'].'.'.date('ymdHis').$config->config_data['recording_file_extension'].'"}\' 2>&1 | tee '.$config->config_data['log_directory'].'/'.$device->device_data['name'].'.'.date('ymdHis').'.log & jobs -p > '.$config->config_data['pid_directory'].'/'.$device->device_data['name'].'.pid');
    } else {
        exec($config->config_data['vlc_exec'].' '.$device->full_url().' vlc://quit --sout \'#std{access=file,mux='.$config->config_data['recording_file_mux'].',dst="'.$config->config_data['recording_directory'].'/'.$device->device_data['name'].'.'.date('ymdHis').$config->config_data['recording_file_extension'].'"}\' 2>&1 | tee '.$config->config_data['log_directory'].'/'.$device->device_data['name'].'.'.date('ymdHis').'.log & jobs -p > '.$config->config_data['pid_directory'].'/'.$device->device_data['name'].'.pid');
    }

    unlink($config->config_data['pid_directory'].'/'.$device->device_data['name'].'.pid');
}

$device->log_add('Device stopped.', 0);

unlink($config->config_data['pid_directory'].'/'.$device->device_data['name'].'.norespawn');
?>
