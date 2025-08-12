<?php

/**
 * Root Bootstrap File
 * Automatically redirects users based on installation status and authentication
 */

// Check if system is installed
$installedLockFile = __DIR__ . '/config/installed.lock';
$databaseConfigFile = __DIR__ . '/config/database.php';

// Debug information (remove in production)
if (isset($_GET['debug'])) {
    echo "Debug Information:<br>";
    echo "Installed Lock File: " . $installedLockFile . " - Exists: " . (file_exists($installedLockFile) ? 'Yes' : 'No') . "<br>";
    echo "Database Config File: " . $databaseConfigFile . " - Exists: " . (file_exists($databaseConfigFile) ? 'Yes' : 'No') . "<br>";
    if (file_exists($installedLockFile)) {
        echo "Lock File Content: " . file_get_contents($installedLockFile) . "<br>";
    }
    exit();
}

if (!file_exists($installedLockFile) || !file_exists($databaseConfigFile)) {
    // Not installed, redirect to installation
    header('Location: install.php');
    exit();
}

// Check if we can read the lock file content
$lockContent = file_get_contents($installedLockFile);
if (empty($lockContent)) {
    // Lock file is empty or unreadable, redirect to installation
    header('Location: install.php');
    exit();
}

require_once __DIR__ . '/config/base_url.php';
require_once __DIR__ . '/config/auth.php';

// Run migrations and initial setup checks
require_once __DIR__ . '/config/init.php';

// If we reach here, migrations are complete and we can proceed with normal flow
$auth = new Auth();
$auth->startSession();

// Check if user is logged in
if ($auth->isLoggedIn()) {
    // User is logged in, redirect to dashboard
    header('Location: ' . BASE_URL . 'dashboard/');
    exit();
} else {
    // User is not logged in, redirect to login page
    header('Location: ' . BASE_URL . 'Login/');
    exit();
}
