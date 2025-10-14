<?php
// This page simulates the Flutterwave checkout interface.
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
    <title>Complete Your Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .flutterwave-container { max-width: 480px; margin: 4rem auto; }
        .flutterwave-card { border: none; border-radius: 10px; box-shadow: 0 6px 24px rgba(0,0,0,0.1); }
        .flutterwave-header {
            background-color: #f5a623; /* Flutterwave Orange */
            color: white;
            padding: 1.5rem;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .flutterwave-logo { font-weight: bold; font-size: 1.5rem; }
        .customer-info { font-size: 0.9rem; }
        .amount-display { font-size: 2.5rem; font-weight: bold; }
    </style>
</head>
<body>
    <div class="flutterwave-container">
        <div class="card flutterwave-card">
            <div class="flutterwave-header">
                <div class="flutterwave-logo">FLW</div>
                <div class="customer-info text-end">
                    <div><?php echo htmlspecialchars($transaction['customer_email']); ?></div>
                    <div>Pay</div>
                </div>
            </div>
            <div class="card-body p-4 text-center">
                <p class="text-muted">You are paying</p>
                <h1 class="amount-display mb-4">
                    <?php echo $transaction['currency']; ?> <?php echo number_format($transaction['amount'], 2); ?>
                </h1>
                <p class="small">to <strong><?php echo s_get($pdo, 'site_name', 'ARS'); ?></strong></p>

                <p class="text-center small text-muted mt-4">This is a simulation. No real payment will be processed.</p>

                <form action="/controllers/flutterwave/verify_payment.php" method="GET">
                    <input type="hidden" name="tx_ref" value="<?php echo htmlspecialchars($transaction['reference']); ?>">
                    <input type="hidden" name="transaction_id" value="SIMULATED_<?php echo time(); ?>">
                    <div class="d-grid mt-3">
                        <button type="submit" class="btn btn-lg w-100" style="background-color: #f5a623; color: white;">
                            Pay <?php echo $currency_symbol; ?><?php echo number_format($transaction['amount'], 2); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>