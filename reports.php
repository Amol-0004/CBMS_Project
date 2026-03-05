<?php
require_once 'config.php';
requireAdmin();
$conn = getDB();

// Fetch stats
$total_bookings = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
$active_schedules = $conn->query("SELECT COUNT(*) FROM schedules WHERE status = 'scheduled'")->fetch_row()[0];
$occupancy_query = $conn->query("
    SELECT AVG((SELECT COUNT(*) FROM bookings b2 WHERE b2.schedule_id = s.id AND b2.status = 'booked') / bu.capacity * 100) AS avg_occupancy
    FROM schedules s JOIN buses bu ON s.bus_id = bu.id WHERE s.status = 'scheduled'
");
$avg_occupancy = $occupancy_query->fetch_row()[0] ?? 0;

// Monthly bookings
$monthly = $conn->query("
    SELECT DATE_FORMAT(booking_date, '%Y-%m') AS month, COUNT(*) AS count
    FROM bookings GROUP BY month ORDER BY month DESC LIMIT 6
")->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'header.php'; ?>

<h2><i class="fas fa-chart-bar"></i> Reports & Analytics</h2>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5>Total Bookings</h5>
                <h2><?php echo $total_bookings; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <h5>Active Schedules</h5>
                <h2><?php echo $active_schedules; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5>Avg Occupancy (%)</h5>
                <h2><?php echo round($avg_occupancy, 1); ?>%</h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Monthly Bookings (Last 6 Months)</div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>Month</th><th>Bookings</th></tr></thead>
                    <tbody>
                        <?php foreach (array_reverse($monthly) as $row): ?>
                            <tr><td><?php echo $row['month']; ?></td><td><?php echo $row['count']; ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Top Routes by Bookings</div>
            <div class="card-body">
                <?php
                $top_routes = $conn->query("
                    SELECT r.name, COUNT(b.id) AS bookings
                    FROM routes r
                    JOIN schedules s ON r.id = s.route_id
                    JOIN bookings b ON s.id = b.schedule_id AND b.status = 'booked'
                    GROUP BY r.id ORDER BY bookings DESC LIMIT 5
                ")->fetch_all(MYSQLI_ASSOC);
                ?>
                <ul class="list-group">
                    <?php foreach ($top_routes as $route): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <?php echo htmlspecialchars($route['name']); ?>
                            <span class="badge bg-primary"><?php echo $route['bookings']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php $conn->close(); ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Optional: Add Chart.js for graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Example chart (if you add canvas elements)
    // const ctx = document.getElementById('monthlyChart').getContext('2d');
    // new Chart(ctx, { type: 'bar', data: { /* from PHP */ } });
    </script>
</body>
</html>