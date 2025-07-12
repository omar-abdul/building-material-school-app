<?php
/**
 * Common Layout File
 * Handles common HTML structure and sidebar navigation
 */

require_once __DIR__ . '/../config/auth.php';

$auth = new Auth();
$auth->startSession();

// Get current page for active navigation
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Define navigation items
$navItems = [
    'dashbood' => [
        'url' => '/backend/dashbood/dashbood.php',
        'icon' => 'fa-tachometer-alt',
        'text' => 'Dashboard',
        'admin_only' => false
    ],
    'categories' => [
        'url' => '/backend/Categories/index.php',
        'icon' => 'fa-tags',
        'text' => 'Categories',
        'admin_only' => true
    ],
    'suppliers' => [
        'url' => '/backend/Suppliers/index.php',
        'icon' => 'fa-file-invoice-dollar',
        'text' => 'Suppliers',
        'admin_only' => true
    ],
    'employees' => [
        'url' => '/backend/Employees/index.php',
        'icon' => 'fa-users',
        'text' => 'Employees',
        'admin_only' => true
    ],
    'customers' => [
        'url' => '/backend/Customers/index.php',
        'icon' => 'fa-exchange-alt',
        'text' => 'Customers',
        'admin_only' => true
    ],
    'items' => [
        'url' => '/backend/Items/index.php',
        'icon' => 'fa-boxes',
        'text' => 'Items',
        'admin_only' => false
    ],
    'inventory' => [
        'url' => '/backend/Inventory/index.php',
        'icon' => 'fa-user-tie',
        'text' => 'Inventory',
        'admin_only' => false
    ],
    'orders' => [
        'url' => '/backend/Orders/index.php',
        'icon' => 'fa-truck',
        'text' => 'Orders',
        'admin_only' => false
    ],
    'transactions' => [
        'url' => '/backend/Transactions/index.php',
        'icon' => 'fa-warehouse',
        'text' => 'Transactions',
        'admin_only' => true
    ],
    'salaries' => [
        'url' => '/backend/Salaries/index.php',
        'icon' => 'fa-money-bill-wave',
        'text' => 'Salaries',
        'admin_only' => true
    ],
    'signup' => [
        'url' => '/backend/signup/index.php',
        'icon' => 'fa-user-plus',
        'text' => 'Sign Up',
        'admin_only' => true
    ]
];

// Report dropdown items
$reportItems = [
    'inventory' => [
        'url' => '/backend/reports/inventory.php',
        'text' => 'Inventory Report'
    ],
    'items' => [
        'url' => '/backend/reports/items.php',
        'text' => 'Items Report'
    ],
    'orders' => [
        'url' => '/backend/reports/orders.php',
        'text' => 'Orders Report'
    ],
    'salaries' => [
        'url' => '/backend/reports/salaries.php',
        'text' => 'Salaries Report',
        'admin_only' => true
    ],
    'transactions' => [
        'url' => '/backend/reports/transactions.php',
        'text' => 'Transactions Report',
        'admin_only' => true
    ],
    'backup' => [
        'url' => '/backend/signup/backup.php',
        'text' => 'Backup',
        'admin_only' => true
    ]
];

/**
 * Render the sidebar navigation
 */
function renderSidebar($navItems, $reportItems, $auth, $currentDir) {
    $userRole = $auth->getUserRole();
    $isAdmin = $auth->isAdmin();
    
    echo '<div class="sidebar">';
    echo '<div class="brand">';
    echo '<i class="fas fa-building"></i>';
    echo '<span class="brand-name">BMMS</span>';
    echo '</div>';
    echo '<div class="sidebar-menu">';
    
    // Main navigation items
    foreach ($navItems as $key => $item) {
        if ($item['admin_only'] && !$isAdmin) {
            continue;
        }
        
        $isActive = ($currentDir === $key) ? 'active' : '';
        echo '<a href="' . $item['url'] . '" class="sidebar-link ' . $isActive . '">';
        echo '<i class="fas ' . $item['icon'] . '"></i>';
        echo '<span>' . $item['text'] . '</span>';
        echo '</a>';
    }
    
    // Reports dropdown
    echo '<nav class="sidebar">';
    echo '<ul>';
    echo '<li class="report-dropdown">';
    echo '<a href="#" class="sidebar-link sidebar-report-btn">';
    echo '<i class="fa-solid fa-chart-pie"></i>';
    echo '<span>Reports</span>';
    echo '<i class="fa-solid fa-angle-down dropdown-icon"></i>';
    echo '</a>';
    echo '<ul class="report-dropdown-content">';
    
    foreach ($reportItems as $key => $item) {
        if (isset($item['admin_only']) && $item['admin_only'] && !$isAdmin) {
            continue;
        }
        echo '<li><a href="' . $item['url'] . '">' . $item['text'] . '</a></li>';
    }
    
    echo '</ul>';
    echo '</li>';
    echo '</ul>';
    echo '</nav>';
    
    // Logout link
    echo '<a href="/backend/dashbood/logout.php" class="sidebar-link">';
    echo '<i class="fa-solid fa-right-from-bracket"></i>';
    echo '<span>Logout</span>';
    echo '</a>';
    
    echo '</div>';
    echo '</div>';
}

/**
 * Render the header section
 */
function renderHeader($pageTitle = '') {
    echo '<div class="header">';
    echo '<i class="fa-solid fa-bars bar-item"></i>';
    echo '<div class="search">';
    echo '<input type="search" placeholder="Search materials, suppliers..." />';
    echo '<i class="fa-solid fa-search"></i>';
    echo '</div>';
    echo '<div class="profile">';
    echo '<span class="bell"><i class="fa-regular fa-bell"></i></span>';
    echo '</div>';
    echo '</div>';
    
    if (!empty($pageTitle)) {
        echo '<div class="title">';
        echo '<h1>' . htmlspecialchars($pageTitle) . '</h1>';
        echo '</div>';
    }
}

/**
 * Start the HTML document
 */
function startDocument($title = 'BMMS', $additionalCss = []) {
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . htmlspecialchars($title) . '</title>';
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;500&display=swap" rel="stylesheet">';
    
    // Include additional CSS files
    foreach ($additionalCss as $css) {
        echo '<link rel="stylesheet" href="' . htmlspecialchars($css) . '">';
    }
    echo '</head>';
    echo '<body>';
}

/**
 * End the HTML document
 */
function endDocument($additionalJs = []) {
    // Include additional JavaScript files
    foreach ($additionalJs as $js) {
        echo '<script src="' . htmlspecialchars($js) . '"></script>';
    }
    echo '</body>';
    echo '</html>';
}

/**
 * Render the main container structure
 */
function renderMainContainer($pageTitle = '', $additionalCss = [], $additionalJs = []) {
    global $navItems, $reportItems, $auth, $currentDir;
    
    startDocument('BMMS - ' . $pageTitle, $additionalCss);
    
    echo '<div class="container">';
    
    // Render sidebar
    renderSidebar($navItems, $reportItems, $auth, $currentDir);
    
    echo '<div class="main-content">';
    
    // Render header
    renderHeader($pageTitle);
    
    echo '<div class="main-content-boxes">';
    
    endDocument($additionalJs);
}
?> 