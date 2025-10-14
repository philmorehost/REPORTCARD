<?php
$page_title = "Create Support Ticket";
include 'partials/header.php';
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Create New Support Ticket</h1>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 fw-bold text-primary">New Ticket Details</h6>
        </div>
        <div class="card-body">
            <form action="/controllers/support_controller.php" method="POST">
                <input type="hidden" name="action" value="create_ticket">
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                    <div class="form-text">Please describe your issue in as much detail as possible.</div>
                </div>
                <button type="submit" class="btn btn-primary">Submit Ticket</button>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
