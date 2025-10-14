<?php
/**
 * payment_controller.php - Handles callbacks from payment gateways and processes transactions.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';

// --- Route based on gateway ---
$gateway = $_GET['gateway'] ?? '';
$purchase_id = $_GET['purchase_id'] ?? null;

// A purchase ID is required.
if (!$purchase_id) {
    $_SESSION['error'] = 'Invalid payment callback. Purchase ID is missing.';
    header('Location: /index.php'); // Generic redirect
    exit;
}

// Find the transaction in the session.
$transaction_details = $_SESSION['payment_transactions'][$purchase_id] ?? null;

if (!$transaction_details) {
    error_log("CRITICAL: Session lost for purchase_id: {$purchase_id}. User may have paid but not received service.");
    redirect_with_error(
        'Your session has expired, but we see you are returning from a payment gateway. Please contact support to verify your payment status.',
        null
    );
}

switch ($gateway) {
    case 'flutterwave':
        handle_flutterwave_callback($pdo, $purchase_id, $transaction_details);
        break;
    case 'paystack':
        handle_paystack_callback($pdo, $purchase_id, $transaction_details);
        break;
    default:
        redirect_with_error('Invalid payment gateway specified.', $transaction_details);
}

/**
 * Handles the callback from Flutterwave.
 */
function handle_flutterwave_callback($pdo, $purchase_id, $transaction_details) {
    $transaction_id = $_GET['transaction_id'] ?? null;
    $tx_ref = $_GET['tx_ref'] ?? null;

    if (!$transaction_id || $tx_ref !== $purchase_id) {
        redirect_with_error('Invalid transaction reference. Payment verification failed.', $transaction_details);
    }

    $flutterwave_secret_key = s_get($pdo, 'flutterwave_secret_key');
    if (empty($flutterwave_secret_key)) {
        redirect_with_error('Flutterwave payment gateway is not configured.', $transaction_details);
    }

    $ch = curl_init('https://api.flutterwave.com/v3/transactions/' . urlencode($transaction_id) . '/verify');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $flutterwave_secret_key,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    // Format both amounts to a consistent string with 2 decimal places to avoid float precision errors.
    $remote_amount_formatted = number_format((float)($result['data']['amount'] ?? 0.0), 2, '.', '');
    $local_amount_formatted = number_format((float)($transaction_details['amount'] ?? 0.0), 2, '.', '');
    $is_amount_matched = ($remote_amount_formatted === $local_amount_formatted);

    if (($result['status'] ?? '') === 'success' &&
        ($result['data']['tx_ref'] ?? '') === $purchase_id &&
        $is_amount_matched &&
        ($result['data']['currency'] ?? '') === $transaction_details['currency']) {

        process_successful_payment($pdo, $purchase_id, $transaction_details, $transaction_id);
    } else {
        error_log('Flutterwave Verification Failed: ' . $response);
        redirect_with_error('Payment verification failed. Please contact support.', $transaction_details);
    }
}

/**
 * Handles the callback from Paystack.
 */
function handle_paystack_callback($pdo, $purchase_id, $transaction_details) {
    $reference = $_GET['reference'] ?? null;

    if (!$reference || $reference !== $purchase_id) {
        redirect_with_error('Invalid transaction reference. Payment verification failed.', $transaction_details);
    }

    $paystack_secret_key = s_get($pdo, 'paystack_secret_key');
    if (empty($paystack_secret_key)) {
        redirect_with_error('Paystack payment gateway is not configured.', $transaction_details);
    }

    $ch = curl_init('https://api.paystack.co/transaction/verify/' . urlencode($reference));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $paystack_secret_key,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    // Use round() and compare against `requested_amount` to prevent float/fee errors.
    $is_amount_matched = isset($result['data']['requested_amount']) && (int)$result['data']['requested_amount'] === (int)round($transaction_details['amount'] * 100);

    if (($result['status'] ?? false) === true &&
        ($result['data']['status'] ?? '') === 'success' &&
        ($result['data']['reference'] ?? '') === $purchase_id &&
        $is_amount_matched) {

        process_successful_payment($pdo, $purchase_id, $transaction_details, $reference);
    } else {
        error_log('Paystack Verification Failed: ' . $response);
        redirect_with_error('Payment verification failed. Please contact support.', $transaction_details);
    }
}

/**
 * Processes the payment after successful verification.
 */
