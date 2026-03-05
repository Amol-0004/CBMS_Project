<?php
require_once 'config.php';
requireStudent();
$conn = getDB();
$schedules = $conn->query("
    SELECT s.id, s.date, s.departure_time, r.name AS route, b.reg_number AS bus, b.capacity,
    (SELECT COUNT(*) FROM bookings bk WHERE bk.schedule_id = s.id AND bk.status = 'booked') AS booked_seats
    FROM schedules s JOIN routes r ON s.route_id = r.id JOIN buses b ON s.bus_id = b.id
    WHERE s.date >= CURDATE() AND s.status = 'scheduled' ORDER BY s.date, s.departure_time
");
?>
<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?>!</h2>
    <div class="card">
        <div class="card-header">Upcoming Schedules</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead><tr><th>Bus</th><th>Route</th><th>Date</th><th>Time</th><th>Seats Available</th><th>Action</th></tr></thead>
                <tbody>
                    <?php while ($row = $schedules->fetch_assoc()): 
                        $available = $row['capacity'] - $row['booked_seats'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['bus']); ?></td>
                        <td><?php echo htmlspecialchars($row['route']); ?></td>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo $row['departure_time']; ?></td>
                        <td><?php echo $available > 0 ? $available : '<span class="text-danger">Full</span>'; ?></td>
                        <td><?php if ($available > 0): ?><a href="book_bus.php?schedule_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Book</a><?php else: ?><button class="btn btn-sm btn-secondary" disabled>Full</button><?php endif; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if ($schedules->num_rows === 0): ?><p class="text-center">No schedules available.</p><?php endif; ?>
        </div>
    </div>
</div>

<?php $conn->close(); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>