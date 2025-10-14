<?php
/**
 * flutterwave_controller.php - Handles initializing a Flutterwave transaction.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';

// --- DEBUG: Log POST data ---
file_put_contents(__DIR__ . '/../debug_log.txt', "Flutterwave POST: " . print_r($_POST, true) . "\n", FILE_APPEND);

// --- Determine Context ---
$purchase_type = $_POST['purchase_type'] ?? 'slot_purchase';
$is_registration = false;
$is_existing_school = isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'school_admin';
$school_id = $_SESSION['school_id'] ?? null;

if (!$is_existing_school) {
    die("Invalid access. You must be a logged-in school admin.");
}

// --- Initialize variables ---
$amount = 0;
$transaction_data = [];
$metadata = [];
$customization_description = '';
$redirect_path = '/views/school_admin/dashboard.php';

// --- Fetch common customer details ---
$stmt_user = $pdo->prepare("SELECT email, full_name FROM users WHERE school_id = :id AND role = 'school_admin'");
$stmt_user->execute(['id' => $school_id]);
$customer = $stmt_user->fetch(PDO::FETCH_ASSOC);
if (!$customer) {
    die("Could not find an admin user for this school.");
}
$customer_email = $customer['email'];
$customer_name = $customer['full_name'];

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
    $customization_description = "Payment for {$credits_to_purchase} SMS credits.";

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
    $customization_description = "Payment for {$slots_to_purchase} student slots.";
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

// --- Prepare for Flutterwave API Call ---
$currency = s_get($pdo, 'currency_code', 'USD');
$flutterwave_secret_key = s_get($pdo, 'flutterwave_secret_key');
if (empty($flutterwave_secret_key)) {
    $_SESSION['error'] = 'Flutterwave payment gateway is not configured.';
    header("Location: {$redirect_path}");
    exit;
}

$reference = 'ARS-FLW-' . time() . '-' . bin2hex(random_bytes(8));
$purchase_id = $reference;

// Store transaction details in session for verification after callback
$transaction_data['currency'] = $currency;
$transaction_data['amount'] = $amount; // Update with the final amount including fee
$_SESSION['payment_transactions'][$purchase_id] = $transaction_data;

// --- Call Flutterwave API ---
$metadata['purchase_id'] = $purchase_id;
$request_body = json_encode([
    'tx_ref' => $reference,
    'amount' => $amount,
    'currency' => $currency,
    'redirect_url' => APP_URL . '/controllers/payment_controller.php?gateway=flutterwave&purchase_id=' . $purchase_id,
    'customer' => [
        'email' => $customer_email,
        'name' => $customer_name,
    ],
    'customizations' => [
        'title' => 'Automated Report Card System - Purchase',
        'description' => $customization_description,
    ],
    'meta' => $metadata
]);

$ch = curl_init('https://api.flutterwave.com/v3/payments');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $flutterwave_secret_key,
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

if (isset($result['status']) && $result['status'] === 'success') {
    // Force the session to be written before redirecting.
    session_write_close();
    // Redirect to the payment link
    header('Location: ' . $result['data']['link']);
    exit;
} else {
    // Handle API error
    $message = $result['message'] ?? 'An unknown error occurred with the payment gateway.';
    $_SESSION['error'] = 'Payment Gateway Error: ' . $message;
    error_log('Flutterwave API Error: ' . $response);
    header("Location: {$redirect_path}");
    exit;
}
?>