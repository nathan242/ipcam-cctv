<?php
    require_once '../include/inc.main.php';
    $pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('ADMIN SETTINGS', '/admin/'), array('USER MANAGER', $_SERVER['REQUEST_URI']));
    $topbar = true;
    require '../include/header.php';
?>

<script>
function remove(id) {
    if (confirm('Delete user '+id)) {
        var form = document.createElement("form");
        var e1 = document.createElement("input");
        var e2 = document.createElement("input");

        form.action = "edituser.php";
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
</script>

<?php

    $db->query('select id, username, password, enabled from users');

    ob_start();
    gui::table($db->result, ['ID', 'USERNAME', 'PASSWORD HASH', 'ENABLED'], false, [
        [
            'text'    => 'EDIT',
            'path'    => 'edituser.php',
            'key'     => 'id',
            'vkey'    => 'id'
        ],
        [
            'text'    => 'DELETE',
            'vkey'    => 'id',
            'colour'  => 'danger',
            'options' => [
                'onclick' => 'remove(\'{vkey}\');'
            ]
        ]
    ]);
    gui::button('CREATE USER', 'edituser.php?new=1', 'info');
    gui::panel('Users', ob_get_clean());

    require '../include/footer.php';
?>
