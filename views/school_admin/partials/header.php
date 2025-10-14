<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../core/init.php';
require_once __DIR__ . '/../../../core/auth_check.php';
require_auth('school_admin');

$site_settings = []; $school = ['name' => 'School Admin', 'logo_url' => null, 'language' => 'en', 'student_slots' => 0];
try {
    $stmt_site_settings = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    $site_settings = $stmt_site_settings->fetchAll(PDO::FETCH_KEY_PAIR);
    $stmt = $pdo->prepare("SELECT name, logo_url, language, student_slots FROM schools WHERE id = :school_id");
    $stmt->execute(['school_id' => $_SESSION['school_id']]);
    $school = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Graceful error handling
}

load_language($school['language'] ?? 'en');
$current_page = basename($_SERVER['PHP_SELF']);

if (($school['student_slots'] ?? 0) <= 0) {
    $restricted_pages = ['student_management.php', 'teacher_management.php', 'class_management.php', 'report_builder.php', 'publish_reports.php'];
    if (in_array($current_page, $restricted_pages)) {
        $_SESSION['message'] = 'You must purchase student slots before you can access this feature.';
        $_SESSION['message_type'] = 'warning';
        header('Location: /views/school_admin/billing.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $school['language'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'School Admin'; ?> - <?php echo htmlspecialchars($site_settings['site_name'] ?? 'ARS'); ?></title>
    <?php if (!empty($site_settings['site_favicon_url'])): ?><link rel="icon" type="image/x-icon" href="/<?php echo htmlspecialchars($site_settings['site_favicon_url']); ?>"><?php endif; ?>
    <link rel="manifest" href="/manifest.php">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { width: 260px; background-color: #343a40; min-height: 100vh; color: white; }
        .sidebar .sidebar-header { padding: 1.5rem; text-align: center; }
        .sidebar .sidebar-header h5 { margin: 0; }
        .sidebar .nav-link { color: #ced4da; padding: 0.8rem 1.5rem; border-left: 3px solid transparent; }
        .sidebar .nav-link:hover { color: #fff; background-color: #495057; }
        .sidebar .nav-link.active { color: #fff; font-weight: 500; border-left-color: #0d6efd; background-color: #495057; }
        #content-wrapper { flex-grow: 1; display: flex; flex-direction: column; }
        .impersonation-banner { background-color: #ffc107; color: #000; padding: 0.75rem; text-align: center; }
        #main-content { flex-grow: 1; padding: 2rem; }
        @media (min-width: 992px) { body { display: flex; } #content-wrapper { width: calc(100% - 260px); } }
    </style>
</head>
<body>
    <!-- Sidebar for Desktop -->
    <div class="sidebar d-none d-lg-flex flex-column p-3">
        <div class="sidebar-header">
            <?php if (!empty($school['logo_url'])): ?><img src="/<?php echo htmlspecialchars($school['logo_url']); ?>" alt="School Logo" class="img-fluid rounded-circle mb-2" style="max-width: 80px;"><?php endif; ?>
            <h5 class="fs-5"><?php echo htmlspecialchars($school['name']); ?></h5>
            <small class="text-muted"><?php echo __('school_control_center'); ?></small>
        </div>
        <hr class="text-secondary">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="/views/school_admin/dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>"><i class="bi bi-grid-1x2-fill me-2"></i><?php echo __('dashboard'); ?></a></li>
            <li><a href="/views/school_admin/student_management.php" class="nav-link <?php echo $current_page == 'student_management.php' ? 'active' : ''; ?>"><i class="bi bi-people-fill me-2"></i><?php echo __('student_management'); ?></a></li>
            <li><a href="/views/school_admin/teacher_management.php" class="nav-link <?php echo $current_page == 'teacher_management.php' ? 'active' : ''; ?>"><i class="bi bi-person-video3 me-2"></i><?php echo __('teacher_management'); ?></a></li>
            <li><a href="/views/school_admin/class_management.php" class="nav-link <?php echo $current_page == 'class_management.php' ? 'active' : ''; ?>"><i class="bi bi-book-half me-2"></i><?php echo __('class_management'); ?></a></li>
            <li><a href="/views/school_admin/subject_management.php" class="nav-link <?php echo $current_page == 'subject_management.php' ? 'active' : ''; ?>"><i class="bi bi-journals me-2"></i>Subject Management</a></li>
            <li><a href="/views/school_admin/report_builder.php" class="nav-link <?php echo $current_page == 'report_builder.php' ? 'active' : ''; ?>"><i class="bi bi-layout-text-window-reverse me-2"></i><?php echo __('report_card_builder'); ?></a></li>
            <li><a href="/views/school_admin/publish_reports.php" class="nav-link <?php echo $current_page == 'publish_reports.php' ? 'active' : ''; ?>"><i class="bi bi-cloud-upload-fill me-2"></i>Publish Reports</a></li>
            <li><a href="/views/school_admin/email_templates.php" class="nav-link <?php echo $current_page == 'email_templates.php' ? 'active' : ''; ?>"><i class="bi bi-envelope-paper-fill me-2"></i>Email Templates</a></li>
            <li><a href="/views/school_admin/announcements.php" class="nav-link <?php echo $current_page == 'announcements.php' ? 'active' : ''; ?>"><i class="bi bi-megaphone-fill me-2"></i>Announcements</a></li>
            <li><a href="/views/school_admin/billing.php" class="nav-link <?php echo $current_page == 'billing.php' ? 'active' : ''; ?>"><i class="bi bi-credit-card-fill me-2"></i><?php echo __('billing_slots'); ?></a></li>
            <li><a href="/views/school_admin/sms.php" class="nav-link <?php echo $current_page == 'sms.php' ? 'active' : ''; ?>"><i class="bi bi-chat-left-text-fill me-2"></i>SMS & Notifications</a></li>
            <li><a href="/views/school_admin/sender_id.php" class="nav-link <?php echo $current_page == 'sender_id.php' ? 'active' : ''; ?>"><i class="bi bi-person-badge-fill me-2"></i>Sender ID</a></li>
            <li><a href="/views/school_admin/support_tickets.php" class="nav-link <?php echo in_array($current_page, ['support_tickets.php', 'view_ticket.php', 'create_ticket.php']) ? 'active' : ''; ?>"><i class="bi bi-question-circle-fill me-2"></i>Support</a></li>
        </ul>
        <hr class="text-secondary">
        <div class="dropdown"><a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-person-circle me-2"></i><strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="/views/school_admin/school_settings.php"><i class="bi bi-gear-fill me-2"></i>School Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/controllers/auth_controller.php?action=logout"><i class="bi bi-box-arrow-right me-2"></i><?php echo __('logout'); ?></a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <div id="content-wrapper">
        <!-- Topbar for mobile -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light d-lg-none">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar"><span class="navbar-toggler-icon"></span></button>
                <a class="navbar-brand" href="#"><?php echo htmlspecialchars($school['name']); ?></a>
            </div>
        </nav>

        <!-- Offcanvas Sidebar for mobile -->
        <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mobileSidebar">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title"><?php echo htmlspecialchars($school['name']); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <div class="sidebar d-flex flex-column p-0">
                    <hr class="text-secondary">
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item"><a href="/views/school_admin/dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>"><i class="bi bi-grid-1x2-fill me-2"></i><?php echo __('dashboard'); ?></a></li>
                        <li><a href="/views/school_admin/student_management.php" class="nav-link <?php echo $current_page == 'student_management.php' ? 'active' : ''; ?>"><i class="bi bi-people-fill me-2"></i><?php echo __('student_management'); ?></a></li>
                        <li><a href="/views/school_admin/teacher_management.php" class="nav-link <?php echo $current_page == 'teacher_management.php' ? 'active' : ''; ?>"><i class="bi bi-person-video3 me-2"></i><?php echo __('teacher_management'); ?></a></li>
                        <li><a href="/views/school_admin/class_management.php" class="nav-link <?php echo $current_page == 'class_management.php' ? 'active' : ''; ?>"><i class="bi bi-book-half me-2"></i><?php echo __('class_management'); ?></a></li>
                        <li><a href="/views/school_admin/subject_management.php" class="nav-link <?php echo $current_page == 'subject_management.php' ? 'active' : ''; ?>"><i class="bi bi-journals me-2"></i>Subject Management</a></li>
                        <li><a href="/views/school_admin/report_builder.php" class="nav-link <?php echo $current_page == 'report_builder.php' ? 'active' : ''; ?>"><i class="bi bi-layout-text-window-reverse me-2"></i><?php echo __('report_card_builder'); ?></a></li>
                        <li><a href="/views/school_admin/publish_reports.php" class="nav-link <?php echo $current_page == 'publish_reports.php' ? 'active' : ''; ?>"><i class="bi bi-cloud-upload-fill me-2"></i>Publish Reports</a></li>
                        <li><a href="/views/school_admin/email_templates.php" class="nav-link <?php echo $current_page == 'email_templates.php' ? 'active' : ''; ?>"><i class="bi bi-envelope-paper-fill me-2"></i>Email Templates</a></li>
                        <li><a href="/views/school_admin/announcements.php" class="nav-link <?php echo $current_page == 'announcements.php' ? 'active' : ''; ?>"><i class="bi bi-megaphone-fill me-2"></i>Announcements</a></li>
                        <li><a href="/views/school_admin/billing.php" class="nav-link <?php echo $current_page == 'billing.php' ? 'active' : ''; ?>"><i class="bi bi-credit-card-fill me-2"></i><?php echo __('billing_slots'); ?></a></li>
                        <li><a href="/views/school_admin/sms.php" class="nav-link <?php echo $current_page == 'sms.php' ? 'active' : ''; ?>"><i class="bi bi-chat-left-text-fill me-2"></i>SMS & Notifications</a></li>
                        <li><a href="/views/school_admin/sender_id.php" class="nav-link <?php echo $current_page == 'sender_id.php' ? 'active' : ''; ?>"><i class="bi bi-person-badge-fill me-2"></i>Sender ID</a></li>
                    </ul>
                    <hr class="text-secondary">
                    <div class="dropdown"><a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-person-circle me-2"></i><strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                            <li><a class="dropdown-item" href="/views/school_admin/school_settings.php"><i class="bi bi-gear-fill me-2"></i>School Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/controllers/auth_controller.php?action=logout"><i class="bi bi-box-arrow-right me-2"></i><?php echo __('logout'); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['original_user'])): ?>
        <div class="impersonation-banner fw-bold">
            <i class="bi bi-exclamation-triangle-fill"></i> You are logged in as <?php echo htmlspecialchars($_SESSION['user_name']); ?>.
            <a href="/controllers/auth_controller.php?action=switch_back" class="btn btn-dark btn-sm ms-2">Switch Back to Super Admin</a>
        </div>
        <?php endif; ?>
        <main id="main-content">
            <!-- Page content starts here -->