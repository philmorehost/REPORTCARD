<?php
// This is a public page for the school admin to request a password reset.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - ARS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f0f2f5;
        }
        .forgot-password-card {
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
    <div class="card forgot-password-card">
        <div class="text-center mb-4">
            <h1>Forgot Password</h1>
            <p class="text-muted">Enter your email address and we'll send you a link to reset your password.</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <form action="/controllers/password_reset_controller.php?action=request_reset" method="POST">
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="name@yourschool.com" required>
                <label for="email">Email address</label>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Send Password Reset Link</button>
            </div>
            <div class="text-center mt-3">
                <a href="login.php">Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>
