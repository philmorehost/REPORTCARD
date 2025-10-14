<?php
$page_title = "School Management";
include 'partials/header.php'; // Also includes db connection via config.php which is included in auth_check.php->header
require_once __DIR__ . '/../../config/config.php'; // Re-include to be sure $pdo is available

// Fetch schools and their admins from the database
try {
    // Fetch packages for the modal
    $stmt_packages = $pdo->query("SELECT id, name FROM packages ORDER BY name");
    $packages = $stmt_packages->fetchAll(PDO::FETCH_ASSOC);

    $search_term = $_GET['search'] ?? '';
    $status_filter = $_GET['status'] ?? '';

    $sql = "SELECT s.id, s.name, s.status, s.student_slots, u.full_name AS admin_name, u.email AS admin_email, u.id as user_id
            FROM schools s
            LEFT JOIN users u ON s.id = u.school_id AND u.role = 'school_admin'";

    $where_clauses = [];
    $params = [];

    if (!empty($search_term)) {
        $where_clauses[] = "(s.name LIKE :search OR u.email LIKE :search OR u.full_name LIKE :search)";
        $params[':search'] = '%' . $search_term . '%';
    }

    if (!empty($status_filter)) {
        $where_clauses[] = "s.status = :status";
        $params[':status'] = $status_filter;
    }

    if (count($where_clauses) > 0) {
        $sql .= " WHERE " . implode(' AND ', $where_clauses);
    }

    $sql .= " ORDER BY s.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In a real app, log this error. For now, show a simple message.
    $schools = [];
    echo '<div class="alert alert-danger">Could not fetch school data. Error: ' . $e->getMessage() . '</div>';
}

// Check for session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">School Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#schoolModal" onclick="prepareAddModal()">
            <i class="bi bi-plus-circle me-2"></i>Add New School
        </button>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Filters & Search</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search" placeholder="Search by School Name, Admin Name, or Email..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" <?php if ($status_filter == 'active') echo 'selected'; ?>>Active</option>
                        <option value="suspended" <?php if ($status_filter == 'suspended') echo 'selected'; ?>>Suspended</option>
                        <option value="pending_payment" <?php if ($status_filter == 'pending_payment') echo 'selected'; ?>>Pending Payment</option>
                        <option value="closed" <?php if ($status_filter == 'closed') echo 'selected'; ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="/views/super_admin/school_management.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Registered Schools List (<?php echo count($schools); ?>)</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>School Name</th>
                            <th>Admin Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schools)): ?>
                            <tr><td colspan="4" class="text-center">No schools found. Add one to get started!</td></tr>
                        <?php else: ?>
                            <?php foreach ($schools as $school): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($school['name']); ?></td>
                                <td><?php echo htmlspecialchars($school['admin_email'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php
                                    $status_class = 'secondary';
                                    if ($school['status'] == 'active') $status_class = 'success';
                                    elseif (in_array($school['status'], ['suspended', 'closed'])) $status_class = 'danger';
                                    elseif ($school['status'] == 'pending_payment') $status_class = 'warning';
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $school['status'])); ?></span>
                                </td>
                                <td>
                                    <?php if ($school['status'] != 'closed'): ?>
                                        <a href="/controllers/auth_controller.php?action=login_as&user_id=<?php echo $school['user_id']; ?>" class="btn btn-sm btn-primary" title="Login as this Admin"><i class="bi bi-box-arrow-in-right"></i> Login As</a>
                                        <button class="btn btn-sm btn-info" onclick='prepareEditModal(<?php echo json_encode($school); ?>)' data-bs-toggle="modal" data-bs-target="#schoolModal" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                                    <?php endif; ?>

                                    <?php if ($school['status'] == 'active'): ?>
                                        <a href="/controllers/school_controller.php?action=toggle_status&school_id=<?php echo $school['id']; ?>" class="btn btn-sm btn-warning" title="Suspend" onclick="return confirm('Are you sure you want to suspend this school?');"><i class="bi bi-shield-slash-fill"></i></a>
                                    <?php elseif (in_array($school['status'], ['suspended', 'closed'])): ?>
                                        <a href="/controllers/school_controller.php?action=toggle_status&school_id=<?php echo $school['id']; ?>" class="btn btn-sm btn-success" title="Activate/Restore" onclick="return confirm('Are you sure you want to activate/restore this school?');"><i class="bi bi-shield-check"></i></a>
                                    <?php endif; ?>

                                    <?php if ($school['status'] != 'closed'): ?>
                                        <button class="btn btn-sm btn-success" onclick='prepareCreditModal(<?php echo json_encode($school); ?>)' data-bs-toggle="modal" data-bs-target="#creditModal" title="Add SMS Credits"><i class="bi bi-coin"></i></button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-danger" onclick='prepareDeleteModal(<?php echo json_encode($school); ?>)' data-bs-toggle="modal" data-bs-target="#deleteModal" title="Delete School"><i class="bi bi-trash-fill"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit School Modal -->
