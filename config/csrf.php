<?php
/**
 * CSRF Protection Class
 * Handles CSRF token generation, validation, and protection
 */

class CSRF {
    
    /**
     * Generate a CSRF token
     */
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Get the current CSRF token
     */
    public static function getToken() {
        return self::generateToken();
    }
    
    /**
     * Validate a CSRF token
     */
    public static function validateToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate a hidden input field with CSRF token
     */
    public static function getHiddenInput() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Validate CSRF token from POST data
     */
    public static function validatePostToken() {
        $token = $_POST['csrf_token'] ?? '';
        return self::validateToken($token);
    }
    
    /**
     * Validate CSRF token from GET data
     */
    public static function validateGetToken() {
        $token = $_GET['csrf_token'] ?? '';
        return self::validateToken($token);
    }
    
    /**
     * Regenerate CSRF token (for security)
     */
    public static function regenerateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Check if request should be protected by CSRF
     */
    public static function shouldProtect($method) {
        // Protect POST, PUT, DELETE requests
        return in_array(strtoupper($method), ['POST', 'PUT', 'DELETE']);
    }
    
    /**
     * Require CSRF validation for current request
     */
    public static function requireValidation() {
        if (self::shouldProtect($_SERVER['REQUEST_METHOD'])) {
            if (!self::validatePostToken()) {
                http_response_code(403);
                die('CSRF token validation failed');
            }
        }
    }
    
    /**
     * Add CSRF token to URL
     */
    public static function addTokenToUrl($url) {
        $separator = strpos($url, '?') !== false ? '&' : '?';
        return $url . $separator . 'csrf_token=' . urlencode(self::getToken());
    }
    
    /**
     * Get CSRF token for AJAX requests
     */
    public static function getAjaxToken() {
        return [
            'csrf_token' => self::getToken()
        ];
    }
}
?> 