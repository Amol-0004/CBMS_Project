<?php
require_once 'config.php';
requireAdmin();
$conn = getDB();

// Handle AJAX GET for edit data
if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM buses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $bus = $stmt->get_result()->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($bus ?: []);
    exit;
}

// Handle POST (Add/Edit/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid token.');
        header('Location: manage_buses.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    try {
        if ($action === 'add' || $action === 'edit') {
            $reg_number = sanitize($_POST['reg_number']);
            $model = sanitize($_POST['model']);
            $capacity = (int)($_POST['capacity']);
            $status = sanitize($_POST['status']);
            $driver_name = sanitize($_POST['driver_name']);

            if (strlen($reg_number) < 5 || strlen($model) < 2 || $capacity < 1) {
                throw new Exception('Invalid input data.');
            }

            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO buses (reg_number, model, capacity, status, driver_name) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiss", $reg_number, $model, $capacity, $status, $driver_name);
            } else {
                $stmt = $conn->prepare("UPDATE buses SET reg_number = ?, model = ?, capacity = ?, status = ?, driver_name = ? WHERE id = ?");
                $stmt->bind_param("ssissi", $reg_number, $model, $capacity, $status, $driver_name, $id);
            }
            $stmt->execute();
            setFlash('success', $action === 'add' ? 'Bus added!' : 'Bus updated!');
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM buses WHERE id = ?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) throw new Exception('Delete failed.');
            setFlash('success', 'Bus deleted!');
        }
    } catch (Exception $e) {
        setFlash('danger', $e->getMessage());
    }
    header('Location: manage_buses.php');
    exit;
}

// Fetch all buses
$buses = $conn->query("SELECT * FROM buses ORDER BY created_at DESC");
?>
<?php include 'header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-bus"></i> Manage Buses</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#busModal"><i class="fas fa-plus"></i> Add Bus</button>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Reg Number</th>
                    <th>Model</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Driver</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $buses->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['reg_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['model']); ?></td>
                    <td><?php echo $row['capacity']; ?></td>
                    <td>
                        <span class="badge bg-<?php echo $row['status'] === 'active' ? 'success' : ($row['status'] === 'maintenance' ? 'warning' : 'secondary'); ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($row['driver_name'] ?? 'N/A'); ?></td>
                    <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-sm btn-info edit-btn" data-id="<?php echo $row['id']; ?>" data-bs-toggle="modal" data-bs-target="#busModal">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $row['id']; ?>">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php if ($buses->num_rows === 0): ?>
            <p class="text-center text-muted">No buses yet. Add one to get started!</p>
        <?php endif; ?>
    </div>
</div>

<!-- Bus Modal -->
<div class="modal fade" id="busModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Bus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="busForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" id="busId">
                    <div class="mb-3">
                        <label class="form-label">Registration Number *</label>
                        <input type="text" name="reg_number" class="form-control" required minlength="5">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Model *</label>
                        <input type="text" name="model" class="form-control" required minlength="2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacity *</label>
                        <input type="number" name="capacity" class="form-control" required min="1" value="50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Driver Name</label>
                        <input type="text" name="driver_name" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    const modal = new bootstrap.Modal(document.getElementById('busModal'));

    // Edit button
    $('.edit-btn').click(function() {
        const id = $(this).data('id');
        $('#modalTitle').text('Edit Bus');
        $('input[name="action"]').val('edit');
        $('#busId').val(id);
        $.get('manage_buses.php?action=get&id=' + id, function(data) {
            const bus = typeof data === 'object' ? data : JSON.parse(data);
            $('input[name="reg_number"]').val(bus.reg_number);
            $('input[name="model"]').val(bus.model);
            $('input[name="capacity"]').val(bus.capacity);
            $('select[name="status"]').val(bus.status);
            $('input[name="driver_name"]').val(bus.driver_name);
        });
    });

    // Reset modal for add
    $('#busModal').on('hidden.bs.modal', function() {
        $('#busForm')[0].reset();
        $('input[name="action"]').val('add');
        $('#busId').val('');
        $('#modalTitle').text('Add Bus');
    });

    // Delete confirmation
    $('.delete-btn').click(function() {
        if (confirm('Are you sure? This will remove the bus and related schedules.')) {
            const id = $(this).data('id');
            $.post('manage_buses.php', {action: 'delete', id: id, csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'}, function() {
                location.reload();
            }).fail(function() {
                alert('Delete failed.');
            });
        }
    });

    // Form submit (for add/edit)
    $('#busForm').submit(function(e) {
        e.preventDefault();
        $.post('manage_buses.php', $(this).serialize(), function() {
            modal.hide();
            location.reload();
        }).fail(function() {
            alert('Save failed.');
        });
    });
});
</script>
<?php $conn->close(); ?>
</body>
</html>