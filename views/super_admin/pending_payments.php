<?php
$page_title = "Pending Manual Payments";
include 'partials/header.php'; // Includes auth, db, etc.

// Fetch all pending manual payment transactions
try {
    $search_term = $_GET['search'] ?? '';

    $sql = "SELECT pt.*, s.name as school_name
            FROM payment_transactions pt
            JOIN schools s ON pt.school_id = s.id
            WHERE pt.payment_method = 'Bank Transfer' AND pt.status = 'pending'";

    $params = [];

    if (!empty($search_term)) {
        $sql .= " AND (s.name LIKE :search OR pt.description LIKE :search)";
        $params[':search'] = '%' . $search_term . '%';
    }

    $sql .= " ORDER BY pt.created_at ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pending_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pending_payments = [];
    echo '<div class="alert alert-danger">Could not fetch pending payments. Error: ' . $e->getMessage() . '</div>';
}

?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Pending Manual Payments</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Search Payments</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-center">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="search" placeholder="Search by School Name or Description..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="/views/super_admin/pending_payments.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Awaiting Review (<?php echo count($pending_payments); ?>)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date Submitted</th>
                            <th>School Name</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Proof</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pending_payments)): ?>
                            <tr><td colspan="6" class="text-center">No pending payments found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($pending_payments as $payment): ?>
                            <tr>
                                <td><?php echo date('Y-m-d H:i', strtotime($payment['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['school_name']); ?></td>
                                <td><?php echo htmlspecialchars($payment['description']); ?></td>
                                <td><?php echo get_currency_symbol($pdo) . number_format($payment['amount'], 2); ?></td>
                                <td>
                                    <a href="/<?php echo htmlspecialchars($payment['proof_url']); ?>" class="btn btn-sm btn-info" target="_blank">
                                        <i class="bi bi-eye-fill"></i> View Proof
                                    </a>
                                </td>
                                <td>
                                    <a href="/controllers/payment_approval_controller.php?action=approve&id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this payment and credit the school\'s account?');">
                                        <i class="bi bi-check-circle-fill"></i> Approve
                                    </a>
                                    <a href="/controllers/payment_approval_controller.php?action=decline&id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to decline this payment?');">
                                        <i class="bi bi-x-circle-fill"></i> Decline
                                    </a>
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