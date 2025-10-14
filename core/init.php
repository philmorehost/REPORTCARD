<?php
/**
 * init.php - Core application initialization script.
 *
 * This script is responsible for checking if the application is installed
 * and loading the core configuration. It should be included by all
 * entry-point files.
 */

// Define the path to the config file.
$config_path = __DIR__ . '/../config/config.php';
$installer_path = __DIR__ . '/../install/index.php';

// Check if the config file exists. If not, the app is not installed.
if (!file_exists($config_path)) {
    // If the installer exists, load it and stop execution.
    if (file_exists($installer_path)) {
        // By including the installer, we show the setup screen without a redirect,
        // which avoids URL pathing issues.
        include($installer_path);
        exit;
    } else {
        // This is a critical error state.
        die('CRITICAL ERROR: Configuration file is missing and the installer could not be found.');
    }
}

// If the config file exists, load it to make the database connection
// and other constants available.
require_once $config_path;

// Run the database upgrade script to ensure the schema is up-to-date.
require_once __DIR__ . '/upgrade.php';

// Load the internationalization (i18n) core functions.
require_once __DIR__ . '/i18n.php';

// Load global helper functions.
require_once __DIR__ . '/helpers.php';

?>