<?php

/**
 * Suppliers Management Page
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
    <title>BMMS - Suppliers Management</title>
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
                    <h1>Suppliers Management</h1>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="addSupplierBtn">
                        <i class="fas fa-plus"></i> Add New Supplier
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search suppliers...">
                </div>
            </div>

            <!-- Suppliers Table -->
            <table class="suppliers-table">
                <thead>
                    <tr>
                        <th>Supplier ID</th>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Date Added</th>
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

    <!-- Add/Edit Supplier Modal -->
    <div class="modal" id="supplierModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New Supplier</h3>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <form id="supplierForm">
                <input type="hidden" id="supplierId">

                <div class="form-group">
                    <label for="supplierName">Supplier Name</label>
                    <input type="text" id="supplierName" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contactPerson">Contact Person</label>
                        <input type="text" id="contactPerson" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email">
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

    <!-- View Supplier Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Supplier Details</h3>
                <button class="close-btn" id="closeViewModal">&times;</button>
            </div>
            <div class="item-details">
                <div class="detail-row">
                    <strong>Supplier ID:</strong> <span id="viewId">SUP-1001</span>
                </div>
                <div class="detail-row">
                    <strong>Supplier Name:</strong> <span id="viewName">Global Electronics</span>
                </div>
                <div class="detail-row">
                    <strong>Contact Person:</strong> <span id="viewContact">John Smith</span>
                </div>
                <div class="detail-row">
                    <strong>Phone:</strong> <span id="viewPhone">+1234567890</span>
                </div>
                <div class="detail-row">
                    <strong>Email:</strong> <span id="viewEmail">john@globalelectronics.com</span>
                </div>
                <div class="detail-row">
                    <strong>Address:</strong>
                    <p id="viewAddress">123 Tech Street, Silicon Valley</p>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" id="viewItemsBtn">
                    <i class="fas fa-boxes"></i> View Supplied Items
                </button>
                <button type="button" class="btn btn-success" id="sendEmailBtn">
                    <i class="fas fa-envelope"></i> Send Email
                </button>
                <button type="button" class="btn" id="closeViewBtn">Close</button>
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
                <p>Are you sure you want to delete this supplier?</p>
                <p><strong>Supplier ID:</strong> <span id="deleteSupplierId">SUP-1001</span> - <span id="deleteSupplierName">Global Electronics</span></p>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                <button type="button" class="btn" id="cancelDeleteBtn">Cancel</button>
            </div>
        </div>
    </div>



    <!-- JavaScript Configuration -->\n <script src="<?= BASE_URL ?>config/js-config.php"></script>\n <script src="suppliers.js"></script>
</body>

</html>