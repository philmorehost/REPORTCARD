<?php
/**
 * helpers.php - Global helper functions for the application.
 */

// Global variables to cache settings and content
$app_settings = null;
$app_content = null;

/**
 * Fetches a system setting value.
 *
 * @param PDO $pdo The database connection object.
 * @param string $key The setting key to fetch.
 * @param mixed $default The default value to return if the key is not found.
 * @return mixed The setting value.
 */
function s_get($pdo, $key, $default = '') {
    global $app_settings;

    // Load settings from DB only once per request
    if ($app_settings === null) {
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
            $app_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            $app_settings = []; // Prevent further errors on this request
        }
    }

    return htmlspecialchars($app_settings[$key] ?? $default);
}


/**
 * Fetches multiple system settings at once.
 *
 * @param PDO $pdo The database connection object.
 * @param array $keys The array of setting keys to fetch.
 * @return array An associative array of the requested settings.
 */
function get_system_settings($pdo, $keys = []) {
    global $app_settings;
    $results = [];

    // Load settings from DB only once per request if not already loaded
    if ($app_settings === null) {
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
            $app_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            $app_settings = []; // Prevent further errors on this request
        }
    }

    if (empty($keys)) {
        return $app_settings; // Return all settings if no specific keys are requested
    }

    foreach ($keys as $key) {
        // Note: We don't use htmlspecialchars here because the consumer might need the raw value
        $results[$key] = $app_settings[$key] ?? null;
    }

    return $results;
}


/**
 * Fetches a CMS content value.
 *
 * @param PDO $pdo The database connection object.
 * @param string $key The content key to fetch.
 * @param mixed $default The default value to return if the key is not found.
 * @return mixed The content value.
 */
function c_get($pdo, $key, $default = '') {
    global $app_content;

    // Load content from DB only once per request
    if ($app_content === null) {
        try {
            $stmt = $pdo->query("SELECT content_key, content_value FROM cms_content");
            $app_content = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            $app_content = []; // Prevent further errors
        }
    }

    return htmlspecialchars($app_content[$key] ?? $default);
}

/**
 * Gets the currency symbol for the application.
 *
 * @param PDO $pdo The database connection object.
 * @return string The currency symbol.
 */
function get_currency_symbol($pdo) {
    return s_get($pdo, 'currency_symbol', '$');
}

/**
 * Imports an SQL file into the database, executing statements one by one.
 *
 * @param PDO $pdo The database connection object.
 * @param string $sql_file_path The path to the .sql file.
 * @throws Exception if the file cannot be found or if a query fails.
 */
function import_sql_file($pdo, $sql_file_path) {
    if (!file_exists($sql_file_path)) {
        throw new Exception("SQL file not found: {$sql_file_path}");
    }

    $templine = '';
    $lines = file($sql_file_path);

    if ($lines === false) {
        throw new Exception("Could not read SQL file: {$sql_file_path}");
    }

    foreach ($lines as $line) {
        // Skip comments
        if (substr($line, 0, 2) == '--' || trim($line) == '') {
            continue;
        }

        $templine .= $line;

        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';') {
            try {
                $pdo->exec($templine);
            } catch (PDOException $e) {
                // Log the error and re-throw to notify the installer
                error_log("SQL Import Error on query: " . $templine . " | Error: " . $e->getMessage());
                throw $e;
            }
            // Reset temp variable
            $templine = '';
        }
    }
}

/**
 * Gets the base URL of the site.
 *
 * @return string The base URL.
 */
function get_site_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . $host;
}

/**
 * Gets the domain for a given school, falling back to the system default.
 *
 * @param PDO $pdo The database connection object.
 * @param int $school_id The ID of the school.
 * @return string The domain to use for links.
 */
function get_school_domain($pdo, $school_id) {
    try {
        $stmt = $pdo->prepare("SELECT custom_domain FROM schools WHERE id = :id");
        $stmt->execute(['id' => $school_id]);
        $school = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($school && !empty($school['custom_domain'])) {
            // Ensure the custom domain has a protocol for consistency
            if (strpos($school['custom_domain'], 'http') !== 0) {
                return 'https://' . $school['custom_domain'];
            }
            return rtrim($school['custom_domain'], '/');
        }
    } catch (PDOException $e) {
        error_log("Could not fetch school custom domain: " . $e->getMessage());
        // Fall through to default
    }

    // Fallback to the main system URL
    return rtrim(s_get($pdo, 'system_url', get_site_url()), '/');
}
?>