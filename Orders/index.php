<?php

/**
 * Orders Management Page
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
    <title>BMMS - Sales Orders Management</title>
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
                    <h1>Sales Orders Management</h1>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="addOrderBtn">
                        <i class="fas fa-plus"></i> Add New Order
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search orders...">
                </div>
                <select id="statusFilter" class="form-group">
                    <option value="">Filter by Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Processing">Processing</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Orders Table -->
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items Count</th>
                        <th>Employee</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Order Date</th>
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

    <!-- Add/Edit Order Modal -->
    <div class="modal sales-orders-modal" id="orderModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New Order</h3>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <form id="orderForm" class="sales-orders-form">
                <input type="hidden" id="orderId">

                <div class="form-row">
                    <div class="form-group">
                        <label for="customerSelect">Customer</label>
                        <div class="autocomplete-container">
                            <input type="text" id="customerSelect" placeholder="Search customers..." autocomplete="off">
                            <div class="autocomplete-dropdown" id="customerDropdown"></div>
                        </div>
                        <input type="hidden" id="customerId">
                    </div>
                    <div class="form-group">
                        <label for="employeeSelect">Employee (Optional)</label>
                        <div class="autocomplete-container">
                            <input type="text" id="employeeSelect" placeholder="Search employees..." autocomplete="off">
                            <div class="autocomplete-dropdown" id="employeeDropdown"></div>
                        </div>
                        <input type="hidden" id="employeeId">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" required>
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="orderDate">Order Date</label>
                        <input type="date" id="orderDate" required>
                    </div>
                </div>

                <h4>Order Items</h4>

                <div class="form-row">
                    <div class="form-group">
                        <label for="itemSelect">Item</label>
                        <div class="autocomplete-container">
                            <input type="text" id="itemSelect" placeholder="Search items..." autocomplete="off">
                            <div class="autocomplete-dropdown" id="itemDropdown"></div>
                        </div>
                        <input type="hidden" id="itemId">
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" min="1" value="1">
                    </div>
                    <div class="form-group">
                        <label for="unitPrice">Unit Price ($)</label>
                        <input type="number" id="unitPrice" step="0.01" readonly>
                    </div>
                    <div class="form-group" style="align-self: flex-end;">
                        <button type="button" class="btn btn-primary" id="addItemBtn">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                </div>

                <div class="items-list" id="itemsList">
                    <div class="no-items">No items added to this order</div>
                </div>

                <div class="summary-row">
                    <span>Total Items:</span>
                    <span id="totalItems">0</span>
                </div>
                <div class="summary-row">
                    <span>Total Amount:</span>
                    <span id="totalAmount">$0.00</span>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-danger" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-success" id="saveBtn">Save Order</button>
                </div>
            </form>
        </div>
    </div>






    <!-- View Order Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Order Details</h3>
                <button class="close-btn" id="closeViewModal">&times;</button>
            </div>
            <div class="order-details">
                <div class="detail-row">
                    <strong>Order ID:</strong> <span id="viewId">ORD-1001</span>
                </div>
                <div class="detail-row">
                    <strong>Customer:</strong> <span id="viewCustomer">Ahmed Mohamed (CUST-001)</span>
                </div>
                <div class="detail-row">
                    <strong>Employee:</strong> <span id="viewEmployee">Omar Ali (EMP-001)</span>
                </div>
                <div class="detail-row">
                    <strong>Status:</strong> <span id="viewStatus" class="status-pending">Pending</span>
                </div>
                <div class="detail-row">
                    <strong>Order Date:</strong> <span id="viewOrderDate">2025-05-15</span>
                </div>

                <h4 style="margin-top: 20px;">Order Items</h4>
                <table style="width: 100%; margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="viewItemsList">
                        <tr>
                            <td>Laptop (ITM-001)</td>
                            <td>1</td>
                            <td>$500.00</td>
                            <td>$500.00</td>
                        </tr>
                        <tr>
                            <td>Smartphone (ITM-002)</td>
                            <td>2</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Monitor (ITM-003)</td>
                            <td>1</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>

                <div class="summary-row">
                    <strong>Total Items:</strong> <span>3</span>
                </div>
                <!-- <div class="summary-row">
                    <strong>Total Amount:</strong> <span>$1,350.00</span>
                </div> -->
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
                <p>Are you sure you want to delete this order record?</p>
                <p><strong>Order ID:</strong> <span id="deleteOrderId">ORD-1001</span></p>
                <p><strong>Customer:</strong> <span id="deleteCustomerName">Ahmed Mohamed</span></p>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                <button type="button" class="btn" id="cancelDeleteBtn">Cancel</button>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reportLinks = document.querySelectorAll('.report-dropdown-content a');
            const reportContainer = document.getElementById('report-frame');
            const dashboardWidgets = document.getElementById('dashboard-boxes');

            reportLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (reportContainer && dashboardWidgets) {
                        reportContainer.style.display = 'block';
                        dashboardWidgets.style.display = 'none';
                    }
                });
            });
        });

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

    <script src="orders.js"></script>
</body>

</html>