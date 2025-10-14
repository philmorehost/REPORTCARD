<?php
$page_title = "Banner Ad Management";
include 'partials/header.php';

// Fetch all banner ads
$stmt = $pdo->query("SELECT * FROM banner_ads ORDER BY created_at DESC");
$banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Banner Ad Management</h1>

    <!-- Create Banner Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Create New Banner Ad</h6>
        </div>
        <div class="card-body">
            <form action="/controllers/banner_controller.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_banner">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="banner_image" class="form-label">Banner Image (Recommended: 800x400)</label>
                            <input class="form-control" type="file" id="banner_image" name="banner_image" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="target_link" class="form-label">Target Link (URL)</label>
                            <input type="url" class="form-control" id="target_link" name="target_link" placeholder="https://example.com" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="expires_at" class="form-label">Expiry Date (Optional)</label>
                            <input type="date" class="form-control" id="expires_at" name="expires_at">
                            <div class="form-text">The banner will not be shown after this date.</div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Create Banner</button>
            </form>
        </div>
    </div>

    <!-- Existing Banners Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Existing Banner Ads</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Target Link</th>
                            <th>Expires At</th>
                            <th>Clicks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($banners)): ?>
                            <tr><td colspan="5" class="text-center">No banner ads have been created yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($banners as $banner): ?>
                                <tr>
                                    <td><img src="/<?php echo htmlspecialchars($banner['image_url']); ?>" alt="Banner Ad" style="max-height: 50px;"></td>
                                    <td><a href="<?php echo htmlspecialchars($banner['target_link']); ?>" target="_blank"><?php echo htmlspecialchars($banner['target_link']); ?></a></td>
                                    <td><?php echo $banner['expires_at'] ? date('F j, Y', strtotime($banner['expires_at'])) : 'Never'; ?></td>
                                    <td><?php echo $banner['click_count']; ?></td>
                                    <td>
                                        <form action="/controllers/banner_controller.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this banner?');">
                                            <input type="hidden" name="action" value="delete_banner">
                                            <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
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

<?php include 'partials/footer.php'; ?>
