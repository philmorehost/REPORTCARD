<?php
$page_title = "Sender ID Management";
include 'partials/header.php'; // Includes auth, db, etc.

$school_id = $_SESSION['school_id'];

// Fetch the school's current sender ID if it exists
$stmt = $pdo->prepare("SELECT sender_id FROM schools WHERE id = :id");
$stmt->execute(['id' => $school_id]);
$current_sender_id = $stmt->fetchColumn();

?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">SMS Sender ID Management</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6">
            <!-- Register New Sender ID -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Register a New Sender ID</h6>
                </div>
                <div class="card-body">
                    <p>Submit a new Sender ID for approval. Note that Sender IDs are subject to review by the SMS provider and may take some time to be approved.</p>
                    <form action="/controllers/sender_id_controller.php" method="POST">
                        <input type="hidden" name="action" value="register_sender_id">
                        <div class="mb-3">
                            <label for="sender_id" class="form-label">Sender ID (max 11 characters)</label>
                            <input type="text" class="form-control" id="sender_id" name="sender_id" maxlength="11" required>
                        </div>
                        <div class="mb-3">
                            <label for="sample_message" class="form-label">Sample Message</label>
                            <textarea class="form-control" id="sample_message" name="sample_message" rows="3" required></textarea>
                            <div class="form-text">Provide a typical example of a message you will send with this ID.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit for Approval</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- Check Sender ID Status -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Check Sender ID Status</h6>
                </div>
                <div class="card-body">
                    <p>If you have already submitted a Sender ID, you can check its approval status here.</p>
                    <form action="/controllers/sender_id_controller.php" method="POST">
                        <input type="hidden" name="action" value="check_sender_id_status">
                        <div class="mb-3">
                            <label for="sender_id_check" class="form-label">Sender ID to Check</label>
                            <input type="text" class="form-control" id="sender_id_check" name="sender_id" value="<?php echo htmlspecialchars($current_sender_id ?: ''); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-secondary">Check Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>