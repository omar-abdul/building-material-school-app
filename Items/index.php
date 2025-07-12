<?php
include 'connection.php';
// dashboard.php - Dashboard Page
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}


$role = $_SESSION['role']; // 'admin' or 'user'
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMMS - Items Management</title>
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
                <a href="/backend/dashbood/dashbood.php" class="sidebar-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <?php if ($role === 'admin'): ?>
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
                <?php endif; ?>
                <a href="/backend/Items/index.php" class="sidebar-link active">
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
                <?php if ($role === 'admin'): ?>
                <a href="/backend/Transactions/index.php" class="sidebar-link">
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
<?php endif; ?>
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
                                <?php if ($role === 'admin'): ?>
                                <li><a href="/backend/reports/salaries.php"> Salaries Report</a></li>
                                 <li><a href="/backend/reports/transactions.php"> Transactions Report</a></li>
                                 <li><a href="\backend\signup\backup.php"> backup </a></li>
                                 <?php endif; ?>
                            </ul>
                        </li>
                    </ul>
              </nav>

              <a href="/backend/Items/logout.php" class="sidebar-link" >
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>logout</span>
                </a>

            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1>Items Management</h1>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="addItemBtn">
                        <i class="fas fa-plus"></i> Add New Item
                    </button>
                </div>
            </div>
            
            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search items...">
                </div>
                <select id="categoryFilter" class="form-group">
                    <option value="">Filter by Category</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Office Supplies">Office Supplies</option>
                </select>
            </div>
            
            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item ID</th>
                        <th>Item Name</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Registered By</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>ITM-1001</td>
                        <td>Laptop Dell XPS 15</td>
                        <td>Electronics</td>
                        <td>Tech Suppliers Inc.</td>
                        <td>Ahmed Mohamed</td>
                        <td>2025-05-15</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewItem('ITM-1001')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editItem('ITM-1001')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteItem('ITM-1001')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>ITM-1002</td>
                        <td>Office Chair</td>
                        <td>Furniture</td>
                        <td>Furniture World</td>
                        <td>Fatima Ali</td>
                        <td>2025-05-16</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewItem('ITM-1002')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editItem('ITM-1002')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteItem('ITM-1002')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>ITM-1003</td>
                        <td>Printer HP LaserJet</td>
                        <td>Electronics</td>
                        <td>Office Equipment Co.</td>
                        <td>Omar Hassan</td>
                        <td>2025-05-17</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewItem('ITM-1003')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editItem('ITM-1003')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteItem('ITM-1003')">
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
    
    <!-- Add/Edit Item Modal -->
<div class="modal" id="itemModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Add New Item</h3>
            <button class="close-btn" id="closeModal">&times;</button>
        </div>
        <form id="itemForm">
            <input type="hidden" id="itemId">
            
            <div class="form-group">
                <label for="itemName">Item Name</label>
                <input type="text" id="itemName" required>
            </div>
              <!-- Added Price field -->
              <div class="form-group">
                <label for="itemPrice">Price ($)</label>
                <input type="number" step="0.01" min="0" id="itemPrice" placeholder="0.00" required>
              </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="categoryId">Category ID</label>
                    <input type="text" id="categoryId" required>
                </div>
                <div class="form-group">
                    <label for="categoryName">Category Name</label>
                    <input type="text" id="categoryName" readonly>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="supplierId">Supplier ID</label>
                    <input type="text" id="supplierId" required>
                </div>
                <div class="form-group">
                    <label for="supplierName">Supplier Name</label>
                    <input type="text" id="supplierName" readonly>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="employeeId">Registered By (Employee ID)</label>
                    <input type="text" id="employeeId" required>
                </div>
                <div class="form-group">
                    <label for="employeeName">Employee Name</label>
                    <input type="text" id="employeeName" readonly>
                </div>
            </div>
            
            <!-- Note Field -->
            <div class="form-group">
                <label for="note">Note</label>
                <input type="text" id="note" placeholder="Enter any notes about the item">
            </div>
            
            <!-- Description Field -->
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" rows="3" placeholder="Enter detailed description of the item"></textarea>
            </div>
            
            <!-- Created Date Field -->
            <div class="form-group">
                <label for="createdDate">Created Date</label>
                <input type="date" id="createdDate" required>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-danger" id="cancelBtn">Cancel</button>
                <button type="submit" class="btn btn-success" id="saveBtn">Save</button>
            </div>
        </form>
    </div>
</div>
    
    <!-- View Item Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Item Details</h3>
                <button class="close-btn" id="closeViewModal">&times;</button>
            </div>
            <div class="item-details">
                <div class="detail-row">
                    <strong>Item ID:</strong> <span id="viewId">ITM-1001</span>
                </div>
                <div class="detail-row">
                    <strong>Item Name:</strong> <span id="viewName">Laptop Dell XPS 15</span>
                </div>
                <div class="detail-row">
                <strong>Price:</strong> <span id="viewPrice"></span>
            </div>
                <div class="detail-row">
                    <strong>Category:</strong> <span id="viewCategory">Electronics</span>
                </div>
                <div class="detail-row">
                    <strong>Supplier:</strong> <span id="viewSupplier">Tech Suppliers Inc.</span>
                </div>
                <div class="detail-row">
                    <strong>Registered By:</strong> <span id="viewEmployee">Ahmed Mohamed</span>
                </div>
                <div class="detail-row">
                    <strong>Note:</strong> <span id="viewNote">High-performance laptop for executives</span>
                </div>
                <div class="detail-row">
                    <strong>Description:</strong> 
                    <p id="viewDescription">Dell XPS 15 with 16GB RAM, 1TB SSD, and 4K display. Perfect for graphic design and video editing.</p>
                </div>
                <div class="detail-row">
                    <strong>Created Date:</strong> <span id="viewDate">2025-05-15</span>
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
                <p>Are you sure you want to delete this item?</p>
                <p><strong>Item ID:</strong> <span id="deleteItemId">ITM-1001</span> - <span id="deleteItemName">Laptop Dell XPS 15</span></p>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                <button type="button" class="btn" id="cancelDeleteBtn">Cancel</button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>