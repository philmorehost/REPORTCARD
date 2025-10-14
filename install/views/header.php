<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARS Installer | <?php echo $page_title ?? 'Welcome'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
        }
        .installer-container {
            max-width: 700px;
            margin-top: 5rem;
        }
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
        }
        .progress-bar {
            background-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container installer-container">
        <div class="text-center mb-4">
            <h1 class="h2">Automated Report Card System</h1>
            <p class="lead">Installation Wizard</p>
        </div>
        <div class="card">
            <div class="card-body p-4 p-md-5">
                <!-- Progress Bar -->
                <?php if (isset($stage)): ?>
                <div class="progress mb-4" style="height: 25px;">
                    <div class="progress-bar" role="progressbar" style="width: <?php echo (25 * $stage); ?>%;" aria-valuenow="<?php echo (25 * $stage); ?>" aria-valuemin="0" aria-valuemax="100"><?php echo "Stage $stage of 4"; ?></div>
                </div>
                <?php endif; ?>

                <!-- Page content starts here -->