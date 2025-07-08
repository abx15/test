<?php
require_once '../config/config.php';

// Destroy session
session_destroy();

// Redirect to login page
header('Location: /admin/login.php');
exit();
?>
