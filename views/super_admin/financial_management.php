<?php
$page_title = "Financial Management";
include 'partials/header.php';
require_once __DIR__ . '/../../config/config.php';

// Fetch all settings from the database
try {
    $stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    $settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
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
    <h1 class="h3 mb-4 text-gray-800">Financial Management</h1>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="/controllers/settings_controller.php" method="POST">
        <input type="hidden" name="action" value="update_financial">

        <div class="row">
            <div class="col-lg-7">
                <!-- Currency Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">Currency Settings</h6></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="currency_symbol" class="form-label">Currency Symbol</label>
                                <input type="text" class="form-control" name="currency_symbol" id="currency_symbol" value="<?php echo get_currency_symbol($pdo); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="currency_code" class="form-label">Currency Code (e.g., USD, NGN)</label>
                                <input type="text" class="form-control" name="currency_code" id="currency_code" value="<?php echo htmlspecialchars($settings['currency_code'] ?? 'USD'); ?>">
                            </div>
                        </div>
                        <small class="text-muted">Note: Package pricing is now managed in the <a href="/views/super_admin/packages.php">Package Management</a> section.</small>
                    </div>
                </div>

                <!-- Payment Gateways -->
                <div class="card shadow mb-4">
                     <div class="card-header"><h6 class="m-0 fw-bold text-primary">Payment Gateways</h6></div>
                     <div class="card-body">
                        <div class="accordion" id="gatewayAccordion">
                            <!-- Paystack -->
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePaystack">Paystack <div class="form-check form-switch ms-auto me-3"><input class="form-check-input" type="checkbox" name="paystack_enabled" <?php echo ($settings['paystack_enabled'] ?? 0) ? 'checked' : ''; ?>></div></button></h2>
                                <div id="collapsePaystack" class="accordion-collapse collapse" data-bs-parent="#gatewayAccordion"><div class="accordion-body"><div class="mb-3"><label class="form-label">Paystack Secret Key</label><input type="text" class="form-control" name="paystack_secret_key" value="<?php echo htmlspecialchars($settings['paystack_secret_key'] ?? ''); ?>"></div><div class="mb-3"><label class="form-label">Paystack Public Key</label><input type="text" class="form-control" name="paystack_public_key" value="<?php echo htmlspecialchars($settings['paystack_public_key'] ?? ''); ?>"></div></div></div>
                            </div>
                            <!-- Flutterwave -->
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFlutterwave">Flutterwave <div class="form-check form-switch ms-auto me-3"><input class="form-check-input" type="checkbox" name="flutterwave_enabled" <?php echo ($settings['flutterwave_enabled'] ?? 0) ? 'checked' : ''; ?>></div></button></h2>
                                <div id="collapseFlutterwave" class="accordion-collapse collapse" data-bs-parent="#gatewayAccordion"><div class="accordion-body"><div class="mb-3"><label class="form-label">Flutterwave Secret Key</label><input type="text" class="form-control" name="flutterwave_secret_key" value="<?php echo htmlspecialchars($settings['flutterwave_secret_key'] ?? ''); ?>"></div><div class="mb-3"><label class="form-label">Flutterwave Public Key</label><input type="text" class="form-control" name="flutterwave_public_key" value="<?php echo htmlspecialchars($settings['flutterwave_public_key'] ?? ''); ?>"></div></div></div>
                            </div>
                            <!-- Stripe -->
                             <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStripe">Stripe <div class="form-check form-switch ms-auto me-3"><input class="form-check-input" type="checkbox" name="stripe_enabled" <?php echo ($settings['stripe_enabled'] ?? 0) ? 'checked' : ''; ?>></div></button></h2>
                                <div id="collapseStripe" class="accordion-collapse collapse" data-bs-parent="#gatewayAccordion"><div class="accordion-body"><div class="mb-3"><label class="form-label">Stripe Secret Key</label><input type="text" class="form-control" name="stripe_secret_key" value="<?php echo htmlspecialchars($settings['stripe_secret_key'] ?? ''); ?>"></div><div class="mb-3"><label class="form-label">Stripe Public Key</label><input type="text" class="form-control" name="stripe_public_key" value="<?php echo htmlspecialchars($settings['stripe_public_key'] ?? ''); ?>"></div></div></div>
                            </div>
                        </div>
                     </div>
                </div>
            </div>

            <div class="col-lg-5">
                <!-- Manual Bank Transfer -->
                <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">Manual Bank Transfer</h6></div>
                    <div class="card-body">
                        <p>Provide bank details for schools who wish to pay via manual deposit.</p>
                        <div class="mb-3">
                            <label for="manual_details" class="form-label">Bank Account Details</label>
                            <textarea class="form-control" name="manual_transfer_details" id="manual_details" rows="5"><?php echo htmlspecialchars($settings['manual_transfer_details'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-grid mb-4">
            <button type="submit" class="btn btn-lg btn-success">Save All Financial Settings</button>
        </div>
    </form>
</div>

<?php include 'partials/footer.php'; ?>