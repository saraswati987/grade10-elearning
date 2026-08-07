<?php 
session_start();

session_destroy();

header("Location: /grade10-elearning/admin/login.php");

?>