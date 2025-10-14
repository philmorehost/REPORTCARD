<?php
// --- Database Configuration ---
define('DB_HOST', 'localhost');
define('DB_USER', 'schoolreport_card');
define('DB_PASS', 'Eben@234.com');
define('DB_NAME', 'schoolreport_card');

// --- Application Paths ---
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' ? 'https://' : 'http://';
define('APP_URL', $protocol . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));
define('APP_ROOT', dirname(__DIR__));

// --- Other Settings ---
date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect to the database. " . $e->getMessage());
}
?>