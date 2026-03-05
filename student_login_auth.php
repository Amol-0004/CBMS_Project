<?php
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success' => false, 'message' => 'Invalid method.']); exit; }
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { echo json_encode(['success' => false, 'message' => 'Invalid token.']); exit; }

$student_id = sanitize($_POST['student_id'] ?? '');
$password = $_POST['password'] ?? '';  // Temp: password = student_id

if (strlen($student_id) < 5) { echo json_encode(['success' => false, 'message' => 'Invalid Student ID.']); exit; }

$conn = getDB();
$stmt = $conn->prepare("SELECT id, name FROM students WHERE student_id = ? AND student_id = ?");  // Temp check
$stmt->bind_param("ss", $student_id, $password);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if ($student) {
    $_SESSION['student_logged_in'] = true;
    $_SESSION['student_id'] = $student_id;
    $_SESSION['student_name'] = $student['name'];
    echo json_encode(['success' => true, 'message' => 'Welcome!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Student ID or password.']);
}
$conn->close();
?>