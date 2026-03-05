<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);  // Set to 0 in production

define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Update as needed
define('DB_PASS', '');         // Update as needed
define('DB_NAME', 'college_bus_db');

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function requireAdmin() {
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        header('Location: login.php');
        exit;
    }
}

// Student auth check
function requireStudent() {
    if (!isset($_SESSION['student_logged_in']) || !$_SESSION['student_logged_in']) {
        header('Location: student_login.php');
        exit;
    }
}

// Flash messages helper (optional)
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
?>