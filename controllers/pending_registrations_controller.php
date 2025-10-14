<?php
/**
 * pending_registrations_controller.php - Handles approving/rejecting new school registrations.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';
require_once __DIR__ . '/../core/email.php'; // Include email function

// Ensure only super admins can execute these actions
require_auth('super_admin');

$action = $_GET['action'] ?? '';
$school_id = $_GET['school_id'] ?? 0;
$transaction_id = $_GET['transaction_id'] ?? 0;

if (!$school_id) {
    redirect_with_message('Invalid School ID specified.', 'error');
}

switch ($action) {
    case 'approve':
        handle_approve($pdo, $school_id, $transaction_id);
        break;
    case 'reject':
        handle_reject($pdo, $school_id, $transaction_id);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error');
}

function handle_approve($pdo, $school_id, $transaction_id) {
    if (!$transaction_id) {
        redirect_with_message('Invalid Transaction ID specified.', 'error');
    }

    try {
        $pdo->beginTransaction();

        // Fetch transaction and school admin details for email
        $stmt_trans = $pdo->prepare("SELECT pt.description, u.email as admin_email, u.full_name as admin_name, s.name as school_name FROM payment_transactions pt JOIN schools s ON pt.school_id = s.id JOIN users u ON s.id = u.school_id WHERE pt.id = :id AND u.role = 'school_admin'");
        $stmt_trans->execute(['id' => $transaction_id]);
        $trans_info = $stmt_trans->fetch(PDO::FETCH_ASSOC);

        preg_match('/(\d+)/', $trans_info['description'], $matches);
        $slots_to_add = $matches[0] ?? 0;

        // 1. Activate the school, add slots, and set their subscription expiry
        $expiry_date = date('Y-m-d H:i:s', strtotime('+120 days'));
        $stmt_school = $pdo->prepare("UPDATE schools SET status = 'active', student_slots = :slots, subscription_expires_at = :expiry WHERE id = :id AND status = 'pending_payment'");
        $stmt_school->execute(['slots' => $slots_to_add, 'expiry' => $expiry_date, 'id' => $school_id]);

        // 2. Mark the transaction as completed
        $stmt_payment = $pdo->prepare("UPDATE payment_transactions SET status = 'completed', reviewed_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt_payment->execute(['id' => $transaction_id]);

        // 3. Send notification email
        $subject = "Your Account is Activated! Welcome to " . s_get($pdo, 'site_name');
        $body = "
            <p>Dear {$trans_info['admin_name']},</p>
            <p>Congratulations! Your payment has been confirmed and your account for <strong>{$trans_info['school_name']}</strong> has been activated.</p>
            <p>Your subscription is active until " . date('F j, Y', strtotime($expiry_date)) . ".</p>
            <p>You can now log in to your School Admin dashboard to begin setting up your school.</p>
            <p>Thank you for choosing our platform.</p>
        ";
        send_email_simulation($pdo, $trans_info['admin_email'], $subject, $body);

        $pdo->commit();
        redirect_with_message('School registration approved and account activated. A welcome email has been sent.', 'success');

    } catch (PDOException $e) {
        $pdo->rollBack();
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function handle_reject($pdo, $school_id, $transaction_id) {
     if (!$transaction_id) {
        redirect_with_message('Invalid Transaction ID specified.', 'error');
    }
    try {
        $pdo->beginTransaction();

        // 1. Mark transaction as rejected
        $stmt_payment = $pdo->prepare("UPDATE payment_transactions SET status = 'rejected', reviewed_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt_payment->execute(['id' => $transaction_id]);

        // 2. Delete the pending school. ON DELETE CASCADE will handle the user account.
        $stmt = $pdo->prepare("DELETE FROM schools WHERE id = :id AND status = 'pending_payment'");
        $stmt->execute(['id' => $school_id]);

        if ($stmt->rowCount() > 0) {
            $pdo->commit();
            redirect_with_message('Registration rejected and all associated data has been deleted.', 'success');
        } else {
            throw new Exception('Could not find the pending registration to reject. It may have already been processed.');
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        redirect_with_message('Error: ' . $e->getMessage(), 'error');
    }
}

/**
 * Redirects back to the pending registrations page with a message.
 */
function redirect_with_message($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: /views/super_admin/pending_registrations.php');
    exit;
}
?>