<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../core/init.php';
require_once __DIR__ . '/../../../core/auth_check.php';
require_auth('super_admin');

$settings = [];
try {
    $stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    $settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
    $stmt_pending_regs = $pdo->query("SELECT COUNT(id) FROM schools WHERE status = 'pending_payment'");
    $pending_regs_count = $stmt_pending_regs->fetchColumn();
    $stmt_pending_payments = $pdo->query("SELECT COUNT(id) FROM payment_transactions WHERE status = 'pending'");
    $pending_payments_count = $stmt_pending_payments->fetchColumn();
} catch (PDOException $e) {
    $pending_regs_count = 0;
    $pending_payments_count = 0;
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Super Admin'; ?> - <?php echo htmlspecialchars($settings['site_name'] ?? 'ARS'); ?></title>
    <?php if (!empty($settings['site_favicon_url'])): ?><link rel="icon" type="image/x-icon" href="/<?php echo htmlspecialchars($settings['site_favicon_url']); ?>"><?php endif; ?>
    <link rel="manifest" href="/manifest.php">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            width: 250px;
            background-color: #212529;
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link { color: #adb5bd; padding: 0.75rem 1.5rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background-color: #343a40; }
        #content-wrapper { flex-grow: 1; }
        @media (min-width: 992px) {
            body { display: flex; }
            #content-wrapper { display: flex; flex-direction: column; width: calc(100% - 250px); }
            #main-content { flex-grow: 1; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar d-none d-lg-flex flex-column p-3">
        <a href="/views/super_admin/dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <?php if (!empty($settings['site_logo_url'])): ?><img src="/<?php echo htmlspecialchars($settings['site_logo_url']); ?>" alt="Logo" style="height: 32px;" class="me-2"><?php else: ?><i class="bi bi-shield-lock-fill me-2"></i><?php endif; ?>
            <span class="fs-4"><?php echo htmlspecialchars($settings['site_name'] ?? 'ARS Admin'); ?></span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="/views/super_admin/dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
            <li><a href="/views/super_admin/school_management.php" class="nav-link <?php echo $current_page == 'school_management.php' ? 'active' : ''; ?>"><i class="bi bi-building me-2"></i>School Management</a></li>
            <li><a href="/views/super_admin/credit_school.php" class="nav-link <?php echo $current_page == 'credit_school.php' ? 'active' : ''; ?>"><i class="bi bi-wallet2 me-2"></i>Credit School SMS</a></li>
            <li><a href="/views/super_admin/pending_registrations.php" class="nav-link d-flex justify-content-between align-items-center <?php echo $current_page == 'pending_registrations.php' ? 'active' : ''; ?>"><span><i class="bi bi-person-check-fill me-2"></i>Pending Regs</span><?php if ($pending_regs_count > 0): ?><span class="badge bg-danger rounded-pill"><?php echo $pending_regs_count; ?></span><?php endif; ?></a></li>
            <li><a href="/views/super_admin/pending_payments.php" class="nav-link d-flex justify-content-between align-items-center <?php echo $current_page == 'pending_payments.php' ? 'active' : ''; ?>"><span><i class="bi bi-credit-card-2-front-fill me-2"></i>Pending Payments</span><?php if ($pending_payments_count > 0): ?><span class="badge bg-danger rounded-pill"><?php echo $pending_payments_count; ?></span><?php endif; ?></a></li>
            <li><a href="/views/super_admin/announcements.php" class="nav-link <?php echo $current_page == 'announcements.php' ? 'active' : ''; ?>"><i class="bi bi-megaphone-fill me-2"></i>Announcements</a></li>
            <li><a href="/views/super_admin/financial_management.php" class="nav-link <?php echo $current_page == 'financial_management.php' ? 'active' : ''; ?>"><i class="bi bi-cash-coin me-2"></i>Financials</a></li>
            <li><a href="/views/super_admin/packages.php" class="nav-link <?php echo $current_page == 'packages.php' ? 'active' : ''; ?>"><i class="bi bi-box-seam me-2"></i>Package Management</a></li>
            <li><a href="/views/super_admin/report_templates.php" class="nav-link <?php echo $current_page == 'report_templates.php' ? 'active' : ''; ?>"><i class="bi bi-journal-text me-2"></i>Report Templates</a></li>
            <li><a href="/views/super_admin/cms_management.php" class="nav-link <?php echo $current_page == 'cms_management.php' ? 'active' : ''; ?>"><i class="bi bi-file-earmark-text-fill me-2"></i>Landing Page CMS</a></li>
            <li><a href="/views/super_admin/system_settings.php" class="nav-link <?php echo $current_page == 'system_settings.php' ? 'active' : ''; ?>"><i class="bi bi-gear me-2"></i>System Settings</a></li>
            <li><a href="/views/super_admin/support_tickets.php" class="nav-link <?php echo in_array($current_page, ['support_tickets.php', 'view_ticket.php']) ? 'active' : ''; ?>"><i class="bi bi-question-circle-fill me-2"></i>Support Tickets</a></li>
        </ul>
        <hr>
        <div class="dropdown"><a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-person-circle me-2"></i><strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></a><ul class="dropdown-menu dropdown-menu-dark text-small shadow"><li><a class="dropdown-item" href="/controllers/auth_controller.php?action=logout">Sign out</a></li></ul></div>
    </div>

    <!-- Main Content Wrapper -->
    <div id="content-wrapper">
        <!-- Topbar for mobile -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light d-lg-none">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a class="navbar-brand" href="#"><?php echo htmlspecialchars($settings['site_name'] ?? 'ARS'); ?></a>
            </div>
        </nav>

        <!-- Offcanvas Sidebar for mobile -->
        <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mobileSidebar">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title"><?php echo htmlspecialchars($settings['site_name'] ?? 'ARS Admin'); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <!-- Re-using the same sidebar content -->
                <div class="sidebar d-flex flex-column p-3">
                    <a href="/views/super_admin/dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none"><i class="bi bi-shield-lock-fill me-2"></i><span class="fs-4">ARS Admin</span></a><hr>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item"><a href="/views/super_admin/dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                        <li><a href="/views/super_admin/school_management.php" class="nav-link <?php echo $current_page == 'school_management.php' ? 'active' : ''; ?>"><i class="bi bi-building me-2"></i>School Management</a></li>
                        <li><a href="/views/super_admin/credit_school.php" class="nav-link <?php echo $current_page == 'credit_school.php' ? 'active' : ''; ?>"><i class="bi bi-wallet2 me-2"></i>Credit School SMS</a></li>
                        <li><a href="/views/super_admin/pending_registrations.php" class="nav-link d-flex justify-content-between align-items-center <?php echo $current_page == 'pending_registrations.php' ? 'active' : ''; ?>"><span><i class="bi bi-person-check-fill me-2"></i>Pending Regs</span><?php if ($pending_regs_count > 0): ?><span class="badge bg-danger rounded-pill"><?php echo $pending_regs_count; ?></span><?php endif; ?></a></li>
                        <li><a href="/views/super_admin/pending_payments.php" class="nav-link d-flex justify-content-between align-items-center <?php echo $current_page == 'pending_payments.php' ? 'active' : ''; ?>"><span><i class="bi bi-credit-card-2-front-fill me-2"></i>Pending Payments</span><?php if ($pending_payments_count > 0): ?><span class="badge bg-danger rounded-pill"><?php echo $pending_payments_count; ?></span><?php endif; ?></a></li>
                        <li><a href="/views/super_admin/announcements.php" class="nav-link <?php echo $current_page == 'announcements.php' ? 'active' : ''; ?>"><i class="bi bi-megaphone-fill me-2"></i>Announcements</a></li>
                        <li><a href="/views/super_admin/financial_management.php" class="nav-link <?php echo $current_page == 'financial_management.php' ? 'active' : ''; ?>"><i class="bi bi-cash-coin me-2"></i>Financials</a></li>
                        <li><a href="/views/super_admin/packages.php" class="nav-link <?php echo $current_page == 'packages.php' ? 'active' : ''; ?>"><i class="bi bi-box-seam me-2"></i>Package Management</a></li>
                        <li><a href="/views/super_admin/report_templates.php" class="nav-link <?php echo $current_page == 'report_templates.php' ? 'active' : ''; ?>"><i class="bi bi-journal-text me-2"></i>Report Templates</a></li>
                        <li><a href="/views/super_admin/cms_management.php" class="nav-link <?php echo $current_page == 'cms_management.php' ? 'active' : ''; ?>"><i class="bi bi-file-earmark-text-fill me-2"></i>Landing Page CMS</a></li>
                        <li><a href="/views/super_admin/system_settings.php" class="nav-link <?php echo $current_page == 'system_settings.php' ? 'active' : ''; ?>"><i class="bi bi-gear me-2"></i>System Settings</a></li>
                    </ul>
                    <hr>
                    <div class="dropdown"><a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-person-circle me-2"></i><strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></a><ul class="dropdown-menu dropdown-menu-dark text-small shadow"><li><a class="dropdown-item" href="/controllers/auth_controller.php?action=logout">Sign out</a></li></ul></div>
                </div>
            </div>
        </div>

        <main id="main-content" class="p-4">
            <!-- Page content starts here -->