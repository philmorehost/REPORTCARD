<?php
/**
 * payment_approval_controller.php - Handles approval and decline actions for manual payments.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only super admins can execute these actions
require_auth('super_admin');

$action = $_GET['action'] ?? '';
$payment_id = (int)($_GET['id'] ?? 0);

if (empty($action) || $payment_id <= 0) {
    redirect_with_message('Invalid action or payment ID.', 'error');
}

switch ($action) {
    case 'approve':
        handle_approve($pdo, $payment_id);
        break;
    case 'decline':
        handle_decline($pdo, $payment_id);
        break;
    default:
        redirect_with_message('Unknown action.', 'error');
}

/**
 * Approves a payment, updates its status, and credits the school's account.
 */
function handle_approve($pdo, $payment_id) {
    try {
        $pdo->beginTransaction();

        // 1. Fetch the transaction details
        $stmt = $pdo->prepare("SELECT * FROM payment_transactions WHERE id = :id AND status = 'pending'");
        $stmt->execute(['id' => $payment_id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            throw new Exception("Payment not found or already processed.");
        }

        // 2. Determine the number of credits from the description
        // This is a bit fragile but necessary with the current structure.
        // Assumes description is "Bank Transfer for X SMS credits."
        $credits = 0;
        if (preg_match('/for ([\d,]+) SMS credits/', $payment['description'], $matches)) {
            $credits = (int)str_replace(',', '', $matches[1]);
        }

        if ($credits <= 0) {
            throw new Exception("Could not determine the number of credits to add from the description.");
        }

        // 3. Credit the school's SMS account
        $stmt_credit = $pdo->prepare("UPDATE schools SET sms_credits = sms_credits + :credits WHERE id = :school_id");
        $stmt_credit->execute(['credits' => $credits, 'school_id' => $payment['school_id']]);

        // 4. Update the payment status to 'completed'
        $stmt_update = $pdo->prepare("UPDATE payment_transactions SET status = 'completed' WHERE id = :id");
        $stmt_update->execute(['id' => $payment_id]);

        $pdo->commit();
        redirect_with_message('Payment approved and credits added successfully.', 'success');

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        redirect_with_message('Error approving payment: ' . $e->getMessage(), 'error');
    }
}

/**
 * Declines a payment and updates its status.
 */
function handle_decline($pdo, $payment_id) {
    try {
        $stmt = $pdo->prepare("UPDATE payment_transactions SET status = 'declined' WHERE id = :id AND status = 'pending'");
        $stmt->execute(['id' => $payment_id]);

        if ($stmt->rowCount() > 0) {
            redirect_with_message('Payment has been declined.', 'success');
        } else {
            redirect_with_message('Payment not found or already processed.', 'error');
        }
    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

/**
 * Redirects back to the pending payments page with a message.
 */
function redirect_with_message($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: /views/super_admin/pending_payments.php");
    exit;
}
?>