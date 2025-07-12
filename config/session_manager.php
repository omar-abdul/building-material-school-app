<?php
/**
 * Session Manager Class
 * Enhanced session management with security features
 */

class SessionManager {
    
    // Session configuration
    const SESSION_TIMEOUT = 1800; // 30 minutes
    const SESSION_REGEN_TIME = 300; // 5 minutes
    const REMEMBER_ME_TIMEOUT = 2592000; // 30 days
    
    /**
     * Initialize secure session
     */
    public static function init() {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', self::isHttps());
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set session timeout
        self::setSessionTimeout();
        
        // Regenerate session ID if needed
        self::regenerateIfNeeded();
        
        // Validate session
        self::validateSession();
    }
    
    /**
     * Check if HTTPS is being used
     */
    private static function isHttps() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
    }
    
    /**
     * Set session timeout
     */
    private static function setSessionTimeout() {
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        }
        
        // Check if session has expired
        if (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT) {
            self::destroy();
            return;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Regenerate session ID if needed
     */
    private static function regenerateIfNeeded() {
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        }
        
        // Regenerate session ID every 5 minutes
        if (time() - $_SESSION['created'] > self::SESSION_REGEN_TIME) {
            self::regenerate();
        }
    }
    
    /**
     * Validate session data
     */
    private static function validateSession() {
        // Check if user agent has changed (session hijacking protection)
        if (isset($_SESSION['user_agent'])) {
            if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
                self::destroy();
                return;
            }
        } else {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }
        
        // Check if IP has changed (optional - can be disabled for mobile users)
        if (isset($_SESSION['ip_address'])) {
            if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
                // Log potential session hijacking attempt
                error_log("Potential session hijacking: IP changed from {$_SESSION['ip_address']} to {$_SERVER['REMOTE_ADDR']}");
                // Uncomment the next line to enable strict IP checking
                // self::destroy();
                // return;
            }
        } else {
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        }
    }
    
    /**
     * Regenerate session ID
     */
    public static function regenerate() {
        // Save current session data
        $oldSessionData = $_SESSION;
        
        // Regenerate session ID
        session_regenerate_id(true);
        
        // Restore session data
        $_SESSION = $oldSessionData;
        $_SESSION['created'] = time();
    }
    
    /**
     * Set user session data
     */
    public static function setUserSession($userId, $username, $role, $rememberMe = false) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['created'] = time();
        $_SESSION['last_activity'] = time();
        
        if ($rememberMe) {
            self::setRememberMe($userId);
        }
    }
    
    /**
     * Set remember me cookie
     */
    private static function setRememberMe($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + self::REMEMBER_ME_TIMEOUT;
        
        // Store token in database (you'll need to add a remember_me_tokens table)
        // For now, we'll just set a cookie
        setcookie('remember_me', $token, $expires, '/', '', self::isHttps(), true);
        $_SESSION['remember_me_token'] = $token;
    }
    
    /**
     * Check remember me cookie
     */
    public static function checkRememberMe() {
        if (isset($_COOKIE['remember_me']) && !isset($_SESSION['logged_in'])) {
            // Validate remember me token
            // This would typically involve checking the database
            // For now, we'll just return false
            return false;
        }
        return false;
    }
    
    /**
     * Clear remember me cookie
     */
    public static function clearRememberMe() {
        setcookie('remember_me', '', time() - 3600, '/', '', self::isHttps(), true);
        unset($_SESSION['remember_me_token']);
    }
    
    /**
     * Get session data
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Set session data
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Check if session has a key
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session data
     */
    public static function remove($key) {
        unset($_SESSION[$key]);
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Get user ID
     */
    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get username
     */
    public static function getUsername() {
        return $_SESSION['username'] ?? null;
    }
    
    /**
     * Get user role
     */
    public static function getUserRole() {
        return $_SESSION['role'] ?? null;
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return self::isLoggedIn() && $_SESSION['role'] === 'admin';
    }
    
    /**
     * Get session timeout remaining
     */
    public static function getTimeoutRemaining() {
        if (!isset($_SESSION['last_activity'])) {
            return 0;
        }
        
        $remaining = self::SESSION_TIMEOUT - (time() - $_SESSION['last_activity']);
        return max(0, $remaining);
    }
    
    /**
     * Extend session timeout
     */
    public static function extendTimeout() {
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Destroy session
     */
    public static function destroy() {
        // Clear all session data
        $_SESSION = array();
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Clear remember me cookie
        self::clearRememberMe();
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Flash message system
     */
    public static function flash($key, $message = null) {
        if ($message === null) {
            // Get and remove flash message
            $message = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $message;
        } else {
            // Set flash message
            $_SESSION['flash'][$key] = $message;
        }
    }
    
    /**
     * Check if flash message exists
     */
    public static function hasFlash($key) {
        return isset($_SESSION['flash'][$key]);
    }
    
    /**
     * Get all flash messages
     */
    public static function getAllFlash() {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }
}
?> 