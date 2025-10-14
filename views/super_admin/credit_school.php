<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../core/init.php';
require_once __DIR__ . '/../../core/auth_check.php';

require_auth('super_admin');

$page_title = "Credit School SMS Wallet";

// Fetch all schools for the dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM schools ORDER BY name ASC");
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $schools = [];
    $form_disabled = true;
    $_SESSION['error'] = "Could not fetch schools: " . $e->getMessage();
}

include __DIR__ . '/partials/header.php';
?>

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
            </div>

            <?php include APP_ROOT . '/views/partials/alerts.php'; ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Manually Add SMS Credits</h5>
                    <p class="card-text">Use this form to manually add SMS credits to a school's wallet. This should be used to record credits for manual payments like bank transfers.</p>

                    <form action="/controllers/school_controller.php" method="POST">
                        <input type="hidden" name="action" value="credit_sms">

                        <div class="mb-3">
                            <label for="school_id" class="form-label">Select School</label>
                            <select class="form-select" id="school_id" name="school_id" required <?php echo isset($form_disabled) ? 'disabled' : ''; ?>>
                                <option value="" selected disabled>-- Choose a school --</option>
                                <?php foreach ($schools as $school): ?>
                                    <option value="<?php echo htmlspecialchars($school['id']); ?>">
                                        <?php echo htmlspecialchars($school['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="credits_to_add" class="form-label">Number of Credits to Add</label>
                            <input type="number" class="form-control" id="credits_to_add" name="credits_to_add" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="transaction_reference" class="form-label">Transaction Reference (Optional)</label>
                            <input type="text" class="form-control" id="transaction_reference" name="transaction_reference" placeholder="e.g., Bank Transfer INV-12345">
                             <div class="form-text">Provide a reference for this transaction, such as an invoice number or payment confirmation code.</div>
                        </div>

                        <button type="submit" class="btn btn-primary" <?php echo isset($form_disabled) ? 'disabled' : ''; ?>>Credit School Wallet</button>
                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>