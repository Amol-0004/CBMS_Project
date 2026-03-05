<?php
require_once 'config.php';
requireAdmin();
$conn = getDB();

// Handle POST for status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid token.');
        header('Location: manage_bookings.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');

    if ($action === 'update_status' && in_array($status, ['booked', 'cancelled', 'no-show'])) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            setFlash('success', 'Booking status updated!');
        } else {
            setFlash('danger', 'Update failed.');
        }
    }
    header('Location: manage_bookings.php');
    exit;
}

// Fetch bookings with joins
$bookings = $conn->query("
    SELECT b.*, s.student_id, s.name AS student_name, s.email, sch.date, sch.departure_time, r.name AS route_name, bu.reg_number
    FROM bookings b
    JOIN students s ON b.student_id = s.id
    JOIN schedules sch ON b.schedule_id = sch.id
    JOIN routes r ON sch.route_id = r.id
    JOIN buses bu ON sch.bus_id = bu.id
    ORDER BY b.booking_date DESC
");
?>
<?php include 'header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-ticket-alt"></i> Manage Bookings</h2>
    <a href="#" class="btn btn-primary" onclick="exportBookings()"><i class="fas fa-download"></i> Export CSV</a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Student ID</th>
                    <th>Bus</th>
                    <th>Route</th>
                    <th>Date & Time</th>
                    <th>Seat</th>
                    <th>Status</th>
                    <th>Booked On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $bookings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['reg_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['route_name']); ?></td>
                    <td><?php echo $row['date'] . ' ' . $row['departure_time']; ?></td>
                    <td><?php echo $row['seat_number']; ?></td>
                    <td>
                        <span class="badge bg-<?php echo $row['status'] === 'booked' ? 'success' : ($row['status'] === 'cancelled' ? 'danger' : 'secondary'); ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y H:i', strtotime($row['booking_date'])); ?></td>
                    <td>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Update status?');">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit();">
                                <option value="booked" <?php echo $row['status'] === 'booked' ? 'selected' : ''; ?>>Booked</option>
                                <option value="cancelled" <?php echo $row['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="no-show" <?php echo $row['status'] === 'no-show' ? 'selected' : ''; ?>>No-Show</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php if ($bookings->num_rows === 0): ?>
            <p class="text-center text-muted">No bookings yet.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function exportBookings() {
    // Simple JS CSV export (or use PHP for server-side)
    let csv = 'ID,Student,Student ID,Bus,Route,Date Time,Seat,Status,Booked On\n';
    $('tbody tr').each(function() {
        let row = $(this).find('td');
        csv += row.eq(0).text() + ',' + row.eq(1).text() + ',' + row.eq(2).text() + ',' + row.eq(3).text() + ',' + 
               row.eq(4).text() + ',' + row.eq(5).text() + ',' + row.eq(6).text() + ',' + row.eq(7).text() + ',' + row.eq(8).text() + '\n';
    });
    let blob = new Blob([csv], { type: 'text/csv' });
    let url = window.URL.createObjectURL(blob);
    let a = document.createElement('a');
    a.href = url;
    a.download = 'bookings.csv';
    a.click();
}
</script>
<?php $conn->close(); ?>
</body>
</html>