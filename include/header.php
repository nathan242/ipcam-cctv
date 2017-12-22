<?php
    if (isset($pagepath)) {
        $pagetitle = $pagepath[count($pagepath)-1][0];
        $breadcrumb = '';
        foreach ($pagepath as $v) {
            if ($breadcrumb != '') { $breadcrumb .= ' > '; }
            $breadcrumb .= '<a class="header-location-link" href="'.$v[1].'">'.$v[0].'</a>';
        }
    }
?>
<!doctype html>
<head>

<link rel="stylesheet" href="/include/css/style.css">

<link rel="stylesheet" href="/include/bootstrap/css/bootstrap.min.css">
<script src="/include/js/jquery-2.2.4.min.js"></script>
<script src="/include/bootstrap/js/bootstrap.min.js"></script>

<title><?php echo $pagetitle; ?></title>

</head>
<body>

<?php if (isset($topbar)) {
    echo '<div class="topbar">';
    echo '<ul class="topbar">';
    echo '<li class="topbar header-location">'.$breadcrumb.'</li>';
    echo '<li class="topbar header-username">'.$_SESSION['loginuser'].'</li>';
    echo '<li class="topbar"><a class="btn btn-info logout-button" href="/logout.php">LOGOUT</a></li>';
    echo '</ul>';
    echo '<hr>';
    echo '</div>';
}
?>

