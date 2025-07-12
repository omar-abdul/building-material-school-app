
<?php include 'connection.php';?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMMS - Categories Management</title>
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
                </a>
                <a href="/backend/Categories/index.php" class="sidebar-link active">
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
            <a href="/backend/Categories/logout.php" class="sidebar-link" >
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>logout</span>
                </a>


            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1>Categories Management</h1>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="addCategoryBtn">
                        <i class="fas fa-plus"></i> Add New Category
                    </button>
                </div>
            </div>
            
            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search categories...">
                </div>
            </div>
            
            <!-- Categories Table -->
            <table class="categories-table">
                <thead>
                    <tr>
                        <th>Category ID</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>CAT-1001</td>
                        <td>Electronics</td>
                        <td>All electronic devices and components</td>
                        <td>2023-05-15</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewItems('CAT-1001')">
                                <i class="fas fa-eye"></i> View Items
                            </button>
                            <button class="action-btn edit-btn" onclick="editCategory('CAT-1001')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteCategory('CAT-1001')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>CAT-1002</td>
                        <td>Clothing</td>
                        <td>Men's, women's and children's clothing</td>
                        <td>2023-05-15</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewItems('CAT-1002')">
                                <i class="fas fa-eye"></i> View Items
                            </button>
                            <button class="action-btn edit-btn" onclick="editCategory('CAT-1002')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteCategory('CAT-1002')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>CAT-1003</td>
                        <td>Furniture</td>
                        <td>Home and office furniture</td>
                        <td>2023-05-15</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewItems('CAT-1003')">
                                <i class="fas fa-eye"></i> View Items
                            </button>
                            <button class="action-btn edit-btn" onclick="editCategory('CAT-1003')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteCategory('CAT-1003')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>CAT-1004</td>
                        <td>Groceries</td>
                        <td>Food items and household supplies</td>
                        <td>2023-05-15</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewItems('CAT-1004')">
                                <i class="fas fa-eye"></i> View Items
                            </button>
                            <button class="action-btn edit-btn" onclick="editCategory('CAT-1004')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteCategory('CAT-1004')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>CAT-1005</td>
                        <td>Books</td>
                        <td>Educational and entertainment books</td>
                        <td>2023-05-15</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewItems('CAT-1005')">
                                <i class="fas fa-eye"></i> View Items
                            </button>
                            <button class="action-btn edit-btn" onclick="editCategory('CAT-1005')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteCategory('CAT-1005')">
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
    
    <!-- Add/Edit Category Modal -->
    <div class="modal" id="categoryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New Category</h3>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <form id="categoryForm">
                <input type="hidden" id="categoryId">
                
                <div class="form-group">
                    <label for="categoryName">Category Name</label>
                    <input type="text" id="categoryName" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" rows="3"></textarea>
                </div>

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
    
    <!-- View Items Modal -->
    <div class="modal" id="viewItemsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Items in Category: <span id="categoryNameTitle">Electronics</span></h3>
                <button class="close-btn" id="closeViewItemsModal">&times;</button>
            </div>
            <div class="items-list">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Item Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody id="itemsListBody">
                        <!-- Items will be populated here -->
                    </tbody>
                </table>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" id="closeViewItemsBtn">Close</button>
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
                <p>Are you sure you want to delete this category?</p>
                <p><strong>Category ID:</strong> <span id="deleteCategoryId">CAT-1001</span> - <span id="deleteCategoryName">Electronics</span></p>
                <p class="warning-text">All items in this category will be moved to 'Uncategorized'.</p>
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