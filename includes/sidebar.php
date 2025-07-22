<?php

/**
 * Centralized Sidebar Component
 * This file contains the sidebar navigation that is used across all pages
 */

// Ensure auth is available
if (!isset($auth)) {
    require_once __DIR__ . '/../config/base_url.php';
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
        <a href="<?= BASE_URL ?>dashboard/index.php" class="sidebar-link <?= isActive('dashboard') ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>

        <?php if ($isAdmin): ?>
            <a href="<?= BASE_URL ?>Categories/index.php" class="sidebar-link <?= isActive('Categories') ?>">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </a>
            <a href="<?= BASE_URL ?>Suppliers/index.php" class="sidebar-link <?= isActive('Suppliers') ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Suppliers</span>
            </a>
            <a href="<?= BASE_URL ?>Employees/index.php" class="sidebar-link <?= isActive('Employees') ?>">
                <i class="fas fa-users"></i>
                <span>Employees</span>
            </a>
            <a href="<?= BASE_URL ?>Customers/index.php" class="sidebar-link <?= isActive('Customers') ?>">
                <i class="fas fa-exchange-alt"></i>
                <span>Customers</span>
            </a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>Items/index.php" class="sidebar-link <?= isActive('Items') ?>">
            <i class="fas fa-boxes"></i>
            <span>Items</span>
        </a>
        <a href="<?= BASE_URL ?>Orders/index.php" class="sidebar-link <?= isActive('Orders') ?>">
            <i class="fas fa-truck"></i>
            <span>Sales Orders</span>
        </a>
        <a href="<?= BASE_URL ?>PurchaseOrders/index.php" class="sidebar-link <?= isActive('PurchaseOrders') ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Purchase Orders</span>
        </a>

        <?php if ($isAdmin): ?>
            <a href="<?= BASE_URL ?>Salaries/index.php" class="sidebar-link <?= isActive('Salaries') ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Salaries</span>
            </a>
            <a href="<?= BASE_URL ?>Financial/index.php" class="sidebar-link <?= isActive('Financial') ?>">
                <i class="fas fa-chart-line"></i>
                <span>Financial Management</span>
            </a>
            <a href="<?= BASE_URL ?>Users/index.php" class="sidebar-link <?= isActive('Users') ?>">
                <i class="fas fa-user-plus"></i>
                <span>Users Management</span>
            </a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>Logout/" class="sidebar-link">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
</div>