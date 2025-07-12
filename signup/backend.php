<?php
/**
 * User Management Backend API
 * Uses centralized authentication and database system
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/utils.php';

$auth = new Auth();
$auth->startSession();

// Set headers for API
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_users':
            handleGetUsers($auth);
            break;
        case 'get_user':
            handleGetUser($auth);
            break;
        case 'add_user':
            handleAddUser($auth);
            break;
        case 'edit_user':
            handleEditUser($auth);
            break;
        case 'delete_user':
            handleDeleteUser($auth);
            break;
        case 'signup_user':
            handleSignupUser($auth);
            break;
        default:
            Utils::sendErrorResponse('Invalid action');
    }
} catch(Exception $e) {
    Utils::logError('User Management API Error: ' . $e->getMessage());
    Utils::sendErrorResponse('Database error: ' . $e->getMessage());
}

function handleGetUsers($auth) {
    $search = $_POST['search'] ?? '';
    $roleFilter = $_POST['roleFilter'] ?? '';
    
    $result = $auth->getAllUsers($search, $roleFilter);
    Utils::sendJsonResponse($result);
}



function handleDeleteUser($auth) {
    $id = $_POST['id'] ?? 0;
    
    // Sanitize input
    $id = Utils::sanitizeInput($id);
    
    if (empty($id)) {
        Utils::sendErrorResponse('User ID is required');
        return;
    }
    
    $result = $auth->deleteUser($id);
    Utils::sendJsonResponse($result);
}

function handleSignupUser($auth) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Sanitize inputs
    $username = Utils::sanitizeInput($username);
    $password = Utils::sanitizeInput($password);
    $confirmPassword = Utils::sanitizeInput($confirmPassword);
    
    // Validate inputs
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        Utils::sendErrorResponse('All fields are required');
        return;
    }
    
    if ($password !== $confirmPassword) {
        Utils::sendErrorResponse('Passwords do not match');
        return;
    }
    
    // Create user using centralized authentication
    $result = $auth->createUser($username, $password, 'user');
    Utils::sendJsonResponse($result);
}



function handleGetUser($conn) {
    $id = $_POST['id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT id, username, role, password, created_at FROM users WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Return the actual password (not recommended for security reasons)
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
}





// 1. handleAddUser (Add new user)
function handleAddUser($auth) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    // Sanitize inputs
    $username = Utils::sanitizeInput($username);
    $password = Utils::sanitizeInput($password);
    $confirmPassword = Utils::sanitizeInput($confirmPassword);
    $role = Utils::sanitizeInput($role);
    
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        Utils::sendErrorResponse('All fields are required');
        return;
    }
    
    if ($password !== $confirmPassword) {
        Utils::sendErrorResponse('Passwords do not match');
        return;
    }
    
    // Create user using centralized authentication
    $result = $auth->createUser($username, $password, $role);
    Utils::sendJsonResponse($result);
}

// =====================================
// 2. handleEditUser (Cusboonaysii user)

function handleEditUser($conn) {
    $id = $_POST['id'] ?? 0;
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        return;
    }
    
    if (!empty($password) && $password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        return;
    }
    
    // Check if username exists (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        return;
    }
    
   
    // Haddii password la bixin, isticmaal plain text
    
    if (!empty($password)) {
        $plainPassword = $password; // No hashing
        $query = "UPDATE users SET username = :username, password = :password, role = :role WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $plainPassword); // Plain text password
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $id);
    } else {
        $query = "UPDATE users SET username = :username, role = :role WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update user']);
    }
}


?>