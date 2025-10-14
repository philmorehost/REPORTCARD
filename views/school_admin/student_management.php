<?php
$page_title = "Student Management";
include 'partials/header.php'; // Includes auth, db, etc.

$school_id = $_SESSION['school_id'];

// Fetch school package info and current student count
try {
    // Get school's package details and total slots
    $stmt_school = $pdo->prepare(
        "SELECT s.student_slots, p.name as package_name
         FROM schools s
         LEFT JOIN packages p ON s.package_id = p.id
         WHERE s.id = :school_id"
    );
    $stmt_school->execute(['school_id' => $school_id]);
    $school_info = $stmt_school->fetch(PDO::FETCH_ASSOC);

    // Get current student count
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM students WHERE school_id = :school_id");
    $stmt_count->execute(['school_id' => $school_id]);
    $current_students = $stmt_count->fetchColumn();

    // Fetch unique classes for the filter dropdown
    $stmt_classes = $pdo->prepare("SELECT DISTINCT class FROM students WHERE school_id = :school_id ORDER BY class ASC");
    $stmt_classes->execute(['school_id' => $school_id]);
    $classes = $stmt_classes->fetchAll(PDO::FETCH_COLUMN);

    // Handle search and filtering
    $search_term = $_GET['search'] ?? '';
    $class_filter = $_GET['class_filter'] ?? '';
    $status_filter = $_GET['status_filter'] ?? '';

    $sql = "SELECT * FROM students WHERE school_id = :school_id";
    $params = [':school_id' => $school_id];

    if (!empty($search_term)) {
        $sql .= " AND (full_name LIKE :search OR student_id_number LIKE :search)";
        $params[':search'] = '%' . $search_term . '%';
    }
    if (!empty($class_filter)) {
        $sql .= " AND class = :class";
        $params[':class'] = $class_filter;
    }
    if (!empty($status_filter)) {
        $sql .= " AND status = :status";
        $params[':status'] = $status_filter;
    }

    $sql .= " ORDER BY full_name ASC";

    $stmt_students = $pdo->prepare($sql);
    $stmt_students->execute($params);
    $students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $students = [];
    $school_info = null;
    $current_students = 0;
    echo '<div class="alert alert-danger">Could not fetch page data. Error: ' . $e->getMessage() . '</div>';
}

