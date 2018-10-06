<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/include/main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/include/camera.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/control/config.php');

// Add

if (isset($_POST['add']) && isset($_POST['name']) && isset($_POST['ip_address']) && isset($_POST['protocol']) && isset($_POST['url']) && isset($_POST['username']) && isset($_POST['password'])) {
    $data = array(
        'name'=>$_POST['name'],
        'ip_address'=>$_POST['ip_address'],
        'protocol'=>$_POST['protocol'],
        'url'=>$_POST['url'],
        'username'=>$_POST['username'],
        'password'=>$_POST['password']
        );
    if (camera::create_camera($db, $data)) {
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit();
    } else {
        echo "Error adding device!";
        exit();
    }
}

// Delete

if (isset($_POST['delete']) && isset($_POST['id'])) {
    $config = new config($db, $_POST['id']);
    if (camera::delete_camera($db, $config->config_data, $_POST['id'])) {
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit();
    } else {
        echo "Error deleting device!";
        exit();
    }
}

// Update
if (isset($_POST['edit']) && isset($_POST['id']) && isset($_POST['field']) && isset($_POST['value'])) {
    if (!in_array($_POST['field'], camera::get_valid_properties())) {
        exit('1');
    }

    $config = new config($db, $_POST['id']);
    $device = new camera($db, $config->config_data, $_POST['id']);
    if ($device->device_data === false || $device->is_active()) {
        // Camera does not exist or is running
        exit('1');
    }

    $device->device_data[$_POST['field']] = $_POST['value'];
    if ($device->update() === false) {
        exit('1');
    } else {
        exit('0');
    }
}

$pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('CAMERA MANAGER', $_SERVER['REQUEST_URI']));
$topbar = true;
include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';
?>

<script>
function remove(id) {
    if (confirm('Delete device '+id+'?')) {
        var form = document.createElement("form");
        var e1 = document.createElement("input");
        var e2 = document.createElement("input");

        form.method = "POST";

        e1.name = "delete";
        e1.value = 1;
        e1.type = "hidden";
        e2.name = "id";
        e2.value = id;
        e2.type = "hidden";

        form.appendChild(e1);
        form.appendChild(e2);
        document.body.appendChild(form);

        form.submit();
    }
}

function update(field) {
    field.style.backgroundColor = "#FFFF00";
    var data = field.name.split("+");
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
    formData.append('id', data[0]);
    formData.append('field', data[1]);
    formData.append('value', field.value);
    xhr.send(formData);
}
</script>

<p><a class="btn btn-default" href="/cctv.php">&lt&ltBACK</a></p>

<p>ADD DEVICE:</p>

<form action="" method="POST">
<input type="hidden" name="add" value="1">
<table border="1">
    <tr>
        <th>ID</th>
        <th>NAME</th>
        <th>IP ADDRESS</th>
        <th>PROTOCOL</th>
        <th>URL</th>
        <th>USERNAME</th>
        <th>PASSWORD</th>
    </tr>
    <tr>
        <td>#</td>
        <td><input type="text" name="name"></td>
        <td><input type="text" name="ip_address"></td>
        <td><input type="text" name="protocol"></td>
        <td><input type="text" name="url"></td>
        <td><input type="text" name="username"></td>
        <td><input type="text" name="password"></td>
        <td><input class="btn btn-success" type="submit" value="ADD"></td>
    </tr>
</table>
</form>

<hr>

<p>DEVICE LIST:</p>

<table border="1">
    <tr>
        <th>ID</th>
        <th>NAME</th>
        <th>IP ADDRESS</th>
        <th>PROTOCOL</th>
        <th>URL</th>
        <th>USERNAME</th>
        <th>PASSWORD</th>
    </tr>

<?php
$db->query("select `id` from devices");
$highlight = 0;
if (isset($db->result[0])) {
    $ids = $db->result;
    foreach ($ids as $id) {
        $config = new config($db, $id['id']);
        $device = new camera($db, $config->config_data, $id['id']);
        if ($device->is_active()) {
            $running = ' disabled';
        } else {
            $running = '';
        }
        echo ($highlight) ? '<tr bgcolor="#CCCCCC">' : '<tr>';
        $highlight = ($highlight) ? 0 : 1;
        echo '<td>'.$device->device_data['id'].'</td>';
        echo '<td><input type="text" name="'.$device->device_data['id'].'+name" value="'.$device->device_data['name'].'" onchange="update(this);"'.$running.'></td>';
        echo '<td><input type="text" name="'.$device->device_data['id'].'+ip_address" value="'.$device->device_data['ip_address'].'" onchange="update(this);"'.$running.'></td>';
        echo '<td><input type="text" name="'.$device->device_data['id'].'+protocol" value="'.$device->device_data['protocol'].'" onchange="update(this);"'.$running.'></td>';
        echo '<td><input type="text" name="'.$device->device_data['id'].'+url" value="'.$device->device_data['url'].'" onchange="update(this);"'.$running.'></td>';
        echo '<td><input type="text" name="'.$device->device_data['id'].'+username" value="'.$device->device_data['username'].'" onchange="update(this);"'.$running.'></td>';
        echo '<td><input type="text" name="'.$device->device_data['id'].'+password" value="'.$device->device_data['password'].'" onchange="update(this);"'.$running.'></td>';
        echo '<td><a class="btn btn-danger" href="#" onclick="remove('.$device->device_data['id'].')">DELETE</a></td>';
        echo '</tr>';
    }
}
?>
</table>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
?>
