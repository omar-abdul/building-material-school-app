<?php

/**
 * Password Manager Class
 * Handles password hashing, verification, and migration
 */

require_once __DIR__ . '/database.php';

class PasswordManager
{
    private $db;

    // Password hashing options
    const HASH_ALGO = PASSWORD_DEFAULT;
    const HASH_COST = 12; // Higher cost = more secure but slower

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Hash a password
     */
    public function hashPassword($password)
    {
        return password_hash($password, self::HASH_ALGO, ['cost' => self::HASH_COST]);
    }

    /**
     * Verify a password against a hash
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if a password needs to be rehashed (for security updates)
     */
    public function needsRehash($hash)
    {
        return password_needs_rehash($hash, self::HASH_ALGO, ['cost' => self::HASH_COST]);
    }

    /**
     * Validate password strength
     */
    public function validatePasswordStrength($password)
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }



        return $errors;
    }

    /**
     * Generate a secure random password
     */
    public function generateSecurePassword($length = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
        $password = '';

        // Ensure at least one of each required character type
        $password .= chr(rand(65, 90)); // Uppercase
        $password .= chr(rand(97, 122)); // Lowercase
        $password .= chr(rand(48, 57)); // Number
        $password .= '!@#$%^&*'[rand(0, 7)]; // Special character

        // Fill the rest randomly
        for ($i = 4; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }

        // Shuffle the password
        return str_shuffle($password);
    }

    /**
     * Migrate plain text passwords to hashed passwords
     * This is called during login for users with plain text passwords
     */
    public function migratePlainTextPassword($userId, $plainTextPassword)
    {
        try {
            $hashedPassword = $this->hashPassword($plainTextPassword);

            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $this->db->query($sql, [$hashedPassword, $userId]);

            return true;
        } catch (Exception $e) {
            error_log("Password migration failed for user ID $userId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a password is stored as plain text (not hashed)
     */
    public function isPlainTextPassword($password)
    {
        // If password doesn't start with $2y$ (bcrypt hash), it's likely plain text
        return !preg_match('/^\$2y\$/', $password);
    }

    /**
     * Get password statistics (for admin dashboard)
     */
    public function getPasswordStats()
    {
        try {
            $stats = [];

            // Total users
            $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM users");
            $stats['total_users'] = $result['total'];

            // Users with hashed passwords
            $result = $this->db->fetchOne("SELECT COUNT(*) as hashed FROM users WHERE password LIKE '\$2y\$%'");
            $stats['hashed_passwords'] = $result['hashed'];

            // Users with plain text passwords
            $stats['plain_text_passwords'] = $stats['total_users'] - $stats['hashed_passwords'];

            // Migration percentage
            $stats['migration_percentage'] = $stats['total_users'] > 0
                ? round(($stats['hashed_passwords'] / $stats['total_users']) * 100, 2)
                : 0;

            return $stats;
        } catch (Exception $e) {
            error_log("Failed to get password stats: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Force migration of all plain text passwords (admin function)
     */
    public function forceMigrateAllPasswords()
    {
        try {
            // Get all users with plain text passwords
            $sql = "SELECT id, password FROM users WHERE password NOT LIKE '\$2y\$%'";
            $users = $this->db->fetchAll($sql);

            $migrated = 0;
            $failed = 0;

            foreach ($users as $user) {
                // Hash the plain text password
                $hashedPassword = $this->hashPassword($user['password']);

                // Update the user
                $updateSql = "UPDATE users SET password = ? WHERE id = ?";
                if ($this->db->query($updateSql, [$hashedPassword, $user['id']])) {
                    $migrated++;
                } else {
                    $failed++;
                }
            }

            return [
                'success' => true,
                'migrated' => $migrated,
                'failed' => $failed,
                'total' => count($users)
            ];
        } catch (Exception $e) {
            error_log("Force password migration failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
