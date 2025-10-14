<?php
$page_title = "Bank Transfer for SMS Credits";
include 'partials/header.php'; // Includes auth, db, etc.

// Get quantity from URL and calculate cost
$quantity = (int)($_GET['quantity'] ?? 100);
if ($quantity <= 0) {
    $quantity = 100; // Default to 100 if invalid
}

$credit_cost = (float)s_get($pdo, 'sms_credit_cost', 0.05);
$currency_symbol = get_currency_symbol($pdo);
$total_amount = $quantity * $credit_cost;

// Fetch bank details from settings
try {
    $bank_details = s_get($pdo, 'manual_transfer_details', 'Bank details are not configured. Please contact support.');
} catch (PDOException $e) {
    $bank_details = "Bank details could not be loaded. Please contact support.";
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Bank Transfer for SMS Credits</h1>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Instructions</h6>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info">
                        <h5 class="alert-heading">Step 1: Make Payment</h5>
                        <p>To purchase <strong><?php echo number_format($quantity); ?></strong> SMS credits, please make a payment of <strong><?php echo $currency_symbol . number_format($total_amount, 2); ?></strong> to the following bank account:</p>
                        <pre class="bg-light p-3 rounded"><?php echo htmlspecialchars($bank_details); ?></pre>
                    </div>

                    <hr>

                    <h5 class="mb-3">Step 2: Upload Proof of Payment</h5>
                    <p>After making the payment, please upload a screenshot or deposit slip below. Your credits will be added by an administrator within 24 hours of confirming your payment.</p>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type'] === 'success' ? 'success' : 'danger'; ?>"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message'], $_SESSION['message_type']); ?></div>
                    <?php endif; ?>

                    <form action="/controllers/sms_payment_controller.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_proof">
                        <input type="hidden" name="credit_quantity" value="<?php echo $quantity; ?>">
                        <input type="hidden" name="amount" value="<?php echo $total_amount; ?>">

                        <div class="mb-3">
                            <label for="payment_proof" class="form-label">Proof of Payment File</label>
                            <input type="file" class="form-control" id="payment_proof" name="payment_proof" accept="image/*,.pdf" required>
                        </div>
                         <div class="mb-3">
                            <label for="payment_reference" class="form-label">Reference / Transaction ID (Optional)</label>
                            <input type="text" class="form-control" id="payment_reference" name="payment_reference">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">Submit for Review</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>