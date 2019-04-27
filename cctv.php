<?php
    require_once 'include/inc.main.php';

    $pagepath = array(array('CCTV CONTROL', $_SERVER['REQUEST_URI']));
    $topbar = true;
    require 'include/header.php';

    // Recording controls
    ob_start();
    gui::button('START ALL', 'control.php?action=startall', 'success');
    echo ' ';
    gui::button('STOP ALL', 'control.php?action=stopall', 'danger');
    echo ' ';
    gui::button('RELOAD ALL', 'control.php?action=reloadall', 'warning');
    gui::panel('RECORDING CONTROL', ob_get_clean(), 'primary', array('class' => 'control-box'));
    
    echo ' ';
    
    // Recording data
    ob_start();
    gui::button('VIDEO FILES', 'recording-video.php');
    echo ' ';
    gui::button('RECORDING LOG VIEWER', 'recording-log.php');
    gui::panel('RECORDING DATA', ob_get_clean(), 'primary', array('class' => 'control-box'));
    
    echo ' ';
    
    // Admin settings
    ob_start();
    gui::button('ADMIN SETTINGS', '/admin/');
    gui::panel('FUNCTIONS', ob_get_clean(), 'primary', array('class' => 'control-box'));
    
?>

<hr>

<p>DISK USAGE:</p>

<?php

    $config = config::get_key_instances($db, 'recording_directory');
    if ($config === false) {
        echo '<p>Error getting [recording_directory] configuration key!</p>';
    }

    echo '<table border="1">';
    $headers_shown = false;
    foreach ($config as $c) {
        $rec_path = preg_replace('/[^a-zA-Z0-9\-\/]/', '_', $c['value']);
        $dev_ids = ($c['devices'] == '') ? '**DEFAULT**' : $c['devices'];
        exec('df -h '.$rec_path.' | tr -s \' \'', $df);
        $first = 1;
        foreach ($df as $line) {
            $word = explode(' ',$line);
            if ($first == '1') {
                if ($headers_shown === false) {
                    echo '<tr><td><b>Device IDs</b></td><td><b>'.$word[0].'</b></td><td><b>'.$word[1].'</b></td><td><b>'.$word[2].'</b></td><td><b>'.$word[3].'</b></td><td><b>'.$word[4].'</b></td><td><b>'.$word[5].'</b></td><td><b>% Used</b></td>';
                    $headers_shown = true;
                }
                $first = 0;
            } else {
                echo '<tr><td>'.$dev_ids.'</td><td>'.$word[0].'</td><td>'.$word[1].'</td><td>'.$word[2].'</td><td>'.$word[3].'</td><td>'.$word[4].'</td><td>'.$word[5].'</td>';
                echo '<td><div class="percentbar" style="width: 100px;"><div style="width:'.$word[4].';"></div></div></td></tr>';
            }
        }
        unset($df);
    }

    echo '</table>';

?>

<hr>

<?php
    $devices = escaper::escape_html_array(camera::get_all($db));
    $headings = [];
    foreach (camera::get_valid_properties() as $heading) {
        $headings[] = strtoupper(preg_replace('/_/', ' ', $heading));
    }
    $headings[] = 'STATUS';
    $headings[] = 'CONTROLS';
    
    // Add device status
    foreach ($devices as &$device) {
        $config = new config($db, $device['ID']);
        $camera = new camera($db, $config->config_data, $device['ID']);
        
        if ($camera->has_pid()) {
            if ($camera->is_running()) {
                $device['STATUS'] = 'RUNNING PID: '.$camera->get_pid();
            } else {
                $device['STATUS'] = 'PROCESS DIED PID: '.$camera->get_pid();
            }
        } elseif ($camera->has_sleep_pid()) {
            $device['STATUS'] = 'SLEEPING: '.$camera->get_sleep_pid();
        } else {
            $device['STATUS'] = 'STOPPED';
        }
    }
    
    
    // Device table
    ob_start();
    echo '<p>';
    gui::button('EDIT CAMERAS', '/camera-manager.php');
    echo '</p>';
    gui::table($devices, $headings, false, array(
        array(
            'text'    => 'START',
            'path'    => 'control.php?action=start',
            'key'     => 'camera',
            'vkey'    => 'NAME',
            'colour'  => 'success'
        ),
        array(
            'text'    => 'STOP',
            'path'    => 'control.php?action=stop',
            'key'     => 'camera',
            'vkey'    => 'NAME',
            'colour'  => 'danger'
        ),
        array(
            'text'    => 'RELOAD',
            'path'    => 'control.php?action=reload',
            'key'     => 'camera',
            'vkey'    => 'NAME',
            'colour'  => 'warning'
        ),
        array(
            'text'    => 'LIVE',
            'vkey'    => 'NAME',
            'options' => array(
                'onclick' => 'window.open(\'liveview.php?camera={vkey}\', \'{vkey}\', \'height=500, menubar=no, status=no, toolbar=no, width=660, location=no, scrollbars=no\');'
            )
        ),
        array(
            'text'    => 'RECORDINGS',
            'vkey'    => 'NAME',
            'options' => array(
                'onclick' => 'window.open(\'recording-viewer.php?camera={vkey}\', \'{vkey}\', \'height=500, menubar=no, status=no, toolbar=no, width=660, location=no, scrollbars=yes\');'
            )
        ),
        array(
            'text'    => 'LOG',
            'vkey'    => 'NAME',
            'options' => array(
                'onclick' => 'window.open(\'viewlog.php?camera={vkey}\', \'{vkey}\', \'height=700, menubar=no, status=no, toolbar=no, width=800, location=no, scrollbars=yes\');'
            )
        ),
        array(
            'text'    => 'WEB',
            'path'    => 'http://{vkey}/',
            'vkey'    => 'IP ADDRESS',
            'options' => array(
                'target' => '_blank'
            )
        )
    ));
    gui::panel('DEVICE LIST', ob_get_clean());

    require 'include/footer.php';
