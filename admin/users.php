<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/include/main.php');
$pagepath = array(array('CCTV CONTROL', '/cctv.php'), array('ADMIN SETTINGS', '/admin/'), array('USER MANAGER', $_SERVER['REQUEST_URI']));
$topbar = true;
include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';
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

<p><a class="btn btn-default" href="index.php">&lt&ltBACK</a></p>
<p>USERS:</p>
<input type="hidden" name="set_enable" value="1">
<table class="table table-hover">
    <tr>
        <th>ID</th>
        <th>USERNAME</th>
        <th>PASSWORD</th>
        <th>ENABLED</th>
        <th colspan=2>ACTIONS</th>
    </tr>

<?php

$db->query('select id, username, password, enabled from users');
foreach ($db->result as $row) {
    echo '<tr>';
    echo '<td>'.$row['id'].'</td>';
    echo '<td>'.$row['username'].'</td>';
    echo '<td>'.$row['password'].'</td>';
    if ($row['enabled']==1) {
        echo '<td><input type="checkbox" id="enabled" checked disabled></td>';
    } else {
        echo '<td><input type="checkbox" id="enabled" disabled></td>';
    }
    echo '<td><a class="btn btn-info" href="edituser.php?id='.$row['id'].'">EDIT</a></td>';
    echo '<td><a class="btn btn-danger" href="#" onclick="remove('.$row['id'].')">DELETE</a></td>';
    echo '</tr>';
}
?>

</table>

<p><a class="btn btn-info" href="edituser.php?new=1">CREATE USER</a></p>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
?>
