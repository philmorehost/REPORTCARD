<?php
$page_title = "Pending Registrations";
include 'partials/header.php';
require_once __DIR__ . '/../../config/config.php';

// Check for session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

// Fetch all pending schools and their payment transactions
try {
    $search_term = $_GET['search'] ?? '';

    $sql = "SELECT s.id as school_id, s.name, s.created_at, pt.proof_url, pt.id as transaction_id
            FROM schools s
            JOIN payment_transactions pt ON s.id = pt.school_id
            WHERE s.status = 'pending_payment' AND pt.status = 'pending'";

    $params = [];

    if (!empty($search_term)) {
        $sql .= " AND s.name LIKE :search";
        $params[':search'] = '%' . $search_term . '%';
    }

    $sql .= " ORDER BY s.created_at ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pending_schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pending_schools = [];
    echo '<div class="alert alert-danger">Could not load pending registrations. Error: ' . $e->getMessage() . '</div>';
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Pending School Registrations</h1>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Search Registrations</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-center">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="search" placeholder="Search by School Name..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="/views/super_admin/pending_registrations.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Schools Awaiting Payment Approval (<?php echo count($pending_schools); ?>)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>School Name</th>
                            <th>Registration Date</th>
                            <th>Proof of Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pending_schools)): ?>
                            <tr><td colspan="4" class="text-center">No pending registrations found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($pending_schools as $school): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($school['name']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($school['created_at'])); ?></td>
                                <td>
                                    <a href="/<?php echo htmlspecialchars($school['proof_url']); ?>" target="_blank" class="btn btn-sm btn-info">View Proof</a>
                                </td>
                                <td>
                                    <a href="/controllers/pending_registrations_controller.php?action=approve&school_id=<?php echo $school['school_id']; ?>&transaction_id=<?php echo $school['transaction_id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this registration?');">Approve</a>
                                    <a href="/controllers/pending_registrations_controller.php?action=reject&school_id=<?php echo $school['school_id']; ?>&transaction_id=<?php echo $school['transaction_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this registration? This will delete all associated data.');">Reject</a>
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