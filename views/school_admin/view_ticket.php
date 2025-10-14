<?php
$page_title = "View Support Ticket";
include 'partials/header.php';

if (!isset($_GET['id'])) {
    redirect_with_error('/views/school_admin/support_tickets.php', 'No ticket ID specified.');
}

$ticket_id = $_GET['id'];
$school_id = $_SESSION['school_id'];

// Fetch the ticket ensuring it belongs to the current school
$stmt = $pdo->prepare("SELECT t.*, u.full_name FROM support_tickets t JOIN users u ON t.user_id = u.id WHERE t.id = :id AND t.school_id = :school_id");
$stmt->execute(['id' => $ticket_id, 'school_id' => $school_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    redirect_with_error('/views/school_admin/support_tickets.php', 'Ticket not found or you do not have permission to view it.');
}

// Fetch replies for this ticket
$stmt_replies = $pdo->prepare("SELECT r.*, u.full_name, u.role FROM support_ticket_replies r JOIN users u ON r.user_id = u.id WHERE r.ticket_id = :ticket_id ORDER BY r.created_at ASC");
$stmt_replies->execute(['ticket_id' => $ticket_id]);
$replies = $stmt_replies->fetchAll(PDO::FETCH_ASSOC);

// Get the first message of the ticket
$first_message = $replies[0] ?? null;
if ($first_message) {
    // Remove the first message from the replies array so we don't display it twice
    array_shift($replies);
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Ticket #<?php echo $ticket['id']; ?>: <?php echo htmlspecialchars($ticket['subject']); ?></h1>
    <p class="mb-4">
        <span class="badge bg-<?php echo $ticket['status'] === 'closed' ? 'danger' : ($ticket['status'] === 'in_progress' ? 'warning' : 'success'); ?>">
            <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
        </span>
        <span class="text-muted"> | Created on <?php echo date('F j, Y', strtotime($ticket['created_at'])); ?> by <?php echo htmlspecialchars($ticket['full_name']); ?></span>
    </p>

    <!-- Initial Message -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Original Message</h6>
        </div>
        <div class="card-body">
            <?php echo nl2br(htmlspecialchars($first_message['message'] ?? 'No message found.')); ?>
        </div>
    </div>

    <!-- Replies -->
    <?php foreach ($replies as $reply): ?>
        <div class="card shadow mb-4 <?php echo $reply['role'] === 'super_admin' ? 'border-left-primary' : 'border-left-info'; ?>">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <?php echo htmlspecialchars($reply['full_name']); ?>
                    (<?php echo ucfirst(str_replace('_', ' ', $reply['role'])); ?>)
                </h6>
                <span class="text-muted small"><?php echo date('F j, Y, g:i a', strtotime($reply['created_at'])); ?></span>
            </div>
            <div class="card-body">
                <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Reply Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Add a Reply</h6>
        </div>
        <div class="card-body">
            <form action="/controllers/support_controller.php" method="POST">
                <input type="hidden" name="action" value="add_reply">
                <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                <div class="mb-3">
                    <textarea class="form-control" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Reply</button>
                <?php if ($ticket['status'] === 'closed'): ?>
                    <div class="alert alert-info mt-3">This ticket is currently closed. Submitting a reply will automatically reopen it.</div>
                <?php endif; ?>
            </form>
        </div>
    </div>

</div>

<?php include 'partials/footer.php'; ?>
