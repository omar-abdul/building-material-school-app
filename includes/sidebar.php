<?php

/**
 * Centralized Sidebar Component
 * This file contains the sidebar navigation that is used across all pages
 */

// Ensure auth is available
if (!isset($auth)) {
    require_once __DIR__ . '/../config/auth.php';
    $auth = new Auth();
    $auth->requireAuth();
}

$role = $auth->getUserRole(); // 'admin' or 'user'
$isAdmin = $role === 'admin';

// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Function to check if a link should be active
function isActive($pageName)
{
    global $currentPage, $currentDir;
    return ($currentPage === $pageName || $currentDir === $pageName) ? 'active' : '';
}
?>

<div class="sidebar">
    <div class="brand">
        <i class="fas fa-building"></i>
        <span class="brand-name">BMMS</span>
    </div>
    <div class="sidebar-menu">
        <a href="/backend/dashboard/dashboard.php" class="sidebar-link <?= isActive('dashboard') ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>

        <?php if ($isAdmin): ?>
            <a href="/backend/Categories/index.php" class="sidebar-link <?= isActive('Categories') ?>">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </a>
            <a href="/backend/Suppliers/index.php" class="sidebar-link <?= isActive('Suppliers') ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Suppliers</span>
            </a>
            <a href="/backend/Employees/index.php" class="sidebar-link <?= isActive('Employees') ?>">
                <i class="fas fa-users"></i>
                <span>Employees</span>
            </a>
            <a href="/backend/Customers/index.php" class="sidebar-link <?= isActive('Customers') ?>">
                <i class="fas fa-exchange-alt"></i>
                <span>Customers</span>
            </a>
        <?php endif; ?>

        <a href="/backend/Items/index.php" class="sidebar-link <?= isActive('Items') ?>">
            <i class="fas fa-boxes"></i>
            <span>Items</span>
        </a>
        <a href="/backend/Orders/index.php" class="sidebar-link <?= isActive('Orders') ?>">
            <i class="fas fa-truck"></i>
            <span>Sales Orders</span>
        </a>
        <a href="/backend/PurchaseOrders/index.php" class="sidebar-link <?= isActive('PurchaseOrders') ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Purchase Orders</span>
        </a>

        <?php if ($isAdmin): ?>
            <a href="/backend/Transactions/index.php" class="sidebar-link <?= isActive('Transactions') ?>">
                <i class="fas fa-warehouse"></i>
                <span>Transactions</span>
            </a>
            <a href="/backend/Salaries/index.php" class="sidebar-link <?= isActive('Salaries') ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Salaries</span>
            </a>
            <a href="/backend/signup/index.php" class="sidebar-link <?= isActive('signup') ?>">
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
                        <?php if ($isAdmin): ?>
                            <li><a href="/backend/reports/salaries.php">Salaries Report</a></li>
                            <li><a href="/backend/reports/transactions.php">Transactions Report</a></li>
                            <li><a href="/backend/signup/backup.php">Backup</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </nav>

        <a href="/backend/dashboard/logout.php" class="sidebar-link">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
</div>