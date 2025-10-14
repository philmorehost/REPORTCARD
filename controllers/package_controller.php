<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

$action = $_POST['action'] ?? '';

if ($action === 'update_packages') {
    require_auth('super_admin');
    handle_package_update($pdo);
} else {
    // Redirect or show an error if the action is invalid
    $_SESSION['error'] = 'Invalid action specified.';
    header('Location: /views/super_admin/dashboard.php');
    exit;
}

/**
 * Handles updating the pricing packages.
 */
function handle_package_update($pdo) {
    $price_term = $_POST['price_per_student_term'] ?? null;
    $price_semester = $_POST['price_per_student_semester'] ?? null;

    if (!is_numeric($price_term) || !is_numeric($price_semester) || $price_term < 0 || $price_semester < 0) {
        $_SESSION['error'] = 'Invalid prices provided. Please enter valid numbers.';
        header('Location: /views/super_admin/packages.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Use INSERT ... ON DUPLICATE KEY UPDATE to either create or update the setting
        $stmt = $pdo->prepare(
            "INSERT INTO system_settings (setting_key, setting_value)
             VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE setting_value = :value"
        );

        // Update term price
        $stmt->execute(['key' => 'price_per_student_term', 'value' => $price_term]);

        // Update semester price
        $stmt->execute(['key' => 'price_per_student_semester', 'value' => $price_semester]);

        $pdo->commit();

        $_SESSION['message'] = 'Pricing packages have been updated successfully.';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Database error while updating prices: ' . $e->getMessage();
    }

    header('Location: /views/super_admin/packages.php');
    exit;
}
?>