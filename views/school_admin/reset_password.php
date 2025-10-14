<?php
// This is a public page for the school admin to reset their password.
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die("No reset token provided.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - ARS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f0f2f5;
        }
        .reset-password-card {
            width: 100%;
            max-width: 420px;
            padding: 2rem;
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="card reset-password-card">
        <div class="text-center mb-4">
            <h1>Reset Your Password</h1>
            <p class="text-muted">Enter and confirm your new password below.</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="/controllers/password_reset_controller.php?action=reset_password" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password" required>
                <label for="new_password">New Password</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm New Password" required>
                <label for="confirm_password">Confirm New Password</label>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Reset Password</button>
            </div>
        </form>
    </div>
</body>
</html>
