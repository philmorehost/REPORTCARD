<?php
$page_title = "Manage Assignments";
include 'partials/header.php'; // Includes auth, db, etc.

$class_id = $_GET['class_id'] ?? 0;
if (!$class_id) {
    echo '<div class="alert alert-danger">No class selected. Please go back and select a class to manage.</div>';
    include 'partials/footer.php';
    exit;
}

$school_id = $_SESSION['school_id'];

// Check for session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

// --- Fetch all necessary data from the database ---
try {
    $stmt_class = $pdo->prepare("SELECT class_name, academic_period FROM classes WHERE id = :class_id AND school_id = :school_id");
    $stmt_class->execute(['class_id' => $class_id, 'school_id' => $school_id]);
    $class_info = $stmt_class->fetch(PDO::FETCH_ASSOC);

    if (!$class_info) { throw new Exception("Class not found or you do not have permission to access it."); }

    $stmt_assigned_teacher = $pdo->prepare("SELECT u.id, u.full_name FROM users u JOIN teacher_assignments ta ON u.id = ta.teacher_id WHERE ta.class_id = :class_id");
    $stmt_assigned_teacher->execute(['class_id' => $class_id]);
    $assigned_teacher = $stmt_assigned_teacher->fetch(PDO::FETCH_ASSOC);

    $stmt_all_teachers = $pdo->prepare("SELECT id, full_name FROM users WHERE school_id = :school_id AND role = 'teacher'");
    $stmt_all_teachers->execute(['school_id' => $school_id]);
    $available_teachers = $stmt_all_teachers->fetchAll(PDO::FETCH_ASSOC);

    $stmt_enrolled = $pdo->prepare("SELECT s.id, s.full_name FROM students s JOIN enrollments e ON s.id = e.student_id WHERE e.class_id = :class_id ORDER BY s.full_name ASC");
    $stmt_enrolled->execute(['class_id' => $class_id]);
    $enrolled_students = $stmt_enrolled->fetchAll(PDO::FETCH_ASSOC);
    $enrolled_student_ids = array_column($enrolled_students, 'id');

    $sql_available_students = "SELECT id, full_name FROM students WHERE school_id = ? AND status = 'active'";
    $params = [$school_id];
    if (!empty($enrolled_student_ids)) {
        $placeholders = implode(',', array_fill(0, count($enrolled_student_ids), '?'));
        $sql_available_students .= " AND id NOT IN ($placeholders)";
        $params = array_merge($params, $enrolled_student_ids);
    }
    $sql_available_students .= " ORDER BY full_name ASC";

    $stmt_available = $pdo->prepare($sql_available_students);
    $stmt_available->execute($params);
    $available_students = $stmt_available->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error fetching data: ' . $e->getMessage() . '</div>';
    include 'partials/footer.php';
    exit;
}
?>

<div class="container-fluid">
    <a href="/views/school_admin/class_management.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Class List</a>
    <h1 class="h3 mb-2 text-gray-800">Manage Assignments</h1>
    <p class="text-muted"><strong>Class:</strong> <?php echo htmlspecialchars($class_info['class_name']); ?> | <strong>Period:</strong> <?php echo htmlspecialchars($class_info['academic_period']); ?></p>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Teacher Assignment -->
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Teacher Assignment</h6></div>
                <div class="card-body">
                    <?php if ($assigned_teacher): ?>
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <span>Assigned: <strong><?php echo htmlspecialchars($assigned_teacher['full_name']); ?></strong></span>
                            <a href="/controllers/class_controller.php?action=unassign_teacher&class_id=<?php echo $class_id; ?>&teacher_id=<?php echo $assigned_teacher['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to unassign this teacher?');">Unassign</a>
                        </div>
                    <?php else: ?>
                        <form action="/controllers/class_controller.php" method="POST">
                            <input type="hidden" name="action" value="assign_teacher">
                            <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                            <label for="teacherSelect" class="form-label">Select a teacher to assign to this class:</label>
                            <select class="form-select" id="teacherSelect" name="teacher_id" required>
                                <option selected disabled value="">Choose a teacher...</option>
                                <?php foreach ($available_teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary mt-3">Assign Teacher</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Student Enrollment -->
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Student Enrollment</h6></div>
                <div class="card-body">
                    <form action="/controllers/class_controller.php" method="POST" id="enrollmentForm">
                        <input type="hidden" name="action" value="update_enrollments">
                        <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                        <p>Select students to enroll in this class. Use Ctrl/Cmd to select multiple students.</p>
                        <div class="row">
                            <div class="col-5"><label class="form-label">Available Students</label><select multiple class="form-control" id="available-students" style="height: 200px;"><?php foreach($available_students as $student): ?><option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['full_name']); ?></option><?php endforeach; ?></select></div>
                            <div class="col-2 text-center align-self-center"><button type="button" id="enroll-btn" class="btn btn-primary mb-2 w-100">&gt;&gt;</button><button type="button" id="unenroll-btn" class="btn btn-danger w-100">&lt;&lt;</button></div>
                            <div class="col-5"><label class="form-label">Enrolled Students</label><select multiple class="form-control" name="enrolled_students[]" id="enrolled-students" style="height: 200px;"><?php foreach($enrolled_students as $student): ?><option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['full_name']); ?></option><?php endforeach; ?></select></div>
                        </div>
                        <button type="submit" class="btn btn-success mt-3">Save Enrollments</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('enroll-btn').addEventListener('click', () => moveOptions('available-students', 'enrolled-students'));
document.getElementById('unenroll-btn').addEventListener('click', () => moveOptions('enrolled-students', 'available-students'));

function moveOptions(fromId, toId) {
    const fromList = document.getElementById(fromId);
    const toList = document.getElementById(toId);
    Array.from(fromList.selectedOptions).forEach(option => toList.appendChild(option));
}

document.getElementById('enrollmentForm').addEventListener('submit', function() {
    const enrolledList = document.getElementById('enrolled-students');
    Array.from(enrolledList.options).forEach(option => option.selected = true);
});
</script>

<?php include 'partials/footer.php'; ?>