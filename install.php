<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * BMMS Installation System
 * WordPress-like installation process for Building Material Management System
 */

session_start();

// Check if already installed
if (file_exists('config/installed.lock')) {
    die('BMMS is already installed. Remove config/installed.lock to reinstall.');
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1:
            // Database connection test
            $dbHost = $_POST['db_host'] ?? 'localhost';
            $dbName = $_POST['db_name'] ?? '';
            $dbUser = $_POST['db_user'] ?? '';
            $dbPass = $_POST['db_pass'] ?? '';

            if (empty($dbName) || empty($dbUser)) {
                $error = 'Database name and user are required.';
            } else {
                try {
                    // Test connection
                    $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Store in session for next step
                    $_SESSION['db_config'] = [
                        'host' => $dbHost,
                        'name' => $dbName,
                        'user' => $dbUser,
                        'pass' => $dbPass
                    ];

                    header('Location: install.php?step=2');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Database connection failed: ' . $e->getMessage();
                }
            }
            break;

        case 2:
            // Admin user creation
            $adminUser = $_POST['admin_user'] ?? '';
            $adminPass = $_POST['admin_pass'] ?? '';
            $adminEmail = $_POST['admin_email'] ?? '';

            if (empty($adminUser) || empty($adminPass) || empty($adminEmail)) {
                $error = 'All admin fields are required.';
            } else {
                try {
                    $dbConfig = $_SESSION['db_config'];

                    // Create database if it doesn't exist
                    $pdo = new PDO("mysql:host={$dbConfig['host']}", $dbConfig['user'], $dbConfig['pass']);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['name']}`");

                    // Connect to the new database
                    try {
                        $pdo = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['name']}", $dbConfig['user'], $dbConfig['pass']);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (PDOException $e) {
                        throw new Exception('Database connection failed: ' . $e->getMessage());
                    }

                    // Create tables
                    $sql = file_get_contents('database_structure.sql');
                    if ($sql === false) {
                        throw new Exception('Could not read database_structure.sql file');
                    }

                    if (empty($sql)) {
                        throw new Exception('database_structure.sql file is empty');
                    }

                    $pdo->exec($sql);

                    // Create admin user
                    $hashedPassword = password_hash($adminPass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, created_at) VALUES (?, ?, 'admin', NOW())");
                    $stmt->execute([$adminUser, $hashedPassword]);

                    // Create default category
                    $stmt = $pdo->prepare("INSERT INTO categories (CategoryName, Description) VALUES ('Uncategorized', 'Default category for uncategorized items')");
                    $stmt->execute();

                    // Debug: Show current directory and paths
                    $currentDir = getcwd();
                    $dbTemplatePath = $currentDir . '/config/database_template.php';
                    $dbOutputPath = $currentDir . '/config/database.php';
                    $lockOutputPath = $currentDir . '/config/installed.lock';

                    if (!file_exists($dbTemplatePath)) {
                        throw new Exception('Template file not found at: ' . $dbTemplatePath);
                    }

                    // Generate database.php file
                    $dbTemplate = file_get_contents($dbTemplatePath);
                    if ($dbTemplate === false) {
                        throw new Exception('Could not read database template file');
                    }

                    $dbContent = str_replace(
                        ['{{DB_HOST}}', '{{DB_NAME}}', '{{DB_USER}}', '{{DB_PASS}}'],
                        [$dbConfig['host'], $dbConfig['name'], $dbConfig['user'], $dbConfig['pass']],
                        $dbTemplate
                    );

                    // Debug: Check if content was generated
                    if (empty($dbContent)) {
                        throw new Exception('Database content is empty after template processing');
                    }

                    $dbFileResult = file_put_contents($dbOutputPath, $dbContent);
                    if ($dbFileResult === false) {
                        $error = error_get_last();
                        throw new Exception('Could not create database.php file. Error: ' . ($error['message'] ?? 'Unknown error'));
                    }

                    // Create installed lock file
                    $lockFileResult = file_put_contents($lockOutputPath, date('Y-m-d H:i:s'));
                    if ($lockFileResult === false) {
                        $error = error_get_last();
                        throw new Exception('Could not create installed.lock file. Error: ' . ($error['message'] ?? 'Unknown error'));
                    }

                    // Cross-platform permission fix
                    fixFilePermissions($dbOutputPath);
                    fixFilePermissions($lockOutputPath);

                    // Verify files were created
                    if (!file_exists($dbOutputPath)) {
                        throw new Exception('database.php file was not created');
                    }
                    if (!file_exists($lockOutputPath)) {
                        throw new Exception('installed.lock file was not created');
                    }

                    // Debug: Show file sizes
                    $dbFileSize = filesize($dbOutputPath);
                    $lockFileSize = filesize($lockOutputPath);

                    if ($dbFileSize === 0) {
                        throw new Exception('database.php file is empty');
                    }
                    if ($lockFileSize === 0) {
                        throw new Exception('installed.lock file is empty');
                    }

                    $success = 'Installation completed successfully! Files created: database.php (' . $dbFileSize . ' bytes), installed.lock (' . $lockFileSize . ' bytes)';

                    // Redirect to login page after successful installation
                    header('Location: index.php');
                    exit();
                } catch (Exception $e) {
                    $error = 'Installation failed: ' . $e->getMessage();
                }
            }
            break;
    }
}

/**
 * Fix file permissions cross-platform
 */
function fixFilePermissions($filePath)
{
    if (PHP_OS_FAMILY === 'Windows') {
        // Windows: Set proper ACLs for web server access
        setWindowsPermissions($filePath);
    } else {
        // Unix/Linux: Set proper ownership and permissions
        setUnixPermissions($filePath);
    }
}

/**
 * Set Windows file permissions
 */
function setWindowsPermissions($filePath)
{
    // Try to identify web server user
    $webServerUser = getWebServerUser();

    if ($webServerUser) {
        // Use icacls to set permissions (Windows equivalent of chmod)
        $command = "icacls \"$filePath\" /grant \"$webServerUser\":(F) /T";
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            // Fallback: just ensure file is writable by current user
            chmod($filePath, 0666);
        }
    } else {
        // Fallback: make file writable by everyone
        chmod($filePath, 0666);
    }
}

/**
 * Set Unix/Linux file permissions
 */
function setUnixPermissions($filePath)
{
    // Try to identify web server user
    $webServerUser = getWebServerUser();

    if ($webServerUser) {
        // Change ownership to web server user
        chown($filePath, $webServerUser);
        chgrp($filePath, $webServerUser);
        chmod($filePath, 0644);
    } else {
        // Fallback: make file writable by owner and readable by others
        chmod($filePath, 0644);
    }
}

/**
 * Get web server user based on platform
 */
function getWebServerUser()
{
    if (PHP_OS_FAMILY === 'Windows') {
        // Common Windows web server users
        $possibleUsers = ['IUSR', 'IIS_IUSRS', 'apache', 'www-data'];
    } else {
        // Common Unix web server users
        $possibleUsers = ['www-data', 'apache', 'nginx', 'httpd'];
    }

    // Check if any of these users exist
    foreach ($possibleUsers as $user) {
        if (function_exists('posix_getpwnam') && posix_getpwnam($user)) {
            return $user;
        }
    }

    return null;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMMS Installation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .install-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
        }

        .logo h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .logo p {
            color: #666;
            font-size: 16px;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
        }

        .step.active {
            background: #667eea;
            color: white;
        }

        .step.completed {
            background: #4caf50;
            color: white;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }

        .btn:hover {
            background: #5a6fd8;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
        }

        .success {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
        }

        .info-box {
            background: #e3f2fd;
            color: #1565c0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #1565c0;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="install-container">
        <div class="logo">
            <i class="fas fa-building"></i>
            <h1>BMMS Installation</h1>
            <p>Building Material Management System</p>
        </div>

        <div class="step-indicator">
            <div class="step <?= $step >= 1 ? 'active' : '' ?>">1</div>
            <div class="step <?= $step >= 2 ? 'active' : '' ?>">2</div>
            <div class="step <?= $step >= 3 ? 'completed' : '' ?>">3</div>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <h2>Database Configuration</h2>
            <p style="margin-bottom: 20px; color: #666;">Enter your database connection details.</p>

            <form method="POST">
                <div class="form-group">
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                </div>

                <div class="form-group">
                    <label for="db_name">Database Name</label>
                    <input type="text" id="db_name" name="db_name" placeholder="bmmss" required>
                </div>

                <div class="form-group">
                    <label for="db_user">Database Username</label>
                    <input type="text" id="db_user" name="db_user" placeholder="root" required>
                </div>

                <div class="form-group">
                    <label for="db_pass">Database Password</label>
                    <input type="password" id="db_pass" name="db_pass" placeholder="Leave empty if no password">
                </div>

                <button type="submit" class="btn">Test Connection & Continue</button>
            </form>

        <?php elseif ($step == 2): ?>
            <h2>Admin User Setup</h2>
            <p style="margin-bottom: 20px; color: #666;">Create your administrator account.</p>

            <form method="POST">
                <div class="form-group">
                    <label for="admin_user">Admin Username</label>
                    <input type="text" id="admin_user" name="admin_user" placeholder="admin" required>
                </div>

                <div class="form-group">
                    <label for="admin_pass">Admin Password</label>
                    <input type="password" id="admin_pass" name="admin_pass" placeholder="Choose a strong password" required>
                </div>

                <div class="form-group">
                    <label for="admin_email">Admin Email</label>
                    <input type="email" id="admin_email" name="admin_email" placeholder="admin@example.com" required>
                </div>

                <button type="submit" class="btn">Complete Installation</button>
            </form>

        <?php elseif ($step == 3): ?>
            <h2>Installation Complete!</h2>
            <div class="info-box">
                <p><strong>Congratulations!</strong> BMMS has been successfully installed.</p>
                <p style="margin-top: 10px;">Your system is now ready to use. You can:</p>
                <ul style="margin: 10px 0 0 20px;">
                    <li>Login with your admin credentials</li>
                    <li>Start managing your building materials</li>
                    <li>Add categories, items, and suppliers</li>
                    <li>Track financial transactions</li>
                </ul>
            </div>

            <a href="index.php" class="btn">Go to Login</a>
        <?php endif; ?>
    </div>
</body>

</html>