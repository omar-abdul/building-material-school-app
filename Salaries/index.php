<?php

/**
 * Salaries Management Page
 * Uses centralized authentication system
 */

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
    <link rel="stylesheet" href="steyle.css">
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
                <a href="/backend/dashboard/dashboard.php" class="sidebar-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/backend/Categories/index.php" class="sidebar-link">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="/backend/Suppliers/index.php" class="sidebar-link">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>suppliers</span>
                </a>
                <a href="/backend/Employees/index.php" class="sidebar-link">
                    <i class="fas fa-users"></i>
                    <span>Employees</span>
                </a>
                <a href="/backend/Customers/index.php" class="sidebar-link">
                    <i class="fas fa-exchange-alt"></i>
                    <span>customers</span>
                </a>
                <a href="/backend/Items/index.php" class="sidebar-link">
                    <i class="fas fa-boxes"></i>
                    <span>Items</span>
                </a>
                <a href="/backend/Inventory/index.php" class="sidebar-link">
                    <i class="fas fa-user-tie"></i>
                    <span>inventory</span>
                </a>
                <a href="/backend/Orders/index.php" class="sidebar-link">
                    <i class="fas fa-truck"></i>
                    <span>orders</span>
                </a>
                <a href="/backend/Transactions/index.php" class="sidebar-link">
                    <i class="fas fa-warehouse"></i>
                    <span>transactions</span>
                </a>
                <a href="/backend/Salaries/index.php" class="sidebar-link active">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Salaries</span>
                </a>
                <!-- Inside your sidebar-menu div, add this link before the Settings link -->
                <a href="/backend/signup/index.php" class="sidebar-link">
                    <i class="fas fa-user-plus"></i>
                    <span>Sign Up</span>
                </a>
                <nav class="sidebar">
                    <ul>
                        <li class="report-dropdown">
                            <a href="#" class="sidebar-link sidebar-report-btn">
                                <i class="fa-solid fa-chart-pie"></i>
                                <span>Reports</span>
                                <i class="fa-solid fa-angle-down dropdown-icon"></i>
                            </a>
                            <ul class="report-dropdown-content">
                                <li><a href="/backend/reports/inventory.php">Inventory Report</a></li>
                                <li><a href="/backend/reports/items.php">Items Report</a></li>
                                <li><a href="/backend/reports/orders.php">Orders Report</a></li>
                                <li><a href="/backend/reports/salaries.php"> Salaries Report</a></li>
                                <li><a href="/backend/reports/transactions.php"> Transactions Report</a></li>
                                <li><a href="\backend\signup\backup.php"> backup </a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
                <a href="/backend/dashboard/logout.php" class="sidebar-link">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>logout</span>
                </a>

            </div>
        </div>

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
                <select id="employeeFilter" class="form-group">
                    <option value="">Filter by Employee</option>
                    <option value="EMP-1001">Ahmed Mohamed</option>
                    <option value="EMP-1002">Fatima Ali</option>
                    <option value="EMP-1003">Omar Hassan</option>
                    <option value="EMP-1004">Aisha Abdi</option>
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
                        <th>Payment Method</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>SAL-2023-001</td>
                        <td>EMP-1001</td>
                        <td>Ahmed Mohamed</td>
                        <td>$1,650.00</td>
                        <td>$200.00</td>
                        <td>Bank Transfer</td>
                        <td>2023-06-05</td>
                        <td><span class="status-paid">Paid</span></td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewSalary('SAL-2023-001')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editSalary('SAL-2023-001')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteSalary('SAL-2023-001')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>SAL-2023-002</td>
                        <td>EMP-1002</td>
                        <td>Fatima Ali</td>
                        <td>$1,320.00</td>
                        <td>$0.00</td>
                        <td>Cash</td>
                        <td>2023-06-05</td>
                        <td><span class="status-paid">Paid</span></td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewSalary('SAL-2023-002')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editSalary('SAL-2023-002')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteSalary('SAL-2023-002')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>SAL-2023-003</td>
                        <td>EMP-1003</td>
                        <td>Omar Hassan</td>
                        <td>$1,430.00</td>
                        <td>$300.00</td>
                        <td>Online Payment</td>
                        <td>2023-06-05</td>
                        <td><span class="status-pending">Pending</span></td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewSalary('SAL-2023-003')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editSalary('SAL-2023-003')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteSalary('SAL-2023-003')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>SAL-2023-004</td>
                        <td>EMP-1004</td>
                        <td>Aisha Abdi</td>
                        <td>$1,210.00</td>
                        <td>$0.00</td>
                        <td>Bank Transfer</td>
                        <td>2023-06-05</td>
                        <td><span class="status-paid">Paid</span></td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewSalary('SAL-2023-004')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editSalary('SAL-2023-004')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteSalary('SAL-2023-004')">
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
                    <label for="employeeId">Employee ID</label>
                    <input type="text" id="employeeId" required>
                </div>

                <div class="form-group">
                    <label for="employeeName">Employee Name</label>
                    <input type="text" id="employeeName" readonly>
                </div>
                <div class="form-group">
                    <label for="Basesalary">Base salary</label>
                    <input type="text" id="Basesalary" readonly>
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
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Online">Online</option>
                            <option value="Check">Check</option>
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
                    <strong>Salary ID:</strong> <span id="viewId">SAL-2023-001</span>
                </div>
                <div class="detail-row">
                    <strong>Employee ID:</strong> <span id="viewEmployeeId">EMP-1001</span>
                </div>
                <div class="detail-row">
                    <strong>Employee Name:</strong> <span id="viewEmployeeName">Ahmed Mohamed</span>
                </div>
                <div class="detail-row">
                    <strong>Amount:</strong> <span id="viewAmount">$1,650.00</span>
                </div>
                <div class="detail-row">
                    <strong>Advance Salary:</strong> <span id="viewAdvance">$200.00</span>
                </div>
                <div class="detail-row">
                    <strong>Net Salary:</strong> <span id="viewNetSalary">$1,450.00</span>
                </div>
                <div class="detail-row">
                    <strong>Payment Method:</strong> <span id="viewPaymentMethod">Bank Transfer</span>
                </div>
                <div class="detail-row">
                    <strong>Payment Date:</strong> <span id="viewPaymentDate">15/06/2023</span>
                </div>
                <div class="detail-row">
                    <strong>Status:</strong> <span id="viewStatus" class="status-paid">Paid</span>
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
                <p>Are you sure you want to delete this salary record?</p>
                <p><strong>Salary ID:</strong> <span id="deleteSalaryId">SAL-2023-001</span></p>
                <p><strong>Employee:</strong> <span id="deleteEmployeeName">Ahmed Mohamed</span></p>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                <button type="button" class="btn" id="cancelDeleteBtn">Cancel</button>
            </div>
        </div>
    </div>



    <script>
        // Toggle dropdown when clicking the report button
        document.querySelector('.sidebar-report-btn').addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = this.closest('.report-dropdown');
            dropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside 
        document.addEventListener('click', function(e) {
            // Check if the click was outside the dropdown menu
            if (!e.target.closest('.sidebar-report-btn') && !e.target.closest('.report-dropdown-content')) {
                // If click was outside, remove the 'active' class to hide the dropdown
                document.querySelectorAll('.report-dropdown').forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });
    </script>

    <script src="jscript.js"></script>
</body>

</html>