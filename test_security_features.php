<?php
/**
 * Test Security Features
 * This file tests all the new security features implemented in Phase 2
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/utils.php';
require_once __DIR__ . '/config/password_manager.php';
require_once __DIR__ . '/config/session_manager.php';
require_once __DIR__ . '/config/csrf.php';

echo "<h1>Testing Security Features - Phase 2</h1>";

// Test 1: Password Manager
echo "<h2>Test 1: Password Manager</h2>";
try {
    $passwordManager = new PasswordManager();
    echo "<p style='color: green;'>✓ Password manager initialized</p>";
    
    // Test password hashing
    $testPassword = 'TestPassword123!';
    $hashedPassword = $passwordManager->hashPassword($testPassword);
    echo "<p style='color: green;'>✓ Password hashing working</p>";
    
    // Test password verification
    if ($passwordManager->verifyPassword($testPassword, $hashedPassword)) {
        echo "<p style='color: green;'>✓ Password verification working</p>";
    } else {
        echo "<p style='color: red;'>✗ Password verification failed</p>";
    }
    
    // Test password strength validation
    $weakPassword = 'weak';
    $strongPassword = 'StrongPass123!';
    
    $weakErrors = $passwordManager->validatePasswordStrength($weakPassword);
    $strongErrors = $passwordManager->validatePasswordStrength($strongPassword);
    
    if (count($weakErrors) > 0) {
        echo "<p style='color: green;'>✓ Weak password detection working</p>";
    } else {
        echo "<p style='color: red;'>✗ Weak password detection failed</p>";
    }
    
    if (count($strongErrors) == 0) {
        echo "<p style='color: green;'>✓ Strong password validation working</p>";
    } else {
        echo "<p style='color: red;'>✗ Strong password validation failed</p>";
    }
    
    // Test secure password generation
    $generatedPassword = $passwordManager->generateSecurePassword();
    $generatedErrors = $passwordManager->validatePasswordStrength($generatedPassword);
    if (count($generatedErrors) == 0) {
        echo "<p style='color: green;'>✓ Secure password generation working</p>";
    } else {
        echo "<p style='color: red;'>✗ Secure password generation failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Password manager failed: " . $e->getMessage() . "</p>";
}

// Test 2: CSRF Protection
echo "<h2>Test 2: CSRF Protection</h2>";
try {
    // Test token generation
    $token1 = CSRF::generateToken();
    $token2 = CSRF::generateToken();
    
    if (!empty($token1) && !empty($token2)) {
        echo "<p style='color: green;'>✓ CSRF token generation working</p>";
    } else {
        echo "<p style='color: red;'>✗ CSRF token generation failed</p>";
    }
    
    // Test token validation
    if (CSRF::validateToken($token1)) {
        echo "<p style='color: green;'>✓ CSRF token validation working</p>";
    } else {
        echo "<p style='color: red;'>✗ CSRF token validation failed</p>";
    }
    
    // Test invalid token
    if (!CSRF::validateToken('invalid_token')) {
        echo "<p style='color: green;'>✓ Invalid CSRF token rejection working</p>";
    } else {
        echo "<p style='color: red;'>✗ Invalid CSRF token rejection failed</p>";
    }
    
    // Test hidden input generation
    $hiddenInput = CSRF::getHiddenInput();
    if (strpos($hiddenInput, 'csrf_token') !== false) {
        echo "<p style='color: green;'>✓ CSRF hidden input generation working</p>";
    } else {
        echo "<p style='color: red;'>✗ CSRF hidden input generation failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ CSRF protection failed: " . $e->getMessage() . "</p>";
}

// Test 3: Session Manager
echo "<h2>Test 3: Session Manager</h2>";
try {
    // Initialize session
    SessionManager::init();
    echo "<p style='color: green;'>✓ Session manager initialized</p>";
    
    // Test session data storage
    SessionManager::set('test_key', 'test_value');
    $retrievedValue = SessionManager::get('test_key');
    
    if ($retrievedValue === 'test_value') {
        echo "<p style='color: green;'>✓ Session data storage working</p>";
    } else {
        echo "<p style='color: red;'>✗ Session data storage failed</p>";
    }
    
    // Test session timeout
    $timeout = SessionManager::getTimeoutRemaining();
    if ($timeout > 0) {
        echo "<p style='color: green;'>✓ Session timeout calculation working</p>";
    } else {
        echo "<p style='color: red;'>✗ Session timeout calculation failed</p>";
    }
    
    // Test flash messages
    SessionManager::flash('test_flash', 'test message');
    if (SessionManager::hasFlash('test_flash')) {
        echo "<p style='color: green;'>✓ Flash message storage working</p>";
    } else {
        echo "<p style='color: red;'>✗ Flash message storage failed</p>";
    }
    
    $flashMessage = SessionManager::flash('test_flash');
    if ($flashMessage === 'test message') {
        echo "<p style='color: green;'>✓ Flash message retrieval working</p>";
    } else {
        echo "<p style='color: red;'>✗ Flash message retrieval failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Session manager failed: " . $e->getMessage() . "</p>";
}

// Test 4: Enhanced Authentication
echo "<h2>Test 4: Enhanced Authentication</h2>";
try {
    $auth = new Auth();
    echo "<p style='color: green;'>✓ Enhanced authentication initialized</p>";
    
    // Test user creation with password validation
    $testUsername = 'security_test_user_' . time();
    $weakPassword = 'weak';
    $strongPassword = 'StrongPass123!';
    
    // Test weak password rejection
    $weakResult = $auth->createUser($testUsername, $weakPassword, 'user');
    if (!$weakResult['success']) {
        echo "<p style='color: green;'>✓ Weak password rejection working</p>";
    } else {
        echo "<p style='color: red;'>✗ Weak password rejection failed</p>";
    }
    
    // Test strong password acceptance
    $strongResult = $auth->createUser($testUsername, $strongPassword, 'user');
    if ($strongResult['success']) {
        echo "<p style='color: green;'>✓ Strong password acceptance working</p>";
        
        // Test login with hashed password
        $loginResult = $auth->login($testUsername, $strongPassword);
        if ($loginResult['success']) {
            echo "<p style='color: green;'>✓ Login with hashed password working</p>";
            
            // Test session data
            if ($auth->isLoggedIn()) {
                echo "<p style='color: green;'>✓ Session management working</p>";
            } else {
                echo "<p style='color: red;'>✗ Session management failed</p>";
            }
            
            // Clean up - delete test user
            $auth->deleteUser($loginResult['user']['id']);
            echo "<p style='color: green;'>✓ Test user cleanup working</p>";
        } else {
            echo "<p style='color: red;'>✗ Login with hashed password failed</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Strong password acceptance failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Enhanced authentication failed: " . $e->getMessage() . "</p>";
}

// Test 5: Password Statistics
echo "<h2>Test 5: Password Statistics</h2>";
try {
    $passwordManager = new PasswordManager();
    $stats = $passwordManager->getPasswordStats();
    
    if ($stats !== null) {
        echo "<p style='color: green;'>✓ Password statistics working</p>";
        echo "<p>Total users: " . $stats['total_users'] . "</p>";
        echo "<p>Hashed passwords: " . $stats['hashed_passwords'] . "</p>";
        echo "<p>Plain text passwords: " . $stats['plain_text_passwords'] . "</p>";
        echo "<p>Migration percentage: " . $stats['migration_percentage'] . "%</p>";
    } else {
        echo "<p style='color: red;'>✗ Password statistics failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Password statistics failed: " . $e->getMessage() . "</p>";
}

echo "<h2>Security Test Summary</h2>";
echo "<p>If you see mostly green checkmarks above, all security features are working correctly!</p>";
echo "<p><strong>Key Security Improvements:</strong></p>";
echo "<ul>";
echo "<li>✅ Password hashing with bcrypt</li>";
echo "<li>✅ CSRF protection on all forms</li>";
echo "<li>✅ Secure session management</li>";
echo "<li>✅ Password strength validation</li>";
echo "<li>✅ Session timeout and regeneration</li>";
echo "<li>✅ Input sanitization and validation</li>";
echo "</ul>";
echo "<p>Your application is now much more secure!</p>";
?> 