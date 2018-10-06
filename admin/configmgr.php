<?php
    require_once($_SERVER['DOCUMENT_ROOT'].'/include/main.php');
    require_once($_SERVER['DOCUMENT_ROOT'].'/include/camera.php');
    require_once($_SERVER['DOCUMENT_ROOT'].'/control/config.php');

    // Add
    if (isset($_POST['add']) && isset($_POST['device']) && isset($_POST['key']) && isset($_POST['value']) && $_POST['key'] != '' && !preg_match('/^\s+$/', $_POST['key'])) {
        if (preg_match('/^[0-9]+/', $_POST['device']) && $_POST['device'] != 0) {
            $device = $_POST['device'];
        } else {
            $device = false;
        }

        if (!config::set($db, $device, $_POST['key'], $_POST['value'])) {
            echo 'Error adding config!';
            exit();
        }
    }

    // Delete
    if (isset($_POST['delete']) && isset($_POST['device']) && isset($_POST['key'])) {
        if (preg_match('/^[0-9]+/', $_POST['device']) && $_POST['device'] != 0) {
            $device = $_POST['device'];
        } else {
            $device = false;
        }

        if (!config::del($db, $device, $_POST['key'])) {
            echo 'Error deleting config!';
            exit();
        }
    }

    // Update
    if (isset($_POST['edit']) && isset($_POST['device']) && isset($_POST['key']) && isset($_POST['value']) && $_POST['key'] != '' && !preg_match('/^\s+$/', $_POST['key'])) {
        if (preg_match('/^[0-9]+/', $_POST['device']) && $_POST['device'] != 0) {
            $device = $_POST['device'];
        } else {
            $device = false;
        }

        if (config::set($db, $device, $_POST['key'], $_POST['value'])) {
            exit('0');
        } else {
            exit('1');
        }
    }

    $pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('ADMIN SETTINGS', '/admin/'), array('CONFIGURATION MANAGER', $_SERVER['REQUEST_URI']));
    $topbar = true;

    // Get devices
    $devices = array();
    $db->query('SELECT DISTINCT `devices`.`id`, `devices`.`name`, `config`.`device` FROM `devices` LEFT JOIN `config` ON `config`.`device` = `devices`.`id`');
    if (isset($db->result[0])) {
        foreach ($db->result as $r) {
            if ($r['device'] != '') { $r['name'] .= '*'; }
            $devices[] = $r;
        }
    }

    // Get config
    $running = '';
    $run_warn = '';
    if (isset($_GET['config']) && preg_match('/^[0-9]+/', $_GET['config']) && $_GET['config'] != 0) {
        $config = new config($db, $_GET['config'], false);
        $full_config = new config($db, $_GET['config']);
        $device = new camera($db, $full_config->config_data, $_GET['config']);
        if ($device->is_active()) {
            $running = ' disabled';
            $run_warn = 'This device is currently active.';
        }
    } else {
        $_GET['config'] = 0;
        $config = new config($db);
        // Are any cameras active?
        $db->query('SELECT `id` FROM `devices`');
        if (isset($db->result[0])) {
            $result = $db->result;
            foreach ($result as $row) {
                $full_config = new config($db, $row['id']);
                $device = new camera($db, $full_config->config_data, $row['id']);
                if ($device->is_active()) {
                    $running = ' disabled';
                    $run_warn = 'A device is currently active.';
                    break;
                }
            }
        }
    }

    include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';
?>

<script>
    function remove(device, key) {
        if (confirm('Delete key ['+key+']?')) {
            var form = document.createElement("form");
            var e1 = document.createElement("input");
            var e2 = document.createElement("input");
            var e3 = document.createElement("input");

            form.method = "POST";

            e1.name = "delete";
            e1.value = 1;
            e1.type = "hidden";
            e2.name = "device";
            e2.value = device;
            e2.type = "hidden";
            e3.name = "key";
            e3.value = key;
            e3.type = "hidden";

            form.appendChild(e1);
            form.appendChild(e2);
            form.appendChild(e3);
            document.body.appendChild(form);

            form.submit();
        }
    }

    function update(device, field) {
        field.style.backgroundColor = "#FFFF00";
        xhr = new XMLHttpRequest();
        xhr.open("POST", "");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                if (xhr.responseText == 0) {
                    field.style.backgroundColor = "#FFFFFF";
                } else {
                    field.style.backgroundColor = "#FF0000";
                }
            }
        };
        formData = new FormData();
        formData.append('edit', 1);
        formData.append('device', device);
        formData.append('key', field.name);
        formData.append('value', field.value);
        xhr.send(formData);
    }
</script>

<p><a class="btn btn-default" href="/admin/">&lt&ltBACK</a></p>

<div class="panel panel-default control-box">
    <div class="panel-body">
        <form action="" method="get">
            <p>CONFIGURATION: <select name="config" onchange="javascript:this.form.submit();">
            <option value="0"<?= (!isset($_GET['config'])) ? ' selected' : '' ?>>[GLOBAL]</option>
            <?php
                foreach ($devices as $d) {
                    echo '<option'.((isset($_GET['config']) && $_GET['config'] == $d['id']) ? ' selected' : '').' value="'.$d['id'].'">'.$d['name'].'</option>';
                }
            ?>
            </select>
        </form>
    </div>
</div>

<?php
    if ($run_warn != '') {
        echo '<br>';
        echo '<div class="panel panel-danger control-box">';
        echo '<div class="panel-body">';
        echo $run_warn;
        echo '</div>';
        echo '</div>';
    }
?>

<p>ADD CONFIGURATION:</p>

<form action="" method="POST">
    <input type="hidden" name="add" value="1">
    <input type="hidden" name="device" value="<?= $_GET['config'] ?>">
    <table border="1">
        <tr>
            <th>KEY</th>
            <th>VALUE</th>
        </tr>
        <tr>
            <td><input type="text" name="key"<?= $running ?>></td>
            <td><input type="text" name="value"<?= $running ?>></td>
            <?php if ($running == '') { ?><td><input class="btn btn-success" type="submit" value="ADD"></td><?php } ?>
        </tr>
    </table>
</form>

<hr>

<p>CONFIGURATION DATA:</p>

<table border="1">
    <tr>
        <th>KEY</th>
        <th>VALUE</th>
    <tr>
    <?php
        $highlight = 0;
        foreach ($config->config_data as $k => $v) {
            echo ($highlight) ? '<tr bgcolor="#CCCCCC">' : '<tr>';
            $highlight = ($highlight) ? 0 : 1;
            echo '<td>'.$k.'</td>';
            echo '<td><input type="text" name="'.$k.'" value="'.$v.'" onchange="update('.$_GET['config'].', this);"'.$running.'></td>';
            if ($running == '') { echo '<td><a class="btn btn-danger" href="#" onclick="remove('.$_GET['config'].', \''.$k.'\')">DELETE</a></td>'; }
            echo '</tr>';
        }
    ?>
</table>

<?php
    include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
?>
