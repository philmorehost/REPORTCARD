<?php
// This is Step 2 of the registration process for paid packages.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/core/init.php';

// If registration data isn't in the session, or if it's a free package, redirect.
if (!isset($_SESSION['registration_data']) || (float)$_SESSION['registration_data']['package_price'] <= 0) {
    header('Location: /register.php');
    exit;
}

$reg_data = $_SESSION['registration_data'];
$package_name = $reg_data['package_name'];
$package_price = (float)$reg_data['package_price'];

// Fetch currency and gateway status from settings
$currency_symbol = get_currency_symbol($pdo);
$paystack_enabled = (bool)s_get($pdo, 'paystack_enabled', 0);
$flutterwave_enabled = (bool)s_get($pdo, 'flutterwave_enabled', 0);
// For this implementation, we assume at least one online gateway is a stand-in for a generic "Online Payment"
$online_payment_enabled = $paystack_enabled || $flutterwave_enabled;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Student Slots - ARS Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .registration-container { max-width: 600px; margin: 4rem auto; }
        .price-display { font-size: 2rem; font-weight: bold; color: #198754; }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="text-center mb-4">
            <h1 class="h2">Purchase Slots for <?php echo htmlspecialchars($package_name); ?></h1>
            <p class="lead">Step 2 of 3: Specify Student Quantity</p>
        </div>
        <div class="card shadow">
            <div class="card-body p-4">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <p class="text-muted">To activate your school, you must purchase an initial number of student slots. You can add more at any time from your dashboard.</p>

                <form id="purchaseForm" method="POST">
                    <div class="mb-3">
                        <label for="slot_quantity" class="form-label">Number of Student Slots:</label>
                        <input type="number" class="form-control" name="slot_quantity" id="slot_quantity" value="100" min="1">
                    </div>

                    <div class="alert alert-info text-center">
                        Price: <strong><?php echo $currency_symbol . number_format($package_price, 2); ?></strong> per student
                    </div>

                    <div class="text-center my-3">
                        <small>Total Cost</small>
                        <div id="price-display" class="price-display"></div>
                    </div>
                    <hr>
                    <p class="fw-bold text-center">Choose Your Payment Method</p>
                    <div class="d-grid gap-2">
                        <?php if ($paystack_enabled): ?>
                            <button type="button" class="btn btn-primary btn-lg" data-gateway="/controllers/paystack_controller.php">
                                <i class="bi bi-credit-card-fill me-2"></i>Pay with Paystack
                            </button>
                        <?php endif; ?>

                        <?php if ($flutterwave_enabled): ?>
                            <button type="button" class="btn btn-warning btn-lg" data-gateway="/controllers/flutterwave_controller.php">
                                <i class="bi bi-credit-card-fill me-2"></i>Pay with Flutterwave
                            </button>
                        <?php endif; ?>

                        <button type="submit" name="payment_method" value="bank_transfer" class="btn btn-secondary btn-lg" data-gateway="/controllers/registration_controller.php">
                            <i class="bi bi-bank me-2"></i>Pay by Bank Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center mt-3"><a href="/register.php">Back to Package Selection</a></div>
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
    updatePrice(); // Initial calculation

    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const gatewayUrl = this.getAttribute('data-gateway');
            form.setAttribute('action', gatewayUrl);
            form.submit();
        });
    });
});
</script>
</body>
</html>