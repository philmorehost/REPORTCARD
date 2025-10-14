<?php
$page_title = "Email Templates";
include 'partials/header.php';
require_once __DIR__ . '/../../config/config.php';

$school_id = $_SESSION['school_id'];

// Check for session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

// Fetch existing email templates
try {
    $stmt = $pdo->prepare("SELECT * FROM email_templates WHERE school_id = :school_id ORDER BY name ASC");
    $stmt->execute(['school_id' => $school_id]);
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $templates = [];
    echo '<div class="alert alert-danger">Could not load email templates. Error: ' . $e->getMessage() . '</div>';
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Email Template Manager</h1>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Create/Edit Template Form -->
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header"><h6 class="m-0 fw-bold text-primary" id="form-title">Create New Template</h6></div>
                <div class="card-body">
                    <form id="templateForm" action="/controllers/email_template_controller.php" method="POST">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="template_id" id="templateId">

                        <div class="mb-3">
                            <label for="name" class="form-label">Template Name</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="e.g., Term 1 Report Card Notification" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Email Subject</label>
                            <input type="text" class="form-control" name="subject" id="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="body" class="form-label">Email Body</label>
                            <textarea class="form-control" name="body" id="body" rows="8" required></textarea>
                        </div>
                        <div class="alert alert-light small">
                            <p class="mb-1"><strong>Available Placeholders:</strong></p>
                            <code>{parent_name}</code>, <code>{student_name}</code>, <code>{report_link}</code>, <code>{school_name}</code>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save Template</button>
                            <button type="button" class="btn btn-secondary" id="cancelEditBtn" style="display: none;" onclick="resetForm()">Cancel Edit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Existing Templates List -->
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header"><h6 class="m-0 fw-bold text-primary">Your Templates</h6></div>
                <div class="card-body">
                    <?php if (empty($templates)): ?>
                        <p class="text-center">You have not created any email templates yet.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($templates as $template): ?>
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($template['name']); ?></h5>
                                        <div>
                                            <button class="btn btn-sm btn-info" onclick='editTemplate(<?php echo json_encode($template); ?>)'><i class="bi bi-pencil-fill"></i></button>
                                            <a href="/controllers/email_template_controller.php?action=delete&id=<?php echo $template['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');"><i class="bi bi-trash-fill"></i></a>
                                        </div>
                                    </div>
                                    <p class="mb-1 small text-muted"><strong>Subject:</strong> <?php echo htmlspecialchars($template['subject']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editTemplate(template) {
    document.getElementById('form-title').innerText = 'Edit Template';
    document.getElementById('formAction').value = 'update';
    document.getElementById('templateId').value = template.id;
    document.getElementById('name').value = template.name;
    document.getElementById('subject').value = template.subject;
    document.getElementById('body').value = template.body;
    document.getElementById('cancelEditBtn').style.display = 'block';
    window.scrollTo(0, 0);
}

function resetForm() {
    document.getElementById('templateForm').reset();
    document.getElementById('form-title').innerText = 'Create New Template';
    document.getElementById('formAction').value = 'create';
    document.getElementById('templateId').value = '';
    document.getElementById('cancelEditBtn').style.display = 'none';
}
</script>

<?php include 'partials/footer.php'; ?>