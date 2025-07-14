<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /backend/');
    exit;
}

$db = Database::getInstance();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMMS - Users Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="users.css">
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="brand">
                <i class="fas fa-building"></i>
                <span class="brand-name">BMMS</span>
            </div>
            <div class="sidebar-menu">
                <a href="/backend/dashboard/" class="sidebar-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>

                <a href="/backend/Categories/" class="sidebar-link">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>

                <a href="/backend/Suppliers/" class="sidebar-link">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>suppliers</span>
                </a>
                <a href="/backend/Employees/" class="sidebar-link">
                    <i class="fas fa-users"></i>
                    <span>Employees</span>
                </a>

                <a href="/backend/Customers/" class="sidebar-link">
                    <i class="fas fa-exchange-alt"></i>
                    <span>customers</span>
                </a>

                <a href="/backend/Items/" class="sidebar-link">
                    <i class="fas fa-boxes"></i>
                    <span>Items</span>
                </a>


                <a href="/backend/Inventory/" class="sidebar-link">
                    <i class="fas fa-user-tie"></i>
                    <span>inventory</span>
                </a>


                <a href="/backend/Orders/" class="sidebar-link">
                    <i class="fas fa-truck"></i>
                    <span>orders</span>
                </a>

                <a href="/backend/Transactions/" class="sidebar-link">
                    <i class="fas fa-warehouse"></i>
                    <span>transactions</span>
                </a>
                <a href="/backend/Salaries/" class="sidebar-link">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Salaries</span>
                </a>


                <!-- Inside your sidebar-menu div, add this link before the Settings link -->
                <a href="#" class="sidebar-link  active" id="signUpBtn">
                    <i class="fas fa-user-plus"></i>
                    <span>Sign Up</span>
                </a>
                <div class="report-dropdown">
                    <a href="#" class="sidebar-link sidebar-report-btn">
                        <i class="fas fa-chart-pie"></i>
                        <span>Reports</span>
                        <i class="fas fa-angle-down dropdown-icon"></i>
                    </a>
                    <ul class="report-dropdown-content">
                        <li><a href="/backend/reports/inventory.php">Inventory Report</a></li>
                        <li><a href="/backend/reports/items.php">Items Report</a></li>
                        <li><a href="/backend/reports/orders.php">Orders Report</a></li>
                        <li><a href="/backend/reports/salaries.php"> Salaries Report</a></li>
                        <li><a href="/backend/reports/transactions.php"> Transactions Report</a></li>
                        <li><a href="/backend/signup/backup.php"> backup </a></li>
                    </ul>
                </div>
                <a href="/backend/api/auth/logout.php" class="sidebar-link">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>logout</span>
                </a>

            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1>Users Management</h1>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="addUserBtn">
                        <i class="fas fa-plus"></i> Add New User
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search users...">
                </div>
                <select id="roleFilter" class="form-group">
                    <option value="">Filter by Role</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <!-- Users Table -->
            <!-- Users Table -->
            <table class="users-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Password</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>admin</td>
                        <td><span class="role-admin">Admin</span></td>
                        <td>admin123</td>
                        <td>2023-05-15 08:30:00</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewUser('1')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editUser('1')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteUser('1')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>manager</td>
                        <td><span class="role-user">User</span></td>
                        <td>manager456</td>
                        <td>2023-06-20 10:15:00</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewUser('2')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editUser('2')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteUser('2')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>sales1</td>
                        <td><span class="role-user">User</span></td>
                        <td>sales789</td>
                        <td>2023-07-10 14:45:00</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewUser('3')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editUser('3')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteUser('3')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>


            <!-- Pagination -->
            <div class="pagination">
                <button class="page-btn"><i class="fas fa-angle-left"></i></button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">3</button>
                <button class="page-btn"><i class="fas fa-angle-right"></i></button>
            </div>
        </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New User</h3>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <form id="userForm">
                <input type="hidden" id="userId">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" required>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" required>
                </div>

                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-danger" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-success" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">User Details</h3>
                <button class="close-btn" id="closeViewModal">&times;</button>
            </div>
            <div class="user-details">
                <div class="detail-row">
                    <strong>User ID:</strong> <span id="viewId">1</span>
                </div>
                <div class="detail-row">
                    <strong>Username:</strong> <span id="viewUsername">admin</span>
                </div>
                <div class="detail-row">
                    <strong>Role:</strong> <span id="viewRole" class="role-admin">Admin</span>
                </div>
                <div class="detail-row">
                    <strong>Created At:</strong> <span id="viewCreatedAt">2023-05-15 08:30:00</span>
                </div>
                <div class="detail-row">
                    <strong>Password:</strong> <span id="viewPassword">Password</span>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" id="closeViewBtn">Close</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Confirm Delete</h3>
                <button class="close-btn" id="closeDeleteModal">&times;</button>
            </div>
            <div class="delete-message">
                <p>Are you sure you want to delete this user?</p>
                <p><strong>User ID:</strong> <span id="deleteUserId">1</span> - <span id="deleteUsername">admin</span></p>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                <button type="button" class="btn" id="cancelDeleteBtn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Sign Up Modal -->
    <div class="modal" id="signUpModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Sign Up New User</h3>
                <button class="close-btn" id="closeSignUpModal">&times;</button>
            </div>
            <form id="signUpForm">
                <div class="form-group">
                    <label for="signUpUsername">Username</label>
                    <input type="text" id="signUpUsername" required>
                </div>

                <div class="form-group">
                    <label for="signUpPassword">Password</label>
                    <input type="password" id="signUpPassword" required>
                </div>

                <div class="form-group">
                    <label for="signUpConfirmPassword">Confirm Password</label>
                    <input type="password" id="signUpConfirmPassword" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-danger" id="cancelSignUpBtn">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submitSignUpBtn">Sign Up</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Toggle dropdown when clicking the report button
        document.querySelector('.sidebar-report-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = this.closest('.report-dropdown');
            dropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside 
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.sidebar-report-btn') && !e.target.closest('.report-dropdown-content')) {
                document.querySelectorAll('.report-dropdown').forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });
    </script>
    <script src="users.js"></script>
</body>

</html>