<?php
$page_title = "System Settings";
include 'partials/header.php';
require_once __DIR__ . '/../../config/config.php';

// Fetch all settings from the database
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $settings = [];
    echo '<div class="alert alert-danger">Could not fetch system settings. Error: ' . $e->getMessage() . '</div>';
}

// Check for session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">System Settings</h1>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="/controllers/settings_controller.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_system">

        <div class="row">
            <div class="col-lg-8">
                <!-- Site Identity & SEO -->
                <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">Site Identity & SEO</h6></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="system_url" class="form-label">System Base URL</label>
                            <input type="url" class="form-control" id="system_url" name="system_url" value="<?php echo htmlspecialchars($settings['system_url'] ?? APP_URL); ?>" required>
                            <div class="form-text text-danger">
                                <strong>Crucial:</strong> This is the most important setting for ensuring report card links work correctly.
                                Enter the full, public URL of your application, without a trailing slash.
                                For example: <code>https://reportcard.yourschool.com</code>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3"><label for="site_name" class="form-label">Site Name</label><input type="text" class="form-control" name="site_name" id="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>"></div>
                        <div class="mb-3"><label for="seo_description" class="form-label">SEO Meta Description</label><textarea class="form-control" name="seo_description" id="seo_description" rows="3"><?php echo htmlspecialchars($settings['seo_description'] ?? ''); ?></textarea></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="site_logo" class="form-label">Site Logo (PNG)</label><input type="file" class="form-control" name="site_logo" id="site_logo" accept="image/png"><?php if (!empty($settings['site_logo_url'])): ?><small class="text-muted">Current: <a href="/<?php echo htmlspecialchars($settings['site_logo_url']); ?>" target="_blank">View Logo</a></small><?php endif; ?></div>
                            <div class="col-md-6 mb-3"><label for="site_favicon" class="form-label">Site Favicon (.ico)</label><input type="file" class="form-control" name="site_favicon" id="site_favicon" accept="image/x-icon"><?php if (!empty($settings['site_favicon_url'])): ?><small class="text-muted">Current: <a href="/<?php echo htmlspecialchars($settings['site_favicon_url']); ?>" target="_blank">View Favicon</a></small><?php endif; ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="hero_image" class="form-label">Landing Page Hero Image (JPG, PNG)</label>
                            <input type="file" class="form-control" name="hero_image" id="hero_image" accept="image/jpeg,image/png">
                            <?php if (!empty($settings['hero_image_url'])): ?>
                                <small class="text-muted">Current: <a href="<?php echo htmlspecialchars($settings['hero_image_url']); ?>" target="_blank">View Image</a></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Payment Gateway Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">Payment Gateway Settings</h6></div>
                    <div class="card-body">
                         <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="billing_fee_percentage" class="form-label">Billing Payment Fee (%)</label>
                                <input type="number" step="0.01" class="form-control" name="billing_payment_fee_percentage" id="billing_fee_percentage" value="<?php echo htmlspecialchars($settings['billing_payment_fee_percentage'] ?? '0'); ?>">
                                <div class="form-text">Percentage fee to add for student slot purchases.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sms_fee_percentage" class="form-label">SMS Payment Fee (%)</label>
                                <input type="number" step="0.01" class="form-control" name="sms_payment_fee_percentage" id="sms_fee_percentage" value="<?php echo htmlspecialchars($settings['sms_payment_fee_percentage'] ?? '0'); ?>">
                                <div class="form-text">Percentage fee to add for SMS credit purchases.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SMS Integration Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">SMS Integration Settings (PhilmoreSMS)</h6></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="sms_api_key" class="form-label">API Key</label>
                            <input type="password" class="form-control" name="sms_api_key" id="sms_api_key" value="<?php echo htmlspecialchars($settings['sms_api_key'] ?? ''); ?>">
                            <div class="form-text">Your secret API key from PhilmoreSMS.</div>
                        </div>
                        <div class="mb-3">
                            <label for="sms_sender_id" class="form-label">Sender ID</label>
                            <input type="text" class="form-control" name="sms_sender_id" id="sms_sender_id" value="<?php echo htmlspecialchars($settings['sms_sender_id'] ?? ''); ?>" placeholder="Max 11 characters">
                            <div class="form-text">Your approved Sender ID.</div>
                        </div>
                        <div class="mb-3">
                            <label for="sms_credit_cost" class="form-label">Cost per SMS (in <?php echo htmlspecialchars($settings['currency_code'] ?? 'USD'); ?>)</label>
                            <input type="text" class="form-control" name="sms_credit_cost" id="sms_credit_cost" value="<?php echo htmlspecialchars($settings['sms_credit_cost'] ?? '0.05'); ?>">
                            <div class="form-text">The amount to deduct from a school's balance for each message sent.</div>
                        </div>
                    </div>
                </div>

                 <!-- Email (SMTP) Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">Email (SMTP) Settings</h6></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 mb-3"><label for="smtp_host" class="form-label">SMTP Host</label><input type="text" class="form-control" name="smtp_host" id="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>"></div>
                            <div class="col-md-4 mb-3"><label for="smtp_port" class="form-label">SMTP Port</label><input type="text" class="form-control" name="smtp_port" id="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? ''); ?>"></div>
                        </div>
                        <div class="row">
                             <div class="col-md-6 mb-3"><label for="smtp_username" class="form-label">SMTP Username</label><input type="text" class="form-control" name="smtp_username" id="smtp_username" value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>"></div>
                            <div class="col-md-6 mb-3"><label for="smtp_password" class="form-label">SMTP Password</label><input type="password" class="form-control" name="smtp_password" id="smtp_password" value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>"></div>
                        </div>
                         <div class="row">
                             <div class="col-md-6 mb-3"><label for="smtp_from_email" class="form-label">From Email</label><input type="email" class="form-control" name="smtp_from_email" id="smtp_from_email" value="<?php echo htmlspecialchars($settings['smtp_from_email'] ?? ''); ?>"></div>
                            <div class="col-md-6 mb-3"><label for="smtp_from_name" class="form-label">From Name</label><input type="text" class="form-control" name="smtp_from_name" id="smtp_from_name" value="<?php echo htmlspecialchars($settings['smtp_from_name'] ?? ''); ?>"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                 <!-- Cron Job & PWA -->
                <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">Advanced Settings</h6></div>
                    <div class="card-body">
                        <p class="mb-2"><strong>PWA Functionality</strong></p>
                        <div class="form-check form-switch fs-5 mb-3">
                            <input class="form-check-input" type="checkbox" id="pwaToggle" name="pwa_enabled" value="1" <?php echo ($settings['pwa_enabled'] ?? 0) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="pwaToggle">Enable PWA</label>
                        </div>
                        <p class="mb-2"><strong>User Registration</strong></p>
                        <div class="form-check form-switch fs-5 mb-3">
                            <input class="form-check-input" type="checkbox" id="registrationToggle" name="registration_enabled" value="1" <?php echo ($settings['registration_enabled'] ?? 0) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="registrationToggle">Allow New School Registrations</label>
                        </div>
                        <hr>
                        <p class="mb-2"><strong>Automated Task Settings</strong></p>
                        <div class="mb-3">
                             <label for="grace_period_days" class="form-label">Subscription Grace Period (Days)</label>
                             <input type="number" class="form-control" name="grace_period_days" id="grace_period_days" value="<?php echo htmlspecialchars($settings['grace_period_days'] ?? '7'); ?>">
                        </div>
                         <div class="alert alert-info small">
                            <strong>Cron Command:</strong>
                            <code class="d-block" style="word-wrap: break-word;">/usr/bin/php <?php echo APP_ROOT; ?>/core/cron.php --key=<?php echo htmlspecialchars($settings['cron_job_key'] ?? 'YOUR_KEY'); ?></code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-grid mb-4">
            <button type="submit" class="btn btn-lg btn-success">Save All System Settings</button>
        </div>
    </form>
</div>

<script>
// No script needed for this section anymore
</script>

<?php include 'partials/footer.php'; ?>