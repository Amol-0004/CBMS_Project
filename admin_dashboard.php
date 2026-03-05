<?php
require_once 'config.php';
requireAdmin();
$conn = getDB();
?>
<?php include 'header.php'; ?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-bus"></i> Total Buses</h5>
                <h2><?php echo $conn->query("SELECT COUNT(*) FROM buses")->fetch_row()[0]; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-route"></i> Routes</h5>
                <h2><?php echo $conn->query("SELECT COUNT(*) FROM routes")->fetch_row()[0]; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-calendar-day"></i> Today's Schedules</h5>
                <h2><?php echo $conn->query("SELECT COUNT(*) FROM schedules WHERE date = CURDATE()")->fetch_row()[0]; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-ticket-alt"></i> Total Bookings</h5>
                <h2><?php echo $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'booked'")->fetch_row()[0]; ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Recent Bookings</div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Student</th><th>Route</th><th>Date</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("
                            SELECT b.id, s.name AS student_name, r.name AS route_name, sch.date, b.status
                            FROM bookings b
                            JOIN students s ON b.student_id = s.id
                            JOIN schedules sch ON b.schedule_id = sch.id
                            JOIN routes r ON sch.route_id = r.id
                            ORDER BY b.booking_date DESC LIMIT 10
                        ");
                        if ($result->num_rows === 0) {
                            echo '<tr><td colspan="5" class="text-center">No bookings yet.</td></tr>';
                        } else {
                            while ($row = $result->fetch_assoc()) {
                                $badgeClass = $row['status'] === 'booked' ? 'bg-success' : 'bg-secondary';
                                echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>{$row['student_name']}</td>
                                    <td>{$row['route_name']}</td>
                                    <td>{$row['date']}</td>
                                    <td><span class='badge {$badgeClass}'>{$row['status']}</span></td>
                                </tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Quick Actions</div>
            <div class="card-body">
               <a href="student_login.php" class="btn btn-success w-100 mb-2">Student Portal</a>
                <a href="manage_buses.php" class="btn btn-primary w-100 mb-2"><i class="fas fa-plus"></i> Add Bus</a>
                <a href="manage_routes.php" class="btn btn-info w-100 mb-2"><i class="fas fa-plus"></i> Add Route</a>
                <a href="manage_schedules.php" class="btn btn-warning w-100 mb-2"><i class="fas fa-plus"></i> Add Schedule</a>
                <a href="manage_bookings.php" class="btn btn-success w-100"><i class="fas fa-list"></i> View Bookings</a>
            </div>
        </div>
    </div>
</div>

<?php $conn->close(); ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>