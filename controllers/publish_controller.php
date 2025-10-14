<?php
/**
 * publish_controller.php - Handles publishing student report cards.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/email.php';
require_once __DIR__ . '/../core/sms.php'; // Include the new SMS function

// Ensure only school admins can execute these actions
require_auth('school_admin');

$action = $_GET['action'] ?? '';
$school_id = $_SESSION['school_id'];

switch ($action) {
    case 'publish_single':
        handle_publish_single($pdo, $school_id);
        break;
    case 'publish_bulk':
        handle_publish_bulk($pdo, $school_id);
        break;
    case 'view_report':
        handle_view_report($pdo, $school_id);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error');
}

/**
 * Publishes a single student's report card.
 */
function handle_publish_single($pdo, $school_id) {
    $student_id = $_GET['student_id'] ?? 0;
    $academic_period = $_GET['period'] ?? '';
    $email_template_id = $_GET['email_template_id'] ?? 0;
    $send_sms = isset($_GET['send_sms']) && $_GET['send_sms'] == '1';

    if (!$student_id || !$academic_period) {
        redirect_with_message('Missing student ID or academic period.', 'error');
    }

    try {
        publish_student_report($pdo, $school_id, $student_id, $academic_period, $email_template_id, $send_sms);
        redirect_with_message('Report card published successfully!', 'success');
    } catch (Exception $e) {
        redirect_with_message('Error publishing report: ' . $e->getMessage(), 'error');
    }
}

/**
 * Publishes report cards for all unpublished students in a given period.
 */
function handle_publish_bulk($pdo, $school_id) {
    $academic_period = $_GET['period'] ?? '';
    $email_template_id = $_GET['email_template_id'] ?? 0;
    $send_sms = isset($_GET['send_sms']) && $_GET['send_sms'] == '1';

    if (!$academic_period) {
        redirect_with_message('Academic period not specified for bulk publish.', 'error');
    }

    try {
        $stmt_unpublished = $pdo->prepare(
            "SELECT s.id FROM students s
             LEFT JOIN published_reports pr ON s.id = pr.student_id AND pr.academic_period = :period
             WHERE s.school_id = :school_id AND s.status = 'active' AND pr.id IS NULL"
        );
        $stmt_unpublished->execute(['period' => $academic_period, 'school_id' => $school_id]);
        $student_ids_to_publish = $stmt_unpublished->fetchAll(PDO::FETCH_COLUMN);

        if (empty($student_ids_to_publish)) {
            redirect_with_message('No unpublished reports found for this period.', 'info');
        }

        $published_count = 0;
        foreach ($student_ids_to_publish as $student_id) {
            try {
                publish_student_report($pdo, $school_id, $student_id, $academic_period, $email_template_id, $send_sms);
                $published_count++;
            } catch (Exception $e) {
                error_log("Failed to publish report for student ID {$student_id}: " . $e->getMessage());
            }
        }

        redirect_with_message("Successfully published {$published_count} new report cards.", 'success');

    } catch (Exception $e) {
        redirect_with_message('An error occurred during bulk publish: ' . $e->getMessage(), 'error');
    }
}

/**
 * Helper function to generate and save a single student report and send notifications.
 */
