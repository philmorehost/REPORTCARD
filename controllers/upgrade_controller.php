<?php
/**
 * upgrade_controller.php - Handles the logic for an existing school upgrading their package via bank transfer.
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';
require_once __DIR__ . '/../core/helpers.php';
require_auth('school_admin');

$school_id = $_SESSION['school_id'];
$package_id = $_POST['package_id'] ?? 0;
$slot_quantity = $_POST['slot_quantity'] ?? 0;

if (!$package_id || !$slot_quantity) {
    $_SESSION['message'] = 'Missing package or slot quantity information.';
    $_SESSION['message_type'] = 'error';
    header('Location: /views/school_admin/upgrade_package.php?pkg_id=' . ($package_id ?: ''));
    exit;
}

try {
    // Get package info to calculate the total cost
    $stmt_pkg = $pdo->prepare("SELECT name, price FROM packages WHERE id = :id");
    $stmt_pkg->execute([':id' => $package_id]);
    $package = $stmt_pkg->fetch(PDO::FETCH_ASSOC);

    if (!$package) {
        throw new Exception("Invalid package selected.");
    }

    $amount = (float)$package['price'] * (int)$slot_quantity;
    $description = "Upgrade to '{$package['name']}' package and purchase of {$slot_quantity} student slots.";
    $reference = 'UPG-' . $school_id . '-' . time();

    // Insert a 'pending' transaction into the database
    $stmt_insert = $pdo->prepare(
        "INSERT INTO payment_transactions (school_id, description, amount, payment_method, status, reference, metadata)
         VALUES (:school_id, :description, :amount, 'bank_transfer', 'pending', :reference, :metadata)"
    );
    // Store upgrade details in metadata for the admin to process
    $metadata = json_encode(['upgrade' => true, 'package_id' => $package_id, 'slot_quantity' => $slot_quantity]);

    $stmt_insert->execute([
        ':school_id' => $school_id,
        ':description' => $description,
        ':amount' => $amount,
        ':reference' => $reference,
        ':metadata' => $metadata
    ]);

    // Redirect to the billing page with instructions for the user
    $bank_details = s_get($pdo, 'bank_details', 'Please contact the system administrator for bank details.');
    $_SESSION['message'] = "Your upgrade request has been successfully logged. To complete the process, please make a payment of " . get_currency_symbol($pdo) . number_format($amount, 2) . ".<br><br><strong>Bank Details:</strong><br>" . nl2br(htmlspecialchars($bank_details)) . "<br><br>Your account will be upgraded by the administrator once your payment is confirmed.";
    $_SESSION['message_type'] = 'success';
    header('Location: /views/school_admin/billing.php');
    exit;

} catch (Exception $e) {
    $_SESSION['message'] = 'Error processing your upgrade request: ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    header('Location: /views/school_admin/upgrade_package.php?pkg_id=' . $package_id);
    exit;
}
