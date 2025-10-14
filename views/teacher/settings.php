<?php
$page_title = "Settings";
include 'partials/header.php'; // Includes auth, db, etc.

$teacher_id = $_SESSION['user_id'];
$school_id = $_SESSION['school_id'];

// Check for session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

// Fetch existing teacher comments
try {
    $stmt_comments = $pdo->prepare("SELECT comment_text FROM report_comments WHERE user_id = :user_id AND comment_type = 'teacher_general'");
    $stmt_comments->execute(['user_id' => $teacher_id]);
    $teacher_comments_array = $stmt_comments->fetchAll(PDO::FETCH_COLUMN);
    $teacher_comments = implode("\n", $teacher_comments_array);
} catch (PDOException $e) {
    $teacher_comments = '';
    $message = "Error fetching your comments: " . $e->getMessage();
    $message_type = 'danger';
}

?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?php echo $page_title; ?></h1>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">My Comment Bank</h6>
                </div>
                <div class="card-body">
                    <p>Enter a list of your most frequently used general comments here. Separate each comment with a new line. You'll be able to select from this list when entering student grades.</p>
                    <form action="/controllers/teacher_settings_controller.php" method="POST">
                        <input type="hidden" name="action" value="save_comments">
                        <div class="mb-3">
                            <label for="teacherComments" class="form-label">Teacher's General Comments</label>
                            <textarea class="form-control" id="teacherComments" name="teacher_comments" rows="10"><?php echo htmlspecialchars($teacher_comments); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save My Comments</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>