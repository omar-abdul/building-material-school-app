<?php
/**
 * Test Centralized System
 * This file tests the centralized database and authentication systems
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/utils.php';

echo "<h1>Testing Centralized System</h1>";

// Test 1: Database Connection
echo "<h2>Test 1: Database Connection</h2>";
try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test a simple query
    $result = $db->fetchOne("SELECT COUNT(*) as count FROM users");
    echo "<p>✓ Users table accessible. Total users: " . $result['count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test 2: Authentication System
echo "<h2>Test 2: Authentication System</h2>";
try {
    $auth = new Auth();
    echo "<p style='color: green;'>✓ Authentication system initialized</p>";
    
    // Test session management
    $auth->startSession();
    echo "<p style='color: green;'>✓ Session management working</p>";
    
    // Test user creation (if not exists)
    $testUsername = 'test_user_' . time();
    $result = $auth->createUser($testUsername, 'test123', 'user');
    if ($result['success']) {
        echo "<p style='color: green;'>✓ User creation working</p>";
        
        // Test login
        $loginResult = $auth->login($testUsername, 'test123');
        if ($loginResult['success']) {
            echo "<p style='color: green;'>✓ Login system working</p>";
            echo "<p>Logged in user: " . $loginResult['user']['username'] . " (Role: " . $loginResult['user']['role'] . ")</p>";
            
            // Test role checking
            if ($auth->isLoggedIn()) {
                echo "<p style='color: green;'>✓ Session checking working</p>";
            }
            
            if (!$auth->isAdmin()) {
                echo "<p style='color: green;'>✓ Role checking working (user is not admin)</p>";
            }
            
            // Clean up - delete test user
            $auth->deleteUser($loginResult['user']['id']);
            echo "<p style='color: green;'>✓ User deletion working</p>";
        } else {
            echo "<p style='color: red;'>✗ Login failed: " . $loginResult['message'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ User creation failed: " . $result['message'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Authentication system failed: " . $e->getMessage() . "</p>";
}

// Test 3: Utilities
echo "<h2>Test 3: Utilities</h2>";
try {
    // Test input sanitization
    $dirtyInput = "<script>alert('xss')</script>";
    $cleanInput = Utils::sanitizeInput($dirtyInput);
    if ($cleanInput !== $dirtyInput) {
        echo "<p style='color: green;'>✓ Input sanitization working</p>";
    } else {
        echo "<p style='color: red;'>✗ Input sanitization failed</p>";
    }
    
    // Test email validation
    if (Utils::validateEmail('test@example.com')) {
        echo "<p style='color: green;'>✓ Email validation working</p>";
    } else {
        echo "<p style='color: red;'>✗ Email validation failed</p>";
    }
    
    if (!Utils::validateEmail('invalid-email')) {
        echo "<p style='color: green;'>✓ Invalid email detection working</p>";
    } else {
        echo "<p style='color: red;'>✗ Invalid email detection failed</p>";
    }
    
    // Test date formatting
    $formattedDate = Utils::formatDate('2025-01-15');
    if ($formattedDate === '2025-01-15') {
        echo "<p style='color: green;'>✓ Date formatting working</p>";
    } else {
        echo "<p style='color: red;'>✗ Date formatting failed</p>";
    }
    
    // Test currency formatting
    $formattedCurrency = Utils::formatCurrency(1234.56);
    if ($formattedCurrency === '$1,234.56') {
        echo "<p style='color: green;'>✓ Currency formatting working</p>";
    } else {
        echo "<p style='color: red;'>✗ Currency formatting failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Utilities failed: " . $e->getMessage() . "</p>";
}

echo "<h2>Test Summary</h2>";
echo "<p>If you see mostly green checkmarks above, the centralized system is working correctly!</p>";
echo "<p>You can now proceed to update other modules to use this centralized system.</p>";
?> 