<?php
$page_title = "Teacher Management";
include 'partials/header.php'; // Includes auth, db, etc.

$school_id = $_SESSION['school_id'];

// Fetch teachers for the current school
try {
    $search_term = $_GET['search'] ?? '';

    $sql = "SELECT id, full_name, email, status FROM users WHERE school_id = :school_id AND role = 'teacher'";
    $params = [':school_id' => $school_id];

    if (!empty($search_term)) {
        $sql .= " AND (full_name LIKE :search OR email LIKE :search)";
        $params[':search'] = '%' . $search_term . '%';
    }

    $sql .= " ORDER BY full_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $teachers = [];
    echo '<div class="alert alert-danger">Could not fetch teacher data. Error: ' . $e->getMessage() . '</div>';
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
        <h1 class="h3 mb-0 text-gray-800">Teacher Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#teacherModal" onclick="prepareAddModal()">
            <i class="bi bi-person-plus-fill me-2"></i>Add New Teacher
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
            <h6 class="m-0 fw-bold text-primary">Search Teachers</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-center">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="search" placeholder="Search by Name or Email..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="/views/school_admin/teacher_management.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Teacher Account List (<?php echo count($teachers); ?> found)</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead><tr><th>Full Name</th><th>Email Address</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($teachers)): ?>
                            <tr><td colspan="4" class="text-center">No teacher accounts found. Add one to get started.</td></tr>
                        <?php else: ?>
                            <?php foreach ($teachers as $teacher): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($teacher['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                <td><span class="badge bg-<?php echo $teacher['status'] == 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($teacher['status']); ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick='prepareEditModal(<?php echo json_encode($teacher); ?>)' data-bs-toggle="modal" data-bs-target="#teacherModal" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                                    <button class="btn btn-sm btn-secondary" title="Reset Password (Soon)" disabled><i class="bi bi-key-fill"></i></button>
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

<!-- Add/Edit Teacher Modal -->
<div class="modal fade" id="teacherModal" tabindex="-1" aria-labelledby="teacherModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="teacherForm" action="/controllers/teacher_controller.php" method="POST">
                <div class="modal-header"><h5 class="modal-title" id="teacherModalLabel">Add New Teacher</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="user_id" id="userIdHidden">

                    <p id="modalDescription" class="text-muted">Create a new teacher account. They can use these credentials to log in.</p>
                    <div class="mb-3"><label for="teacherName" class="form-label">Full Name</label><input type="text" class="form-control" id="teacherName" name="full_name" required></div>
                    <div class="mb-3"><label for="teacherEmail" class="form-label">Email Address</label><input type="email" class="form-control" id="teacherEmail" name="email" required></div>
                    <div class="mb-3"><label for="teacherPassword" class="form-label">Password</label><input type="password" class="form-control" id="teacherPassword" name="password"><div id="passwordHelp" class="form-text">Required for new teachers. Leave blank if not changing.</div></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Teacher</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function prepareAddModal() {
    document.getElementById('teacherForm').reset();
    document.getElementById('teacherModalLabel').innerText = 'Add New Teacher';
    document.getElementById('formAction').value = 'create';
    document.getElementById('teacherPassword').required = true;
    document.getElementById('passwordHelp').innerText = 'Required for new teachers.';
    document.getElementById('modalDescription').style.display = 'block';
}

function prepareEditModal(teacher) {
    document.getElementById('teacherForm').reset();
    document.getElementById('teacherModalLabel').innerText = 'Edit Teacher: ' + teacher.full_name;
    document.getElementById('formAction').value = 'update'; // This action needs to be created in the controller
    document.getElementById('userIdHidden').value = teacher.id;
    document.getElementById('teacherName').value = teacher.full_name;
    document.getElementById('teacherEmail').value = teacher.email;
    document.getElementById('teacherPassword').required = false;
    document.getElementById('passwordHelp').innerText = 'Leave blank to keep current password.';
    document.getElementById('modalDescription').style.display = 'none';
}
</script>

<?php
include 'partials/footer.php';
?>