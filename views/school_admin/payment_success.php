<?php
$page_title = "Payment Successful";
include 'partials/header.php'; // Includes auth, db, etc.

$slots_purchased = $_POST['slots_purchased'] ?? 0;
$amount_paid = $_POST['amount_paid'] ?? 0;

if ($slots_purchased == 0 || $amount_paid == 0) {
    echo '<div class="alert alert-danger">Invalid payment details. Please try again.</div>';
    include 'partials/footer.php';
    exit;
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-7 text-center">
            <div class="card shadow">
                <div class="card-body p-5">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    <h1 class="mt-3">Payment Successful!</h1>
                    <p class="lead">Your transaction has been processed successfully.</p>
                    <hr>
                    <p>You have purchased <strong><?php echo number_format($slots_purchased); ?> student slots</strong> for a total of <strong><?php echo get_currency_symbol($pdo); ?><?php echo number_format($amount_paid, 2); ?></strong>.</p>
                    <p class="text-muted">Click the button below to add the slots to your school's account and return to the dashboard.</p>

                    <form action="/controllers/payment_controller.php" method="POST">
                        <input type="hidden" name="action" value="confirm_purchase">
                        <input type="hidden" name="slots_purchased" value="<?php echo $slots_purchased; ?>">
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Confirm and Add Slots to My Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>