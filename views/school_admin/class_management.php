<?php
$page_title = "Class & Subject Management";
include 'partials/header.php'; // Includes auth, db, etc.

$school_id = $_SESSION['school_id'];

// Get the selected academic period, default to the most recent one if not set
$selected_period = $_GET['period'] ?? '';

// Fetch all distinct academic periods for the dropdown
try {
    $stmt_periods = $pdo->prepare("SELECT DISTINCT academic_period FROM classes WHERE school_id = :school_id ORDER BY academic_period DESC");
    $stmt_periods->execute(['school_id' => $school_id]);
    $academic_periods = $stmt_periods->fetchAll(PDO::FETCH_COLUMN);

    if (!$selected_period && !empty($academic_periods)) {
        $selected_period = $academic_periods[0];
    }
} catch (PDOException $e) {
    $academic_periods = [];
    echo '<div class="alert alert-danger">Could not fetch academic periods.</div>';
}

// Fetch classes for the selected academic period
try {
    $search_term = $_GET['search'] ?? '';

    $sql = "SELECT c.id, c.class_name, u.full_name AS teacher_name,
            (SELECT COUNT(*) FROM enrollments e WHERE e.class_id = c.id) as student_count
            FROM classes c
            LEFT JOIN teacher_assignments ta ON c.id = ta.class_id
            LEFT JOIN users u ON ta.teacher_id = u.id
            WHERE c.school_id = :school_id AND c.academic_period = :period";

    $params = [':school_id' => $school_id, ':period' => $selected_period];

    if (!empty($search_term)) {
        $sql .= " AND c.class_name LIKE :search";
        $params[':search'] = '%' . $search_term . '%';
    }

    $sql .= " ORDER BY c.class_name ASC";

    $stmt_classes = $pdo->prepare($sql);
    $stmt_classes->execute($params);
    $classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $classes = [];
    echo '<div class="alert alert-danger">Could not fetch class data. Error: ' . $e->getMessage() . '</div>';
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
        <h1 class="h3 mb-0 text-gray-800">Class & Subject Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#classModal"><i class="bi bi-plus-circle me-2"></i>Create New Class/Subject</button>
    </div>

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
                <input type="hidden" name="period" value="<?php echo htmlspecialchars($selected_period); ?>">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search" placeholder="Search by Class/Subject Name..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="/views/school_admin/class_management.php?period=<?php echo urlencode($selected_period); ?>" class="btn btn-secondary">Reset</a>
                </div>
                <div class="col-md-4 text-md-end">
                    <?php if (!empty($academic_periods)): ?>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Change Period</button>
                        <ul class="dropdown-menu">
                            <?php foreach ($academic_periods as $period): ?>
                                <li><a class="dropdown-item" href="?period=<?php echo urlencode($period); ?>"><?php echo $period; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Class List for <?php echo htmlspecialchars($selected_period ?: 'N/A'); ?> (<?php echo count($classes); ?> found)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr><th>Class/Subject Name</th><th>Assigned Teacher</th><th>Enrolled Students</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($classes)): ?>
                            <tr><td colspan="4" class="text-center">No classes found for this period. Create one to get started.</td></tr>
                        <?php else: ?>
                            <?php foreach ($classes as $class): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                <td><?php echo htmlspecialchars($class['teacher_name'] ?? 'Not Assigned'); ?></td>
                                <td><?php echo htmlspecialchars($class['student_count']); ?></td>
                                <td>
                                    <a href="/views/school_admin/manage_assignments.php?class_id=<?php echo $class['id']; ?>" class="btn btn-sm btn-info" title="Manage Assignments"><i class="bi bi-people-fill"></i></a>
                                    <button class="btn btn-sm btn-secondary" title="Edit" disabled><i class="bi bi-pencil-fill"></i></button>
                                    <button class="btn btn-sm btn-danger" title="Delete" disabled><i class="bi bi-trash-fill"></i></button>
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

<!-- Create Class Modal -->
<div class="modal fade" id="classModal" tabindex="-1" aria-labelledby="classModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/controllers/class_controller.php" method="POST">
                <div class="modal-header"><h5 class="modal-title" id="classModalLabel">Create New Class/Subject</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3"><label for="className" class="form-label">Class/Subject Name</label><input type="text" class="form-control" name="class_name" placeholder="e.g., Mathematics - Grade 10" required></div>
                    <div class="mb-3"><label for="academicPeriod" class="form-label">Academic Period</label><input type="text" class="form-control" name="academic_period" value="<?php echo htmlspecialchars($selected_period); ?>" placeholder="e.g., Term 1 2024" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Create Class</button></div>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>