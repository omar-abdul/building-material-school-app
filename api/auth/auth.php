<?php

/**
 * Auth API
 * Handles authentication-related API endpoints
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/utils.php';
require_once __DIR__ . '/../../config/auth.php';

// Set headers for API
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$db = Database::getInstance();
$auth = new Auth();

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'completeSetup':
            completeSetup();
            break;
        default:
            Utils::sendErrorResponse('Invalid action');
            break;
    }
} catch (Exception $e) {
    Utils::sendErrorResponse($e->getMessage());
}

function completeSetup()
{
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Mark setup as complete
    $_SESSION['setup_complete'] = true;
    $_SESSION['initial_setup'] = false;

    Utils::sendSuccessResponse('Setup completed successfully');
}
