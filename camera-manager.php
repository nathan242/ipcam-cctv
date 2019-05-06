<?php
    require_once 'include/inc.main.php';
    
    $editor = new editortable('CAMERAS', 'id');
    $editor->handle_edit(
        function (&$db, $id, $field, $value) {
            $config = new config($db, $id);
            $camera = new camera($db, $config->config_data, $id);
            if (false === $camera->device_data || $camera->is_active()) {
                return false;
            }

            $camera->device_data[$field] = $value;
            return $camera->update();
        },
        array(&$db)
    );
    $editor->handle_delete(
        function (&$db, $id) {
            $config = new config($db, $id);
            return camera::delete_camera($db, $config->config_data, $_POST['id']);
        },
        [&$db]
    );

    $add_form = new form('ADD CAMERA', 'Add');
    $add_form->input('add', 'add', 'hidden', false, '1');
    foreach (camera::get_valid_properties() as $field_name) {
        if ('id' === $field_name) { continue; }

        $add_form->input($field_name,
                strtoupper(preg_replace('/_/', ' ', $field_name)),
                'text',
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
    
    $devices = escaper::escape_html_array(camera::get_all($db));
    $editor->set_data($devices, true);
    
    $headings = [];
    foreach (camera::get_valid_properties() as $heading) {
        $headings[] = strtoupper(preg_replace('/_/', ' ', $heading));
    }
    $editor->set_headings($headings);

    $pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('CAMERA MANAGER', $_SERVER['REQUEST_URI']));
    $topbar = true;
    require 'include/header.php';
    
    $add_form->html(true, true, true);
    $editor->html(true);
    
    require 'include/footer.php';
