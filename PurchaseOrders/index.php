<?php

/**
 * Purchase Orders Management Page
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
    <title>BMMS - Purchase Orders Management</title>
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
                    <h1>Purchase Orders Management</h1>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="addPurchaseOrderBtn">
                        <i class="fas fa-plus"></i> Add New Purchase Order
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search purchase orders...">
                </div>
                <select id="statusFilter" class="form-group">
                    <option value="">Filter by Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Processing">Processing</option>
                    <option value="Received">Received</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Purchase Orders Table -->
            <table class="purchase-orders-table">
                <thead>
                    <tr>
                        <th>Purchase Order ID</th>
                        <th>Supplier</th>
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

    <!-- Add/Edit Purchase Order Modal -->
    <div class="modal purchase-orders-modal" id="purchaseOrderModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New Purchase Order</h3>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <form id="purchaseOrderForm" class="purchase-orders-form">
                <input type="hidden" id="purchaseOrderId">

                <div class="form-row">
                    <div class="form-group">
                        <label for="supplierSelect">Supplier</label>
                        <div class="autocomplete-container">
                            <input type="text" id="supplierSelect" placeholder="Search suppliers..." autocomplete="off">
                            <div class="autocomplete-dropdown" id="supplierDropdown"></div>
                        </div>
                        <input type="hidden" id="supplierId">
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
                            <option value="Received">Received</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="orderDate">Order Date</label>
                        <input type="date" id="orderDate" required>
                    </div>
                </div>

                <h4>Purchase Order Items</h4>

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
                        <input type="number" id="unitPrice" step="0.01" min="0" placeholder="Enter price">
                    </div>
                    <div class="form-group" style="align-self: flex-end;">
                        <button type="button" class="btn btn-primary" id="addItemBtn">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                </div>

                <div class="items-list" id="itemsList">
                    <div class="no-items">No items added to this purchase order</div>
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
                    <button type="submit" class="btn btn-success" id="saveBtn">Save Purchase Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Purchase Order Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Purchase Order Details</h3>
                <button class="close-btn" id="closeViewModal">&times;</button>
            </div>
            <div class="purchase-order-details">
                <div class="detail-row">
                    <strong>Purchase Order ID:</strong> <span id="viewId">PO-1001</span>
                </div>
                <div class="detail-row">
                    <strong>Supplier:</strong> <span id="viewSupplier">Tech Suppliers Inc. (SUP-001)</span>
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

                <h4 style="margin-top: 20px;">Purchase Order Items</h4>
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
                            <td>5</td>
                            <td>$400.00</td>
                            <td>$2,000.00</td>
                        </tr>
                    </tbody>
                </table>

                <div class="summary-row">
                    <strong>Total Items:</strong> <span>5</span>
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
                <p>Are you sure you want to delete this purchase order record?</p>
                <p><strong>Purchase Order ID:</strong> <span id="deletePurchaseOrderId">PO-1001</span></p>
                <p><strong>Supplier:</strong> <span id="deleteSupplierName">Tech Suppliers Inc.</span></p>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                <button type="button" class="btn" id="cancelDeleteBtn">Cancel</button>
            </div>
        </div>
    </div>



        <!-- JavaScript Configuration -->\n    <script src="<?= BASE_URL ?>config/js-config.php"></script>\n    <script src="purchase-orders.js"></script>
</body>

</html>