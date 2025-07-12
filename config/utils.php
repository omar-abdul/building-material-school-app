<?php
/**
 * Utilities Class
 * Common helper functions for the Building Material Management System
 */

class Utils {
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Generate random string
     */
    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
    
    /**
     * Format date for display
     */
    public static function formatDate($date, $format = 'Y-m-d') {
        if (empty($date)) return '';
        return date($format, strtotime($date));
    }
    
    /**
     * Format currency
     */
    public static function formatCurrency($amount, $currency = '$') {
        return $currency . number_format($amount, 2);
    }
    
    /**
     * Get current timestamp
     */
    public static function getCurrentTimestamp() {
        return date('Y-m-d H:i:s');
    }
    
    /**
     * Validate required fields
     */
    public static function validateRequired($data, $requiredFields) {
        $errors = [];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = ucfirst($field) . ' is required';
            }
        }
        return $errors;
    }
    
    /**
     * Send JSON response
     */
    public static function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Send success response
     */
    public static function sendSuccessResponse($message = 'Success', $data = null) {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        self::sendJsonResponse($response);
    }
    
    /**
     * Send error response
     */
    public static function sendErrorResponse($message = 'Error', $statusCode = 400) {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        self::sendJsonResponse($response, $statusCode);
    }
    
    /**
     * Log error message
     */
    public static function logError($message, $context = []) {
        $logEntry = date('Y-m-d H:i:s') . ' - ERROR: ' . $message;
        if (!empty($context)) {
            $logEntry .= ' - Context: ' . json_encode($context);
        }
        $logEntry .= PHP_EOL;
        
        $logFile = __DIR__ . '/../logs/error.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Redirect to URL
     */
    public static function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Check if request is AJAX
     */
    public static function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Get request method
     */
    public static function getRequestMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Get POST data
     */
    public static function getPostData() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }
        
        return $_POST;
    }
    
    /**
     * Get GET data
     */
    public static function getGetData() {
        return $_GET;
    }
    
    /**
     * Escape HTML output
     */
    public static function escapeHtml($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generate pagination data
     */
    public static function generatePagination($totalRecords, $recordsPerPage, $currentPage) {
        $totalPages = ceil($totalRecords / $recordsPerPage);
        $currentPage = max(1, min($currentPage, $totalPages));
        $offset = ($currentPage - 1) * $recordsPerPage;
        
        return [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'records_per_page' => $recordsPerPage,
            'offset' => $offset,
            'has_previous' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages
        ];
    }
    
    /**
     * Create directory if it doesn't exist
     */
    public static function createDirectory($path) {
        if (!is_dir($path)) {
            return mkdir($path, 0755, true);
        }
        return true;
    }
    
    /**
     * Check if file exists and is readable
     */
    public static function fileExists($filePath) {
        return file_exists($filePath) && is_readable($filePath);
    }
    
    /**
     * Get file extension
     */
    public static function getFileExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
        $errors = [];
        
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed';
            return $errors;
        }
        
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed size';
        }
        
        if (!empty($allowedTypes)) {
            $extension = self::getFileExtension($file['name']);
            if (!in_array($extension, $allowedTypes)) {
                $errors[] = 'File type not allowed';
            }
        }
        
        return $errors;
    }
}
?> 