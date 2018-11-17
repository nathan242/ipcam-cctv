<?php
    require_once 'include/inc.main.php';
    
    $add_form = new form('Add Camera', 'Add');
    $add_form->input('add', 'add', 'hidden', false, '1');
    foreach (camera::get_valid_properties() as $field_name) {
        $add_form->input($field_name,
                strtoupper(preg_replace('/_/', ' ', $field_name)),
                ($field_name == 'id') ? 'hidden' : 'text',
                true,
                false,
                array());
    }
    
    $result = $add_form->handle(
        'camera::create_camera',
        array(&$db)
    );
    
    if ($result) {
        if ($add_form->result) {
            header('Location: '.$_SERVER['HTTP_REFERER']);
            exit();
        } else {
            echo 'Error adding device!';
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
    require 'include/header.php';
    
    $add_form->html(true, true, true);
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

<?php
    $devices = escaper::escape_html_array(camera::get_all($db));
    
    $editor = new editortable('TEST', 'ID');
    $editor->set_data($devices);
    $editor->html();

    require 'include/footer.php';
