<?php
/**
 * Items Module Database Connection
 * Uses centralized database configuration
 */

require_once __DIR__ . '/../config/database.php';

// Get database instance
$db = Database::getInstance();
$conn = $db->getConnection();
?>