<?php
$page_title = "My Classes";
include 'partials/header.php'; // Includes auth, db, etc.

$teacher_id = $_SESSION['user_id'];
$school_id = $_SESSION['school_id'];

// --- Fetch all necessary data ---
try {
    // Get the most recent academic period as the current one for the call to action
    $stmt_period = $pdo->prepare("SELECT academic_period FROM classes WHERE school_id = :school_id ORDER BY academic_period DESC LIMIT 1");
    $stmt_period->execute(['school_id' => $school_id]);
    $current_period = $stmt_period->fetchColumn() ?: "the current term";

    // Fetch classes and calculate progress for each
    $stmt_classes = $pdo->prepare(
        "SELECT c.id, c.class_name, c.academic_period,
            (SELECT COUNT(e.id) FROM enrollments e WHERE e.class_id = c.id) as total_students,
            (SELECT COUNT(g.id) FROM grades g JOIN enrollments e ON g.enrollment_id = e.id WHERE e.class_id = c.id) as graded_students
         FROM classes c JOIN teacher_assignments ta ON c.id = ta.class_id
         WHERE ta.teacher_id = :teacher_id ORDER BY c.academic_period DESC, c.class_name ASC"
    );
    $stmt_classes->execute(['teacher_id' => $teacher_id]);
    $assigned_classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);

    // Fetch announcements (platform-wide and school-specific for this teacher's school)
    $stmt_announcements = $pdo->prepare(
        "SELECT * FROM announcements
         WHERE school_id IS NULL OR school_id = :school_id
         ORDER BY created_at DESC LIMIT 5"
    );
    $stmt_announcements->execute(['school_id' => $school_id]);
    $announcements = $stmt_announcements->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $assigned_classes = []; $announcements = [];
    $current_period = "Error";
    echo '<div class="alert alert-danger">Could not fetch dashboard data. Error: ' . $e->getMessage() . '</div>';
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <!-- Call to Action -->
            <div class="alert alert-primary text-center" role="alert">
                <h4 class="alert-heading">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h4>
                <p>Your current task is to enter results for **<?php echo htmlspecialchars($current_period); ?>**. Please ensure all grades are submitted before the deadline.</p>
            </div>
            <h3 class="h4 mb-3 text-gray-800">My Assigned Classes</h3>
            <?php if (empty($assigned_classes)): ?>
                <div class="card"><div class="card-body text-center">
                    <p class="lead">You have not been assigned to any classes yet.</p>
                    <p class="text-muted">Please contact your school administrator to get assigned to your classes.</p>
                </div></div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($assigned_classes as $class): ?>
                        <?php $progress = ($class['total_students'] > 0) ? round(($class['graded_students'] / $class['total_students']) * 100) : 0; ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100 shadow-sm"><div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($class['class_name']); ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($class['academic_period']); ?></h6>
                                <div class="d-flex justify-content-between"><small>Grade Entry Progress</small><small><?php echo $class['graded_students']; ?> / <?php echo $class['total_students']; ?> Submitted</small></div>
                                <div class="progress mb-3" style="height: 10px;"><div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%;"></div></div>
                                <a href="/views/teacher/grade_entry.php?class_id=<?php echo $class['id']; ?>" class="btn btn-primary w-100"><i class="bi bi-pencil-square me-2"></i>Enter Grades</a>
                            </div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Recent Announcements</h6></div>
                <div class="card-body">
                    <?php if (empty($announcements)): ?>
                        <p class="text-center">No recent announcements.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($announcements as $announcement): ?>
                                <li class="list-group-item">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($announcement['created_at'])); ?></small>
                                    <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>