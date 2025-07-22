<?php

/**
 * Dashboard Page
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
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        <main>
            <div class="header">
                <i class="fa-solid fa-bars bar-item"></i>
                <div class="search">
                    <input type="search" placeholder="Search materials, suppliers..." />
                    <i class="fa-solid fa-search"></i>
                </div>
                <div class="profile">
                    <span class="bell"><i class="fa-regular fa-bell"></i></span>
                    <!-- <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Profile" /> -->
                </div>
            </div>
            <div class="main-content">
                <div class="title">
                    <h1>Building Material Management System</h1>
                </div>

                <!-- Data Management Buttons -->
                <div class="data-management-buttons">
                    <?php if ($role === 'admin'): ?>
                        <a href="<?= BASE_URL ?>Categories/index.php" class="data-btn add-category">
                            <i class="fa-solid fa-tag"></i>
                            <span>Add Category</span>
                        </a>
                        <a href="<?= BASE_URL ?>Suppliers/index.php" class="data-btn add-supplier">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                            <span>Add Supplier</span>
                        </a>
                        <a href="<?= BASE_URL ?>Employees/index.php" class="data-btn view-orders">
                            <i class="fa-solid fa-cart-flatbed"></i>
                            <span>Add Employees</span>
                        </a>
                        <a href="<?= BASE_URL ?>Customers/index.php" class="data-btn view-transactions">
                            <i class="fa-solid fa-receipt"></i>
                            <span>Add Customers</span>
                        </a>
                        <a href="<?= BASE_URL ?>Salaries/index.php" class="data-btn add-salary">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <span>Add Salary</span>
                        </a>
                        <a href="<?= BASE_URL ?>Financial/index.php" class="data-btn add-customer">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Financial Management</span>
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>Items/index.php" class="data-btn add-item">
                        <i class="fa-solid fa-box-circle-plus"></i>
                        <span>Add Item</span>
                    </a>

                    <a href="<?= BASE_URL ?>Orders/index.php" class="data-btn add-employee">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Sales Orders</span>
                    </a>
                    <a href="<?= BASE_URL ?>PurchaseOrders/index.php" class="data-btn add-employee">
                        <i class="fa-solid fa-shopping-cart"></i>
                        <span>Purchase Orders</span>
                    </a>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Dashboard initialization code can go here
        });
    </script>

    <script src="dashboard.js"></script>
</body>

</html>