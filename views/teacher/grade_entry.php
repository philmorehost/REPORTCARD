<?php
$page_title = "Grade Entry";
include 'partials/header.php'; // Includes auth, db, etc.

$class_id = $_GET['class_id'] ?? 0;
$school_id = $_SESSION['school_id'];
$teacher_id = $_SESSION['user_id'];

if (!$class_id) {
    echo '<div class="alert alert-danger">No class selected. Please go back to your dashboard.</div>';
    include 'partials/footer.php';
    exit;
}

// --- Fetch all necessary data ---
try {
    // 1. Get class details & verify teacher is assigned to it
    $stmt_class = $pdo->prepare(
        "SELECT c.class_name, c.academic_period FROM classes c
         JOIN teacher_assignments ta ON c.id = ta.class_id
         WHERE c.id = :class_id AND ta.teacher_id = :teacher_id AND c.school_id = :school_id"
    );
    $stmt_class->execute(['class_id' => $class_id, 'teacher_id' => $teacher_id, 'school_id' => $school_id]);
    $class_info = $stmt_class->fetch(PDO::FETCH_ASSOC);

    if (!$class_info) { throw new Exception("Class not found or you are not assigned to it."); }

    // 2. Get the school's report card structure
    $stmt_settings = $pdo->prepare("SELECT columns FROM report_card_settings WHERE school_id = :school_id");
    $stmt_settings->execute(['school_id' => $school_id]);
    $settings = $stmt_settings->fetch(PDO::FETCH_ASSOC);
    $report_columns = $settings ? json_decode($settings['columns'], true) : ['Term Score', 'Remark'];

    // 3. Get enrolled students and their existing grades
    $stmt_students = $pdo->prepare(
       "SELECT s.id, s.full_name, e.id as enrollment_id, g.grades FROM students s
        JOIN enrollments e ON s.id = e.student_id
        LEFT JOIN grades g ON e.id = g.enrollment_id
        WHERE e.class_id = :class_id ORDER BY s.full_name ASC"
    );
    $stmt_students->execute(['class_id' => $class_id]);
    $enrolled_students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

    // 4. Fetch comment banks
    $stmt_teacher_comments = $pdo->prepare("SELECT comment_text FROM report_comments WHERE user_id = :user_id AND comment_type = 'teacher_general' ORDER BY comment_text ASC");
    $stmt_teacher_comments->execute(['user_id' => $teacher_id]);
    $teacher_comments = $stmt_teacher_comments->fetchAll(PDO::FETCH_COLUMN);

    $stmt_principal_comments = $pdo->prepare("SELECT comment_text FROM report_comments WHERE school_id = :school_id AND comment_type = 'principal' ORDER BY comment_text ASC");
    $stmt_principal_comments->execute(['school_id' => $school_id]);
    $principal_comments = $stmt_principal_comments->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    include 'partials/footer.php';
    exit;
}

function sanitize_key($str) { return strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $str)); }

?>
<div class="container-fluid">
    <a href="/views/teacher/dashboard.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to My Classes</a>
    <form action="/controllers/grade_controller.php" method="POST">
        <input type="hidden" name="action" value="save_grades">
        <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Grade Entry</h1>
                <p class="text-muted">
                    <strong>Class:</strong> <?php echo htmlspecialchars($class_info['class_name']); ?> |
                    <strong>Period:</strong> <?php echo htmlspecialchars($class_info['academic_period']); ?>
                </p>
            </div>
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle-fill me-2"></i>Save All Grades</button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" style="min-width: 800px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">Student Name</th>
                                <?php foreach ($report_columns as $column): ?>
                                    <th class="text-center"><?php echo htmlspecialchars($column); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($enrolled_students)): ?>
                                <tr><td colspan="<?php echo count($report_columns) + 1; ?>" class="text-center">No students enrolled.</td></tr>
                            <?php else: foreach ($enrolled_students as $student): ?>
                                <?php $grades = $student['grades'] ? json_decode($student['grades'], true) : []; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <?php foreach ($report_columns as $column):
                                        $key = sanitize_key($column);
                                        $is_comment_field = (stripos($column, 'comment') !== false || stripos($column, 'remark') !== false);
                                    ?>
                                    <td>
                                        <?php if ($is_comment_field && (!empty($teacher_comments) || !empty($principal_comments))): ?>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm" name="grades[<?php echo $student['enrollment_id']; ?>][<?php echo $key; ?>]" value="<?php echo htmlspecialchars($grades[$key] ?? ''); ?>">
                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Insert a comment"><i class="bi bi-chat-quote"></i></button>
                                            <ul class="dropdown-menu dropdown-menu-end" style="max-height: 250px; overflow-y: auto;">
                                                <?php if (!empty($teacher_comments)): ?>
                                                    <li><h6 class="dropdown-header">My Comments</h6></li>
                                                    <?php foreach($teacher_comments as $comment): ?>
                                                        <li><a class="dropdown-item" href="#"><?php echo htmlspecialchars($comment); ?></a></li>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <?php if (!empty($principal_comments)): ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><h6 class="dropdown-header">Principal's Comments</h6></li>
                                                    <?php foreach($principal_comments as $comment): ?>
                                                        <li><a class="dropdown-item" href="#"><?php echo htmlspecialchars($comment); ?></a></li>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                        <?php else: ?>
                                        <input type="text" class="form-control form-control-sm" name="grades[<?php echo $student['enrollment_id']; ?>][<?php echo $key; ?>]" value="<?php echo htmlspecialchars($grades[$key] ?? ''); ?>">
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const commentDropdowns = document.querySelectorAll('.dropdown-menu');
    commentDropdowns.forEach(menu => {
        menu.addEventListener('click', function (e) {
            if (e.target.classList.contains('dropdown-item')) {
                e.preventDefault();
                const commentText = e.target.textContent;
                const inputGroup = e.target.closest('.input-group');
                const inputField = inputGroup.querySelector('input[type="text"]');
                inputField.value = commentText;
            }
        });
    });
});
</script>

<?php include 'partials/footer.php'; ?>