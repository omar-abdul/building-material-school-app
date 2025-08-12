<?php

/**
 * Base URL Configuration
 * Automatically detects the base URL for the application
 * This makes the application portable regardless of folder name
 */

// Get the current script path
$script_path = $_SERVER['SCRIPT_NAME'];

// Always detect the application root by looking for the config directory
$current_path = $script_path;
$app_root = '/';

// Walk up the path until we find the config directory or reach root
while ($current_path !== '/' && $current_path !== '.') {
    $current_path = dirname($current_path);
    if ($current_path === '.') {
        $current_path = '/';
    }

    // Check if config directory exists at this level
    $config_path = $_SERVER['DOCUMENT_ROOT'] . $current_path . '/config';
    if (is_dir($config_path)) {
        $app_root = $current_path;
        break;
    }
}

$dir_path = $app_root;

// Ensure the path ends with a slash
if (substr($dir_path, -1) !== '/') {
    $dir_path .= '/';
}

// Define the base URL constant
define('BASE_URL', $dir_path);

// Also define a version without trailing slash for flexibility
define('BASE_URL_NO_TRAILING', rtrim($dir_path, '/'));

// For API calls, we need the full path to the api directory
define('API_BASE_URL', BASE_URL . 'api/');

// For assets (CSS, JS, images)
define('ASSETS_BASE_URL', BASE_URL . 'assets/');

// Debug information (can be removed in production)
if (isset($_GET['debug_base_url'])) {
    echo "Script Path: " . $script_path . "<br>";
    echo "Directory Path: " . $dir_path . "<br>";
    echo "BASE_URL: " . BASE_URL . "<br>";
    echo "API_BASE_URL: " . API_BASE_URL . "<br>";
    exit;
}
