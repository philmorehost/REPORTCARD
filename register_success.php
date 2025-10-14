<?php
// This is the final success page for the registration flow.
require_once __DIR__ . '/core/init.php';

$status = $_GET['status'] ?? 'online'; // 'online' or 'bank'
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Complete - ARS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .registration-container { max-width: 600px; margin: 4rem auto; }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="card shadow">
            <div class="card-body p-5 text-center">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>

                <?php if ($status === 'online'): ?>
                    <h1 class="mt-3">Registration Successful!</h1>
                    <p class="lead">Your account has been created and your school is now active.</p>
                    <p>You can now log in to your School Administrator dashboard to start setting up your school.</p>
                    <div class="d-grid mt-4">
                        <a href="/views/school_admin/login.php" class="btn btn-primary btn-lg">Go to Admin Login</a>
                    </div>
                <?php else: // 'bank' status ?>
                    <h1 class="mt-3">Submission Complete!</h1>
                    <p class="lead">Your proof of payment has been submitted for review.</p>
                    <p>Your account will be activated once an administrator confirms your payment. You will receive an email notification upon activation (typically within 24 hours).</p>
                    <div class="d-grid mt-4">
                        <a href="/" class="btn btn-secondary btn-lg">Back to Home Page</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</body>
</html>