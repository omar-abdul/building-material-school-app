<?php

/**
 * Cash/Wallet Management
 * Handles cash and wallet balance tracking and expense payments
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
    <title>Cash/Wallet Management - BMMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../base/styles.css">
    <link rel="stylesheet" href="cash.css">
</head>

<body>
    <div class="page-content">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main>
            <div class="header">
                <i class="fa-solid fa-bars bar-item"></i>
                <div class="search">
                    <input type="search" placeholder="Search transactions..." />
                    <i class="fa-solid fa-search"></i>
                </div>
                <div class="profile">
                    <span class="bell"><i class="fa-regular fa-bell"></i></span>
                </div>
            </div>

            <div class="main-content">
                <div class="title">
                    <h1><i class="fas fa-wallet"></i> Cash/Wallet Management</h1>
                </div>

                <!-- Balance Overview Cards -->
                <div class="balance-overview">
                    <div class="balance-card cash-balance">
                        <div class="card-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="card-content">
                            <h3>Cash Balance</h3>
                            <p class="amount" id="cash-balance">$0.00</p>
                            <span class="trend">Physical Cash</span>
                        </div>
                    </div>

                    <div class="balance-card wallet-balance">
                        <div class="card-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="card-content">
                            <h3>Wallet Balance</h3>
                            <p class="amount" id="wallet-balance">$0.00</p>
                            <span class="trend">Digital Wallet</span>
                        </div>
                    </div>

                    <div class="balance-card total-balance">
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="card-content">
                            <h3>Total Balance</h3>
                            <p class="amount" id="total-balance">$0.00</p>
                            <span class="trend">Combined</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <button class="action-btn" onclick="showExpenseModal()">
                        <i class="fas fa-plus"></i>
                        Record Expense
                    </button>
                    <button class="action-btn" onclick="showTransactionHistory()">
                        <i class="fas fa-history"></i>
                        Transaction History
                    </button>
                    <button class="action-btn" onclick="exportTransactions()">
                        <i class="fas fa-download"></i>
                        Export Data
                    </button>
                </div>

                <!-- Transaction History Section -->
                <div id="transaction-history" class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-history"></i> Transaction History</h2>
                        <div class="section-actions">
                            <input type="text" id="transaction-search" placeholder="Search transactions..." class="search-input">
                            <select id="transaction-type-filter" class="filter-select">
                                <option value="">All Types</option>
                                <option value="expense">Expenses</option>
                                <option value="income">Income</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-container">
                        <table id="transaction-history-table" class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Balance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="transaction-history-body">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Expense Payment Modal -->
    <div id="expense-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-minus-circle"></i> Record Expense</h2>
                <span class="close" onclick="closeExpenseModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="expense-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expense-amount">Amount ($) *</label>
                            <input type="number" id="expense-amount" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="expense-payment-method">Payment Method *</label>
                            <select id="expense-payment-method" required>
                                <option value="">Select Method</option>
                                <option value="Cash">Cash</option>
                                <option value="Wallet">Wallet</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="expense-date">Date *</label>
                            <input type="date" id="expense-date" required>
                        </div>
                        <div class="form-group">
                            <label for="expense-category">Category</label>
                            <select id="expense-category">
                                <option value="">Select Category</option>
                                <option value="Office Supplies">Office Supplies</option>
                                <option value="Utilities">Utilities</option>
                                <option value="Rent">Rent</option>
                                <option value="Transportation">Transportation</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="expense-description">Description *</label>
                        <input type="text" id="expense-description" placeholder="Expense description..." required>
                    </div>

                    <div class="form-group">
                        <label for="expense-notes">Notes</label>
                        <textarea id="expense-notes" rows="3" placeholder="Additional notes..."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeExpenseModal()">Cancel</button>
                        <button type="submit" class="btn btn-success">Record Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transaction Details Modal -->
    <div id="transaction-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-info-circle"></i> Transaction Details</h2>
                <span class="close" onclick="closeTransactionModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="transaction-details">
                    <!-- Transaction details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Configuration -->
    <script src="<?= BASE_URL ?>config/js-config.php"></script>
    <script src="cash.js"></script>
</body>

</html>