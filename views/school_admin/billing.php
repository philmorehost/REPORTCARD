<?php
$page_title = "Billing & Slots";
include 'partials/header.php'; // Includes auth, db, etc.

$school_id = $_SESSION['school_id'];

// --- Fetch billing-related data ---
try {
    // Get school info including package details
    $stmt_school = $pdo->prepare(
        "SELECT s.student_slots, s.status, s.subscription_expires_at, p.price as package_price, p.name as package_name
         FROM schools s
         LEFT JOIN packages p ON s.package_id = p.id
         WHERE s.id = :school_id"
    );
    $stmt_school->execute(['school_id' => $school_id]);
    $school_info = $stmt_school->fetch(PDO::FETCH_ASSOC);

    $total_slots = $school_info['student_slots'] ?? 0;
    $price_per_slot = (float)($school_info['package_price'] ?? 0);
    $package_name = $school_info['package_name'] ?? 'N/A';
    $is_freemium = (strtolower($package_name) === 'freemium');

    // Get active student count
    $stmt_students = $pdo->prepare("SELECT COUNT(id) FROM students WHERE school_id = :school_id AND status = 'active'");
    $stmt_students->execute(['school_id' => $school_id]);
    $used_slots = $stmt_students->fetchColumn();
    $available_slots = $total_slots - $used_slots;

    // Get real payment history
    $stmt_history = $pdo->prepare("SELECT * FROM payment_transactions WHERE school_id = :school_id ORDER BY created_at DESC");
    $stmt_history->execute(['school_id' => $school_id]);
    $payment_history = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

    // Get currency and gateway status
    $currency_symbol = get_currency_symbol($pdo);
    $paystack_enabled = (bool)s_get($pdo, 'paystack_enabled', 0);
    $flutterwave_enabled = (bool)s_get($pdo, 'flutterwave_enabled', 0);

} catch (PDOException $e) {
    $total_slots = 0; $used_slots = 0; $available_slots = 0; $payment_history = [];
    $paystack_enabled = false; $flutterwave_enabled = false; $is_freemium = false; $price_per_slot = 0;
    echo '<div class="alert alert-danger">Could not fetch billing data. Error: ' . $e->getMessage() . '</div>';
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Billing & Student Slots</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Slot Usage Summary -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4"><div class="card"><div class="card-body text-center"><div class="display-4 fw-bold text-primary"><?php echo number_format($total_slots); ?></div><div class="text-xs text-uppercase text-muted">Total Student Slots</div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="card"><div class="card-body text-center"><div class="display-4 fw-bold text-success"><?php echo number_format($used_slots); ?></div><div class="text-xs text-uppercase text-muted">Active Students (Used Slots)</div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="card"><div class="card-body text-center"><div class="display-4 fw-bold text-info"><?php echo number_format($available_slots); ?></div><div class="text-xs text-uppercase text-muted">Available Slots</div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="card"><div class="card-body text-center"><div class="display-4 fw-bold text-danger"><?php echo ($school_info['subscription_expires_at']) ? date('M d, Y', strtotime($school_info['subscription_expires_at'])) : 'N/A'; ?></div><div class="text-xs text-uppercase text-muted">Subscription Expires</div></div></div></div>
    </div>

    <div class="row">
        <!-- Purchase More Slots -->
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">
                        <?php echo $is_freemium ? 'Upgrade Your Plan' : 'Purchase More Slots'; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($is_freemium): ?>

                        <div class="alert alert-info">
                            <strong>You are on the Freemium Plan.</strong><br>
                            To enroll more students and unlock premium features, please upgrade your plan.
                        </div>

                        <h5>Available Premium Plans</h5>
                        <?php
                        $stmt_packages = $pdo->query("SELECT * FROM packages WHERE price > 0 ORDER BY price ASC");
                        $upgrade_packages = $stmt_packages->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="list-group">
                            <?php foreach($upgrade_packages as $pkg): ?>
                                <a href="/views/school_admin/upgrade_package.php?pkg_id=<?php echo $pkg['id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($pkg['name']); ?></h5>
                                        <small><?php echo $currency_symbol . number_format($pkg['price'], 2); ?> / student</small>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($pkg['description'] ?? 'No description available.'); ?></p>
                                </a>
                            <?php endforeach; ?>
                        </div>

                    <?php else: // This block is for PREMIUM users ?>

                        <p>Need to enroll more students? You can purchase additional student slots for your school at any time.</p>
                        <form id="purchaseForm" method="POST">
                            <div class="mb-3">
                                <label for="slot_quantity" class="form-label">Number of Slots to Purchase:</label>
                                <input type="number" class="form-control" name="slot_quantity" id="slot_quantity" value="50" min="1">
                            </div>
                            <div class="alert alert-info">
                                <strong>Price:</strong> <?php echo $currency_symbol; ?><?php echo number_format($price_per_slot, 2); ?> per student / termly
                            </div>

                            <div class="d-grid gap-2">
                                <?php if ($paystack_enabled || $flutterwave_enabled): ?>
                                    <?php if ($paystack_enabled): ?>
                                        <button type="button" class="btn btn-primary" data-gateway="/controllers/paystack_controller.php">Pay with Paystack</button>
                                    <?php endif; ?>
                                    <?php if ($flutterwave_enabled): ?>
                                        <button type="button" class="btn btn-warning" data-gateway="/controllers/flutterwave_controller.php">Pay with Flutterwave</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center">No online payment gateways are currently enabled. Please contact the administrator.</p>
                                <?php endif; ?>
                            </div>
                        </form>

                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Payment History</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Date</th><th>Description</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if (empty($payment_history)): ?>
                                    <tr><td colspan="5" class="text-center">No payment history found.</td></tr>
                                <?php else: ?>
                                    <?php foreach($payment_history as $payment): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d', strtotime($payment['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($payment['description']); ?></td>
                                        <td><?php echo $currency_symbol . number_format($payment['amount'], 2); ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                        <td><span class="badge bg-<?php echo $payment['status'] == 'completed' ? 'success' : ($payment['status'] == 'pending' ? 'warning' : 'danger'); ?>"><?php echo ucfirst($payment['status']); ?></span></td>
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
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('purchaseForm');
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
});
</script>
<?php include 'partials/footer.php'; ?>