<?php
/**
 * report_builder_controller.php - Handles saving the report card settings.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only school admins can execute these actions
require_auth('school_admin');

$action = $_POST['action'] ?? '';
$school_id = $_SESSION['school_id'];
$admin_user_id = $_SESSION['user_id'];

switch ($action) {
    case 'save_settings':
        handle_save_settings($pdo, $school_id, $admin_user_id);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error');
}

function handle_save_settings($pdo, $school_id, $admin_user_id) {
    // --- Get data from the form ---
    $template_id = $_POST['template_id'] ?? 1;
    $brand_color = $_POST['brand_color'] ?? '#0d6efd';
    $columns_raw = $_POST['columns'] ?? '';
    $columns = !empty($columns_raw) ? explode(',', $columns_raw) : [];
    $school_address = $_POST['school_address'] ?? '';
    $principal_comments_raw = $_POST['principal_comments'] ?? '';
    $principal_comments = array_filter(array_map('trim', explode("\n", $principal_comments_raw)));
    $signature_type = $_POST['signature_type'] ?? 'text';
    $signature_text = $_POST['signature_text'] ?? '';
    $existing_signature_data = $_POST['existing_signature_data'] ?? '';

    $pdo->beginTransaction();
    try {
        // --- 1. Handle Logo Upload ---
        $logo_url = $_POST['existing_logo_url'] ?? '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/logos/';
            if (!is_dir(APP_ROOT . '/' . $upload_dir)) {
                mkdir(APP_ROOT . '/' . $upload_dir, 0777, true);
            }
            $file_name = $school_id . '_' . bin2hex(random_bytes(8)) . '_' . basename($_FILES['logo']['name']);
            $target_file = APP_ROOT . '/' . $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
                $logo_url = $upload_dir . $file_name;
            }
        }

        // --- 2. Save Report Card Structure Settings ---
        $sql_rc_settings = "
            INSERT INTO report_card_settings (school_id, template_id, columns)
            VALUES (:school_id, :template_id, :columns)
            ON DUPLICATE KEY UPDATE template_id = VALUES(template_id), columns = VALUES(columns)";
        $stmt_rc = $pdo->prepare($sql_rc_settings);
        $stmt_rc->execute([':school_id' => $school_id, ':template_id' => $template_id, ':columns' => json_encode($columns)]);

        // --- 3. Update School Branding and Address ---
        $stmt_school = $pdo->prepare("UPDATE schools SET logo_url = :logo_url, brand_color = :brand_color, address = :address WHERE id = :school_id");
        $stmt_school->execute([':logo_url' => $logo_url, ':brand_color' => $brand_color, ':address' => $school_address, ':school_id' => $school_id]);

        // --- 4. Update Principal's Comment Bank ---
        // First, delete old principal comments for this school
        $stmt_del_comments = $pdo->prepare("DELETE FROM report_comments WHERE school_id = :school_id AND comment_type = 'principal'");
        $stmt_del_comments->execute([':school_id' => $school_id]);

        // Then, insert the new ones
        if (!empty($principal_comments)) {
            $sql_insert_comments = "INSERT INTO report_comments (school_id, user_id, comment_type, comment_text) VALUES (:school_id, :user_id, 'principal', :comment_text)";
            $stmt_insert_comments = $pdo->prepare($sql_insert_comments);
            foreach ($principal_comments as $comment) {
                $stmt_insert_comments->execute([':school_id' => $school_id, ':user_id' => $admin_user_id, ':comment_text' => $comment]);
            }
        }

        // --- 5. Handle Principal's Signature ---
        $signature_data = $existing_signature_data;
        if ($signature_type === 'image' && isset($_FILES['signature_image']) && $_FILES['signature_image']['error'] == UPLOAD_ERR_OK) {
            // Handle image upload
            $upload_dir_sig = 'uploads/signatures/';
            if (!is_dir(APP_ROOT . '/' . $upload_dir_sig)) {
                mkdir(APP_ROOT . '/' . $upload_dir_sig, 0777, true);
            }
            $sig_file_name = $admin_user_id . '_' . basename($_FILES['signature_image']['name']);
            $sig_target_file = APP_ROOT . '/' . $upload_dir_sig . $sig_file_name;
            if (move_uploaded_file($_FILES['signature_image']['tmp_name'], $sig_target_file)) {
                // If a new image is uploaded and an old one existed, delete the old one
                if ($existing_signature_data && file_exists(APP_ROOT . '/' . $existing_signature_data)) {
                    unlink(APP_ROOT . '/' . $existing_signature_data);
                }
                $signature_data = $upload_dir_sig . $sig_file_name;
            }
        } elseif ($signature_type === 'text') {
            $signature_data = $signature_text;
        }

        // Save signature to the database
        $sql_sig = "
            INSERT INTO report_card_signatures (user_id, signature_type, signature_data)
            VALUES (:user_id, :signature_type, :signature_data)
            ON DUPLICATE KEY UPDATE signature_type = VALUES(signature_type), signature_data = VALUES(signature_data)";
        $stmt_sig = $pdo->prepare($sql_sig);
        $stmt_sig->execute([':user_id' => $admin_user_id, ':signature_type' => $signature_type, ':signature_data' => $signature_data]);

        // --- Commit and Redirect ---
        $pdo->commit();
        redirect_with_message('Report card settings saved successfully.', 'success');

    } catch (PDOException $e) {
        $pdo->rollBack();
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    } catch (Exception $e) {
        $pdo->rollBack();
        redirect_with_message('An error occurred: ' . $e->getMessage(), 'error');
    }
}

function redirect_with_message($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: /views/school_admin/report_builder.php');
    exit;
}
?>