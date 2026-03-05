<?php
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success' => false, 'message' => 'Invalid method.']); exit; }
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { echo json_encode(['success' => false, 'message' => 'Invalid token.']); exit; }

$student_id = sanitize($_POST['student_id'] ?? '');
$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');

if (strlen($student_id) < 5 || strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['success' => false, 'message' => 'Invalid input.']); exit; }

$conn = getDB();
$stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) { echo json_encode(['success' => false, 'message' => 'Student ID already exists.']); $conn->close(); exit; }

$stmt = $conn->prepare("INSERT INTO students (student_id, name, email, phone) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $student_id, $name, $email, $phone);
$success = $stmt->execute();
echo json_encode(['success' => $success, 'message' => $success ? 'Registered!' : 'Failed. Try again.']);
$conn->close();
?>