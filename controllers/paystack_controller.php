<?php
/**
 * paystack_controller.php - Handles initializing a Paystack transaction.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';

// --- DEBUG: Log POST data ---
file_put_contents(__DIR__ . '/../debug_log.txt', "Paystack POST: " . print_r($_POST, true) . "\n", FILE_APPEND);

// --- Determine Context ---
$purchase_type = $_POST['purchase_type'] ?? 'slot_purchase';
$is_registration = false; // WhatsApp credits can't be bought during registration
$is_existing_school = isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'school_admin';
$school_id = $_SESSION['school_id'] ?? null;

if (!$is_existing_school) {
    die("Invalid access. You must be a logged-in school admin.");
}

// --- Initialize variables ---
$amount = 0;
$transaction_data = [];
$metadata = [];
$redirect_path = '/views/school_admin/dashboard.php'; // Default redirect

// --- Fetch customer email (common for all purchase types) ---
$stmt_email = $pdo->prepare("SELECT email FROM users WHERE school_id = :id AND role = 'school_admin'");
$stmt_email->execute(['id' => $school_id]);
$customer_email = $stmt_email->fetchColumn();

if (empty($customer_email)) {
    die("Could not find an admin email for this school.");
}


// --- Logic for different purchase types ---
if ($purchase_type === 'sms_credits') {
    $redirect_path = '/views/school_admin/sms.php';
    $credits_to_purchase = (int)($_POST['credit_quantity'] ?? 0);

    if ($credits_to_purchase <= 0) {
        $_SESSION['error'] = 'Invalid number of credits specified.';
        header("Location: {$redirect_path}");
        exit;
    }

    $price_per_credit = (float)s_get($pdo, 'sms_credit_cost', 0.05);
    $amount = $credits_to_purchase * $price_per_credit;

    $transaction_data = [
        'type' => 'sms_credits',
        'credits' => $credits_to_purchase,
        'amount' => $amount,
        'school_id' => $school_id,
        'is_registration' => false
    ];
    $metadata = ['purchase_type' => 'sms_credits', 'credits' => $credits_to_purchase];

} else { // Default to 'slot_purchase'
    $redirect_path = '/views/school_admin/billing.php';
    $slots_to_purchase = (int)($_POST['slot_quantity'] ?? 0);

    if ($slots_to_purchase <= 0) {
        $_SESSION['error'] = 'Invalid number of slots specified.';
        header("Location: {$redirect_path}");
        exit;
    }

    $stmt_price = $pdo->prepare("SELECT price FROM packages p JOIN schools s ON p.id = s.package_id WHERE s.id = :id");
    $stmt_price->execute(['id' => $school_id]);
    $price_per_slot = (float)$stmt_price->fetchColumn();

    if ($price_per_slot <= 0) {
        $_SESSION['error'] = 'This package is free or has no price. You cannot purchase slots.';
        header("Location: {$redirect_path}");
        exit;
    }

    $amount = $slots_to_purchase * $price_per_slot;

    $transaction_data = [
        'type' => 'slot_purchase',
        'slots' => $slots_to_purchase,
        'amount' => $amount,
        'school_id' => $school_id,
        'is_registration' => false
    ];
    $metadata = ['purchase_type' => 'slot_purchase', 'slots' => $slots_to_purchase];
}


// --- Calculate final amount with fees ---
$fee_percentage = 0;
if ($purchase_type === 'sms_credits') {
    $fee_percentage = (float)s_get($pdo, 'sms_payment_fee_percentage', 0);
} else { // slot_purchase
    $fee_percentage = (float)s_get($pdo, 'billing_payment_fee_percentage', 0);
}

if ($fee_percentage > 0) {
    $fee_amount = ($amount * $fee_percentage) / 100;
    $amount += $fee_amount;
}

// --- Prepare for Paystack API Call ---
$currency = s_get($pdo, 'currency_code', 'USD');
$amount_in_kobo = (int)round($amount * 100);

$paystack_secret_key = s_get($pdo, 'paystack_secret_key');
if (empty($paystack_secret_key)) {
    $_SESSION['error'] = 'Paystack payment gateway is not configured.';
    header("Location: {$redirect_path}");
    exit;
}

$reference = 'ARS-PS-' . time() . '-' . bin2hex(random_bytes(8));
$purchase_id = $reference;

// Store transaction details in session for verification after callback
$transaction_data['currency'] = $currency;
$transaction_data['amount'] = $amount; // Update with the final amount including fee
$_SESSION['payment_transactions'][$purchase_id] = $transaction_data;

// --- Call Paystack API ---
$metadata['purchase_id'] = $purchase_id;
$request_body = json_encode([
    'reference' => $reference,
    'amount' => $amount_in_kobo,
    'email' => $customer_email,
    'currency' => $currency,
    'callback_url' => APP_URL . '/controllers/payment_controller.php?gateway=paystack&purchase_id=' . $purchase_id,
    'metadata' => $metadata
]);

$ch = curl_init('https://api.paystack.co/transaction/initialize');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $paystack_secret_key,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    $_SESSION['error'] = 'Could not connect to payment gateway. cURL Error: ' . $error;
    header("Location: {$redirect_path}");
    exit;
}

$result = json_decode($response, true);

if (isset($result['status']) && $result['status'] === true) {
    // Force the session to be written before redirecting.
    session_write_close();
    // Redirect to the payment page
    header('Location: ' . $result['data']['authorization_url']);
    exit;
} else {
    // Handle API error
    $message = $result['message'] ?? 'An unknown error occurred with the payment gateway.';
    $_SESSION['error'] = 'Payment Gateway Error: ' . $message;
    error_log('Paystack API Error: ' . $response);
    header("Location: {$redirect_path}");
    exit;
}
?>