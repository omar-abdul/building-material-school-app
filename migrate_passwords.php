<?php
/**
 * Password Migration Script
 * Migrates all plain text passwords to secure hashed passwords
 * 
 * WARNING: This script should only be run once and then deleted
 * Run this script from the command line: php migrate_passwords.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/password_manager.php';

echo "=== Password Migration Script ===\n";
echo "This script will migrate all plain text passwords to secure hashed passwords.\n\n";

// Confirm execution
echo "Are you sure you want to proceed? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) !== 'yes') {
    echo "Migration cancelled.\n";
    exit;
}

try {
    $db = Database::getInstance();
    $passwordManager = new PasswordManager();
    
    echo "Starting password migration...\n\n";
    
    // Get password statistics before migration
    $stats = $passwordManager->getPasswordStats();
    echo "Current password statistics:\n";
    echo "- Total users: " . $stats['total_users'] . "\n";
    echo "- Hashed passwords: " . $stats['hashed_passwords'] . "\n";
    echo "- Plain text passwords: " . $stats['plain_text_passwords'] . "\n";
    echo "- Migration percentage: " . $stats['migration_percentage'] . "%\n\n";
    
    if ($stats['plain_text_passwords'] == 0) {
        echo "No plain text passwords found. Migration not needed.\n";
        exit;
    }
    
    // Get all users with plain text passwords
    $sql = "SELECT id, username, password FROM users WHERE password NOT LIKE '\$2y\$%'";
    $users = $db->fetchAll($sql);
    
    echo "Found " . count($users) . " users with plain text passwords.\n\n";
    
    $migrated = 0;
    $failed = 0;
    
    foreach ($users as $user) {
        echo "Migrating password for user: " . $user['username'] . " (ID: " . $user['id'] . ")... ";
        
        try {
            // Hash the plain text password
            $hashedPassword = $passwordManager->hashPassword($user['password']);
            
            // Update the user
            $updateSql = "UPDATE users SET password = ? WHERE id = ?";
            $db->query($updateSql, [$hashedPassword, $user['id']]);
            
            echo "SUCCESS\n";
            $migrated++;
            
        } catch (Exception $e) {
            echo "FAILED: " . $e->getMessage() . "\n";
            $failed++;
        }
    }
    
    echo "\n=== Migration Complete ===\n";
    echo "Successfully migrated: " . $migrated . " passwords\n";
    echo "Failed migrations: " . $failed . "\n";
    echo "Total processed: " . count($users) . "\n\n";
    
    // Get updated statistics
    $newStats = $passwordManager->getPasswordStats();
    echo "Updated password statistics:\n";
    echo "- Total users: " . $newStats['total_users'] . "\n";
    echo "- Hashed passwords: " . $newStats['hashed_passwords'] . "\n";
    echo "- Plain text passwords: " . $newStats['plain_text_passwords'] . "\n";
    echo "- Migration percentage: " . $newStats['migration_percentage'] . "%\n\n";
    
    if ($newStats['migration_percentage'] == 100) {
        echo "✅ All passwords have been successfully migrated!\n";
        echo "You can now safely delete this migration script.\n";
    } else {
        echo "⚠️  Some passwords may still need migration.\n";
        echo "Check the database manually if needed.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nMigration script completed.\n";
?> 