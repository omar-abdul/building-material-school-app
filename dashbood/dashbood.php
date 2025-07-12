<?php
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
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
    <title>BMMS Dashboard</title>
</head>
<body>
    <div class="loader">
        <h1>Loading<span>....</span></h1>
    </div>
    <div class="page-content">
        <div class="sidebar">
            <div class="brand">
                <i class="fa-solid fa-bars toggle-sidebar"></i>
                <h3 class="brand-name">BMMS</h3>
            </div>
            <div class="sidebar-menu">
                <ul>
                <a href="/backend/dashbood/dashbood.php" class="sidebar-link active">
                    <i class="fa-solid fa-tachometer-alt"></i>
                     <span>Dashboard</span>
                </a> 
                <?php if ($role === 'admin'): ?> 
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
                <?php if ($role === 'admin'): ?>
                <a href="/backend/Transactions/index.php" class="sidebar-link">
                    <i class="fas fa-warehouse"></i>
                    <span>transactions</span>
                </a>
                <a href="/backend/Salaries/index.php" class="sidebar-link ">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Salaries</span>
                </a> 
                <a href="/backend/signup/index.php" class="sidebar-link">
                  <i class="fas fa-user-plus"></i>
                  <span>Sign Up</span>
                </a>
                <?php endif; ?>
                <nav class="sidebar">
                    <ul>
                        <li class="report-dropdown">
                            <a href="/backend/reports/index.php" class="sidebar-link sidebar-report-btn">
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
                <a href="/backend/dashbood/logout.php" class="sidebar-link" >
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>logout</span>
                </a>
                </ul>
            </div>
        </div>
        <main>
            <div class="header">
                <i class="fa-solid fa-bars bar-item"></i>
                <div class="search">
                    <input type="search" placeholder="Search materials, suppliers..." />
                    <i class="fa-solid fa-search"></i>
            </div>
                 <div class="profile">
                       <span class="bell"><i class="fa-regular fa-bell"></i></span>
                       <!-- <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Profile" /> 
                       <a href="/backend/dashbood/logout.php" class="logout-button" style="margin-left: 15px; color: red; text-decoration: none;">
                       <i class="fa-solid fa-right-from-bracket"></i>logout</a> -->
                 </div>
            </div>
            <div class="main-content">
                <div class="title">
                    <h1>Building Material Management System</h1>
                </div>
                
                <!-- Data Management Buttons -->
                <div class="data-management-buttons">
                    <?php if ($role === 'admin'): ?>
                    <a href="/backend/Categories/index.php" class="data-btn add-category">
                        <i class="fa-solid fa-tag"></i>
                        <span>Add Category</span>
                    </a>
                    <a href="/backend/Suppliers/index.php" class="data-btn add-supplier">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                        <span>Add Supplier</span>
                    </a>
                    <a href="/backend/Employees/index.php" class="data-btn view-orders">
                        <i class="fa-solid fa-cart-flatbed"></i>
                        <span>add employees </span>
                    </a>
                    <a href="/backend/Customers/index.php" class="data-btn view-transactions">
                        <i class="fa-solid fa-receipt"></i>
                        <span>add Customers</span>
                    </a>
                     <a href="/backend/Salaries/index.php" class="data-btn add-salary">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <span>Add Salary</span>
                    </a>
                     <a href="/backend/Transactions/index.php" class="data-btn add-customer">
                        <i class="fa-solid fa-users"></i>
                        <span>View Transactions</span>
                    </a>
                     <?php endif; ?>
                    <a href="/backend/Items/index.php" class="data-btn add-item">
                        <i class="fa-solid fa-box-circle-plus"></i>
                        <span>Add Item</span>
                    </a>
                     <a href="/backend/Inventory/index.php" class="data-btn update-inventory">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span>Update Inventory</span>
                    </a>
                    <a href="/backend/Orders/index.php" class="data-btn add-employee">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>View Orders</span>
                    </a>
                    
                    <a href="/backend/reports/inventory.php" class="data-btn add-report1">
                        <i class="fa-solid fa-warehouse"></i>
                        <span>Inventory Report</span>
                    </a>
                      <a href="/backend/reports/orders.php" class="data-btn add-report2">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span> Orders Report</span>
                    </a>
                      <a href="/backend/reports/items.php" class="data-btn add-report1">
                        <i class="fa-solid fa-box"></i>
                        <span>Items Report</span>
                    </a>
                    <?php if ($role === 'admin'): ?>
                     <a href="/backend/reports/salaries.php" class="data-btn add-report2">
                        <i class="fa-solid fa-money-check-dollar"></i>
                        <span>Salaries Report</span>
                    </a>
                    <a href="/backend/reports/transactions.php " class="data-btn add-employee">
                        <i class="fa-solid fa-receipt "></i>
                        <span>Transaction Report</span>
                    </a>
                     <?php endif; ?>
                </div>
                
                <div class="main-content-boxes">
                    <!-- Your dashboard boxes/content here -->
                    <!-- ... (same as in your original HTML) ... -->
                </div>
                
                <div class="projects-box">
                    <!-- Your projects table here -->
                    <!-- ... (same as in your original HTML) ... -->
                </div>
            </div>
        </main>
    </div>



<script>

  document.addEventListener('DOMContentLoaded', function () {
    const reportLinks = document.querySelectorAll('.report-dropdown-content a');
    const reportContainer = document.getElementById('report-frame');
    const dashboardWidgets = document.getElementById('dashboard-boxes');

    reportLinks.forEach(link => {
      link.addEventListener('click', function () {
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

    
    <script src="script.js"></script>
</body>
</html>