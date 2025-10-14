<?php
$page_title = "My Support Tickets";
include 'partials/header.php';

// Fetch tickets for the current school
$stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE school_id = :school_id ORDER BY updated_at DESC");
$stmt->execute(['school_id' => $_SESSION['school_id']]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Support Tickets</h1>
        <a href="/views/school_admin/create_ticket.php" class="btn btn-primary btn-icon-split">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text">Create New Ticket</span>
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Tickets</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="4" class="text-center">You have not created any support tickets.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $ticket['status'] === 'closed' ? 'danger' : ($ticket['status'] === 'in_progress' ? 'warning' : 'success'); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('F j, Y, g:i a', strtotime($ticket['updated_at'])); ?></td>
                                    <td>
                                        <a href="/views/school_admin/view_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-info btn-sm">View</a>
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
