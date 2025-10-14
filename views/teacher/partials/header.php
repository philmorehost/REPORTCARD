<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// The init script will handle the installer check and load the config.
require_once __DIR__ . '/../../../core/init.php';
require_once __DIR__ . '/../../../core/auth_check.php';
require_auth('teacher');

// Fetch site-wide settings for branding
try {
    $stmt_site_settings = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    $site_settings = $stmt_site_settings->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $site_settings = [];
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Teacher Hub'; ?> - <?php echo htmlspecialchars($site_settings['site_name'] ?? 'ARS'); ?></title>

    <?php if (!empty($site_settings['site_favicon_url'])): ?>
    <link rel="icon" type="image/x-icon" href="/<?php echo htmlspecialchars($site_settings['site_favicon_url']); ?>">
    <?php endif; ?>

    <link rel="manifest" href="/manifest.php">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/views/teacher/dashboard.php">
                <i class="bi bi-pencil-square"></i> Teacher Grading Hub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="/views/teacher/dashboard.php">My Classes</a>
                    </li>
                </ul>
                <div class="d-flex">
                     <span class="navbar-text me-3">
                        Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                    <a href="/controllers/auth_controller.php?action=logout" class="btn btn-outline-light">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <main>
            <!-- Page content starts here -->