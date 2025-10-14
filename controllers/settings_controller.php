<?php
/**
 * settings_controller.php - Handles updating platform-wide settings.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only super admins can execute these actions
require_auth('super_admin');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_financial':
        handle_update_financial($pdo);
        break;
    case 'update_system':
        handle_update_system($pdo);
        break;
    default:
        redirect_with_message('Invalid settings action.', 'error', 'dashboard.php');
}

/**
 * Handles updating financial settings.
 */
function handle_update_financial($pdo) {
    $settings_to_update = [
        'currency_symbol' => $_POST['currency_symbol'] ?? '$',
        'currency_code' => $_POST['currency_code'] ?? 'USD',
        'paystack_enabled' => isset($_POST['paystack_enabled']) ? '1' : '0',
        'flutterwave_enabled' => isset($_POST['flutterwave_enabled']) ? '1' : '0',
        'stripe_enabled' => isset($_POST['stripe_enabled']) ? '1' : '0',
        'paystack_secret_key' => $_POST['paystack_secret_key'] ?? '',
        'paystack_public_key' => $_POST['paystack_public_key'] ?? '',
        'flutterwave_secret_key' => $_POST['flutterwave_secret_key'] ?? '',
        'flutterwave_public_key' => $_POST['flutterwave_public_key'] ?? '',
        'stripe_secret_key' => $_POST['stripe_secret_key'] ?? '',
        'stripe_public_key' => $_POST['stripe_public_key'] ?? '',
        'manual_transfer_details' => $_POST['manual_transfer_details'] ?? ''
    ];

    try {
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = :value");
        foreach ($settings_to_update as $key => $value) {
            $stmt->execute(['key' => $key, 'value' => $value]);
        }
        redirect_with_message('Financial settings updated successfully.', 'success', 'financial_management.php');
    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error', 'financial_management.php');
    }
}

/**
 * Handles updating general system settings, including file uploads and SMTP config.
 */
function handle_update_system($pdo) {
    // Settings that are always present or can be empty
    $settings_to_update = [
        'system_url' => isset($_POST['system_url']) ? rtrim($_POST['system_url'], '/') : '',
        'site_name' => $_POST['site_name'] ?? 'ARS',
        'seo_description' => $_POST['seo_description'] ?? '',
        'grace_period_days' => $_POST['grace_period_days'] ?? '7',
        'pwa_enabled' => isset($_POST['pwa_enabled']) ? '1' : '0',
        'registration_enabled' => isset($_POST['registration_enabled']) ? '1' : '0',
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? '',
        'smtp_username' => $_POST['smtp_username'] ?? '',
        'smtp_from_email' => $_POST['smtp_from_email'] ?? '',
        'smtp_from_name' => $_POST['smtp_from_name'] ?? '',
        'sms_credit_cost' => $_POST['sms_credit_cost'] ?? '0.05',
        'sms_sender_id' => $_POST['sms_sender_id'] ?? '',
        'billing_payment_fee_percentage' => $_POST['billing_payment_fee_percentage'] ?? '0',
        'sms_payment_fee_percentage' => $_POST['sms_payment_fee_percentage'] ?? '0',
    ];

    // Conditionally update password/key fields only if a new value is provided.
    // This prevents accidentally clearing them if the form field is left blank.
    if (!empty($_POST['smtp_password'])) {
        $settings_to_update['smtp_password'] = $_POST['smtp_password'];
    }
    if (!empty($_POST['sms_api_key'])) {
        $settings_to_update['sms_api_key'] = $_POST['sms_api_key'];
    }


    // --- Handle File Uploads ---
    $upload_dir = 'uploads/site/';
    if (!is_dir(APP_ROOT . '/' . $upload_dir)) {
        mkdir(APP_ROOT . '/' . $upload_dir, 0777, true);
    }

    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == UPLOAD_ERR_OK) {
        $logo_path = $upload_dir . 'logo.png';
        if (move_uploaded_file($_FILES['site_logo']['tmp_name'], APP_ROOT . '/' . $logo_path)) {
            $settings_to_update['site_logo_url'] = $logo_path;
        }
    }

    if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] == UPLOAD_ERR_OK) {
        $favicon_path = $upload_dir . 'favicon.ico';
        if (move_uploaded_file($_FILES['site_favicon']['tmp_name'], APP_ROOT . '/' . $favicon_path)) {
            $settings_to_update['site_favicon_url'] = $favicon_path;
        }
    }

    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] == UPLOAD_ERR_OK) {
        $file_info = pathinfo($_FILES['hero_image']['name']);
        $extension = strtolower($file_info['extension']);
        $hero_path = $upload_dir . 'hero.' . $extension;
        if (move_uploaded_file($_FILES['hero_image']['tmp_name'], APP_ROOT . '/' . $hero_path)) {
            $settings_to_update['hero_image_url'] = $hero_path;
        }
    }

    // --- Save to Database ---
    try {
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = :value");
        foreach ($settings_to_update as $key => $value) {
            $stmt->execute(['key' => $key, 'value' => $value]);
        }
        redirect_with_message('System settings updated successfully.', 'success', 'system_settings.php');
    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error', 'system_settings.php');
    }
}

/**
 * Redirects to a specified page with a session message.
 */
function redirect_with_message($message, $type, $page) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: /views/super_admin/{$page}");
    exit;
}
?>