<?php

/**
 * Financial Management Dashboard
 * Shows transaction history, balances, and financial reports
 */

require_once __DIR__ . '/../config/base_url.php';
require_once __DIR__ . '/../config/auth.php';

$auth = new Auth();
$auth->requireAuth();

$role = $auth->getUserRole();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Management - BMMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../base/styles.css">
    <link rel="stylesheet" href="financial.css">
</head>

<body>
    <div class="page-content">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main>
            <div class="header">
                <i class="fa-solid fa-bars bar-item"></i>
                <div class="search">
                    <input type="search" placeholder="Search transactions, customers, suppliers..." />
                    <i class="fa-solid fa-search"></i>
                </div>
                <div class="profile">
                    <span class="bell"><i class="fa-regular fa-bell"></i></span>
                </div>
            </div>

            <div class="main-content">
                <div class="title">
                    <h1><i class="fas fa-chart-line"></i> Financial Management</h1>
                </div>

                <!-- Period Filter -->
                <div class="period-filter">
                    <div class="filter-controls">
                        <label for="period-select">Period:</label>
                        <select id="period-select" onchange="handlePeriodChange()">
                            <option value="current-month">Current Month</option>
                            <option value="current-quarter">Current Quarter</option>
                            <option value="current-year">Current Year</option>
                            <option value="custom">Custom Range</option>
                        </select>

                        <div id="custom-date-range" class="custom-range" style="display: none;">
                            <label for="start-date">From:</label>
                            <input type="date" id="start-date" onchange="updatePeriodFilter()">
                            <label for="end-date">To:</label>
                            <input type="date" id="end-date" onchange="updatePeriodFilter()">
                        </div>
                    </div>
                </div>

                <!-- Financial Overview Cards -->
                <div class="financial-overview">
                    <div class="overview-card total-revenue">
                        <div class="card-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="card-content">
                            <h3>Total Revenue</h3>
                            <p class="amount" id="total-revenue">$0.00</p>
                            <span class="trend positive">+12.5% <i class="fas fa-arrow-up"></i></span>
                        </div>
                    </div>

                    <div class="overview-card total-cogs">
                        <div class="card-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="card-content">
                            <h3>Cost of Goods Sold</h3>
                            <p class="amount" id="total-cogs">$0.00</p>
                            <span class="trend neutral">COGS</span>
                        </div>
                    </div>

                    <div class="overview-card total-expenses">
                        <div class="card-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="card-content">
                            <h3>Other Expenses</h3>
                            <p class="amount" id="total-expenses">$0.00</p>
                            <span class="trend negative">+8.2% <i class="fas fa-arrow-up"></i></span>
                        </div>
                    </div>

                    <div class="overview-card net-profit">
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="card-content">
                            <h3>Net Profit</h3>
                            <p class="amount" id="net-profit">$0.00</p>
                            <span class="trend positive">+15.3% <i class="fas fa-arrow-up"></i></span>
                        </div>
                    </div>

                    <div class="overview-card pending-payments">
                        <div class="card-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="card-content">
                            <h3>Pending Payments</h3>
                            <p class="amount" id="pending-payments">$0.00</p>
                            <span class="trend neutral">5 transactions</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <button class="action-btn" onclick="showCustomerBalances()">
                        <i class="fas fa-users"></i>
                        Customer Balances
                    </button>
                    <button class="action-btn" onclick="showSupplierBalances()">
                        <i class="fas fa-truck"></i>
                        Supplier Balances
                    </button>
                    <button class="action-btn" onclick="showTransactionHistory()">
                        <i class="fas fa-history"></i>
                        Transaction History
                    </button>
                    <button class="action-btn" onclick="showFinancialReport()">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Financial Report
                    </button>
                </div>

                <!-- Main Content Area -->
                <div class="content-area">
                    <!-- Customer Balances Section -->
                    <div id="customer-balances" class="content-section">
                        <div class="section-header">
                            <h2><i class="fas fa-users"></i> Customer Balances</h2>
                            <div class="section-actions">
                                <input type="text" id="customer-search" placeholder="Search customers..." class="search-input">
                                <button class="btn btn-success" onclick="showCustomerPaymentModal()">
                                    <i class="fas fa-plus"></i> Record Customer Payment
                                </button>
                                <button class="btn btn-primary" onclick="exportCustomerBalances()">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                        <div class="table-container">
                            <table id="customer-balances-table" class="data-table">
                                <thead>
                                    <tr>
                                        <th>Customer ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Balance</th>
                                        <th>Last Transaction</th>
                                    </tr>
                                </thead>
                                <tbody id="customer-balances-body">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Supplier Balances Section -->
                    <div id="supplier-balances" class="content-section" style="display: none;">
                        <div class="section-header">
                            <h2><i class="fas fa-truck"></i> Supplier Balances</h2>
                            <div class="section-actions">
                                <input type="text" id="supplier-search" placeholder="Search suppliers..." class="search-input">
                                <button class="btn btn-success" onclick="showSupplierPaymentModal()">
                                    <i class="fas fa-plus"></i> Record Supplier Payment
                                </button>
                                <button class="btn btn-primary" onclick="exportSupplierBalances()">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                        <div class="table-container">
                            <table id="supplier-balances-table" class="data-table">
                                <thead>
                                    <tr>
                                        <th>Supplier ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Balance</th>
                                        <th>Last Transaction</th>
                                    </tr>
                                </thead>
                                <tbody id="supplier-balances-body">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Transaction History Section -->
                    <div id="transaction-history" class="content-section" style="display: none;">
                        <div class="section-header">
                            <h2><i class="fas fa-history"></i> Transaction History</h2>
                            <div class="section-actions">
                                <input type="text" id="transaction-search" placeholder="Search transactions..." class="search-input">
                                <select id="transaction-type-filter" class="filter-select">
                                    <option value="">All Types</option>
                                    <option value="sale">Sales</option>
                                    <option value="purchase">Purchases</option>
                                    <option value="payment">Payments</option>
                                    <option value="salary">Salaries</option>
                                </select>
                                <button class="btn btn-primary" onclick="exportTransactions()">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                        <div class="table-container">
                            <table id="transaction-history-table" class="data-table">
                                <thead>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Customer/Supplier</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="transaction-history-body">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Financial Report Section -->
                    <div id="financial-report" class="content-section" style="display: none;">
                        <div class="section-header">
                            <h2><i class="fas fa-file-invoice-dollar"></i> Financial Report</h2>
                            <div class="section-actions">
                                <button class="btn btn-primary" onclick="generateReport()">
                                    <i class="fas fa-chart-bar"></i> Generate
                                </button>
                                <button class="btn btn-secondary" onclick="exportReport()">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                        <div class="report-content">
                            <div class="report-summary">
                                <div class="summary-card">
                                    <h3>Revenue Summary</h3>
                                    <div id="revenue-summary"></div>
                                </div>
                                <div class="summary-card">
                                    <h3>Expense Summary</h3>
                                    <div id="expense-summary"></div>
                                </div>
                                <div class="summary-card">
                                    <h3>Profit Analysis</h3>
                                    <div id="profit-analysis"></div>
                                </div>
                            </div>
                            <div class="report-charts">
                                <div class="chart-container">
                                    <h3>Monthly Trends</h3>
                                    <canvas id="monthly-chart"></canvas>
                                </div>
                                <div class="chart-container">
                                    <h3>Transaction Distribution</h3>
                                    <canvas id="distribution-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Customer Payment Modal -->
    <div id="customer-payment-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-credit-card"></i> Record Customer Payment</h2>
                <span class="close" onclick="closeCustomerPaymentModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="customer-payment-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer-payment-customer">Customer *</label>
                            <select id="customer-payment-customer" required>
                                <option value="">Select Customer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="customer-payment-amount">Payment Amount ($) *</label>
                            <input type="number" id="customer-payment-amount" step="0.01" min="0.01" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer-payment-method">Payment Method *</label>
                            <select id="customer-payment-method" required>
                                <option value="">Select Method</option>
                                <option value="Cash">Cash</option>
                                <option value="Wallet">Wallet</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="customer-payment-date">Payment Date *</label>
                            <input type="date" id="customer-payment-date" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="customer-payment-description">Description</label>
                        <input type="text" id="customer-payment-description" placeholder="Payment description...">
                    </div>

                    <div class="form-group">
                        <label for="customer-payment-notes">Notes</label>
                        <textarea id="customer-payment-notes" rows="3" placeholder="Additional notes..."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeCustomerPaymentModal()">Cancel</button>
                        <button type="submit" class="btn btn-success">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Supplier Payment Modal -->
    <div id="supplier-payment-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-credit-card"></i> Record Supplier Payment</h2>
                <span class="close" onclick="closeSupplierPaymentModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="supplier-payment-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="supplier-payment-supplier">Supplier *</label>
                            <select id="supplier-payment-supplier" required>
                                <option value="">Select Supplier</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="supplier-payment-amount">Payment Amount ($) *</label>
                            <input type="number" id="supplier-payment-amount" step="0.01" min="0.01" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="supplier-payment-method">Payment Method *</label>
                            <select id="supplier-payment-method" required>
                                <option value="">Select Method</option>
                                <option value="Cash">Cash</option>
                                <option value="Wallet">Wallet</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="supplier-payment-date">Payment Date *</label>
                            <input type="date" id="supplier-payment-date" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="supplier-payment-description">Description</label>
                        <input type="text" id="supplier-payment-description" placeholder="Payment description...">
                    </div>

                    <div class="form-group">
                        <label for="supplier-payment-notes">Notes</label>
                        <textarea id="supplier-payment-notes" rows="3" placeholder="Additional notes..."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeSupplierPaymentModal()">Cancel</button>
                        <button type="submit" class="btn btn-success">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transaction Details Modal -->
    <div id="transaction-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Transaction Details</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body" id="transaction-details">
                <!-- Transaction details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- JavaScript Configuration -->
    <script src="<?= BASE_URL ?>config/js-config.php"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="financial.js"></script>
</body>

</html>