function publish_student_report($pdo, $school_id, $student_id, $academic_period, $email_template_id, $send_sms) {
    $pdo->beginTransaction();
    try {
        // Use the new helper to get the correct domain for the school
        $domain = get_school_domain($pdo, $school_id);
        if (empty($domain)) {
            throw new Exception("Critical Error: The report card domain could not be determined.");
        }

        $snapshot = [];
        // Store the determined domain in the snapshot for consistency
        $snapshot['system_url'] = $domain;
        $snapshot['site_name'] = s_get($pdo, 'site_name', 'Automated Report Card System');

        $stmt_school = $pdo->prepare("SELECT s.name, s.address, s.logo_url, s.brand_color, p.name as package_name FROM schools s LEFT JOIN packages p ON s.package_id = p.id WHERE s.id = :id");
        $stmt_school->execute(['id' => $school_id]);
        $snapshot['school_info'] = $stmt_school->fetch(PDO::FETCH_ASSOC);

        $stmt_admin = $pdo->prepare("SELECT id FROM users WHERE school_id = :school_id AND role = 'school_admin' LIMIT 1");
        $stmt_admin->execute(['school_id' => $school_id]);
        if ($admin_id = $stmt_admin->fetchColumn()) {
            $stmt_sig = $pdo->prepare("SELECT signature_type, signature_data FROM report_card_signatures WHERE user_id = :user_id");
            $stmt_sig->execute(['user_id' => $admin_id]);
            $snapshot['principal_signature'] = $stmt_sig->fetch(PDO::FETCH_ASSOC);
        } else {
            $snapshot['principal_signature'] = null;
        }

        // Fetch student info including the parent's phone number
        $stmt_student = $pdo->prepare("SELECT full_name, student_id_number, class, parent_name, parent_email, parent_phone_number FROM students WHERE id = :id");
        $stmt_student->execute(['id' => $student_id]);
        $snapshot['student_info'] = $stmt_student->fetch(PDO::FETCH_ASSOC);
        $snapshot['academic_period'] = $academic_period;

        // ... (rest of the snapshot creation is the same)
        $stmt_settings = $pdo->prepare("SELECT template_id, columns FROM report_card_settings WHERE school_id = :id");
        $stmt_settings->execute(['id' => $school_id]);
        $report_settings = $stmt_settings->fetch(PDO::FETCH_ASSOC);
        $snapshot['report_structure'] = ['template_id' => $report_settings['template_id'] ?? 1, 'columns' => $report_settings ? json_decode($report_settings['columns'], true) : ['Term Score', 'Remark']];

        $stmt_grades = $pdo->prepare("SELECT c.class_name, g.grades FROM grades g JOIN enrollments e ON g.enrollment_id = e.id JOIN classes c ON e.class_id = c.id WHERE e.student_id = :student_id AND c.academic_period = :period");
        $stmt_grades->execute(['student_id' => $student_id, 'period' => $academic_period]);
        $snapshot['grades_data'] = $stmt_grades->fetchAll(PDO::FETCH_ASSOC);

        $unique_hash = rtrim(strtr(base64_encode(random_bytes(12)), '+/', '-_'), '=');
        $data_snapshot_json = json_encode($snapshot);

        $sql = "INSERT INTO published_reports (school_id, student_id, academic_period, unique_hash, data_snapshot) VALUES (:school_id, :student_id, :period, :hash, :snapshot) ON DUPLICATE KEY UPDATE unique_hash = VALUES(unique_hash), data_snapshot = VALUES(data_snapshot)";
        $stmt_insert = $pdo->prepare($sql);
        $stmt_insert->execute(['school_id' => $school_id, 'student_id' => $student_id, 'period' => $academic_period, 'hash' => $unique_hash, 'snapshot' => $data_snapshot_json]);

        // --- Handle Notifications ---
        $report_link = rtrim($snapshot['system_url'], '/') . '/public/report.php?id=' . $unique_hash;

        // Send Email (only if an email template is selected)
        if ($email_template_id > 0) {
            $stmt_template = $pdo->prepare("SELECT * FROM email_templates WHERE id = :id AND school_id = :school_id");
            $stmt_template->execute(['id' => $email_template_id, 'school_id' => $school_id]);
            $template = $stmt_template->fetch(PDO::FETCH_ASSOC);

            if ($template && !empty($snapshot['student_info']['parent_email'])) {
                $replacements = [
                    '{parent_name}' => $snapshot['student_info']['parent_name'],
                    '{student_name}' => $snapshot['student_info']['full_name'],
                    '{report_link}' => $report_link,
                    '{school_name}' => $snapshot['school_info']['name']
                ];
                $subject = str_replace(array_keys($replacements), array_values($replacements), $template['subject']);
                $body = str_replace(array_keys($replacements), array_values($replacements), $template['body']);

                $template_data = ['school_info' => $snapshot['school_info'], 'system_url' => $snapshot['system_url']];
                $email_sent = send_email($pdo, $snapshot['student_info']['parent_email'], $subject, nl2br($body), $template_data);
                if ($email_sent !== true) {
                    $_SESSION['email_failure_warning'] = "Email Error: " . htmlspecialchars($email_sent);
                }
            }
        }

        // Send SMS (independently of email)
        if ($send_sms && !empty($snapshot['student_info']['parent_phone_number'])) {
            $sms_message = sprintf(
                "%s, Report Card: %s",
                $snapshot['student_info']['full_name'],
                $report_link
            );

            $sms_sent = send_sms_message($pdo, $school_id, $student_id, $snapshot['student_info']['parent_phone_number'], $sms_message);
            if ($sms_sent !== true) {
                $_SESSION['sms_failure_warning'] = "SMS Error: " . htmlspecialchars($sms_sent);
            }
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function redirect_with_message($message, $type) {
    $period_param = !empty($_GET['period']) ? '?period=' . urlencode($_GET['period']) : '';
    $final_message = htmlspecialchars($message);
    $warnings = [];

    if (isset($_SESSION['email_failure_warning'])) {
        $warnings[] = $_SESSION['email_failure_warning'];
        unset($_SESSION['email_failure_warning']);
    }
    if (isset($_SESSION['sms_failure_warning'])) {
        $warnings[] = $_SESSION['sms_failure_warning'];
        unset($_SESSION['sms_failure_warning']);
    }

    if (!empty($warnings)) {
        $final_message .= " <br><br><strong>Please note:</strong><br>" . implode("<br>", $warnings);
        // If the initial action was a success, downgrade to a warning to show a yellow box
        if ($type === 'success') {
            $type = 'warning';
        }
    }

    $_SESSION['message'] = $final_message;
    $_SESSION['message_type'] = $type;
    header('Location: /views/school_admin/publish_reports.php' . $period_param);
    exit;
}

/**
 * Generates a direct, non-shareable HTML view of a student's report card.
 */
function handle_view_report($pdo, $school_id) {
    // ... (This function remains unchanged)
    $student_id = $_GET['student_id'] ?? 0;
    $academic_period = $_GET['period'] ?? '';

    if (!$student_id || !$academic_period) {
        http_response_code(400);
        echo "Error: Missing student ID or academic period.";
        exit;
    }

    try {
        // 1. Fetch all data required for the report card (similar to creating a snapshot)
        $data = [];
        // School info
        $stmt_school = $pdo->prepare("SELECT s.name, s.address, s.logo_url, s.brand_color, p.name as package_name FROM schools s LEFT JOIN packages p ON s.package_id = p.id WHERE s.id = :id");
        $stmt_school->execute(['id' => $school_id]);
        $data['school_info'] = $stmt_school->fetch(PDO::FETCH_ASSOC);
        $is_freemium = (isset($data['school_info']['package_name']) && strtolower($data['school_info']['package_name']) === 'freemium');

        // Principal's signature
        $stmt_admin = $pdo->prepare("SELECT id FROM users WHERE school_id = :school_id AND role = 'school_admin' LIMIT 1");
        $stmt_admin->execute(['school_id' => $school_id]);
        $admin_id = $stmt_admin->fetchColumn();
        if ($admin_id) {
            $stmt_sig = $pdo->prepare("SELECT signature_type, signature_data FROM report_card_signatures WHERE user_id = :user_id");
            $stmt_sig->execute(['user_id' => $admin_id]);
            $data['principal_signature'] = $stmt_sig->fetch(PDO::FETCH_ASSOC);
        } else {
            $data['principal_signature'] = null;
        }

        // Student info - include parent phone number
        $stmt_student = $pdo->prepare("SELECT full_name, student_id_number, class, parent_name, parent_email, parent_phone_number FROM students WHERE id = :id");
        $stmt_student->execute(['id' => $student_id]);
        $data['student_info'] = $stmt_student->fetch(PDO::FETCH_ASSOC);
        $data['academic_period'] = $academic_period;

        // Report structure (template and columns)
        $stmt_settings = $pdo->prepare("SELECT template_id, columns FROM report_card_settings WHERE school_id = :id");
        $stmt_settings->execute(['id' => $school_id]);
        $report_settings = $stmt_settings->fetch(PDO::FETCH_ASSOC);

        $template_id = $is_freemium ? 1 : ($report_settings['template_id'] ?? 1);
        $data['report_structure'] = [
            'template_id' => $template_id,
            'columns' => $report_settings ? json_decode($report_settings['columns'], true) : ['Term Score', 'Remark']
        ];

        // Grades data
        $stmt_grades = $pdo->prepare("SELECT c.class_name, g.grades FROM grades g JOIN enrollments e ON g.enrollment_id = e.id JOIN classes c ON e.class_id = c.id WHERE e.student_id = :student_id AND c.academic_period = :period");
        $stmt_grades->execute(['student_id' => $student_id, 'period' => $academic_period]);
        $data['grades_data'] = $stmt_grades->fetchAll(PDO::FETCH_ASSOC);

        $stmt_template = $pdo->prepare("SELECT file_path FROM report_templates WHERE id = :id");
        $stmt_template->execute(['id' => $template_id]);
        $template_file = $stmt_template->fetchColumn();

        if (!$template_file || !file_exists(APP_ROOT . '/' . $template_file)) {
            throw new Exception("Report card template file could not be found.");
        }

        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
        echo '<title>Report Card View: ' . htmlspecialchars($data['student_info']['full_name']) . '</title>';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<style>
                body { background-color: #f0f2f5; display: flex; justify-content: center; padding: 2rem; }
                .report-container { position: relative; max-width: 800px; width: 100%; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
                .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 5rem; color: rgba(0, 0, 0, 0.08); font-weight: bold; pointer-events: none; z-index: 1000; text-transform: uppercase; }
              </style>';
        echo '</head><body><div class="report-container">';

        if ($is_freemium) {
            $site_name = s_get($pdo, 'site_name', 'Freemium Plan');
            echo '<div class="watermark"><span>' . htmlspecialchars($site_name) . '</span></div>';
        }

        function d_get($key, $default = '') {
            global $data;
            $keys = explode('.', $key);
            $value = $data;
            foreach ($keys as $k) {
                if (!isset($value[$k])) return is_array($default) ? $default : htmlspecialchars($default);
                $value = $value[$k];
            }
            return is_array($value) ? $value : htmlspecialchars($value);
        }

        include APP_ROOT . '/' . $template_file;

        echo '</div></body></html>';

    } catch (Exception $e) {
        http_response_code(500);
        echo "<h1>Error</h1><p>Could not generate the report card view: " . $e->getMessage() . "</p>";
    }
    exit;
}
?>