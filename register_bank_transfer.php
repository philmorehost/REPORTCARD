<?php
// This is the page shown after selecting bank transfer.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/core/init.php';

// If the user somehow gets here without a pending school ID, redirect them.
if (!isset($_SESSION['pending_school_id'])) {
    header('Location: /register.php');
    exit;
}

// Fetch bank details from settings to display
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'manual_transfer_details'");
    $stmt->execute();
    $bank_details = $stmt->fetchColumn();
} catch (PDOException $e) {
    $bank_details = "Bank details could not be loaded. Please contact support.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Transfer Instructions - ARS Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .registration-container { max-width: 600px; margin: 4rem auto; }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="text-center mb-4">
            <h1 class="h2">Final Step: Bank Transfer</h1>
            <p class="lead">Step 3 of 3: Upload Proof of Payment</p>
        </div>

        <div class="card shadow">
            <div class="card-body p-4">
                <div class="alert alert-info">
                    <h5 class="alert-heading">Payment Instructions</h5>
                    <p>Your account has been created and is pending approval. To activate your school, please make a deposit to the following bank account:</p>
                    <pre class="bg-light p-3 rounded"><?php echo htmlspecialchars($bank_details); ?></pre>
                    <p class="mb-0">After making the payment, please upload a screenshot or deposit slip below. Your account will be activated by an administrator within 24 hours of confirming your payment.</p>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <form action="/controllers/registration_controller.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_proof">
                    <div class="mb-3">
                        <label for="payment_proof" class="form-label">Upload Proof of Payment</label>
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
</body>
</html>