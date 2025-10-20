<?php
// This is the page for an existing school admin to upgrade their package.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../core/init.php';
require_once __DIR__ . '/../../core/auth_check.php';
require_auth('school_admin');

$package_id = $_GET['pkg_id'] ?? 0;
if (!$package_id) {
    header('Location: /views/school_admin/billing.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = :id AND price > 0");
    $stmt->execute([':id' => $package_id]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$package) {
        $_SESSION['message'] = 'Invalid package selected for upgrade.';
        $_SESSION['message_type'] = 'error';
        header('Location: /views/school_admin/billing.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['message'] = 'Database error fetching package details.';
    $_SESSION['message_type'] = 'error';
    header('Location: /views/school_admin/billing.php');
    exit;
}

$package_name = $package['name'];
$package_price = (float)$package['price'];

// Fetch currency and gateway status from settings
$currency_symbol = get_currency_symbol($pdo);
$paystack_enabled = (bool)s_get($pdo, 'paystack_enabled', 0);
$flutterwave_enabled = (bool)s_get($pdo, 'flutterwave_enabled', 0);
$online_payment_enabled = $paystack_enabled || $flutterwave_enabled;

include 'partials/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <h1 class="h2">Upgrade to <?php echo htmlspecialchars($package_name); ?></h1>
                <p class="lead">Purchase your initial student slots to activate the new plan.</p>
            </div>
            <div class="card shadow">
                <div class="card-body p-4">
                    <p class="text-muted">Once you upgrade, your school will be switched to the new plan, and the purchased student slots will be added to your account upon successful payment.</p>

                    <form id="purchaseForm" method="POST">
                        <input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
                        <div class="mb-3">
                            <label for="slot_quantity" class="form-label">Number of Student Slots to Purchase:</label>
                            <input type="number" class="form-control" name="slot_quantity" id="slot_quantity" value="100" min="1">
                        </div>

                        <div class="alert alert-info text-center">
                            Price: <strong><?php echo $currency_symbol . number_format($package_price, 2); ?></strong> per student
                        </div>

                        <div class="text-center my-3">
                            <small>Total Cost</small>
                            <div id="price-display" class="price-display" style="font-size: 2rem; font-weight: bold; color: #198754;"></div>
                        </div>
                        <hr>
                        <p class="fw-bold text-center">Choose Your Payment Method</p>
                        <div class="d-grid gap-2">
                             <?php if ($paystack_enabled): ?>
                                <button type="button" class="btn btn-primary btn-lg" data-gateway="/controllers/paystack_controller.php?action=upgrade">Pay with Paystack</button>
                            <?php endif; ?>
                            <?php if ($flutterwave_enabled): ?>
                                <button type="button" class="btn btn-warning btn-lg" data-gateway="/controllers/flutterwave_controller.php?action=upgrade">Pay with Flutterwave</button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-secondary btn-lg" data-gateway="/controllers/upgrade_controller.php">Pay by Bank Transfer</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3"><a href="/views/school_admin/billing.php">Back to Billing</a></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slotQuantityInput = document.getElementById('slot_quantity');
    const priceDisplay = document.getElementById('price-display');
    const pricePerSlot = <?php echo $package_price; ?>;
    const currencySymbol = '<?php echo $currency_symbol; ?>';
    const form = document.getElementById('purchaseForm');
    const buttons = form.querySelectorAll('button[data-gateway]');

    function updatePrice() {
        const quantity = parseInt(slotQuantityInput.value) || 0;
        const total = quantity * pricePerSlot;
        priceDisplay.textContent = `${currencySymbol}${total.toFixed(2)}`;
    }

    slotQuantityInput.addEventListener('input', updatePrice);
    updatePrice();

    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const gatewayUrl = this.getAttribute('data-gateway');
            form.setAttribute('action', gatewayUrl);
            form.submit();
        });
    });
});
</script>

<?php include 'partials/footer.php'; ?>
