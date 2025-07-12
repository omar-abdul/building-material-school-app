
<?php
/**
 * Login Backend Handler
 * Uses centralized authentication system with CSRF protection
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/utils.php';
require_once __DIR__ . '/../config/csrf.php';

$auth = new Auth();
$auth->startSession();

// Handle login request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate CSRF token
    if (!CSRF::validatePostToken()) {
        Utils::sendErrorResponse('Invalid request token', 403);
    }
    
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    // Sanitize inputs
    $username = Utils::sanitizeInput($username);
    $password = Utils::sanitizeInput($password);

    // Attempt login
    $result = $auth->login($username, $password);
    
    Utils::sendJsonResponse($result);
}
?>












