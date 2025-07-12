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
  <title>Items Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- CSS & Libraries -->
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

    <!-- Main -->
    <main>
      <div class="header">
        <h1 id="main-title">BMMS - Items Report</h1>
      </div>

      <div class="report-box">
        <h2>Items Report</h2>

        <?php
        $conn = new mysqli("localhost", "root", "", "bmmss");
        if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $where = "WHERE 1=1";
        if (!empty($startDate)) {
          $where .= " AND I.CreatedDate >= '$startDate 00:00:00'";
        }
        if (!empty($endDate)) {
          $where .= " AND I.CreatedDate <= '$endDate 23:59:59'";
        }

        $sql = "
          SELECT I.ItemID, I.ItemName, C.CategoryName, S.SupplierName, I.CreatedDate
          FROM Items I
          JOIN Categories C ON I.CategoryID = C.CategoryID
          JOIN Suppliers S ON I.SupplierID = S.SupplierID
          $where
          ORDER BY I.CreatedDate DESC
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

        <table id="itemsTable" class="display nowrap inventory-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Category</th>
              <th>Supplier</th>
              <th>Created Date</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $row['ItemID'] ?></td>
                <td><?= $row['ItemName'] ?></td>
                <td><?= $row['CategoryName'] ?></td>
                <td><?= $row['SupplierName'] ?></td>
                <td><?= $row['CreatedDate'] ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <!-- JS -->
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
      $('#itemsTable').DataTable({
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
          fontSize: 18,
          bold: true,
          alignment: 'center',
          margin: [0, 0, 0, 10]
        },
        {
          text: 'Management System',
          fontSize: 14,
          alignment: 'center',
          margin: [0, 0, 0, 10]
        }
      );

      //  Align header and content
      const table = doc.content.find(n => n.table);
      if (table) {
        // Equal width for all columns
        const columnCount = table.table.body[0].length;
        table.table.widths = Array(columnCount).fill('*');

        // Align right and pad
        table.table.body.forEach((row, i) => {
          row.forEach((cell, j) => {
            if (typeof cell === 'object') {
              cell.alignment = 'center';
              cell.margin = [2, 2, 2, 2];
            } else {
              row[j] = {
                text: cell,
                alignment: 'center',
                margin: [2, 2, 2, 2]
              };
            }
            if (i === 0) row[j].bold = true;
          });
        });
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