<div class="modal fade" id="schoolModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="schoolForm" action="/controllers/school_controller.php" method="POST">
        <div class="modal-header"><h5 class="modal-title" id="schoolModalLabel">Add New School</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="action" id="formAction" value="create"><input type="hidden" name="school_id" id="schoolId"><input type="hidden" name="user_id" id="userId">
            <div class="mb-3"><label for="schoolName" class="form-label">School Name</label><input type="text" class="form-control" id="schoolName" name="school_name" required></div>

            <div class="mb-3">
                <label for="packageId" class="form-label">Subscription Package</label>
                <select class="form-select" id="packageId" name="package_id" required>
                    <option value="">Select a Package...</option>
                    <?php foreach ($packages as $package): ?>
                        <option value="<?php echo $package['id']; ?>"><?php echo htmlspecialchars($package['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <hr><h6 class="text-muted">School Administrator Account</h6><p class="text-muted small">Create or update the primary administrator account for this school.</p>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="adminName" class="form-label">Admin Full Name</label><input type="text" class="form-control" id="adminName" name="admin_name" required></div>
                <div class="col-md-6 mb-3"><label for="adminEmail" class="form-label">Admin Email</label><input type="email" class="form-control" id="adminEmail" name="admin_email" required></div>
            </div>
            <div class="mb-3"><label for="adminPassword" class="form-label">Password</label><input type="password" class="form-control" id="adminPassword" name="admin_password"><div id="passwordHelp" class="form-text">Required for new schools. Leave blank if not changing.</div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary" id="saveButton">Save School</button></div>
    </form>
</div></div></div>

<!-- Add Credits Modal -->
<div class="modal fade" id="creditModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="creditForm" action="/controllers/school_controller.php" method="POST">
        <div class="modal-header"><h5 class="modal-title" id="creditModalLabel">Add SMS Credits</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="action" value="add_credits">
            <input type="hidden" name="school_id" id="creditSchoolId">
            <p>Manually add SMS credits to <strong id="creditSchoolName"></strong>.</p>
            <div class="mb-3">
                <label for="creditAmount" class="form-label">Number of Credits to Add</label>
                <input type="number" class="form-control" id="creditAmount" name="credit_amount" required min="1">
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Credits</button></div>
    </form>
</div></div></div>

<script>
function prepareAddModal() {
    document.getElementById('schoolForm').reset();
    document.getElementById('schoolModalLabel').innerText = 'Add New School';
    document.getElementById('formAction').value = 'create';
    document.getElementById('schoolId').value = '';
    document.getElementById('userId').value = '';
    document.getElementById('adminPassword').required = true;
}
function prepareEditModal(school) {
    document.getElementById('schoolForm').reset();
    document.getElementById('schoolModalLabel').innerText = 'Edit School: ' + school.name;
    document.getElementById('formAction').value = 'update';
    document.getElementById('schoolId').value = school.id;
    document.getElementById('userId').value = school.user_id;
    document.getElementById('schoolName').value = school.name;
    document.getElementById('adminName').value = school.admin_name;
    document.getElementById('adminEmail').value = school.admin_email;
    document.getElementById('adminPassword').required = false;
    document.getElementById('passwordHelp').innerText = 'Leave blank to keep current password.';
}
function prepareCreditModal(school) {
    document.getElementById('creditForm').reset();
    document.getElementById('creditSchoolId').value = school.id;
    document.getElementById('creditSchoolName').innerText = school.name;
}
function prepareDeleteModal(school) {
    document.getElementById('deleteSchoolName').innerText = school.name;
    document.getElementById('deleteSchoolId').value = school.id;
}
</script>

<!-- Delete School Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteForm" action="/controllers/school_controller.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_school">
                    <input type="hidden" name="school_id" id="deleteSchoolId">
                    <p>Are you sure you want to permanently delete <strong id="deleteSchoolName"></strong>?</p>
                    <p class="text-danger"><strong>Warning:</strong> This action is irreversible and will delete all associated data, including teachers, students, subjects, and report cards.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete School</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>