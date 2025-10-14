<?php
// The header must be included first to load all core functionalities, including i18n.
include 'partials/header.php';

// Now that the language is loaded from header.php, we can set the translated page title.
$page_title = __('dashboard');

$school_id = $_SESSION['school_id'];

// --- Fetch dynamic data for the new dashboard ---
try {
    // Get the most recent academic period
    $stmt_period = $pdo->prepare("SELECT academic_period FROM classes WHERE school_id = :school_id ORDER BY academic_period DESC LIMIT 1");
    $stmt_period->execute(['school_id' => $school_id]);
    $current_period = $stmt_period->fetchColumn() ?: "Not Set";

    // --- Report Card Progress Calculation ---
    $total_enrollments = 0; $graded_enrollments = 0;
    if ($current_period != "Not Set") {
        $stmt_total = $pdo->prepare("SELECT COUNT(e.id) FROM enrollments e JOIN classes c ON e.class_id = c.id WHERE c.school_id = :school_id AND c.academic_period = :period");
        $stmt_total->execute(['school_id' => $school_id, 'period' => $current_period]);
        $total_enrollments = $stmt_total->fetchColumn();

        $stmt_graded = $pdo->prepare("SELECT COUNT(g.id) FROM grades g JOIN enrollments e ON g.enrollment_id = e.id JOIN classes c ON e.class_id = c.id WHERE c.school_id = :school_id AND c.academic_period = :period");
        $stmt_graded->execute(['school_id' => $school_id, 'period' => $current_period]);
        $graded_enrollments = $stmt_graded->fetchColumn();
    }
    $progress_percentage = ($total_enrollments > 0) ? round(($graded_enrollments / $total_enrollments) * 100) : 0;

    // --- Incomplete Submissions (Real Data) ---
    $stmt_incomplete = $pdo->prepare(
       "SELECT u.full_name, c.class_name,
               (SELECT COUNT(e.id) FROM enrollments e WHERE e.class_id = c.id) as total_students,
               (SELECT COUNT(g.id) FROM grades g JOIN enrollments e ON g.enrollment_id = e.id WHERE e.class_id = c.id) as graded_students
        FROM teacher_assignments ta
        JOIN users u ON ta.teacher_id = u.id
        JOIN classes c ON ta.class_id = c.id
        WHERE c.school_id = :school_id AND c.academic_period = :period
        HAVING total_students > graded_students"
    );
    $stmt_incomplete->execute(['school_id' => $school_id, 'period' => $current_period]);
    $incomplete_teachers = $stmt_incomplete->fetchAll(PDO::FETCH_ASSOC);

    // --- Recently Added Students ---
    $stmt_recent_students = $pdo->prepare("SELECT full_name, class, created_at FROM students WHERE school_id = :school_id ORDER BY created_at DESC LIMIT 5");
    $stmt_recent_students->execute(['school_id' => $school_id]);
    $recent_students = $stmt_recent_students->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Could not fetch dashboard data. Error: ' . $e->getMessage() . '</div>';
    $current_period = "Error"; $progress_percentage = 0; $incomplete_teachers = []; $recent_students = [];
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo __('dashboard'); ?></h1>
        <div>
            <a href="/views/school_admin/student_management.php" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle me-1"></i>Add Student</a>
            <a href="/views/school_admin/teacher_management.php" class="btn btn-sm btn-success"><i class="bi bi-person-plus-fill me-1"></i>Add Teacher</a>
        </div>
    </div>

    <!-- Report Card Progress -->
    <div class="card shadow mb-4"><div class="card-body"><div class="row align-items-center">
        <div class="col-md-12">
            <h5 class="card-title">Report Card Progress: <?php echo htmlspecialchars($current_period); ?></h5>
            <div class="progress" style="height: 25px;"><div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: <?php echo $progress_percentage; ?>%;" aria-valuenow="<?php echo $progress_percentage; ?>"><span class="fw-bold"><?php echo $progress_percentage; ?>% Complete</span></div></div>
            <small class="text-muted"><?php echo number_format($graded_enrollments); ?> of <?php echo number_format($total_enrollments); ?> total grade entries submitted.</small>
        </div>
    </div></div></div>

    <!-- Actionable Information & Alerts -->
    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Incomplete Submissions</h6></div>
            <div class="card-body">
                <p class="small text-muted">The following teachers have not yet completed their grade entries for <?php echo htmlspecialchars($current_period); ?>.</p>
                <ul class="list-group list-group-flush">
                    <?php if (empty($incomplete_teachers)): ?>
                        <li class="list-group-item text-success"><i class="bi bi-check-circle-fill me-2"></i>All teachers have completed their submissions!</li>
                    <?php else: ?>
                        <?php foreach ($incomplete_teachers as $teacher): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div><strong><?php echo htmlspecialchars($teacher['full_name']); ?></strong><small class="d-block text-muted"><?php echo htmlspecialchars($teacher['class_name']); ?></small></div>
                                <span class="badge bg-warning text-dark"><?php echo $teacher['graded_students']; ?> / <?php echo $teacher['total_students']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Recently Added Students</h6></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php if (empty($recent_students)): ?>
                        <li class="list-group-item">No students have been added recently.</li>
                    <?php else: ?>
                        <?php foreach ($recent_students as $student): ?>
                            <li class="list-group-item">
                                <strong><?php echo htmlspecialchars($student['full_name']); ?></strong>
                                <small class="d-block text-muted">Added to <?php echo htmlspecialchars($student['class']); ?> on <?php echo date('M d', strtotime($student['created_at'])); ?></small>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div></div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>