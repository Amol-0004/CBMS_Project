<?php
require_once 'config.php';
unset($_SESSION['student_logged_in'], $_SESSION['student_id'], $_SESSION['student_name']);
session_destroy();
header('Location: student_login.php');
session_destroy();
header('Location: home.php');  // Instead of login.php
exit;
?>