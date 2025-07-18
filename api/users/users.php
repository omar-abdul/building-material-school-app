<?php

/**
 * Users API
 * Handles user CRUD operations and authentication
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
        case 'getUsers':
            getUsers();
            break;
        case 'getUser':
            getUser();
            break;
        case 'addUser':
            addUser();
            break;
        case 'updateUser':
            updateUser();
            break;
        case 'deleteUser':
            deleteUser();
            break;
        case 'changePassword':
            changePassword();
            break;
        default:
            Utils::sendErrorResponse('Invalid action');
            break;
    }
} catch (Exception $e) {
    Utils::sendErrorResponse($e->getMessage());
}

function getUsers()
{
    global $db;

    $search = $_GET['search'] ?? '';
    $role = $_GET['role'] ?? '';

    $query = "SELECT id, username, role, created_at 
              FROM users WHERE 1=1";

    $params = [];

    if (!empty($search)) {
        $query .= " AND username LIKE ?";
        $searchParam = "%$search%";
        $params[] = $searchParam;
    }

    if (!empty($role)) {
        $query .= " AND role = ?";
        $params[] = $role;
    }

    $query .= " ORDER BY created_at DESC";

    try {
        $users = $db->fetchAll($query, $params);
        Utils::sendSuccessResponse('Users retrieved successfully', $users);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve users: ' . $e->getMessage());
    }
}

function getUser()
{
    global $db;

    $userId = $_GET['userId'] ?? '';

    if (empty($userId)) {
        Utils::sendErrorResponse('User ID is required');
        return;
    }

    try {
        $user = $db->fetchOne("SELECT id, username, role, created_at 
                               FROM users WHERE id = ?", [$userId]);

        if ($user) {
            Utils::sendSuccessResponse('User retrieved successfully', $user);
        } else {
            Utils::sendErrorResponse('User not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve user: ' . $e->getMessage());
    }
}

function addUser()
{
    global $db;

    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $required = ['username', 'password', 'role'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            Utils::sendErrorResponse("$field is required");
            return;
        }
    }

    // Validate password strength
    if (strlen($data['password']) < 6) {
        Utils::sendErrorResponse('Password must be at least 6 characters long');
        return;
    }

    // Check if username already exists
    $existingUser = $db->fetchOne("SELECT id FROM users WHERE username = ?", [$data['username']]);
    if ($existingUser) {
        Utils::sendErrorResponse('Username already exists');
        return;
    }

    // Hash password
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    try {
        $query = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";

        $db->query($query, [
            $data['username'],
            $hashedPassword,
            $data['role']
        ]);

        $userId = $db->lastInsertId();
        Utils::sendSuccessResponse('User created successfully', ['userId' => $userId]);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to create user: ' . $e->getMessage());
    }
}

function updateUser()
{
    global $db;

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['userId'])) {
        Utils::sendErrorResponse('User ID is required');
        return;
    }

    // Get current user
    $currentUser = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$data['userId']]);
    if (!$currentUser) {
        Utils::sendErrorResponse('User not found');
        return;
    }

    // Check if username already exists (excluding current user)
    if (!empty($data['username'])) {
        $existingUser = $db->fetchOne(
            "SELECT id FROM users WHERE username = ? AND id != ?",
            [$data['username'], $data['userId']]
        );
        if ($existingUser) {
            Utils::sendErrorResponse('Username already exists');
            return;
        }
    }

    // Build update query
    $updateFields = [];
    $params = [];

    if (!empty($data['username'])) {
        $updateFields[] = "username = ?";
        $params[] = $data['username'];
    }

    if (!empty($data['password'])) {
        if (strlen($data['password']) < 6) {
            Utils::sendErrorResponse('Password must be at least 6 characters long');
            return;
        }
        $updateFields[] = "password = ?";
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    if (!empty($data['role'])) {
        $updateFields[] = "role = ?";
        $params[] = $data['role'];
    }

    if (empty($updateFields)) {
        Utils::sendErrorResponse('No fields to update');
        return;
    }

    $params[] = $data['userId'];

    try {
        $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $db->query($query, $params);

        Utils::sendSuccessResponse('User updated successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update user: ' . $e->getMessage());
    }
}

function deleteUser()
{
    global $db;

    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['userId'] ?? '';

    if (empty($userId)) {
        Utils::sendErrorResponse('User ID is required');
        return;
    }

    // Check if user exists
    $user = $db->fetchOne("SELECT id FROM users WHERE id = ?", [$userId]);
    if (!$user) {
        Utils::sendErrorResponse('User not found');
        return;
    }

    // Prevent deletion of last admin user
    $adminCount = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")['count'];
    $isAdmin = $db->fetchOne("SELECT role FROM users WHERE id = ?", [$userId])['role'];

    if ($isAdmin === 'admin' && $adminCount <= 1) {
        Utils::sendErrorResponse('Cannot delete the last admin user');
        return;
    }

    try {
        $db->query("DELETE FROM users WHERE id = ?", [$userId]);
        Utils::sendSuccessResponse('User deleted successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete user: ' . $e->getMessage());
    }
}

function changePassword()
{
    global $db, $auth;

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['currentPassword']) || empty($data['newPassword'])) {
        Utils::sendErrorResponse('Current password and new password are required');
        return;
    }

    // Get current user
    $currentUserId = $auth->getUserId();
    if (!$currentUserId) {
        Utils::sendErrorResponse('User not authenticated');
        return;
    }

    $user = $db->fetchOne("SELECT password FROM users WHERE id = ?", [$currentUserId]);
    if (!$user) {
        Utils::sendErrorResponse('User not found');
        return;
    }

    // Verify current password
    if (!password_verify($data['currentPassword'], $user['password'])) {
        Utils::sendErrorResponse('Current password is incorrect');
        return;
    }

    // Validate new password
    if (strlen($data['newPassword']) < 6) {
        Utils::sendErrorResponse('New password must be at least 6 characters long');
        return;
    }

    try {
        $hashedPassword = password_hash($data['newPassword'], PASSWORD_DEFAULT);
        $db->query(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hashedPassword, $currentUserId]
        );

        Utils::sendSuccessResponse('Password changed successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to change password: ' . $e->getMessage());
    }
}
