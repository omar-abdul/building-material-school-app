<?php

/**
 * User Registration Page
 * Used for initial admin user setup
 */

require_once __DIR__ . '/../config/base_url.php';
require_once __DIR__ . '/../config/auth.php';

// Check if this is initial setup
session_start();
if (!isset($_SESSION['initial_setup']) || !$_SESSION['initial_setup']) {
    // Not initial setup, redirect to login
    header('Location: ' . BASE_URL . 'Login/');
    exit();
}

// If setup is already complete, redirect to dashboard
if (isset($_SESSION['setup_complete']) && $_SESSION['setup_complete']) {
    header('Location: ' . BASE_URL . 'dashboard/');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initial Setup - BMMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../base/styles.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .setup-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .setup-header {
            margin-bottom: 30px;
        }

        .setup-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .setup-header p {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
        }

        .setup-icon {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 20px;
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
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }

        .success-message {
            background: #efe;
            color: #3c3;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }

        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="setup-container">
        <div class="setup-header">
            <div class="setup-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>Initial Setup</h1>
            <p>Welcome to BMMS! Let's create your first admin user to get started.</p>
        </div>

        <div id="error-message" class="error-message"></div>
        <div id="success-message" class="success-message"></div>

        <form id="registration-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <div class="password-requirements">
                    Password must be at least 6 characters long
                </div>
            </div>

            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm-password" required>
            </div>

            <button type="submit" class="submit-btn" id="submit-btn">
                <i class="fas fa-user-plus"></i>
                Create Admin User
            </button>
        </form>
    </div>

    <script>
        document.getElementById('registration-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submit-btn');
            const errorMessage = document.getElementById('error-message');
            const successMessage = document.getElementById('success-message');

            // Hide previous messages
            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating User...';

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            // Basic validation
            if (username.length < 3) {
                showError('Username must be at least 3 characters long');
                resetSubmitBtn();
                return;
            }

            if (password.length < 6) {
                showError('Password must be at least 6 characters long');
                resetSubmitBtn();
                return;
            }

            if (password !== confirmPassword) {
                showError('Passwords do not match');
                resetSubmitBtn();
                return;
            }

            try {
                const response = await fetch('<?= BASE_URL ?>api/users/users.php?action=addUser', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        username: username,
                        password: password,
                        role: 'admin'
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showSuccess('Admin user created successfully! Redirecting to dashboard...');

                    // Mark setup as complete
                    await fetch('<?= BASE_URL ?>api/auth/auth.php?action=completeSetup', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    });

                    // Redirect to dashboard after a short delay
                    setTimeout(() => {
                        window.location.href = '<?= BASE_URL ?>dashboard/';
                    }, 2000);
                } else {
                    showError(result.message || 'Failed to create user');
                    resetSubmitBtn();
                }
            } catch (error) {
                showError('Network error. Please try again.');
                resetSubmitBtn();
            }
        });

        function showError(message) {
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
        }

        function showSuccess(message) {
            const successMessage = document.getElementById('success-message');
            successMessage.textContent = message;
            successMessage.style.display = 'block';
        }

        function resetSubmitBtn() {
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-user-plus"></i> Create Admin User';
        }
    </script>
</body>

</html>