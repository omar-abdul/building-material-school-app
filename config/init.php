<?php

/**
 * Application Initialization and Migration
 * Checks for required data and sets up initial configuration
 */

// Start output buffering to prevent header issues
ob_start();

require_once __DIR__ . '/base_url.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

$db = Database::getInstance();

// Check if we have the required initial data
$hasAdminUser = false;
$hasUncategorizedCategory = false;

try {
    // Check for admin user
    $adminUser = $db->fetchOne("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $hasAdminUser = $adminUser !== false;

    // Check for Uncategorized category (case insensitive)
    $uncategorizedCategory = $db->fetchOne("SELECT CategoryID FROM categories WHERE LOWER(CategoryName) = LOWER('Uncategorized') LIMIT 1");
    $hasUncategorizedCategory = $uncategorizedCategory !== false;

    // If both exist, proceed with normal bootstrap
    if ($hasAdminUser && $hasUncategorizedCategory) {
        // Clear any output buffer
        ob_end_clean();
        // Redirect to normal index.php bootstrap
        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }

    // If we're missing data, set up the required initial configuration
    if (!$hasUncategorizedCategory) {
        // Create Uncategorized category (let auto-increment handle the ID)
        try {
            $db->query("INSERT INTO categories (CategoryName, Description) VALUES ('Uncategorized', 'Default category for uncategorized items')");
        } catch (Exception $e) {
            // Category might already exist, continue
        }
    }

    // If no admin user exists, redirect to user registration
    if (!$hasAdminUser) {
        // Store a flag in session to indicate this is initial setup
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['initial_setup'] = true;
        $_SESSION['setup_complete'] = false;

        // Clear any output buffer
        ob_end_clean();
        // Redirect to user registration page
        header('Location: ' . BASE_URL . 'Register/');
        exit();
    }

    // If we reach here, we have admin user but missing category, redirect to normal flow
    ob_end_clean();
    header('Location: ' . BASE_URL . 'index.php');
    exit();
} catch (Exception $e) {
    // If there's a database error, redirect to normal flow
    // This allows the application to work even if migrations fail
    ob_end_clean();
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}
