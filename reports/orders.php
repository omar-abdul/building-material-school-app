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
  <meta charset="UTF-8">
  <title>Orders Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- CSS & DataTables -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
  <link rel="stylesheet" href="style.css">
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
                <ul>
                <a href="/backend/dashbood/dashbood.php" class="sidebar-link ">
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
                        <li class="dropdown">
                            <a href="#" class="sidebar-link active">
                                <i class="fa-solid fa-chart-pie"></i>
                                <span>Reports</span>
                                <i class="fa-solid fa-angle-down dropdown-icon"></i>
                            </a>
                            <ul class="dropdown-menu show">
                                <li><a href="/backend/reports/inventory.php">Inventory Report</a></li>
                                <li><a href="/backend/reports/items.php">Items Report</a></li>
                                <li><a href="/backend/reports/orders.php">Orders Report</a></li>
                                <?php if ($role === 'admin'): ?>
                                <li><a href="/backend/reports/salaries.php"> Salaries Report</a></li>
                                 <li><a href="/backend/reports/transactions.php"> Transactions Report</a></li>
                                 <?php endif; ?>
                            </ul>
                        </li>
                    </ul>
                </nav>
                <a href="/backend/reports/logout.php" class="sidebar-link" >
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>logout</span>
                </a>

                </ul>
          </div>
    </div>

    <!-- Main Content -->
    <main>
      <div class="header">
        <h1 id="main-title">BMMS - Orders Report</h1>
      </div>

      <div class="report-box">
        <h2>Orders Report</h2>

        <?php
        $conn = new mysqli("localhost", "root", "", "bmmss");
        if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $where = "WHERE 1=1";
        if (!empty($startDate)) {
          $where .= " AND O.OrderDate >= '$startDate 00:00:00'";
        }
        if (!empty($endDate)) {
          $where .= " AND O.OrderDate <= '$endDate 23:59:59'";
        }

        $sql = "
          SELECT O.OrderID, C.CustomerName, I.ItemName, O.Quantity, O.UnitPrice, O.TotalAmount, O.Status, O.OrderDate
          FROM Orders O
          JOIN Customers C ON O.CustomerID = C.CustomerID
          JOIN Items I ON O.ItemID = I.ItemID
          $where
          ORDER BY O.OrderDate DESC
        ";
        $result = $conn->query($sql);
        ?>

        <form method="get" class="filter-form">
          <label>Start Date:
            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
          </label>
          <label>End Date:
            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
          </label>
          <button type="submit" class="btn-filter">🔍 Filter</button>
        </form>

        <table id="ordersTable" class="display nowrap inventory-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Item</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Total</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $row['OrderID'] ?></td>
                <td><?= $row['CustomerName'] ?></td>
                <td><?= $row['ItemName'] ?></td>
                <td><?= $row['Quantity'] ?></td>
                <td><?= $row['UnitPrice'] ?></td>
                <td><?= $row['TotalAmount'] ?></td>
                <td><?= $row['Status'] ?></td>
                <td><?= $row['OrderDate'] ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <!-- JS Libraries -->
  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

  <script>
    $(document).ready(function () {
      $('#ordersTable').DataTable({
        dom: 'Bflrtip',
       buttons: [
        { extend: 'copyHtml5', text: '<i class="fas fa-copy"></i> Copy' },
        { extend: 'csvHtml5', text: '<i class="fas fa-file-csv"></i> CSV' },
        { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel' },
        {
          extend: 'pdfHtml5',
          text: '<i class="fas fa-file-pdf"></i> PDF',
          orientation: 'landscape',
          pageSize: 'A4',
          exportOptions: {
            columns: ':visible'
          },
          customize: function (doc) {
            doc.content.splice(0, 0,
              {
                text: 'Kayd Building Material',
                fontSize: 20,
                bold: true,
                alignment: 'center',
                margin: [0, 0, 0, 5]
              },
              {
                text: 'Management System',
                fontSize: 20,
                bold: true,
                alignment: 'center',
                margin: [0, 0, 0, 15]
              }
            );

            var tableNode = doc.content.find(n => n.table);
            if (tableNode) {
              var colCount = tableNode.table.body[0].length;
              tableNode.table.widths = Array(colCount).fill('*');
            }

            doc.pageMargins = [20, 40, 20, 30];
          }
        },
        {
          extend: 'print',
          text: '<i class="fas fa-print"></i> Print',
          customize: function (win) {
            // Inject the company name above the table
            $(win.document.body).prepend(`
              <div style="text-align:center; margin-bottom:20px;">
                <h2 style="margin:0;">Kayd Building Material</h2>
                <h4 style="margin:0;">Management System</h4>
              </div>
            `);

            // Optional: shrink table font slightly for better fit
            $(win.document.body).find('table').css('font-size', '11px');
          }
        },
        { extend: 'colvis', text: '<i class="fas fa-columns"></i> Toggle Columns' }
      ],
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
      });
    });
  </script>
</body>
</html>