// Determine if the student limit has been reached for Freemium plans
$limit_reached = false;
$student_limit = 0;
if ($school_info && strtolower($school_info['package_name']) === 'freemium') {
    $student_limit = (int) $school_info['student_slots'];
    if ($student_limit > 0 && $current_students >= $student_limit) {
        $limit_reached = true;
    }
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
        <h1 class="h3 mb-0 text-gray-800">Student Management</h1>
        <div>
            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal" <?php if ($limit_reached) echo 'disabled'; ?>><i class="bi bi-upload me-2"></i>Bulk Import</button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#studentModal" onclick="prepareAddModal()" <?php if ($limit_reached) echo 'disabled'; ?>>
                <i class="bi bi-plus-circle me-2"></i>Add New Student
            </button>
        </div>
    </div>

    <?php if ($limit_reached): ?>
        <div class="alert alert-warning">
            <strong>Limit Reached!</strong> You have reached the maximum of <?php echo $student_limit; ?> students for your Freemium plan.
            Please <a href="billing.php">upgrade to Premium</a> to add more students.
        </div>
    <?php endif; ?>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Filters & Search</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search by Name or ID..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-3">
                    <select name="class_filter" class="form-select">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo htmlspecialchars($class); ?>" <?php if ($class_filter == $class) echo 'selected'; ?>><?php echo htmlspecialchars($class); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status_filter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" <?php if ($status_filter == 'active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if ($status_filter == 'inactive') echo 'selected'; ?>>Inactive</option>
                        <option value="graduated" <?php if ($status_filter == 'graduated') echo 'selected'; ?>>Graduated</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="/views/school_admin/student_management.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Student List (<?php echo count($students); ?> found)</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead><tr><th>Student Name</th><th>Student ID</th><th>Class</th><th>Parent Contact</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="6" class="text-center">No students found. Add one to get started.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['student_id_number']); ?></td>
                                <td><?php echo htmlspecialchars($student['class']); ?></td>
                                <td>
                                    <?php if (!empty($student['parent_email'])): ?>
                                        <i class="bi bi-envelope-fill text-muted"></i> <?php echo htmlspecialchars($student['parent_email']); ?><br>
                                    <?php endif; ?>
                                    <?php if (!empty($student['parent_phone_number'])): ?>
                                        <i class="bi bi-telephone-fill text-success"></i> <?php echo htmlspecialchars($student['parent_phone_number']); ?>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-<?php echo $student['status'] == 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($student['status']); ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick='prepareEditModal(<?php echo json_encode($student); ?>)' data-bs-toggle="modal" data-bs-target="#studentModal" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                                    <a href="/controllers/student_controller.php?action=delete&id=<?php echo $student['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student? This action cannot be undone.');" title="Delete"><i class="bi bi-trash-fill"></i></a>
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

<!-- Add/Edit Student Modal -->
<div class="modal fade" id="studentModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="studentForm" action="/controllers/student_controller.php" method="POST">
        <div class="modal-header"><h5 class="modal-title" id="studentModalLabel">Add New Student</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="action" id="formAction" value="create"><input type="hidden" name="student_id" id="studentIdHidden">
            <h6 class="text-muted">Student Details</h6>
            <div class="mb-3"><label for="fullName" class="form-label">Full Name</label><input type="text" class="form-control" id="fullName" name="full_name" required></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="studentIdNumber" class="form-label">Student ID Number (Optional)</label><input type="text" class="form-control" id="studentIdNumber" name="student_id_number"></div>
                <div class="col-md-6 mb-3"><label for="studentClass" class="form-label">Class / Grade</label><input type="text" class="form-control" id="studentClass" name="class" required></div>
            </div>
            <hr>
            <h6 class="text-muted">Parent/Guardian Details</h6>
            <div class="mb-3"><label for="parentName" class="form-label">Parent's Full Name</label><input type="text" class="form-control" id="parentName" name="parent_name"></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="parentEmail" class="form-label">Parent's Email Address</label><input type="email" class="form-control" id="parentEmail" name="parent_email"></div>
                <div class="col-md-6 mb-3"><label for="parentPhoneNumber" class="form-label">Parent's Phone Number</label><input type="tel" class="form-control" id="parentPhoneNumber" name="parent_phone_number" placeholder="Include country code, e.g., +1..."></div>
            </div>
            <div class="mb-3" id="statusGroup" style="display: none;"><label for="studentStatus" class="form-label">Status</label><select class="form-select" id="studentStatus" name="status"><option value="active">Active</option><option value="inactive">Inactive</option><option value="graduated">Graduated</option></select></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Student</button></div>
    </form>
</div></div></div>

<!-- Bulk Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form action="/controllers/student_controller.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header"><h5 class="modal-title" id="importModalLabel">Bulk Import Students</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="action" value="bulk_import">
            <p>Upload a CSV file with student data. The file must follow the specified format.</p>
            <a href="/assets/templates/student_import_template.csv" class="btn btn-sm btn-outline-primary mb-3" download><i class="bi bi-download me-2"></i>Download CSV Template</a>
            <div class="mb-3"><label for="csv_file" class="form-label">Select CSV File</label><input type="file" class="form-control" name="csv_file" id="csv_file" accept=".csv" required></div>
            <div class="alert alert-warning"><strong>Note:</strong> The number of students you can import is limited by your available student slots.</div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Import Students</button></div>
    </form>
</div></div></div>

<script>
function prepareAddModal() {
    document.getElementById('studentForm').reset();
    document.getElementById('studentModalLabel').innerText = 'Add New Student';
    document.getElementById('formAction').value = 'create';
    document.getElementById('statusGroup').style.display = 'none';
}
function prepareEditModal(student) {
    document.getElementById('studentForm').reset();
    document.getElementById('studentModalLabel').innerText = 'Edit Student: ' + student.full_name;
    document.getElementById('formAction').value = 'update';
    document.getElementById('studentIdHidden').value = student.id;
    document.getElementById('fullName').value = student.full_name;
    document.getElementById('studentIdNumber').value = student.student_id_number;
    document.getElementById('studentClass').value = student.class;
    document.getElementById('parentName').value = student.parent_name;
    document.getElementById('parentEmail').value = student.parent_email;
    document.getElementById('parentPhoneNumber').value = student.parent_phone_number;
    document.getElementById('studentStatus').value = student.status;
    document.getElementById('statusGroup').style.display = 'block';
}
</script>

<?php include 'partials/footer.php'; ?>