<?php
$page_title = "Landing Page CMS";
include 'partials/header.php';
require_once __DIR__ . '/../../config/config.php';

// Fetch all CMS content from the database
try {
    $stmt = $pdo->query("SELECT content_key, content_value FROM cms_content");
    $content = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $content = [];
    echo '<div class="alert alert-danger">Could not load CMS content. Error: ' . $e->getMessage() . '</div>';
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Landing Page Content Management</h1>

    <form action="/controllers/cms_controller.php" method="POST">
        <input type="hidden" name="action" value="update_content">

        <!-- Hero Section -->
        <div class="card shadow mb-4">
            <div class="card-header"><h6 class="m-0 fw-bold text-primary">Hero Section</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="hero_title" class="form-label">Main Headline</label>
                    <input type="text" class="form-control" name="hero_title" id="hero_title" value="<?php echo htmlspecialchars($content['hero_title'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="hero_subtitle" class="form-label">Sub-headline</label>
                    <textarea class="form-control" name="hero_subtitle" id="hero_subtitle" rows="3"><?php echo htmlspecialchars($content['hero_subtitle'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="card shadow mb-4">
            <div class="card-header"><h6 class="m-0 fw-bold text-primary">Features Section</h6></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3"><label class="form-label">Feature 1 Title</label><input type="text" class="form-control" name="feature1_title" value="<?php echo htmlspecialchars($content['feature1_title'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Feature 1 Text</label><textarea class="form-control" name="feature1_text" rows="3"><?php echo htmlspecialchars($content['feature1_text'] ?? ''); ?></textarea></div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3"><label class="form-label">Feature 2 Title</label><input type="text" class="form-control" name="feature2_title" value="<?php echo htmlspecialchars($content['feature2_title'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Feature 2 Text</label><textarea class="form-control" name="feature2_text" rows="3"><?php echo htmlspecialchars($content['feature2_text'] ?? ''); ?></textarea></div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3"><label class="form-label">Feature 3 Title</label><input type="text" class="form-control" name="feature3_title" value="<?php echo htmlspecialchars($content['feature3_title'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Feature 3 Text</label><textarea class="form-control" name="feature3_text" rows="3"><?php echo htmlspecialchars($content['feature3_text'] ?? ''); ?></textarea></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testimonials & Contact -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">Testimonials Section</h6></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label>Testimonial 1 Text</label><textarea class="form-control" name="testimonial1_text" rows="3"><?php echo htmlspecialchars($content['testimonial1_text'] ?? ''); ?></textarea></div>
                            <div class="col-md-6 mb-3"><label>Testimonial 1 Author</label><input type="text" class="form-control" name="testimonial1_author" value="<?php echo htmlspecialchars($content['testimonial1_author'] ?? ''); ?>"></div>
                            <div class="col-md-6 mb-3"><label>Testimonial 2 Text</label><textarea class="form-control" name="testimonial2_text" rows="3"><?php echo htmlspecialchars($content['testimonial2_text'] ?? ''); ?></textarea></div>
                            <div class="col-md-6 mb-3"><label>Testimonial 2 Author</label><input type="text" class="form-control" name="testimonial2_author" value="<?php echo htmlspecialchars($content['testimonial2_author'] ?? ''); ?>"></div>
                            <div class="col-md-6 mb-3"><label>Testimonial 3 Text</label><textarea class="form-control" name="testimonial3_text" rows="3"><?php echo htmlspecialchars($content['testimonial3_text'] ?? ''); ?></textarea></div>
                            <div class="col-md-6 mb-3"><label>Testimonial 3 Author</label><input type="text" class="form-control" name="testimonial3_author" value="<?php echo htmlspecialchars($content['testimonial3_author'] ?? ''); ?>"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                 <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">Contact Info</h6></div>
                    <div class="card-body">
                         <div class="mb-3"><label class="form-label">Contact Email</label><input type="email" class="form-control" name="contact_email" value="<?php echo htmlspecialchars($content['contact_email'] ?? ''); ?>"></div>
                         <div class="mb-3"><label class="form-label">Contact Phone</label><input type="text" class="form-control" name="contact_phone" value="<?php echo htmlspecialchars($content['contact_phone'] ?? ''); ?>"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-grid mb-4">
            <button type="submit" class="btn btn-lg btn-success">Save All Landing Page Content</button>
        </div>
    </form>
</div>

<?php include 'partials/footer.php'; ?>