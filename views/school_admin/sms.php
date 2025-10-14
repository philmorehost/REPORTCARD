<?php
$page_title = "SMS & Notifications";
include 'partials/header.php'; // Includes auth, db, etc.

$school_id = $_SESSION['school_id'];

// Fetch required data in a single block
try {
    // Get current credit balance
    $stmt_credits = $pdo->prepare("SELECT sms_credits FROM schools WHERE id = :school_id");
    $stmt_credits->execute(['school_id' => $school_id]);
    $current_credits = $stmt_credits->fetchColumn() ?: 0;

    // Get credit cost and currency info
    $credit_cost = (float)s_get($pdo, 'sms_credit_cost', 0.05);
    $currency_symbol = get_currency_symbol($pdo);

    // Get enabled payment gateways
    $paystack_enabled = (bool)s_get($pdo, 'paystack_enabled', 0);
    $flutterwave_enabled = (bool)s_get($pdo, 'flutterwave_enabled', 0);

    // Fetch transaction history
    $stmt_history = $pdo->prepare("SELECT * FROM sms_transactions WHERE school_id = :school_id ORDER BY created_at DESC");
    $stmt_history->execute(['school_id' => $school_id]);
    $credit_history = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

    // Fetch message log
    $stmt_log = $pdo->prepare("SELECT * FROM sms_log WHERE school_id = :school_id ORDER BY created_at DESC LIMIT 100");
    $stmt_log->execute(['school_id' => $school_id]);
    $message_log = $stmt_log->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Handle potential database errors gracefully
    $current_credits = 0;
    $credit_cost = 0;
    $currency_symbol = '';
    $paystack_enabled = false;
    $flutterwave_enabled = false;
    $credit_history = [];
    $message_log = [];
    echo '<div class="alert alert-danger">Could not fetch SMS data. Error: ' . $e->getMessage() . '</div>';
}

?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">SMS & Notifications</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Credit Balance Summary -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Available Credits</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($current_credits); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comment-dollar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Purchase More Credits -->
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Purchase SMS Credits</h6>
                </div>
                <div class="card-body">
                    <p>Top up your balance to send SMS notifications to parents.</p>
                    <form id="purchaseCreditForm" method="POST">
                        <input type="hidden" name="purchase_type" value="sms_credits">
                        <div class="mb-3">
                            <label for="credit_quantity" class="form-label">Number of Credits to Purchase:</label>
                            <input type="number" class="form-control" name="credit_quantity" id="credit_quantity" value="100" min="10">
                        </div>
                        <div class="alert alert-info">
                            <strong>Price:</strong> <?php echo $currency_symbol; ?><?php echo number_format($credit_cost, 2); ?> per credit.
                        </div>

                        <div class="d-grid gap-2">
                            <?php if ($paystack_enabled): ?>
                                <button type="button" class="btn btn-primary" data-gateway="/controllers/paystack_controller.php">Pay with Paystack</button>
                            <?php endif; ?>
                            <?php if ($flutterwave_enabled): ?>
                                <button type="button" class="btn btn-warning" data-gateway="/controllers/flutterwave_controller.php">Pay with Flutterwave</button>
                            <?php endif; ?>
                        </div>
                    </form>
                    <hr>
                    <div class="text-center">
                        <p class="mb-2">Or pay via manual bank transfer:</p>
                        <a href="/views/school_admin/sms_bank_transfer.php?quantity=100" id="bankTransferLink" class="btn btn-outline-secondary">Pay with Bank Transfer</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Credit Transaction History -->
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Credit Purchase History</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Credits Purchased</th>
                                    <th>Amount Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($credit_history)): ?>
                                    <tr><td colspan="3" class="text-center">No credit purchase history found.</td></tr>
                                <?php else: ?>
                                    <?php foreach($credit_history as $tx): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i', strtotime($tx['created_at'] ?? time())); ?></td>
                                        <td><?php echo number_format($tx['credits_purchased'] ?? 0); ?></td>
                                        <td><?php echo $currency_symbol . number_format($tx['amount'] ?? 0, 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sent Message Log -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Message Log</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>To Number</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($message_log)): ?>
                            <tr><td colspan="5" class="text-center">No messages sent recently.</td></tr>
                        <?php else: ?>
                            <?php foreach($message_log as $log): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i', strtotime($log['created_at'] ?? time())); ?></td>
                                    <td><?php echo htmlspecialchars($log['phone_number'] ?? 'N/A'); ?></td>
                                    <td><small><?php echo htmlspecialchars(substr($log['message'] ?? '', 0, 100)); ?>...</small></td>
                                    <td><span class="badge bg-<?php echo ($log['status'] ?? 'failed') == 'sent' ? 'success' : 'danger'; ?>"><?php echo ucfirst($log['status'] ?? 'failed'); ?></span></td>
                                    <td><?php echo rtrim(rtrim(number_format($log['cost'] ?? 0, 4), '0'), '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('purchaseCreditForm');
    if (form) {
        const buttons = form.querySelectorAll('button[data-gateway]');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const gatewayUrl = this.getAttribute('data-gateway');
                form.setAttribute('action', gatewayUrl);
                form.submit();
            });
        });
    }

    const creditQuantityInput = document.getElementById('credit_quantity');
    const bankTransferLink = document.getElementById('bankTransferLink');
    if (creditQuantityInput && bankTransferLink) {
        creditQuantityInput.addEventListener('input', function() {
            const quantity = this.value > 0 ? this.value : 100;
            bankTransferLink.href = `/views/school_admin/sms_bank_transfer.php?quantity=${quantity}`;
        });
    }
});
</script>

<?php include 'partials/footer.php'; ?>