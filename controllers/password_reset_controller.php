<?php
/**
 * password_reset_controller.php - Handles the password reset process.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'request_reset':
        handle_request_reset($pdo);
        break;
    case 'reset_password':
        handle_reset_password($pdo);
        break;
    default:
        header('Location: /index.php');
        exit;
}

function handle_request_reset($pdo) {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        header('Location: /views/school_admin/forgot_password.php?error=' . urlencode('Invalid email format.'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = :email AND role = 'school_admin' AND status = 'active'");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt_update = $pdo->prepare("UPDATE users SET password_reset_token = :token, password_reset_expires_at = :expires WHERE id = :id");
            $stmt_update->execute(['token' => $token, 'expires' => $expires, 'id' => $user['id']]);

            // Send password reset email
            $reset_link = get_site_url() . "/views/school_admin/reset_password.php?token=" . $token;
            $subject = "Password Reset Request";
            $body = "
                <p>Hi " . htmlspecialchars($user['full_name']) . ",</p>
                <p>You recently requested to reset your password. Click the link below to proceed:</p>
                <p><a href='" . $reset_link . "'>" . $reset_link . "</a></p>
                <p>This link will expire in 1 hour. If you did not request a password reset, please ignore this email.</p>
            ";
            send_email($pdo, $email, $subject, $body);
        }

        // Always show a success message to prevent user enumeration
        header('Location: /views/school_admin/forgot_password.php?success=' . urlencode('If an account with that email exists, a password reset link has been sent.'));
        exit;

    } catch (PDOException $e) {
        header('Location: /views/school_admin/forgot_password.php?error=' . urlencode('Database error. Please try again later.'));
        exit;
    }
}

function handle_reset_password($pdo) {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($new_password) || empty($confirm_password)) {
        header('Location: /views/school_admin/reset_password.php?token=' . urlencode($token) . '&error=' . urlencode('All fields are required.'));
        exit;
    }

    if ($new_password !== $confirm_password) {
        header('Location: /views/school_admin/reset_password.php?token=' . urlencode($token) . '&error=' . urlencode('Passwords do not match.'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE password_reset_token = :token AND password_reset_expires_at > NOW()");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt_update = $pdo->prepare("UPDATE users SET password = :password, password_reset_token = NULL, password_reset_expires_at = NULL WHERE id = :id");
            $stmt_update->execute(['password' => $hashed_password, 'id' => $user['id']]);

            header('Location: /views/school_admin/login.php?success=' . urlencode('Your password has been reset successfully. You can now log in.'));
            exit;
        } else {
            header('Location: /views/school_admin/reset_password.php?token=' . urlencode($token) . '&error=' . urlencode('Invalid or expired password reset token.'));
            exit;
        }
    } catch (PDOException $e) {
        header('Location: /views/school_admin/reset_password.php?token=' . urlencode($token) . '&error=' . urlencode('Database error. Please try again later.'));
        exit;
    }
}

?>
