<?php

/**
 * Logout Page
 * Handles user logout and session cleanup
 */

require_once __DIR__ . '/../config/base_url.php';
require_once __DIR__ . '/../config/auth.php';

$auth = new Auth();
$auth->startSession();

// Perform logout
$auth->logout();

// Redirect to login page
header('Location: ' . BASE_URL . 'Login/');
exit();
