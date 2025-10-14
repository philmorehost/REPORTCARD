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
    $_SESSION['error'] = 'Invalid action specified.';
    header('Location: /views/super_admin/dashboard.php');
    exit;
}

/**
 * Handles updating the pricing packages from the new form.
 */
function handle_package_update($pdo) {
    if (!isset($_POST['packages']) || !is_array($_POST['packages'])) {
        $_SESSION['error'] = 'No package data received.';
        header('Location: /views/super_admin/packages.php');
        exit;
    }

    $packages_data = $_POST['packages'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "UPDATE packages SET name = :name, price = :price, student_limit = :student_limit, features = :features
             WHERE id = :id"
        );

        foreach ($packages_data as $package_id => $data) {
            // Basic validation
            if (empty($data['id']) || empty($data['name']) || !isset($data['price']) || !isset($data['student_limit']) || empty($data['features'])) {
                throw new Exception("Missing data for one of the packages.");
            }

            // Validate features are valid JSON
            json_decode($data['features']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON format for features in package '{$data['name']}'.");
            }

            // For Freemium, enforce price is 0
            if (strtolower($data['name']) === 'freemium') {
                $data['price'] = 0;
            }

            // For Premium, enforce student limit is 0
            if (strtolower($data['name']) === 'premium') {
                $data['student_limit'] = 0;
            }

            $stmt->execute([
                ':id' => $data['id'],
                ':name' => trim($data['name']),
                ':price' => $data['price'],
                ':student_limit' => $data['student_limit'],
                ':features' => $data['features'],
            ]);
        }

        $pdo->commit();

        $_SESSION['message'] = 'Packages have been updated successfully.';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'An error occurred while updating packages: ' . $e->getMessage();
    }

    header('Location: /views/super_admin/packages.php');
    exit;
}
?>