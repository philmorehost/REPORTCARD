<?php
/**
 * ad_click.php - Handles tracking clicks on banner ads and redirecting users.
 */

require_once __DIR__ . '/../core/init.php';

$banner_id = $_GET['id'] ?? 0;

if (!$banner_id) {
    // No ID provided, just redirect to the homepage.
    header('Location: /index.php');
    exit;
}

try {
    // 1. Get the target link for the banner
    $stmt_get = $pdo->prepare("SELECT target_link FROM banner_ads WHERE id = :id");
    $stmt_get->execute([':id' => $banner_id]);
    $target_link = $stmt_get->fetchColumn();

    if ($target_link) {
        // 2. Increment the click count for the banner
        $stmt_update = $pdo->prepare("UPDATE banner_ads SET click_count = click_count + 1 WHERE id = :id");
        $stmt_update->execute([':id' => $banner_id]);

        // 3. Redirect the user to the target link
        header('Location: ' . $target_link);
        exit;
    } else {
        // Banner not found, redirect to the homepage
        header('Location: /index.php');
        exit;
    }
} catch (PDOException $e) {
    // Log the error and redirect to the homepage
    error_log("Ad click tracking error: " . $e->getMessage());
    header('Location: /index.php');
    exit;
}
?>
