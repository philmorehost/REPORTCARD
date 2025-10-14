<?php
$page_title = "School Announcements";
include 'partials/header.php';
require_once __DIR__ . '/../../config/config.php';

$school_id = $_SESSION['school_id'];

// Check for session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

// Fetch existing school-specific announcements
try {
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE school_id = :school_id ORDER BY created_at DESC");
    $stmt->execute(['school_id' => $school_id]);
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $announcements = [];
    echo '<div class="alert alert-danger">Could not load announcements. Error: ' . $e->getMessage() . '</div>';
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">School-wide Announcements</h1>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Create New Announcement -->
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header"><h6 class="m-0 fw-bold text-primary">Create New Announcement for Teachers</h6></div>
                <div class="card-body">
                    <form action="/controllers/announcement_controller.php" method="POST">
                        <input type="hidden" name="action" value="create_school_announcement">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" name="content" id="content" rows="5" required></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Post to All Teachers</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Previous Announcements -->
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header"><h6 class="m-0 fw-bold text-primary">Sent Announcements</h6></div>
                <div class="card-body">
                    <?php if (empty($announcements)): ?>
                        <p class="text-center">No announcements have been sent yet.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($announcements as $announcement): ?>
                                <li class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                        <small><?php echo date('M d, Y', strtotime($announcement['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                                    <a href="/controllers/announcement_controller.php?action=delete&id=<?php echo $announcement['id']; ?>" class="text-danger small" onclick="return confirm('Are you sure you want to delete this announcement?');">Delete</a>
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