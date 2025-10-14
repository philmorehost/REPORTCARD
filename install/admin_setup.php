<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the config file exists, otherwise, the user shouldn't be here
if (!file_exists('../config/config.php')) {
    header('Location: /install/index.php');
    exit;
}
require_once '../config/config.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (!$email) {
        $error_message = "Invalid email format.";
    } elseif ($password !== $password_confirm) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    }

    if (empty($error_message)) {
        try {
            // 1. Create the Super Admin user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, role) VALUES (:email, :password, :full_name, 'super_admin')");
            $stmt->execute([
                'email' => $email,
                'password' => $hashed_password,
                'full_name' => $full_name
            ]);

            // 2. Insert default system settings
            $default_settings = [
                'site_name' => 'Automated Report Card System',
                'seo_description' => 'A web-based platform to eliminate the time-consuming and error-prone process of manually creating student report cards.',
                'site_logo_url' => '',
                'site_favicon_url' => '',
                'currency_symbol' => '$',
                'grace_period_days' => '7',
                'paystack_secret_key' => '', 'paystack_public_key' => '',
                'flutterwave_secret_key' => '', 'flutterwave_public_key' => '',
                'stripe_secret_key' => '', 'stripe_public_key' => '',
                'paypal_client_id' => '',
                'platform_currency' => 'USD', 'platform_language' => 'en',
                'price_per_student_term' => '5', 'price_per_student_semester' => '8',
                'paystack_enabled' => '0', 'flutterwave_enabled' => '0',
                'stripe_enabled' => '0', 'paypal_enabled' => '0',
                'manual_transfer_details' => 'Bank Name: ARS Global Inc.\nAccount Number: 1234567890',
                'pwa_enabled' => '1', 'cron_job_key' => bin2hex(random_bytes(16)), // Generate a random key
                'smtp_host' => '', 'smtp_port' => '587', 'smtp_username' => '', 'smtp_password' => '',
                'smtp_encryption' => 'tls', 'smtp_from_email' => '', 'smtp_from_name' => ''
            ];
            $stmt_settings = $pdo->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES (:key, :value)");
            foreach ($default_settings as $key => $value) {
                $stmt_settings->execute(['key' => $key, 'value' => $value]);
            }

            // If everything is successful, redirect to the completion page
            header('Location: /install/complete.php');
            exit;

        } catch (Exception $e) {
            $error_message = "An error occurred: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARS Installer - Admin Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .installer-container { max-width: 700px; margin: 5rem auto; }
        .card-header { background-color: #343a40; color: white; }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="text-center mb-4">
            <h1 class="h2">Automated Report Card System</h1>
            <p class="lead">Installation Wizard</p>
        </div>

        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">Step 3: Super Admin Account</h5>
            </div>
            <div class="card-body p-4">
                <p>You're almost there! Create the primary administrator account for your new platform.</p>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                     <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            Complete Installation <i class="bi bi-check-circle-fill ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>