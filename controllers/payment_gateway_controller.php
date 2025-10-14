<?php
/**
 * payment_gateway_controller.php - Simulates a payment gateway processor.
 *
 * In a real application, this is where you would integrate with Stripe, Paystack, etc.
 * It would create a payment session and redirect the user to the gateway's checkout page.
 * After payment, the gateway would redirect the user back to a success/cancel URL.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

// This is a school-admin initiated action
require_auth('school_admin');

$slots_to_purchase = $_POST['slot_quantity'] ?? 0;
$price_per_slot = (float)s_get($pdo, 'price_per_student_term', 5);
$amount_paid = $slots_to_purchase * $price_per_slot;

if ($slots_to_purchase <= 0) {
    // Redirect back to billing with an error if something is wrong
    $_SESSION['message'] = 'Invalid number of slots specified.';
    $_SESSION['message_type'] = 'error';
    header('Location: /views/school_admin/billing.php');
    exit;
}

// --- Payment Gateway Simulation ---
// 1. Log the attempt to process a payment.
error_log("Attempting to process payment for School ID: {$_SESSION['school_id']} for {$slots_to_purchase} slots.");

// 2. Simulate a successful processing. In a real app, this would be a redirect to Stripe, etc.
// For our simulation, we will immediately redirect to our own success page,
// passing the purchase details via POST. We can do this with a self-submitting form.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to Payment Gateway...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Redirecting to secure payment gateway... Please wait.</p>
    </div>

    <!-- This form auto-submits to carry the data to the success page -->
    <form id="redirectForm" action="/views/school_admin/payment_success.php" method="POST">
        <input type="hidden" name="slots_purchased" value="<?php echo htmlspecialchars($slots_to_purchase); ?>">
        <input type="hidden" name="amount_paid" value="<?php echo htmlspecialchars($amount_paid); ?>">
    </form>

    <script>
        document.getElementById('redirectForm').submit();
    </script>
</body>
</html>