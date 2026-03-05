<?php
require_once 'config.php';
requireStudent();
$conn = getDB();
$student_id = $conn->query("SELECT id FROM students WHERE student_id = '" . $_SESSION['student_id'] . "'")->fetch_row()[0];

if (isset($_GET['cancel_id'])) {
    $cancel_id = (int)$_GET['cancel_id'];
    $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND student_id = ?");
    $stmt->bind_param("ii", $cancel_id, $student_id);
    $stmt->execute();
    setFlash('info', 'Cancelled.');
    header('Location: my_bookings.php'); exit;
}

// Prepare the SQL with a placeholder
$stmt = $conn->prepare("
    SELECT b.id, b.seat_number, b.status, b.booking_date, 
           s.date, s.departure_time, 
           r.name AS route, bu.reg_number AS bus
    FROM bookings b 
    JOIN schedules s ON b.schedule_id = s.id 
    JOIN routes r ON s.route_id = r.id 
    JOIN buses bu ON s.bus_id = bu.id
    WHERE b.student_id = ? AND b.status != 'cancelled' 
    ORDER BY s.date, s.departure_time
");

// Bind the parameter (i = integer, s = string, etc.)
$stmt->bind_param("i", $student_id);

// Execute
$stmt->execute();

// Get result
$bookings = $stmt->get_result();
?>
<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2 style="color:blue">My Bookings</h2>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead><tr><th>Bus</th><th>Route</th><th>Date & Time</th><th>Seat</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php while ($row = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['bus']); ?></td>
                        <td><?php echo htmlspecialchars($row['route']); ?></td>
                        <td><?php echo $row['date'] . ' ' . $row['departure_time']; ?></td>
                        <td><?php echo $row['seat_number']; ?></td>
                        <td><span class="badge bg-<?php echo $row['status'] == 'booked' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                        <td>
                            <?php if ($row['status'] == 'booked' && strtotime($row['date'] . ' ' . $row['departure_time']) > time()): ?>
                                <a href="?cancel_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" onclick="return confirm('Cancel?');">Cancel</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if ($bookings->num_rows === 0): ?><p class="text-center">No bookings. <a href="student_dashboard.php">Book now!</a></p><?php endif; ?>
        </div>
    </div>
</div>

<?php $conn->close(); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>