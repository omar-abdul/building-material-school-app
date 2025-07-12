<?php include 'connection.php';?>

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
        <div class="sidebar">
            <div class="brand">
                <i class="fas fa-building"></i>
                <span class="brand-name">BMMS</span>
            </div>
            <div class="sidebar-menu">
                <a href="/backend/dashbood/dashbood.html" class="sidebar-link">
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
                <a href="/backend/Customers/index.php" class="sidebar-link active">
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
                 <a href="/backend/Customers/logout.php" class="sidebar-link" >
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>logout</span>
                </a>









            </div>
        </div>
        
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
                    <tr>
                        <td>CUST-1001</td>
                        <td>Ahmed Mohamed</td>
                        <td>+252612345678</td>
                        <td>ahmed@example.com</td>
                        <td>Mogadishu, Somalia</td>
                        <td>2023-05-15</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewCustomer('CUST-1001')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editCustomer('CUST-1001')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteCustomer('CUST-1001')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            <button class="action-btn history-btn" onclick="viewOrderHistory('CUST-1001')">
                                <i class="fas fa-history"></i> History
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>CUST-1002</td>
                        <td>Fatima Ali</td>
                        <td>+252623456789</td>
                        <td>fatima@example.com</td>
                        <td>Hargeisa, Somalia</td>
                        <td>2023-05-15</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewCustomer('CUST-1002')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editCustomer('CUST-1002')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteCustomer('CUST-1002')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            <button class="action-btn history-btn" onclick="viewOrderHistory('CUST-1002')">
                                <i class="fas fa-history"></i> History
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>CUST-1003</td>
                        <td>Omar Hassan</td>
                        <td>+252634567890</td>
                        <td>omar@example.com</td>
                        <td>Kismayo, Somalia</td>
                        <td>2023-05-15</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewCustomer('CUST-1003')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editCustomer('CUST-1003')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteCustomer('CUST-1003')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            <button class="action-btn history-btn" onclick="viewOrderHistory('CUST-1003')">
                                <i class="fas fa-history"></i> History
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>CUST-1004</td>
                        <td>Aisha Abdi</td>
                        <td>+252645678901</td>
                        <td>aisha@example.com</td>
                        <td>Bosaso, Somalia</td>
                        <td>2023-05-15</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewCustomer('CUST-1004')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editCustomer('CUST-1004')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteCustomer('CUST-1004')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            <button class="action-btn history-btn" onclick="viewOrderHistory('CUST-1004')">
                                <i class="fas fa-history"></i> History
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>CUST-1005</td>
                        <td>Mohamed Yusuf</td>
                        <td>+252656789012</td>
                        <td>mohamed@example.com</td>
                        <td>Garowe, Somalia</td>
                        <td>2023-05-15</td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewCustomer('CUST-1005')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editCustomer('CUST-1005')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteCustomer('CUST-1005')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            <button class="action-btn history-btn" onclick="viewOrderHistory('CUST-1005')">
                                <i class="fas fa-history"></i> History
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