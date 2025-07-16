<?php

/**
 * Inventory Management Page
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
    <title>BMMS - Inventory Management</title>
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
                    <h1>Inventory Management</h1>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="addInventoryBtn">
                        <i class="fas fa-plus"></i> Add New Item
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search by Item Name...">
                </div>
            </div>

            <!-- Inventory Table -->
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Inventory ID</th>
                        <th>Item ID</th>
                        <th>Item Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Last Updated</th>
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



    <!-- Add/Edit Inventory Modal -->
    <div class="modal" id="inventoryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New Inventory Item</h3>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <form id="inventoryForm">
                <input type="hidden" id="inventoryId">

                <div class="form-group">
                    <label for="itemId">Item ID</label>
                    <input type="text" id="itemId" required>
                </div>

                <div class="form-group">
                    <label for="itemName">Item Name</label>
                    <input type="text" id="itemName" readonly required>
                </div>

                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" step="0.01" readonly required>
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" required>
                </div>

                <div class="form-group">
                    <label for="lastUpdated">Last Updated</label>
                    <input type="datetime-local" id="lastUpdated" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-danger" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-success" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Inventory Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Inventory Item Details</h3>
                <button class="close-btn" id="closeViewModal">&times;</button>
            </div>
            <div class="item-details">
                <div class="detail-row">
                    <strong>Inventory ID:</strong> <span id="viewId">INV-1001</span>
                </div>
                <div class="detail-row">
                    <strong>Item ID:</strong> <span id="viewItemId">ITM-5001</span>
                </div>
                <div class="detail-row">
                    <strong>Item Name:</strong> <span id="viewItemName">Flour (50kg)</span>
                </div>
                <div class="detail-row">
                    <strong>Price:</strong> <span id="viewPrice">$25.99</span>
                </div>
                <div class="detail-row">
                    <strong>Quantity:</strong> <span id="viewQuantity">150</span>
                </div>
                <div class="detail-row">
                    <strong>Last Updated:</strong> <span id="viewLastUpdated">2023-06-15 10:30 AM</span>
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
                <p>Are you sure you want to delete this inventory record?</p>
                <p><strong>Inventory ID:</strong> <span id="deleteInventoryId">INV-1001</span> - <span id="deleteItemName">Flour (50kg)</span></p>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                <button type="button" class="btn" id="cancelDeleteBtn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- <input type="number" id="item_id_input" placeholder="Enter Item ID">
<input type="text" id="item_name_input" placeholder="Item Name" readonly>
<input type="text" id="price_input" placeholder="Price" readonly> -->




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