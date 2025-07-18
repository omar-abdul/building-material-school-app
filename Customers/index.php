<?php

/**
 * Customers Management Page
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
    <title>BMMS - Customers Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <h1>Customers Management</h1>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="addCustomerBtn">
                        <i class="fas fa-plus"></i> Add New Customer
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search customers by name or phone...">
                </div>
            </div>

            <!-- Customers Table -->
            <table class="customers-table">
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Date Added</th>
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

    <!-- Add/Edit Customer Modal -->
    <div class="modal" id="customerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New Customer</h3>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <form id="customerForm">
                <input type="hidden" id="customerId">

                <div class="form-group">
                    <label for="customerName">Customer Name *</label>
                    <input type="text" id="customerName" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone *</label>
                        <input type="tel" id="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email">
                    </div>
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

    <!-- View Customer Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Customer Details</h3>
                <button class="close-btn" id="closeViewModal">&times;</button>
            </div>
            <div class="item-details">
                <div class="detail-row">
                    <strong>Customer ID:</strong> <span id="viewId">CUST-1001</span>
                </div>
                <div class="detail-row">
                    <strong>Name:</strong> <span id="viewName">Ahmed Mohamed</span>
                </div>
                <div class="detail-row">
                    <strong>Phone:</strong> <span id="viewPhone">+252612345678</span>
                </div>
                <div class="detail-row">
                    <strong>Email:</strong> <span id="viewEmail">ahmed@example.com</span>
                </div>
                <div class="detail-row">
                    <strong>Address:</strong>
                    <p id="viewAddress">Mogadishu, Somalia</p>
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
                <p>Are you sure you want to delete this customer?</p>
                <p><strong>Customer ID:</strong> <span id="deleteCustomerId">CUST-1001</span> - <span id="deleteCustomerName">Ahmed Mohamed</span></p>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                <button type="button" class="btn" id="cancelDeleteBtn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Order History Modal -->
    <div class="modal" id="historyModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Order History</h3>
                <button class="close-btn" id="closeHistoryModal">&times;</button>
            </div>
            <div class="item-details">
                <div class="detail-row">
                    <strong>Customer:</strong> <span id="historyCustomerName">Ahmed Mohamed (CUST-1001)</span>
                </div>

                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Transactions</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="orderHistoryBody">
                        <!-- Order history will be populated here -->
                    </tbody>
                </table>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" id="closeHistoryBtn">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Add this script to your existing script section
        // Toggle dropdown when clicking the report button
        document.getElementById('reportBtn').addEventListener('click', function(e) {
            e.preventDefault();
        });
    </script>
    </script>

    <script src="customers.js"></script>
</body>

</html>