<?php
/**
 * cron.php - Handles scheduled tasks for the application.
 *
 * This script is intended to be executed by a server's cron job scheduler.
 * It should be protected by a secret key passed as a GET parameter.
 */

// This script should not be accessible via a web browser directly without the key.
// In a real production environment, you might also want to restrict access by IP address.

require_once __DIR__ . '/init.php'; // Use init to ensure config is loaded

// --- Security Check ---
// 1. Fetch the secret key from the database
try {
    $stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'cron_job_key'");
    $secret_key = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Cannot connect to DB, critical error
    http_response_code(500);
    die("CRON ERROR: Could not connect to the database.");
}

// 2. Compare with the key provided in the URL
// Allow running from CLI with --key=... or from web with ?key=...
$provided_key = $_GET['key'] ?? '';
if (php_sapi_name() === 'cli' && isset($argv)) {
    foreach ($argv as $arg) {
        if (strpos($arg, '--key=') === 0) {
            $provided_key = substr($arg, 6);
        }
    }
}

if (empty($provided_key) || $provided_key !== $secret_key) {
    // If the key is missing or incorrect, deny access.
    http_response_code(403);
    die("CRON ERROR: Access Denied. Invalid or missing security key.");
}


// --- Cron Job Logic ---
echo "Cron job executed successfully at " . date('Y-m-d H:i:s') . "\n";

// --- Task 1: Suspend Expired School Accounts ---
try {
    $stmt_grace = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'grace_period_days'");
    $grace_period_days = (int)($stmt_grace->fetchColumn() ?: 7);

    // SQL to find expired accounts that are past their grace period
    $sql = "
        UPDATE schools
        SET status = 'suspended'
        WHERE status = 'active'
        AND subscription_expires_at IS NOT NULL
        AND subscription_expires_at < DATE_SUB(NOW(), INTERVAL :grace_period_days DAY)
    ";

    $stmt_suspend = $pdo->prepare($sql);
    $stmt_suspend->execute(['grace_period_days' => $grace_period_days]);

    $suspended_count = $stmt_suspend->rowCount();
    if ($suspended_count > 0) {
        echo "Suspended {$suspended_count} expired school account(s).\n";
    } else {
        echo "No school accounts required suspension.\n";
    }

} catch (PDOException $e) {
    echo "CRON ERROR during account suspension: " . $e->getMessage() . "\n";
}


// --- Placeholder for other future tasks ---
// echo "Ran other tasks...\n";


// --- End of Cron Job ---
exit;
?>