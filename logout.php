<?php
require_once 'config.php';
session_destroy();
setFlash('info', 'Logged out successfully.');
header('Location: login.php');
session_destroy();
header('Location: home.php');  // Instead of login.php
exit;
?>
