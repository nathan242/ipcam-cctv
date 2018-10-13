<?php
    require_once 'include/inc.main.php';

    session_destroy();

    header('Location: /index.php');
