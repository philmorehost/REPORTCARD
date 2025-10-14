<?php
$page_title = "Report Card Templates";
include 'partials/header.php';
require_once __DIR__ . '/../../config/config.php';

// Check for session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

// Fetch all existing templates
try {
    $stmt = $pdo->query("SELECT * FROM report_templates ORDER BY name ASC");
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $templates = [];
    echo '<div class="alert alert-danger">Could not load templates. Error: ' . $e->getMessage() . '</div>';
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Report Card Template Library</h1>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Template List -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Available Templates</h6></div>
                <div class="card-body">
                    <div class="row">
                        <?php if (empty($templates)): ?>
                            <p class="text-center">No templates uploaded yet. Use the form to add one.</p>
                        <?php else: ?>
                            <?php foreach ($templates as $template): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <img src="/<?php echo htmlspecialchars($template['thumbnail_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($template['name']); ?>" style="height: 150px; object-fit: cover;">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($template['name']); ?></h5>
                                            <p class="card-text small"><?php echo htmlspecialchars($template['description']); ?></p>
                                        </div>
                                        <div class="card-footer text-center">
                                            <a href="/controllers/template_controller.php?action=delete&id=<?php echo $template['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this template?');">
                                                <i class="bi bi-trash-fill"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload New Template -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Upload New Template</h6></div>
                <div class="card-body">
                    <form action="/controllers/template_controller.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload">
                        <div class="mb-3">
                            <label for="template_name" class="form-label">Template Name</label>
                            <input type="text" class="form-control" name="template_name" id="template_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="template_description" class="form-label">Description</label>
                            <textarea class="form-control" name="template_description" id="template_description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="template_thumbnail" class="form-label">Thumbnail Image</label>
                            <input type="file" class="form-control" name="template_thumbnail" id="template_thumbnail" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label for="template_file" class="form-label">Template PHP File</label>
                            <input type="file" class="form-control" name="template_file" id="template_file" accept=".php" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">Upload Template</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>