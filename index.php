<?php

/**
 * Root Bootstrap File
 * Automatically redirects users based on authentication status
 */

require_once __DIR__ . '/config/base_url.php';
require_once __DIR__ . '/config/auth.php';

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
