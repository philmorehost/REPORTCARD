<?php
// This script generates a dynamic manifest.json file for the PWA.

// We need to load the configuration to get database access and settings.
// We use a try-catch block to prevent errors if the app isn't installed yet.
try {
    require_once __DIR__ . '/core/init.php';

    // Fetch necessary settings from the database
    $stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('site_name', 'site_logo_url')");
    $settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);

    $site_name = $settings['site_name'] ?? 'ARS';
    // Use the site logo for both icon sizes. A real app might generate these sizes.
    $logo_url = !empty($settings['site_logo_url']) ? '/' . $settings['site_logo_url'] : '/assets/icons/default-logo.png'; // Fallback to a default

} catch (Exception $e) {
    // Fallback to default values if the database isn't ready
    $site_name = 'Automated Report Card System';
    $logo_url = '/assets/icons/default-logo.png';
}


// The manifest data
$manifest = [
    'name' => $site_name,
    'short_name' => $site_name,
    'start_url' => '/',
    'display' => 'standalone',
    'background_color' => '#ffffff',
    'theme_color' => '#343a40',
    'icons' => [
        [
            'src' => $logo_url,
            'sizes' => '192x192',
            'type' => 'image/png'
        ],
        [
            'src' => $logo_url,
            'sizes' => '512x512',
            'type' => 'image/png'
        ]
    ]
];

// Set the content type header to application/json and output the manifest
header('Content-Type: application/json');
echo json_encode($manifest);
?>