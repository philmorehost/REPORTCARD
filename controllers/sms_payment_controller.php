<?php
/**
 * sms_payment_controller.php - Handles manual bank transfer submissions for SMS credits.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only school admins can execute these actions
require_auth('school_admin');

$action = $_POST['action'] ?? '';

if ($action === 'upload_proof') {
    handle_upload_proof($pdo);
} else {
    redirect_with_message('Invalid action.', 'error');
}

/**
 * Handles the upload of payment proof for a manual bank transfer.
 */
function handle_upload_proof($pdo) {
    $school_id = $_SESSION['school_id'];
    $credit_quantity = (int)($_POST['credit_quantity'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $reference = $_POST['payment_reference'] ?? '';

    // If the user doesn't provide a reference, generate a unique one to avoid constraint violations.
    if (empty($reference)) {
        $reference = 'manual-' . uniqid();
    }

    // --- Validation ---
    if ($credit_quantity <= 0 || $amount <= 0) {
        redirect_with_message('Invalid credit quantity or amount.', 'error');
    }

    if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
        redirect_with_message('File upload error. Please try again.', 'error');
    }

    // --- Handle File Upload ---
    $file = $_FILES['payment_proof'];
    $upload_dir = 'uploads/proofs/';
    $target_dir = APP_ROOT . '/' . $upload_dir;
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array($file_extension, $allowed_extensions)) {
        redirect_with_message('Invalid file type. Only JPG, PNG, and PDF are allowed.', 'error');
    }

    $new_filename = 'proof_' . $school_id . '_' . time() . '.' . $file_extension;
    $target_path = $target_dir . $new_filename;
    $db_path = $upload_dir . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        redirect_with_message('Failed to save the uploaded file.', 'error');
    }

    // --- Create Pending Transaction ---
    try {
        $description = "Bank Transfer for " . number_format($credit_quantity) . " SMS credits.";

        $stmt = $pdo->prepare(
            "INSERT INTO payment_transactions (school_id, description, amount, payment_method, status, reference, proof_url)
             VALUES (:school_id, :description, :amount, 'Bank Transfer', 'pending', :reference, :proof_url)"
        );

        $stmt->execute([
            ':school_id' => $school_id,
            ':description' => $description,
            ':amount' => $amount,
            ':reference' => $reference,
            ':proof_url' => $db_path
        ]);

        redirect_with_message('Your proof of payment has been submitted successfully. Your credits will be added after an administrator reviews the transaction.', 'success');

    } catch (PDOException $e) {
        // If DB insert fails, try to delete the uploaded file
        if (file_exists($target_path)) {
            unlink($target_path);
        }
        redirect_with_message('Database error: Could not record your transaction. ' . $e->getMessage(), 'error');
    }
}

/**
 * Redirects back to the SMS bank transfer page with a message.
 */
function redirect_with_message($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    // On success, redirect to the main sms page, on error, back to the form.
    $redirect_page = $type === 'success' ? 'sms.php' : 'sms_bank_transfer.php';
    header("Location: /views/school_admin/{$redirect_page}");
    exit;
}
?>