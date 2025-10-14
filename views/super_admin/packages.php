<?php
require_once __DIR__ . '/../../core/init.php';
$page_title = 'Package Management';
include_once __DIR__ . '/partials/header.php';

// Fetch packages from the database
try {
    $stmt = $pdo->query("SELECT * FROM packages ORDER BY id");
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If the table doesn't exist, it might be because the migration hasn't run
    if ($e->getCode() === '42S02') { // SQLSTATE[42S02]: Base table or view not found
        $_SESSION['error'] = "The 'packages' table was not found. Please run the database migration.";
        $packages = [];
    } else {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        $packages = [];
    }
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Package Management</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">

                    <?php
                    if (isset($_SESSION['message'])) {
                        echo '<div class="alert alert-success text-white mx-4">' . htmlspecialchars($_SESSION['message']) . '</div>';
                        unset($_SESSION['message']);
                    }
                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-danger text-white mx-4">' . htmlspecialchars($_SESSION['error']) . '</div>';
                        unset($_SESSION['error']);
                    }
                    ?>

                    <?php if (!empty($packages)): ?>
                    <form action="/controllers/new_package_controller.php" method="POST" class="p-4">
                        <input type="hidden" name="action" value="update_packages">

                        <?php foreach ($packages as $package): ?>
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header">
                                    <h5 class="mb-0">Edit "<?php echo htmlspecialchars($package['name']); ?>" Package</h5>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="packages[<?php echo $package['id']; ?>][id]" value="<?php echo $package['id']; ?>">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Package Name</label>
                                                <input type="text" class="form-control p-2" name="packages[<?php echo $package['id']; ?>][name]" value="<?php echo htmlspecialchars($package['name']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Price per Student</label>
                                                <input type="number" step="0.01" class="form-control p-2" name="packages[<?php echo $package['id']; ?>][price]" value="<?php echo htmlspecialchars($package['price']); ?>" <?php if (strtolower($package['name']) === 'freemium') echo 'readonly'; ?> required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Student Limit (0 for unlimited)</label>
                                        <input type="number" class="form-control p-2" name="packages[<?php echo $package['id']; ?>][student_limit]" value="<?php echo htmlspecialchars($package['student_limit']); ?>" <?php if (strtolower($package['name']) === 'premium') echo 'readonly'; ?> required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Features (JSON format)</label>
                                        <textarea class="form-control p-2" name="packages[<?php echo $package['id']; ?>][features]" rows="8" required><?php
                                            $features_json = $package['features'];
                                            // Check if it's a valid JSON string
                                            if (is_string($features_json) && is_array(json_decode($features_json, true))) {
                                                $features = json_decode($features_json);
                                                echo htmlspecialchars(json_encode($features, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                            } else {
                                                // If not a valid JSON, just display the raw text
                                                echo htmlspecialchars($features_json);
                                            }
                                        ?></textarea>
                                        <small class="form-text text-muted">Enter features as a JSON array of strings (e.g., ["Feature 1", "Feature 2"]).</small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="px-3">
                            <button type="submit" class="btn btn-primary">Update All Packages</button>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="px-4">
                            <p>No packages found in the database or there was an error.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/partials/footer.php';
?>