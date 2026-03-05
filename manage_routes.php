<?php
require_once 'config.php';
requireAdmin();
$conn = getDB();

// Handle AJAX GET for edit
if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM routes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $route = $stmt->get_result()->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($route ?: []);
    exit;
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid token.');
        header('Location: manage_routes.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    try {
        if ($action === 'add' || $action === 'edit') {
            $name = sanitize($_POST['name']);
            $from = sanitize($_POST['from_location']);
            $to = sanitize($_POST['to_location']);
            $distance = (float)($_POST['distance_km'] ?? 0);
            $time = sanitize($_POST['estimated_time']);

            if (strlen($name) < 3 || strlen($from) < 2 || strlen($to) < 2) {
                throw new Exception('Invalid input.');
            }

            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO routes (name, from_location, to_location, distance_km, estimated_time) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssds", $name, $from, $to, $distance, $time);
            } else {
                $stmt = $conn->prepare("UPDATE routes SET name = ?, from_location = ?, to_location = ?, distance_km = ?, estimated_time = ? WHERE id = ?");
                $stmt->bind_param("sssdsi", $name, $from, $to, $distance, $time, $id);
            }
            $stmt->execute();
            setFlash('success', $action === 'add' ? 'Route added!' : 'Route updated!');
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM routes WHERE id = ?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) throw new Exception('Delete failed.');
            setFlash('success', 'Route deleted!');
        }
    } catch (Exception $e) {
        setFlash('danger', $e->getMessage());
    }
    header('Location: manage_routes.php');
    exit;
}

$routes = $conn->query("SELECT * FROM routes ORDER BY created_at DESC");
?>
<?php include 'header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-route"></i> Manage Routes</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#routeModal"><i class="fas fa-plus"></i> Add Route</button>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Distance (km)</th>
                    <th>Est. Time</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $routes->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['from_location']); ?></td>
                    <td><?php echo htmlspecialchars($row['to_location']); ?></td>
                    <td><?php echo $row['distance_km'] ?: 'N/A'; ?></td>
                    <td><?php echo htmlspecialchars($row['estimated_time'] ?? 'N/A'); ?></td>
                    <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-sm btn-info edit-btn" data-id="<?php echo $row['id']; ?>" data-bs-toggle="modal" data-bs-target="#routeModal">
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

<!-- Route Modal -->
<div class="modal fade" id="routeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Route</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="routeForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" id="routeId">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" required minlength="3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">From Location *</label>
                        <input type="text" name="from_location" class="form-control" required minlength="2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">To Location *</label>
                        <input type="text" name="to_location" class="form-control" required minlength="2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Distance (km)</label>
                        <input type="number" step="0.1" name="distance_km" class="form-control" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estimated Time</label>
                        <input type="text" name="estimated_time" class="form-control" placeholder="e.g., 15 mins">
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
    const modal = new bootstrap.Modal(document.getElementById('routeModal'));

    $('.edit-btn').click(function() {
        const id = $(this).data('id');
        $('#modalTitle').text('Edit Route');
        $('input[name="action"]').val('edit');
        $('#routeId').val(id);
        $.get('manage_routes.php?action=get&id=' + id, function(data) {
            const route = typeof data === 'object' ? data : JSON.parse(data);
            $('input[name="name"]').val(route.name);
            $('input[name="from_location"]').val(route.from_location);
            $('input[name="to_location"]').val(route.to_location);
            $('input[name="distance_km"]').val(route.distance_km);
            $('input[name="estimated_time"]').val(route.estimated_time);
        });
    });

    $('#routeModal').on('hidden.bs.modal', function() {
        $('#routeForm')[0].reset();
        $('input[name="action"]').val('add');
        $('#routeId').val('');
        $('#modalTitle').text('Add Route');
    });

    $('.delete-btn').click(function() {
        if (confirm('Delete this route? Related schedules will be affected.')) {
            const id = $(this).data('id');
            $.post('manage_routes.php', {action: 'delete', id: id, csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'}, function() {
                location.reload();
            });
        }
    });

    $('#routeForm').submit(function(e) {
        e.preventDefault();
        $.post('manage_routes.php', $(this).serialize(), function() {
            modal.hide();
            location.reload();
        });
    });
});
</script>
<?php $conn->close(); ?>
</body>
</html>