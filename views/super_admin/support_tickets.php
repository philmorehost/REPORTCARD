<?php
$page_title = "All Support Tickets";
include 'partials/header.php';

// Fetch all tickets from all schools
$stmt = $pdo->prepare("SELECT t.*, s.name as school_name FROM support_tickets t JOIN schools s ON t.school_id = s.id ORDER BY t.updated_at DESC");
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">All Support Tickets</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tickets from All Schools</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>School</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No support tickets have been submitted yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['school_name']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $ticket['status'] === 'closed' ? 'danger' : ($ticket['status'] === 'in_progress' ? 'warning' : 'success'); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('F j, Y, g:i a', strtotime($ticket['updated_at'])); ?></td>
                                    <td>
                                        <a href="/views/super_admin/view_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-info btn-sm">View</a>
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
