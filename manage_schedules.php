<?php
require_once 'config.php';
requireAdmin();
$conn = getDB();

// Handle GET for edit
if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("
        SELECT s.*, b.reg_number, r.name AS route_name 
        FROM schedules s 
        JOIN buses b ON s.bus_id = b.id 
        JOIN routes r ON s.route_id = r.id 
        WHERE s.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $schedule = $stmt->get_result()->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($schedule ?: []);
    exit;
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid token.');
        header('Location: manage_schedules.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    try {
        if ($action === 'add' || $action === 'edit') {
            $bus_id = (int)$_POST['bus_id'];
            $route_id = (int)$_POST['route_id'];
            $departure_time = $_POST['departure_time'];
            $date = $_POST['date'];
            $status = sanitize($_POST['status']);

            if ($bus_id < 1 || $route_id < 1 || empty($departure_time) || empty($date)) {
                throw new Exception('Invalid input.');
            }

            // Check for unique constraint
            $checkStmt = $conn->prepare("SELECT id FROM schedules WHERE bus_id = ? AND route_id = ? AND date = ? AND departure_time = ?");
            $checkStmt->bind_param("iiss", $bus_id, $route_id, $date, $departure_time);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0 && $action === 'add') {
                throw new Exception('Schedule already exists for this bus/route/date/time.');
            }

            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO schedules (bus_id, route_id, departure_time, date, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisss", $bus_id, $route_id, $departure_time, $date, $status);
            } else {
                $stmt = $conn->prepare("UPDATE schedules SET bus_id = ?, route_id = ?, departure_time = ?, date = ?, status = ? WHERE id = ?");
                $stmt->bind_param("iisssi", $bus_id, $route_id, $departure_time, $date, $status, $id);
            }
            $stmt->execute();
            setFlash('success', $action === 'add' ? 'Schedule added!' : 'Schedule updated!');
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM schedules WHERE id = ?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) throw new Exception('Delete failed.');
            setFlash('success', 'Schedule deleted!');
        }
    } catch (Exception $e) {
        setFlash('danger', $e->getMessage());
    }
    header('Location: manage_schedules.php');
    exit;
}

// Fetch buses and routes for dropdowns
$buses = $conn->query("SELECT id, reg_number FROM buses WHERE status = 'active' ORDER BY reg_number");
$routes = $conn->query("SELECT id, name FROM routes ORDER BY name");

// Fetch schedules with joins
$schedules = $conn->query("
    SELECT s.*, b.reg_number, r.name AS route_name 
    FROM schedules s 
    JOIN buses b ON s.bus_id = b.id 
    JOIN routes r ON s.route_id = r.id 
    ORDER BY s.date DESC, s.departure_time ASC
");
?>
<?php include 'header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-calendar-alt"></i> Manage Schedules</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal"><i class="fas fa-plus"></i> Add Schedule</button>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Bus</th>
                    <th>Route</th>
                    <th>Date</th>
                    <th>Departure</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $schedules->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['reg_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['route_name']); ?></td>
                    <td><?php echo $row['date']; ?></td>
                    <td><?php echo $row['departure_time']; ?></td>
                    <td>
                        <span class="badge bg-<?php echo $row['status'] === 'scheduled' ? 'info' : ($row['status'] === 'ongoing' ? 'warning' : ($row['status'] === 'completed' ? 'success' : 'secondary')); ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-sm btn-info edit-btn" data-id="<?php echo $row['id']; ?>" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $row['id']; ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" id="scheduleId">
                    <div class="mb-3">
                        <label class="form-label">Bus *</label>
                        <select name="bus_id" class="form-select" required>
                            <option value="">Select Bus</option>
                            <?php while ($bus = $buses->fetch_assoc()): ?>
                                <option value="<?php echo $bus['id']; ?>"><?php echo htmlspecialchars($bus['reg_number']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Route *</label>
                        <select name="route_id" class="form-select" required>
                            <option value="">Select Route</option>
                            <?php $routes->data_seek(0); while ($route = $routes->fetch_assoc()): ?>
                                <option value="<?php echo $route['id']; ?>"><?php echo htmlspecialchars($route['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" name="date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Departure Time *</label>
                        <input type="time" name="departure_time" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="scheduled">Scheduled</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));

    $('.edit-btn').click(function() {
        const id = $(this).data('id');
        $('#modalTitle').text('Edit Schedule');
        $('input[name="action"]').val('edit');
        $('#scheduleId').val(id);
        $.get('manage_schedules.php?action=get&id=' + id, function(data) {
            const sch = typeof data === 'object' ? data : JSON.parse(data);
            $('select[name="bus_id"]').val(sch.bus_id);
            $('select[name="route_id"]').val(sch.route_id);
            $('input[name="date"]').val(sch.date);
            $('input[name="departure_time"]').val(sch.departure_time);
            $('select[name="status"]').val(sch.status);
        });
    });

    $('#scheduleModal').on('hidden.bs.modal', function() {
        $('#scheduleForm')[0].reset();
        $('input[name="action"]').val('add');
        $('#scheduleId').val('');
        $('#modalTitle').text('Add Schedule');
    });

    $('.delete-btn').click(function() {
        if (confirm('Delete this schedule? Bookings will be cancelled.')) {
            const id = $(this).data('id');
            $.post('manage_schedules.php', {action: 'delete', id: id, csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'}, function() {
                location.reload();
            });
        }
    });

    $('#scheduleForm').submit(function(e) {
        e.preventDefault();
        $.post('manage_schedules.php', $(this).serialize(), function() {
            modal.hide();
            location.reload();
        });
    });
});
</script>
<?php $conn->close(); ?>
</body>
</html>