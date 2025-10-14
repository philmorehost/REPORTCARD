<?php
/**
 * verify_email.php - Handles the email verification process.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/core/init.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $message = "No verification token provided.";
    $status = 'error';
} else {
    try {
        $stmt = $pdo->prepare("SELECT id, full_name, role, school_id FROM users WHERE email_verification_token = :token AND status = 'unverified'");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $stmt_update = $pdo->prepare("UPDATE users SET status = 'active', email_verified_at = NOW(), email_verification_token = NULL WHERE id = :id");
            $stmt_update->execute([':id' => $user['id']]);

            // Automatically log the user in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['full_name'];
            if ($user['school_id']) {
                $_SESSION['school_id'] = $user['school_id'];
            }

            // Redirect to the dashboard
            $redirect_path = [
                'super_admin' => '/views/super_admin/dashboard.php',
                'school_admin' => '/views/school_admin/dashboard.php',
                'teacher' => '/views/teacher/dashboard.php'
            ];
            header('Location: ' . ($redirect_path[$user['role']] ?? '/index.php'));
            exit;

        } else {
            $message = "Invalid or expired verification token. Please try registering again or contact support.";
            $status = 'error';
        }
    } catch (PDOException $e) {
        $message = "Database error. Please try again later.";
        $status = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - ARS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .verification-container { max-width: 500px; margin: 5rem auto; }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="card shadow">
            <div class="card-body p-5 text-center">
                <?php if ($status === 'success'): ?>
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    <h1 class="h3 mt-3">Verification Successful!</h1>
                <?php else: ?>
                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                    <h1 class="h3 mt-3">Verification Failed</h1>
                <?php endif; ?>
                <p class="lead text-muted"><?php echo htmlspecialchars($message); ?></p>
                <hr>
                <a href="/views/school_admin/login.php" class="btn btn-primary">Proceed to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
