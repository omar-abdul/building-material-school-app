<?php
/**
 * Logout Handler
 * Uses centralized authentication system
 */

require_once __DIR__ . '/../config/auth.php';

$auth = new Auth();
$auth->logout();

// Redirect to login page
header('Location: /backend/dashboard/index.php');
exit();
?>
