<?php

/**
 * Authentication Class
 * Handles user authentication and session management
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/password_manager.php';
require_once __DIR__ . '/session_manager.php';
require_once __DIR__ . '/csrf.php';

class Auth
{
    private $db;
    private $passwordManager;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->passwordManager = new PasswordManager();
    }

    /**
     * Start secure session
     */
    public function startSession()
    {
        SessionManager::init();
    }

    /**
     * Login user with username and password
     * Now supports both plain text and hashed passwords
     */
    public function login($username, $password)
    {
        try {
            $sql = "SELECT * FROM users WHERE username = ?";
            $user = $this->db->fetchOne($sql, [$username]);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid username or password.'
                ];
            }

            // Check if password is plain text or hashed
            if ($this->passwordManager->isPlainTextPassword($user['password'])) {
                // Plain text password - check directly
                if ($password === $user['password']) {
                    // Migrate to hashed password
                    $this->passwordManager->migratePlainTextPassword($user['id'], $password);

                    // Set secure session
                    SessionManager::setUserSession($user['id'], $user['username'], $user['role']);

                    return [
                        'success' => true,
                        'user' => [
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'role' => $user['role']
                        ],
                        'message' => 'Login successful. Your password has been securely updated.'
                    ];
                }
            } else {
                // Hashed password - verify using password_verify
                if ($this->passwordManager->verifyPassword($password, $user['password'])) {
                    // Check if password needs rehashing
                    if ($this->passwordManager->needsRehash($user['password'])) {
                        $this->passwordManager->migratePlainTextPassword($user['id'], $password);
                    }

                    // Set secure session
                    SessionManager::setUserSession($user['id'], $user['username'], $user['role']);

                    return [
                        'success' => true,
                        'user' => [
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'role' => $user['role']
                        ]
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn()
    {
        $this->startSession();
        return SessionManager::isLoggedIn();
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin()
    {
        return SessionManager::isAdmin();
    }

    /**
     * Get current user role
     */
    public function getUserRole()
    {
        return SessionManager::getUserRole();
    }

    /**
     * Get current user ID
     */
    public function getUserId()
    {
        return SessionManager::getUserId();
    }

    /**
     * Get current username
     */
    public function getUsername()
    {
        return SessionManager::getUsername();
    }

    /**
     * Require authentication - redirect to login if not authenticated
     */
    public function requireAuth($redirectUrl = null)
    {
        if ($redirectUrl === null) {
            require_once __DIR__ . '/base_url.php';
            $redirectUrl = BASE_URL . 'Login/';
        }
        if (!$this->isLoggedIn()) {
            header('Location: ' . $redirectUrl);
            exit();
        }
    }

    /**
     * Require admin role - redirect to dashboard if not admin
     */
    public function requireAdmin($redirectUrl = null)
    {
        if ($redirectUrl === null) {
            require_once __DIR__ . '/base_url.php';
            $redirectUrl = BASE_URL . 'dashboard/dashboard.php';
        }
        $this->requireAuth();
        if (!$this->isAdmin()) {
            header('Location: ' . $redirectUrl);
            exit();
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $this->startSession();
        SessionManager::destroy();
    }

    /**
     * Create new user (for signup functionality)
     * Now uses secure password hashing
     */
    public function createUser($username, $password, $role = 'user')
    {
        try {
            // Validate password strength
            $passwordErrors = $this->passwordManager->validatePasswordStrength($password);
            if (!empty($passwordErrors)) {
                return [
                    'success' => false,
                    'message' => 'Password validation failed: ' . implode(', ', $passwordErrors)
                ];
            }

            // Check if username already exists
            $existingUser = $this->db->fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
            if ($existingUser) {
                return [
                    'success' => false,
                    'message' => 'Username already exists'
                ];
            }

            // Hash the password
            $hashedPassword = $this->passwordManager->hashPassword($password);

            // Insert new user
            $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
            $this->db->query($sql, [$username, $hashedPassword, $role]);

            return [
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $this->db->lastInsertId()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update user information
     */
    public function updateUser($userId, $username, $password = null, $role = null)
    {
        try {
            $updates = [];
            $params = [];

            if (!empty($username)) {
                $updates[] = "username = ?";
                $params[] = $username;
            }

            if (!empty($password)) {
                $updates[] = "password = ?";
                $params[] = $password;
            }

            if (!empty($role)) {
                $updates[] = "role = ?";
                $params[] = $role;
            }

            if (empty($updates)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update'
                ];
            }

            $params[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $this->db->query($sql, $params);

            return [
                'success' => true,
                'message' => 'User updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($userId)
    {
        try {
            $sql = "DELETE FROM users WHERE id = ?";
            $this->db->query($sql, [$userId]);

            return [
                'success' => true,
                'message' => 'User deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all users (for admin functionality)
     */
    public function getAllUsers($search = '', $roleFilter = '')
    {
        try {
            $sql = "SELECT id, username, role, created_at FROM users WHERE 1=1";
            $params = [];

            if (!empty($search)) {
                $sql .= " AND username LIKE ?";
                $params[] = "%$search%";
            }

            if (!empty($roleFilter)) {
                $sql .= " AND role = ?";
                $params[] = $roleFilter;
            }

            $sql .= " ORDER BY created_at DESC";

            $users = $this->db->fetchAll($sql, $params);

            return [
                'success' => true,
                'users' => $users
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch users: ' . $e->getMessage()
            ];
        }
    }
}
