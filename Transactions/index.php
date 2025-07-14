<?php

/**
 * Transactions Management Page
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
    <title>BMMS - Transactions Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
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
                <a href="/backend/Transactions/index.php" class="sidebar-link active">
                    <i class="fas fa-warehouse"></i>
                    <span>transactions</span>
                </a>
                <a href="/backend/Salaries/index.php" class="sidebar-link ">
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
                    <h1>Transactions Management</h1>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="addTransactionBtn">
                        <i class="fas fa-plus"></i> Add New Transaction
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search transactions...">
                </div>
                <select id="paymentMethodFilter" class="form-group">
                    <option value="">Filter by Payment Method</option>
                    <option value="Cash">Cash</option>
                    <option value="Bank">Bank</option>
                    <option value="Online">Online</option>
                    <option value="Credit">Credit</option>
                </select>
            </div>

            <!-- Transactions Table -->
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Order ID</th>
                        <th>Customer ID</th>
                        <th>Customer Name</th>
                        <th>Payment Method</th>
                        <th>Amount Paid</th>
                        <th>Balance</th>
                        <th>Discount</th>
                        <th>Date</th>
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
    </div>

    <!-- Add/Edit Transaction Modal -->
    <div class="modal" id="transactionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New Transaction</h3>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <form id="transactionForm">
                <input type="hidden" id="transactionId">

                <div class="form-group">
                    <label for="orderId">Order ID</label>
                    <input type="text" id="orderId" required>
                </div>


                <div class="form-row">
                    <div class="form-group">
                        <label for="totalAmount">Total Amount ($)</label>
                        <input type="number" id="totalAmount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="amountPaid">Amount Paid ($)</label>
                        <input type="number" id="amountPaid" step="0.01" required>
                    </div>
                </div>
                <!-- Add this form-group right after the payment method section in the transaction modal -->
                <div class="form-group">
                    <label for="transactionStatus">Status</label>
                    <select id="transactionStatus" required>
                        <option value="Paid">Paid</option>
                        <option value="Partial">Partial</option>
                        <option value="Unpaid">Unpaid</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="paymentMethod">Payment Method</label>
                        <select id="paymentMethod" required>
                            <option value="">Select Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank</option>
                            <option value="Online">Online</option>
                            <option value="Credit">Credit</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="balance">Balance ($)</label>
                        <input type="number" id="balance" step="0.01" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="discountPercentage">Discount (%)</label>
                        <input type="number" id="discountPercentage" step="0.01" min="0" max="100" value="0">
                    </div>
                    <div class="form-group">
                        <label for="discountAmount">Discount Amount ($)</label>
                        <input type="number" id="discountAmount" step="0.01" readonly>
                    </div>
                </div>


                <!-- Add this form-group right after the customer details section in the transaction modal -->
                <div class="form-group">
                    <label for="transactionDate">Transaction Date</label>
                    <input type="date" id="transactionDate" required>
                </div>


                <div class="form-group button-group">
                    <button type="button" class="btn btn-primary" id="applyDiscountBtn">
                        <i class="fas fa-percentage"></i> Apply Discount
                    </button>
                    <button type="button" class="btn btn-primary" id="calculateBalanceBtn">
                        <i class="fas fa-calculator"></i> Calculate Balance
                    </button>
                    <button type="button" class="btn btn-success" id="markAsPaidBtn">
                        <i class="fas fa-check-circle"></i> Mark as Paid
                    </button>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-danger" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-success" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Transaction Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Transaction Details</h3>
                <button class="close-btn" id="closeViewModal">&times;</button>
            </div>
            <div class="item-details">
                <div class="detail-row">
                    <strong>Transaction ID:</strong> <span id="viewId">TRX-1001</span>
                </div>
                <div class="detail-row">
                    <strong>Order ID:</strong> <span id="viewOrderId">ORD-5001</span>
                </div>
                <div class="detail-row">
                    <strong>Customer ID:</strong> <span id="viewCustomerId">CUST-2001</span>
                </div>
                <div class="detail-row">
                    <strong>Customer Name:</strong> <span id="viewCustomerName">Ahmed Mohamed</span>
                </div>
                <div class="detail-row">
                    <strong>Payment Method:</strong> <span id="viewPaymentMethod">Cash</span>
                </div>
                <div class="detail-row">
                    <strong>Total Amount:</strong> <span id="viewTotalAmount">$500.00</span>
                </div>
                <div class="detail-row">
                    <strong>Amount Paid:</strong> <span id="viewAmountPaid">$500.00</span>
                </div>
                <div class="detail-row">
                    <strong>Balance:</strong> <span id="viewBalance">$0.00</span>
                </div>
                <div class="detail-row">
                    <strong>Discount:</strong> <span id="viewDiscount">5% ($25.00)</span>
                </div>
                <div class="detail-row">
                    <strong>Status:</strong> <span id="viewStatus" class="status-paid">Paid</span>
                </div>
                <div class="detail-row">
                    <strong>Transaction Date:</strong> <span id="viewDate">2023-05-15</span>
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
                <p>Are you sure you want to delete this transaction record?</p>
                <p><strong>Transaction ID:</strong> <span id="deleteTransactionId">TRX-1001</span> - <span id="deleteTransactionOrder">ORD-5001</span></p>
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



    <script src="script.js"></script>
</body>

</html>