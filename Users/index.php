<?php

/**
 * Users Management
 * Handles user registration, management, and authentication
 */

require_once __DIR__ . '/../config/auth.php';

$auth = new Auth();
$auth->requireAuth();

$role = $auth->getUserRole();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - BMMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../base/styles.css">
    <link rel="stylesheet" href="users.css">
</head>

<body>
    <div class="page-content">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main>
            <div class="header">
                <i class="fa-solid fa-bars bar-item"></i>
                <div class="search">
                    <input type="search" placeholder="Search users..." />
                    <i class="fa-solid fa-search"></i>
                </div>
                <div class="profile">
                    <span class="bell"><i class="fa-regular fa-bell"></i></span>
                </div>
            </div>

            <div class="main-content">
                <div class="title">
                    <h1><i class="fas fa-users"></i> Users Management</h1>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <button class="action-btn" onclick="showAddUserModal()">
                        <i class="fas fa-user-plus"></i>
                        Add New User
                    </button>
                    <button class="action-btn" onclick="exportUsers()">
                        <i class="fas fa-download"></i>
                        Export Users
                    </button>
                    <button class="action-btn" onclick="showBackupModal()">
                        <i class="fas fa-database"></i>
                        Database Backup
                    </button>
                </div>

                <!-- Users Table -->
                <div class="table-container">
                    <div class="table-header">
                        <div class="table-actions">
                            <input type="text" id="user-search" placeholder="Search users..." class="search-input">
                            <select id="role-filter" class="filter-select">
                                <option value="">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                    </div>

                    <table id="users-table" class="data-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit User Modal -->
    <div id="user-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title"><i class="fas fa-user"></i> Add New User</h2>
                <span class="close" onclick="closeUserModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="user-form">
                    <input type="hidden" id="user-id">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" id="username" required>
                        </div>
                        <div class="form-group">
                            <label for="role">Role *</label>
                            <select id="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm-password">Confirm Password *</label>
                            <input type="password" id="confirm-password" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Cancel</button>
                        <button type="submit" class="btn btn-success">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View User Modal -->
    <div id="view-user-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user"></i> User Details</h2>
                <span class="close" onclick="document.getElementById('view-user-modal').style.display='none'">&times;</span>
            </div>
            <div class="modal-body">
                <div class="user-details">
                    <div class="detail-row">
                        <strong>User ID:</strong> <span id="view-user-id"></span>
                    </div>
                    <div class="detail-row">
                        <strong>Username:</strong> <span id="view-username"></span>
                    </div>
                    <div class="detail-row">
                        <strong>Role:</strong> <span id="view-role"></span>
                    </div>
                    <div class="detail-row">
                        <strong>Created At:</strong> <span id="view-created-at"></span>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('view-user-modal').style.display='none'">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup Modal -->
    <div id="backup-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-database"></i> Database Backup</h2>
                <span class="close" onclick="closeBackupModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="backup-options">
                    <h3>Backup Options</h3>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="backup-users" checked> Users
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="backup-customers" checked> Customers
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="backup-suppliers" checked> Suppliers
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="backup-items" checked> Items
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="backup-orders" checked> Orders
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="backup-financial" checked> Financial Data
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeBackupModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="createBackup()">Create Backup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="users.js"></script>
</body>

</html>