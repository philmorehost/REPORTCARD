<?php
$page_title = "Subject Management";
include 'partials/header.php';

$school_id = $_SESSION['school_id'];

// Fetch subjects and their assigned teachers
try {
    $stmt_subjects = $pdo->prepare("
        SELECT s.id, s.subject_name, GROUP_CONCAT(u.full_name SEPARATOR ', ') AS teachers
        FROM subjects s
        LEFT JOIN subject_teacher_assignments sta ON s.id = sta.subject_id
        LEFT JOIN users u ON sta.teacher_id = u.id
        WHERE s.school_id = :school_id
        GROUP BY s.id
        ORDER BY s.subject_name ASC
    ");
    $stmt_subjects->execute(['school_id' => $school_id]);
    $subjects = $stmt_subjects->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all teachers for the assignment modal
    $stmt_teachers = $pdo->prepare("SELECT id, full_name FROM users WHERE school_id = :school_id AND role = 'teacher'");
    $stmt_teachers->execute(['school_id' => $school_id]);
    $teachers = $stmt_teachers->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $subjects = [];
    $teachers = [];
    echo '<div class="alert alert-danger">Could not fetch data. Error: ' . $e->getMessage() . '</div>';
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Subject Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subjectModal"><i class="bi bi-plus-circle me-2"></i>Create Subjects</button>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Subjects List</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr><th>Subject Name</th><th>Assigned Teachers</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($subjects)): ?>
                            <tr><td colspan="3" class="text-center">No subjects found. Create some to get started.</td></tr>
                        <?php else: ?>
                            <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                <td><?php echo htmlspecialchars($subject['teachers'] ?? 'None'); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" title="Assign Teachers" data-bs-toggle="modal" data-bs-target="#assignTeacherModal" data-subject-id="<?php echo $subject['id']; ?>"><i class="bi bi-person-plus-fill"></i></button>
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

<!-- Create Subjects Modal -->
<div class="modal fade" id="subjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/controllers/subject_controller.php" method="POST">
                <div class="modal-header"><h5 class="modal-title">Create New Subjects</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_bulk">
                    <div class="mb-3">
                        <label for="subject_names" class="form-label">Subject Names</label>
                        <textarea class="form-control" name="subject_names" rows="5" placeholder="Enter one subject name per line..."></textarea>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Create Subjects</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Teacher Modal -->
<div class="modal fade" id="assignTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/controllers/subject_controller.php" method="POST">
                <div class="modal-header"><h5 class="modal-title">Assign Teachers to Subject</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="assign_teachers">
                    <input type="hidden" name="subject_id" id="assignSubjectId">
                    <div class="mb-3">
                        <label for="teacher_ids" class="form-label">Select Teachers</label>
                        <select class="form-select" name="teacher_ids[]" multiple required>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Hold Ctrl/Cmd to select multiple teachers.</div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Assign Teachers</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var assignTeacherModal = document.getElementById('assignTeacherModal');
    assignTeacherModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var subjectId = button.getAttribute('data-subject-id');
        var modalSubjectIdInput = assignTeacherModal.querySelector('#assignSubjectId');
        modalSubjectIdInput.value = subjectId;
    });
});
</script>

<?php include 'partials/footer.php'; ?>