function process_successful_payment($pdo, $purchase_id, $details, $transaction_ref) {
    try {
        $pdo->beginTransaction();

        $purchase_type = $details['type'] ?? 'slot_purchase';
        $school_id = $details['school_id'] ?? null;

        if (empty($school_id) && !($details['is_registration'] ?? false)) {
            throw new Exception("School ID is missing for an existing school transaction.");
        }

        if ($purchase_type === 'sms_credits') {
            // Check if this transaction has already been processed
            $stmt_check = $pdo->prepare("SELECT id FROM payment_transactions WHERE reference = :ref");
            $stmt_check->execute([':ref' => $transaction_ref]);
            if ($stmt_check->fetch()) {
                redirect_to_success_page($details, $purchase_id); // Already processed
                return;
            }

            // 1. Create a general payment transaction record
            $description = "Purchase of " . number_format($details['credits']) . " SMS credits.";
            $stmt_payment = $pdo->prepare(
                "INSERT INTO payment_transactions (school_id, description, amount, payment_method, status, reference)
                 VALUES (:sid, :desc, :amount, :method, 'completed', :ref)"
            );
            $stmt_payment->execute([
                ':sid' => $school_id,
                ':desc' => $description,
                ':amount' => $details['amount'],
                ':method' => ucfirst($_GET['gateway']),
                ':ref' => $transaction_ref
            ]);
            $payment_transaction_id = $pdo->lastInsertId();

            // 2. Create the specific SMS transaction record, linking it to the general payment
            $stmt_sms = $pdo->prepare(
                "INSERT INTO sms_transactions (school_id, payment_transaction_id, credits_purchased, amount, payment_method, status, reference)
                 VALUES (:sid, :ptid, :credits, :amount, :method, 'completed', :ref)"
            );
            $stmt_sms->execute([
                ':sid' => $school_id,
                ':ptid' => $payment_transaction_id,
                ':credits' => $details['credits'],
                ':amount' => $details['amount'],
                ':method' => ucfirst($_GET['gateway']),
                ':ref' => $transaction_ref . '_sms' // Make reference unique
            ]);

            // 3. Update school's credit balance
            $stmt_update = $pdo->prepare("UPDATE schools SET sms_credits = sms_credits + :credits WHERE id = :id");
            $stmt_update->execute([':credits' => $details['credits'], ':id' => $school_id]);

        } elseif ($purchase_type === 'slot_purchase') {
            // Check if this transaction has already been processed
            $stmt_check = $pdo->prepare("SELECT id FROM payment_transactions WHERE reference = :ref");
            $stmt_check->execute([':ref' => $transaction_ref]);
            if ($stmt_check->fetch()) {
                redirect_to_success_page($details, $purchase_id);
                return;
            }

            $is_registration = $details['is_registration'] ?? false;
            $description = '';

            if ($is_registration) {
                if (!isset($details['registration_data'])) {
                    throw new Exception("Registration data missing for new school payment.");
                }
                $reg_data = $details['registration_data'];
                $stmt = $pdo->prepare("INSERT INTO schools (name, package_id, address, student_slots, subscription_expires_at, status) VALUES (:name, :pid, :address, :slots, DATE_ADD(NOW(), INTERVAL 3 MONTH), 'active')");
                $stmt->execute([':name' => $reg_data['school_name'], ':pid' => $reg_data['package_id'], ':address' => $reg_data['school_address'], ':slots' => $details['slots']]);
                $school_id = $pdo->lastInsertId();
                $stmt_user = $pdo->prepare("INSERT INTO users (school_id, full_name, email, password, role) VALUES (:sid, :name, :email, :pass, 'school_admin')");
                $stmt_user->execute([':sid' => $school_id, ':name' => $reg_data['admin_name'], ':email' => $reg_data['admin_email'], ':pass' => password_hash($reg_data['admin_password'], PASSWORD_BCRYPT)]);
                $description = "Purchase of {$details['slots']} student slots during registration.";
            } else {
                // 'slot_purchase' for existing school
                $stmt_update = $pdo->prepare("UPDATE schools SET student_slots = student_slots + :slots, status = 'active' WHERE id = :id");
                $stmt_update->execute([':slots' => $details['slots'], ':id' => $school_id]);
                $description = "Purchase of additional " . number_format($details['slots']) . " student slots.";
            }

            $stmt_trans = $pdo->prepare("INSERT INTO payment_transactions (school_id, description, amount, payment_method, status, reference) VALUES (:sid, :desc, :amount, :method, 'completed', :ref)");
            $stmt_trans->execute([':sid' => $school_id, ':desc' => $description, ':amount' => $details['amount'], ':method' => ucfirst($_GET['gateway']), ':ref' => $transaction_ref]);
        }

        $pdo->commit();
        redirect_to_success_page($details, $purchase_id);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Construct a more detailed error message for logging
        $error_info = "Payment Processing Error: " . $e->getMessage() . " | Details: " . json_encode($details) . " | Ref: " . $transaction_ref;
        error_log($error_info);
        $debug_message = " (Debug Info: " . $e->getMessage() . ")";
        redirect_with_error('A database error occurred while processing your payment. Please contact support.' . $debug_message, $details);
    }
}

/**
 * Redirects user to the correct success page and cleans up session.
 */
function redirect_to_success_page($details, $purchase_id) {
    $purchase_type = $details['type'] ?? 'slot_purchase';
    $is_registration = $details['is_registration'] ?? false;

    $redirect_url = '/views/school_admin/dashboard.php'; // Default
    $success_message = '';

    if ($is_registration) {
        $redirect_url = '/register_success.php';
        $success_message = "Registration successful! Your school account has been created and " . $details['slots'] . " slots have been added.";
    } elseif ($purchase_type === 'sms_credits') {
        $redirect_url = '/views/school_admin/sms.php';
        $success_message = "Successfully purchased " . number_format($details['credits']) . " SMS credits!";
    } else { // Slot purchase for existing school
        $redirect_url = '/views/school_admin/billing.php';
        $success_message = "Successfully added " . number_format($details['slots']) . " student slots to your account!";
    }

    unset($_SESSION['payment_transactions'][$purchase_id]);
    $_SESSION['message'] = $success_message;
    $_SESSION['message_type'] = 'success';
    header("Location: {$redirect_url}");
    exit;
}

/**
 * Redirects with an error message.
 */
function redirect_with_error($message, $details) {
    $is_registration = $details['is_registration'] ?? false;
    $purchase_type = $details['type'] ?? 'slot_purchase';

    $redirect_path = '/views/school_admin/dashboard.php'; // Fallback
    if ($details === null) {
        $redirect_path = isset($_SESSION['user_id']) ? '/views/school_admin/dashboard.php' : '/register.php';
    } elseif ($is_registration) {
        $redirect_path = '/register_purchase.php';
    } else {
        if ($purchase_type === 'sms_credits') {
            $redirect_path = '/views/school_admin/sms.php';
        } else {
            $redirect_path = '/views/school_admin/billing.php';
        }
    }

    $_SESSION['error'] = $message;
    header("Location: {$redirect_path}");
    exit;
}
?>