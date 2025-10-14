<?php
// This page simulates the Paystack checkout interface.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../core/init.php';

// If transaction data isn't in the session, something went wrong.
if (!isset($_SESSION['payment_transaction'])) {
    die("No transaction initiated. Please start the purchase process again.");
}

$transaction = $_SESSION['payment_transaction'];
$currency_symbol = get_currency_symbol($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay with Paystack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f5f7fa; }
        .paystack-container { max-width: 450px; margin: 5rem auto; }
        .paystack-header { text-align: center; margin-bottom: 1.5rem; }
        .paystack-logo { height: 40px; }
        .paystack-card { border-radius: 8px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .paystack-card-footer { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="paystack-container">
        <div class="paystack-header">
            <img src="https://assets.paystack.com/assets/img/logos/paystack-logo-primary-dark.svg" alt="Paystack Logo" class="paystack-logo">
        </div>

        <div class="card paystack-card">
            <div class="card-body p-4">
                <p class="text-center text-muted">You are about to pay</p>
                <h1 class="text-center display-5 fw-bold mb-4">
                    <?php echo $currency_symbol; ?><?php echo number_format($transaction['amount'], 2); ?>
                </h1>

                <div class="mb-3">
                    <label class="form-label">Card Number</label>
                    <div class="input-group">
                        <input type="text" class="form-control" value="4242 4242 4242 4242" disabled>
                        <span class="input-group-text"><i class="bi bi-credit-card-2-front-fill"></i></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Expiry Date</label>
                        <input type="text" class="form-control" value="12 / 25" disabled>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">CVV</label>
                        <input type="text" class="form-control" value="123" disabled>
                    </div>
                </div>

                <p class="text-center small text-muted">This is a simulation. No real payment will be processed.</p>

                <form action="/controllers/paystack/verify_payment.php" method="GET">
                    <input type="hidden" name="reference" value="<?php echo htmlspecialchars($transaction['reference']); ?>">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            Pay <?php echo $currency_symbol; ?><?php echo number_format($transaction['amount'], 2); ?>
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center small">
                <i class="bi bi-lock-fill"></i> Secured by Paystack
            </div>
        </div>
    </div>
</body>
</html>