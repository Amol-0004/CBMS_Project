<?php
require_once 'config.php';
requireStudent();
$conn = getDB();
$schedule_id = (int)($_GET['schedule_id'] ?? 0);
if ($schedule_id === 0) { header('Location: student_dashboard.php'); exit; }

$stmt = $conn->prepare("
    SELECT s.*, r.name AS route, b.reg_number AS bus, b.capacity,
    (SELECT COUNT(*) FROM bookings bk WHERE bk.schedule_id = s.id AND bk.status = 'booked') AS booked_seats
    FROM schedules s JOIN routes r ON s.route_id = r.id JOIN buses b ON s.bus_id = b.id WHERE s.id = ?
");
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();
if (!$schedule || $schedule['date'] < date('Y-m-d')) { header('Location: student_dashboard.php'); exit; }

$available = $schedule['capacity'] - $schedule['booked_seats'];
if ($available <= 0) { header('Location: student_dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) die('Invalid token.');
    $seat_number = (int)$_POST['seat_number'];
    if ($seat_number < 1 || $seat_number > $schedule['capacity']) die('Invalid seat.');
    $check = $conn->prepare("SELECT id FROM bookings WHERE schedule_id = ? AND seat_number = ?");
    $check->bind_param("ii", $schedule_id, $seat_number);
    $check->execute();
    if ($check->get_result()->num_rows > 0) { setFlash('danger', 'Seat taken.'); } else {
        $student_id = $conn->query("SELECT id FROM students WHERE student_id = '" . $_SESSION['student_id'] . "'")->fetch_row()[0];
        $stmt = $conn->prepare("INSERT INTO bookings (student_id, schedule_id, seat_number) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $student_id, $schedule_id, $seat_number);
        if ($stmt->execute()) { setFlash('success', 'Booked! Seat ' . $seat_number); header('Location: my_bookings.php'); exit; } else setFlash('danger', 'Booking failed.');
    }
}
?>
<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2>Book for: <?php echo htmlspecialchars($schedule['bus'] . ' - ' . $schedule['route'] . ' (' . $schedule['date'] . ' at ' . $schedule['departure_time'] . ')'); ?></h2>
    <div class="card">
        <div class="card-body">
            <p><strong>Available:</strong> <?php echo $available; ?> / <?php echo $schedule['capacity']; ?></p>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="mb-3">
                    <label class="form-label">Seat Number (1-<?php echo $schedule['capacity']; ?>)</label>
                    <input type="number" name="seat_number" class="form-control" min="1" max="<?php echo $schedule['capacity']; ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Book</button>
                <a href="student_dashboard.php" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>

<?php $conn->close(); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>