
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports | BMMS Dashboard</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="page-content">
    <div class="sidebar">
      <div class="brand">
        <i class="fa-solid fa-bars toggle-sidebar"></i>
        <h3 class="brand-name">BMMS</h3>
      </div>
      <div class="sidebar-menu">
        <ul>
          <li><a href="/backend/dashbood/dashbood.php" class="sidebar-link"><i class="fa-solid fa-tachometer-alt"></i><span>Dashboard</span></a></li>
          <li><a href="/backend/Items/index.php" class="sidebar-link"><i class="fa-solid fa-boxes"></i><span>Items</span></a></li>
          <li><a href="/backend/Categories/index.php" class="sidebar-link"><i class="fa-solid fa-tags"></i><span>Categories</span></a></li>
          <li><a href="/backend/Suppliers/index.php" class="sidebar-link"><i class="fa-solid fa-truck"></i><span>Suppliers</span></a></li>
          <li><a href="/backend/Inventory/index.php" class="sidebar-link"><i class="fa-solid fa-warehouse"></i><span>Inventory</span></a></li>
          <li><a href="/backend/Employees/index.php" class="sidebar-link"><i class="fa-solid fa-file-invoice-dollar"></i><span>Employees</span></a></li>
          <li><a href="/backend/Customers/index.php" class="sidebar-link"><i class="fa-solid fa-users"></i><span>Customers</span></a></li>
          <li><a href="/backend/Orders/index.php" class="sidebar-link"><i class="fa-solid fa-cart-flatbed"></i><span>Orders</span></a></li>
          <li><a href="/backend/Transactions/index.php" class="sidebar-link"><i class="fa-solid fa-money-bill-wave"></i><span>Transactions</span></a></li>
          <li><a href="/backend/Salaries/index.php" class="sidebar-link"><i class="fa-solid fa-user-tie"></i><span>Salaries</span></a></li>
            <li class="dropdown">
          <a href="#" class="sidebar-link active" onclick="toggleDropdown()">
            <i class="fa-solid fa-file-invoice"></i>
            <span>Reports</span>
            <i class="fa-solid fa-caret-down dropdown-icon"></i>
          </a>
          <ul class="dropdown-menu show">
            <li><a href="/backend/reports/Inventory.php">Inventory Report</a></li>
            <li><a href="/backend/reports/items.php">Items Report</a></li>
            <li><a href="/backend/reports/orders.php">Orders Report</a></li>
            <li><a href="/backend/reports/salaries.php">Salary Report</a></li>
            <li><a href="/backend/reports/Transactions.php">Transaction Report</a></li>
          </ul>
        </li>
          <li><a href="#" class="sidebar-link"><i class="fa-solid fa-cog"></i><span>Settings</span></a></li>
        </ul>
      </div>
    </div>
  <main>
   <div class="header">
    <i class="fa-solid fa-bars bar-item"></i>
    <h1 id="main-title">Reports</h1>
   </div>

   <div id="main-content">
    <h1>Welcome to BMMS Report Form!</h1>
   </div>
 </main>

    <script src="scripts.js"></script>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

</body>
</html>

