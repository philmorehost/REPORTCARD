<?php
// This is a public page, so no session or auth checks are needed.
require_once __DIR__ . '/../core/init.php';

$hash = $_GET['id'] ?? '';
if (empty($hash)) {
    http_response_code(400);
    die("Error: No report ID provided.");
}

// --- Fetch the report data snapshot ---
try {
    $stmt = $pdo->prepare("SELECT data_snapshot FROM published_reports WHERE unique_hash = :hash");
    $stmt->execute(['hash' => $hash]);
    $snapshot_json = $stmt->fetchColumn();

    if (!$snapshot_json) {
        http_response_code(404);
        die("Error: Report not found. The link may be invalid or the report may have been unpublished.");
    }

    $data = json_decode($snapshot_json, true);

    $template_id = $data['report_structure']['template_id'] ?? 1;
    $stmt_template = $pdo->prepare("SELECT file_path FROM report_templates WHERE id = :id");
    $stmt_template->execute(['id' => $template_id]);
    $template_file = $stmt_template->fetchColumn();

    if (!$template_file || !file_exists(__DIR__ . '/../' . $template_file)) {
        $template_file = null; // Fallback to a default display
    }

    $is_freemium = isset($data['school_info']['package_name']) && strtolower($data['school_info']['package_name']) === 'freemium';

} catch (PDOException $e) {
    http_response_code(500);
    die("Database error. Please try again later.");
}

// Helper function to safely get data from the snapshot. MUST BE IDENTICAL to the one in the preview controller.
function d_get($key, $default = '', $escape = true) {
    global $data;
    $keys = explode('.', $key);
    $value = $data;
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            // If we request a non-array with escape=false, return the raw default
            if (!$escape) return $default;
            return is_array($default) ? $default : htmlspecialchars($default ?? '');
        }
        $value = $value[$k];
    }
    // If the final value is an array, or if escaping is turned off, return it raw.
    if (is_array($value) || !$escape) {
        return $value;
    }
    return htmlspecialchars($value ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Report Card - <?php echo d_get('student_info.full_name'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .action-bar { margin: 2rem auto -1rem; max-width: 800px; }
        .report-container { position: relative; max-width: 800px; margin: 2rem auto; background: white; padding: 2rem; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .report-header { border-bottom: 2px solid <?php echo d_get('school_info.brand_color', '#333'); ?>; padding-bottom: 1rem; margin-bottom: 1rem; }
        .school-logo { max-height: 90px; max-width: 200px; }
        .title-bar { background-color: <?php echo d_get('school_info.brand_color', '#333'); ?>; color: white; }

        <?php if ($is_freemium): ?>
        .freemium-restricted { user-select: none; -webkit-user-select: none; -ms-user-select: none; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 5rem; color: rgba(0, 0, 0, 0.08); font-weight: bold; pointer-events: none; z-index: 1000; text-transform: uppercase; }
        <?php endif; ?>

        @media print {
            /* This section controls the printed page */
            @page {
                size: auto;
                margin: 0mm; /* Remove browser-default margins */
            }

            body {
                background-color: white;
                margin: 0; /* Remove body margin for print */
            }
            .action-bar {
                display: none;
            }
            .report-container {
                margin: 1.5cm; /* Add margin to the report card itself for printing */
                padding: 0;
                box-shadow: none;
                border: none;
                max-width: 100%; /* Allow it to fill the page width */
            }
            .freemium-restricted {
                user-select: auto;
                -webkit-user-select: auto;
                -ms-user-select: auto;
            }
        }
    </style>
</head>
<body <?php if ($is_freemium) echo 'class="freemium-restricted"'; ?>>

    <?php if (!$is_freemium): ?>
        <div class="action-bar text-end">
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer-fill me-2"></i>Print / Download PDF</button>
        </div>
    <?php endif; ?>

    <div class="report-container">
        <?php if ($is_freemium): ?>
            <div class="alert alert-warning text-center">
                <strong>Freemium Plan:</strong> Printing, copying, and downloading are disabled. <a href="#" onclick="alert('Please contact your school administrator to upgrade to a Premium plan.'); return false;">Upgrade to Premium</a> to unlock all features.
            </div>
            <div class="watermark"><span><?php echo d_get('site_name', 'Freemium Plan'); ?></span></div>
        <?php endif; ?>

        <?php if ($template_file): ?>
            <?php include __DIR__ . '/../' . $template_file; ?>
        <?php else: ?>
            <header class="report-header d-flex justify-content-between align-items-center">
                <div><?php if (d_get('school_info.logo_url')): ?><img src="<?php echo rtrim(d_get('system_url', (defined('APP_URL') ? APP_URL : '')), '/'); ?>/<?php echo ltrim(d_get('school_info.logo_url'), '/'); ?>" alt="School Logo" class="school-logo"><?php endif; ?></div>
                <div class="text-end"><h3 class="mb-0"><?php echo d_get('school_info.name'); ?></h3></div>
            </header>
            <div class="title-bar text-center p-2 my-3"><h4 class="mb-0">STUDENT REPORT CARD</h4></div>
            <p><strong>Student:</strong> <?php echo d_get('student_info.full_name'); ?></p>
            <p><strong>Class:</strong> <?php echo d_get('student_info.class'); ?></p>
            <p><strong>Academic Period:</strong> <?php echo d_get('academic_period'); ?></p>
            <p class="text-danger">Error: Report card template could not be loaded. Displaying basic data.</p>
        <?php endif; ?>
        
        </div>

    <?php if ($is_freemium): ?>
    <script>
        document.addEventListener('contextmenu', event => event.preventDefault());
        document.addEventListener('keydown', function(event) {
            if ((event.ctrlKey || event.metaKey) && (event.key === 's' || event.key === 'p' || event.key === 'c')) {
                event.preventDefault();
                alert('Saving, printing, or copying this report card is a Premium feature.');
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>