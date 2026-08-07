<?php
session_start();
$_SESSION = array();
session_destroy();
header("Location: /grade10-elearning/admin/login.php");
exit;