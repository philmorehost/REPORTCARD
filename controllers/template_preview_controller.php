<?php
/**
 * template_preview_controller.php - Generates an HTML preview of a report card template.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only school admins can access this
require_auth('school_admin');

$template_id = $_GET['template_id'] ?? 0;
if (!$template_id) {
    http_response_code(400);
    echo "Error: No template ID provided.";
    exit;
}

try {
    // --- Fetch required data for the preview ---
    $stmt_template = $pdo->prepare("SELECT file_path FROM report_templates WHERE id = :id");
    $stmt_template->execute(['id' => $template_id]);
    $template_file = $stmt_template->fetchColumn();

    if (!$template_file || !file_exists(APP_ROOT . '/' . $template_file)) {
        http_response_code(404);
        echo "<div class='alert alert-danger'>Preview not available. Template file not found.</div>";
        exit;
    }

    // --- Create sample data structure for the preview ---
    $school_id = $_SESSION['school_id'];
    $stmt_school = $pdo->prepare("SELECT name, address, logo_url, brand_color FROM schools WHERE id = :id");
    $stmt_school->execute(['id' => $school_id]);
    $school_info = $stmt_school->fetch(PDO::FETCH_ASSOC);

    // Use school's saved logo/color/address if available, otherwise use placeholders
    $school_info['logo_url'] = $school_info['logo_url'] ?? 'assets/img/placeholder_logo.png';
    $school_info['brand_color'] = $school_info['brand_color'] ?? '#0d6efd';
    $school_info['address'] = $school_info['address'] ?? '123 School Lane, Education City, 12345';

    // Fetch the school's defined columns or use a default
    $stmt_settings = $pdo->prepare("SELECT columns FROM report_card_settings WHERE school_id = :id");
    $stmt_settings->execute(['id' => $school_id]);
    $report_settings = $stmt_settings->fetch(PDO::FETCH_ASSOC);
    $columns = $report_settings ? json_decode($report_settings['columns'], true) : ['1st CA', '2nd CA', 'Exam', 'Total', 'Grade', 'Remark'];

    // Create sample grades data with comments
    $sample_grades_array = array_fill_keys(array_map(function($c) { return strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $c)); }, $columns), 'XX');
    // Ensure comment keys exist for the template
    $sample_grades_array['teacher_comment'] = 'A pleasure to have in class. John is a dedicated student.';
    $sample_grades_array['principal_comment'] = 'John consistently demonstrates a positive attitude towards learning.';
    $sample_grades_json = json_encode($sample_grades_array);

    // Fetch principal's signature for preview (using the logged-in admin's signature)
    $principal_signature = null;
    if (isset($_SESSION['user_id'])) {
        $stmt_sig = $pdo->prepare("SELECT signature_type, signature_data FROM report_card_signatures WHERE user_id = :user_id");
        $stmt_sig->execute(['user_id' => $_SESSION['user_id']]);
        $principal_signature = $stmt_sig->fetch(PDO::FETCH_ASSOC);
    }

    $data = [
        'school_info' => $school_info,
        'student_info' => [
            'full_name' => 'John Doe (Sample)',
            'student_id_number' => 'PREVIEW-001',
            'class' => 'Sample Class',
        ],
        'academic_period' => 'Sample Period 202X',
        'report_structure' => [
            'template_id' => $template_id,
            'columns' => $columns
        ],
        'grades_data' => [
            [ 'class_name' => 'Sample Subject 1', 'grades' => $sample_grades_json ],
            [ 'class_name' => 'Sample Subject 2', 'grades' => $sample_grades_json ]
        ],
        'principal_signature' => $principal_signature,
    ];

    // Helper function for the included template file. MUST BE IDENTICAL to the one in public/report.php
    function d_get($key, $default = '') {
        global $data;
        $keys = explode('.', $key);
        $value = $data;
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return is_array($default) ? $default : htmlspecialchars($default ?? '');
            }
            $value = $value[$k];
        }
        return is_array($value) ? $value : htmlspecialchars($value ?? '');
    }

    // --- Render the template file ---
    include APP_ROOT . '/' . $template_file;

} catch (Exception $e) {
    http_response_code(500);
    error_log("Template Preview Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>An error occurred while generating the preview. Please check the system logs.</div>";
    exit;
}
?>