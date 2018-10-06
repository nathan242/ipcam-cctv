<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/include/main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/include/camera.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config.php');

if (!isset($_GET['camera'])) { exit(); }


$db->prepared_query('select `id` from `devices` where `name`=?', array('s'), array($_GET['camera']));
if (isset($db->result[0])) { $config = new config($db, $db->result[0]['id']); } else { $config = new config($db); }

$device = new camera($db, $config->config_data ,false, $_GET['camera']);

$videoaddr = $device->full_url();
$pagetitle = $device->device_data['name'];
include $_SERVER['DOCUMENT_ROOT'].'/include/header.php';
?>

<embed type="application/x-vlc-plugin"
         name="video"
         autoplay="yes" loop="no" hidden="no" width="640" height="480"
         target="<?php echo $videoaddr; ?>" />

<?php
include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php';
?>
