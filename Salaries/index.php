<?php

/**
 * Salaries Management Page
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
    <title>BMMS - Salaries Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../base/styles.css">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1>Salaries Management</h1>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="addSalaryBtn">
                        <i class="fas fa-plus"></i> Add New Salary
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search salaries...">
                </div>
                <select id="statusFilter" class="form-group">
                    <option value="">Filter by Status</option>
                    <option value="Paid">Paid</option>
                    <option value="Pending">Pending</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Salaries Table -->
            <table class="salaries-table">
                <thead>
                    <tr>
                        <th>Salary ID</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Amount</th>
                        <th>Advance</th>
                        <th>Net Salary</th>
                        <th>Payment Method</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

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

    <!-- Add/Edit Salary Modal -->
    <div class="modal" id="salaryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New Salary</h3>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <form id="salaryForm">
                <input type="hidden" id="salaryId">

                <div class="form-group">
                    <label for="employeeSearch">Employee</label>
                    <div class="autocomplete-container">
                        <input type="text" id="employeeSearch" placeholder="Search employee..." required>
                        <div class="autocomplete-dropdown" id="employeeDropdown"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="employeeId">Employee ID</label>
                    <input type="text" id="employeeId" readonly>
                </div>

                <div class="form-group">
                    <label for="employeeName">Employee Name</label>
                    <input type="text" id="employeeName" readonly>
                </div>

                <div class="form-group">
                    <label for="baseSalary">Base Salary</label>
                    <input type="number" id="baseSalary" step="0.01" readonly>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="amount">Amount ($)</label>
                        <input type="number" id="amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="advanceSalary">Advance Salary ($)</label>
                        <input type="number" id="advanceSalary" step="0.01" value="0.00">
                    </div>
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-primary" id="calculateBtn">
                        <i class="fas fa-calculator"></i> Calculate Net Salary
                    </button>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="netSalary">Net Salary ($)</label>
                        <input type="number" id="netSalary" step="0.01" readonly>
                    </div>
                    <div class="form-group">
                        <label for="paymentMethod">Payment Method</label>
                        <select id="paymentMethod" required>
                            <option value="">Select Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Wallet">Wallet</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="paymentDate">Payment Date</label>
                    <input type="date" id="paymentDate" required>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" required>
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-danger" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-success" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Salary Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Salary Details</h3>
                <button class="close-btn" id="closeViewModal">&times;</button>
            </div>
            <div class="item-details">
                <div class="detail-row">
                    <strong>Salary ID:</strong> <span id="viewId"></span>
                </div>
                <div class="detail-row">
                    <strong>Employee ID:</strong> <span id="viewEmployeeId"></span>
                </div>
                <div class="detail-row">
                    <strong>Employee Name:</strong> <span id="viewEmployeeName"></span>
                </div>
                <div class="detail-row">
                    <strong>Amount:</strong> <span id="viewAmount"></span>
                </div>
                <div class="detail-row">
                    <strong>Advance Salary:</strong> <span id="viewAdvance"></span>
                </div>
                <div class="detail-row">
                    <strong>Net Salary:</strong> <span id="viewNetSalary"></span>
                </div>
                <div class="detail-row">
                    <strong>Payment Method:</strong> <span id="viewPaymentMethod"></span>
                </div>
                <div class="detail-row">
                    <strong>Payment Date:</strong> <span id="viewPaymentDate"></span>
                </div>
                <div class="detail-row">
                    <strong>Status:</strong> <span id="viewStatus"></span>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="closeViewBtn">Close</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Delete Salary</h3>
                <button class="close-btn" id="closeDeleteModal">&times;</button>
            </div>
            <div class="delete-message">
                <p>Are you sure you want to delete salary <strong id="deleteSalaryId"></strong> for <strong id="deleteEmployeeName"></strong>?</p>
                <p>This action cannot be undone.</p>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="cancelDeleteBtn">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>

    <!-- JavaScript Configuration -->\n <script src="<?= BASE_URL ?>config/js-config.php"></script>\n <script src="salaries.js"></script>
</body>

</html>