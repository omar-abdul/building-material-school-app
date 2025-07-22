<?php

/**
 * Employees Management Page
 * Uses centralized authentication system
 */

require_once __DIR__ . '/../config/base_url.php';
require_once __DIR__ . '/../config/auth.php';

$auth = new Auth();
$auth->requireAuth();

$role = $auth->getUserRole(); // 'admin' or 'user'
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMMS - Employees Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="page-content">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main>
            <div class="header">
                <i class="fa-solid fa-bars bar-item"></i>
                <div class="search">
                    <input type="search" placeholder="Search employees..." />
                    <i class="fa-solid fa-search"></i>
                </div>
                <div class="profile">
                    <span class="bell"><i class="fa-regular fa-bell"></i></span>
                </div>
            </div>

            <div class="main-content">
                <div class="title">
                    <h1><i class="fas fa-users"></i> Employees Management</h1>
                </div>

                <div class="action-buttons" style="margin-bottom: 20px;">
                    <button class="btn btn-primary" id="addEmployeeBtn">
                        <i class="fas fa-plus"></i> Add New Employee
                    </button>
                </div>

                <!-- Search and Filter -->
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search employees...">
                    </div>
                    <select id="positionFilter" class="form-group">
                        <option value="">Filter by Position</option>
                        <option value="Manager">Manager</option>
                        <option value="Sales">Sales</option>
                        <option value="Accountant">Accountant</option>
                        <option value="Warehouse">Warehouse</option>
                    </select>
                </div>

                <!-- Employees Table -->
                <table class="employees-table">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Base Salary</th>
                            <th>Expected Salary</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Guarantor</th>
                            <th>Address</th>
                            <th>Date Added</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded dynamically via JavaScript -->
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
        </main>
    </div>

    <!-- Add/Edit Employee Modal -->
    <div class="modal" id="employeeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New Employee</h3>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <form id="employeeForm">
                <input type="hidden" id="employeeId">

                <div class="form-group">
                    <label for="employeeName">Employee Name</label>
                    <input type="text" id="employeeName" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="position">Position</label>
                        <select id="position" required>
                            <option value="">Select Position</option>
                            <option value="Manager">Manager</option>
                            <option value="Sales">Sales</option>
                            <option value="Accountant">Accountant</option>
                            <option value="Warehouse">Warehouse</option>
                            <option value="Driver">Driver</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="baseSalary">Base Salary ($)</label>
                        <input type="number" id="baseSalary" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="salaryIncrease">Salary Increase (%)</label>
                        <input type="number" id="salaryIncrease" step="0.1" min="0" max="100" value="0" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="expectedSalary">Expected Salary ($)</label>
                        <input type="number" id="expectedSalary" step="0.01" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-primary" id="calculateBtn">
                        <i class="fas fa-calculator"></i> Calculate Expected Salary
                    </button>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="guarantor">Guarantor</label>
                    <input type="text" id="guarantor">
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" rows="3"></textarea>
                </div>

                <!-- Add this form-group right after the customer details section in the items modal -->
                <div class="form-group">
                    <label for="DateAdded">Date Added</label>
                    <input type="date" id="DateAdded" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-danger" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-success" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Employee Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Employee Details</h3>
                <button class="close-btn" id="closeViewModal">&times;</button>
            </div>
            <div class="item-details">
                <div class="detail-row">
                    <strong>Employee ID:</strong> <span id="viewId">EMP-1001</span>
                </div>
                <div class="detail-row">
                    <strong>Name:</strong> <span id="viewName">Ahmed Mohamed</span>
                </div>
                <div class="detail-row">
                    <strong>Position:</strong> <span id="viewPosition">Manager</span>
                </div>
                <div class="detail-row">
                    <strong>Base Salary:</strong> <span id="viewBaseSalary">$1,500.00</span>
                </div>
                <div class="detail-row">
                    <strong>Expected Salary:</strong> <span id="viewExpectedSalary">$1,650.00</span>
                </div>
                <div class="detail-row">
                    <strong>Phone:</strong> <span id="viewPhone">+252612345678</span>
                </div>
                <div class="detail-row">
                    <strong>Email:</strong> <span id="viewEmail">ahmed@example.com</span>
                </div>
                <div class="detail-row">
                    <strong>Guarantor:</strong> <span id="viewGuarantor">Mohamed Abdi</span>
                </div>
                <div class="detail-row">
                    <strong>Status:</strong> <span id="viewStatus" class="status-active">Active</span>
                </div>
                <div class="detail-row">
                    <strong>Address:</strong>
                    <p id="viewAddress">123 Main Street, Mogadishu, Somalia</p>
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
                <p>Are you sure you want to delete this employee record?</p>
                <p><strong>Employee ID:</strong> <span id="deleteEmployeeId">EMP-1001</span> - <span id="deleteEmployeeName">Ahmed Mohamed</span></p>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                <button type="button" class="btn" id="cancelDeleteBtn">Cancel</button>
            </div>
        </div>
    </div>

        <!-- JavaScript Configuration -->\n    <script src="<?= BASE_URL ?>config/js-config.php"></script>\n    <script src="employees.js"></script>
</body>

</html>