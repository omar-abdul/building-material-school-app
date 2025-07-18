/**
 * Financial Dashboard JavaScript
 * Handles all financial dashboard functionality
 */

// Global variables
let currentSection = 'customer-balances';
const charts = {};

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initializeDashboard();
    setupEventListeners();
    loadFinancialOverview();
    showCustomerBalances(); // Default view
});

/**
 * Initialize dashboard components
 */
function initializeDashboard() {
    console.log('Initializing Financial Dashboard...');
    
    // Set up modal functionality
    setupModal();
    
    // Initialize search functionality
    setupSearch();
    
    // Set up filters
    setupFilters();
}

/**
 * Set up event listeners
 */
function setupEventListeners() {
    // Quick action buttons
    for (const btn of document.querySelectorAll('.action-btn')) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            if (action && typeof window[action] === 'function') {
                window[action].call(this, e);
            }
        });
    }

    // Search inputs
    document.getElementById('customer-search')?.addEventListener('input', debounce(filterCustomerBalances, 300));
    document.getElementById('supplier-search')?.addEventListener('input', debounce(filterSupplierBalances, 300));
    document.getElementById('transaction-search')?.addEventListener('input', debounce(filterTransactions, 300));
    
    // Filter selects
    document.getElementById('transaction-type-filter')?.addEventListener('change', filterTransactions);
    document.getElementById('report-period')?.addEventListener('change', handleReportPeriodChange);
}

/**
 * Load financial overview data
 */
async function loadFinancialOverview() {
    try {
        const response = await fetch('/backend/api/financial/overview.php');
        const data = await response.json();
        
        if (data.success) {
            updateOverviewCards(data.data);
        } else {
            console.error('Failed to load financial overview:', data.message);
        }
    } catch (error) {
        console.error('Error loading financial overview:', error);
        // Set default values
        updateOverviewCards({
            total_revenue: 0,
            total_expenses: 0,
            net_profit: 0,
            pending_payments: 0
        });
    }
}

/**
 * Update overview cards with data
 */
function updateOverviewCards(data) {
    document.getElementById('total-revenue').textContent = formatCurrency(data.total_revenue || 0);
    document.getElementById('total-expenses').textContent = formatCurrency(data.total_expenses || 0);
    document.getElementById('net-profit').textContent = formatCurrency(data.net_profit || 0);
    document.getElementById('pending-payments').textContent = formatCurrency(data.pending_payments || 0);
}

/**
 * Show customer balances section
 */
async function showCustomerBalances() {
    hideAllSections();
    currentSection = 'customer-balances';
    document.getElementById('customer-balances').style.display = 'block';
    
    const tbody = document.getElementById('customer-balances-body');
    tbody.innerHTML = '<tr><td colspan="6" class="loading">Loading customer balances...</td></tr>';
    
    try {
        const response = await fetch('/backend/api/financial/customer-balances.php');
        const data = await response.json();
        
        if (data.success) {
            renderCustomerBalances(data.data);
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No customer balances found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading customer balances:', error);
        tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Error loading customer balances</td></tr>';
    }
}

/**
 * Show supplier balances section
 */
async function showSupplierBalances() {
    hideAllSections();
    currentSection = 'supplier-balances';
    document.getElementById('supplier-balances').style.display = 'block';
    
    const tbody = document.getElementById('supplier-balances-body');
    tbody.innerHTML = '<tr><td colspan="6" class="loading">Loading supplier balances...</td></tr>';
    
    try {
        const response = await fetch('/backend/api/financial/supplier-balances.php');
        const data = await response.json();
        
        if (data.success) {
            renderSupplierBalances(data.data);
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No supplier balances found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading supplier balances:', error);
        tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Error loading supplier balances</td></tr>';
    }
}

/**
 * Show transaction history section
 */
async function showTransactionHistory() {
    hideAllSections();
    currentSection = 'transaction-history';
    document.getElementById('transaction-history').style.display = 'block';
    
    const tbody = document.getElementById('transaction-history-body');
    tbody.innerHTML = '<tr><td colspan="8" class="loading">Loading transaction history...</td></tr>';
    
    try {
        const response = await fetch('/backend/api/financial/transactions.php');
        const data = await response.json();
        
        if (data.success) {
            renderTransactionHistory(data.data);
        } else {
            tbody.innerHTML = '<tr><td colspan="8" class="empty-state">No transactions found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading transaction history:', error);
        tbody.innerHTML = '<tr><td colspan="8" class="empty-state">Error loading transaction history</td></tr>';
    }
}

/**
 * Show financial report section
 */
function showFinancialReport() {
    hideAllSections();
    currentSection = 'financial-report';
    document.getElementById('financial-report').style.display = 'block';
    
    generateReport();
}

/**
 * Hide all content sections
 */
function hideAllSections() {
    const sections = ['customer-balances', 'supplier-balances', 'transaction-history', 'financial-report'];
    for (const section of sections) {
        document.getElementById(section).style.display = 'none';
    }
}

/**
 * Render customer balances table
 */
function renderCustomerBalances(customers) {
    const tbody = document.getElementById('customer-balances-body');
    
    if (!customers || customers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No customer balances found</td></tr>';
        return;
    }
    
    tbody.innerHTML = customers.map(customer => `
        <tr>
            <td>${customer.customer_id}</td>
            <td>${escapeHtml(customer.name)}</td>
            <td>${escapeHtml(customer.email)}</td>
            <td>${escapeHtml(customer.phone)}</td>
            <td>
                <span class="balance-amount ${getBalanceClass(customer.balance)}">
                    ${formatCurrency(customer.balance)}
                </span>
            </td>
            <td>${formatDate(customer.last_transaction_date)}</td>
        </tr>
    `).join('');
}

/**
 * Render supplier balances table
 */
function renderSupplierBalances(suppliers) {
    const tbody = document.getElementById('supplier-balances-body');
    
    if (!suppliers || suppliers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No supplier balances found</td></tr>';
        return;
    }
    
    tbody.innerHTML = suppliers.map(supplier => `
        <tr>
            <td>${supplier.supplier_id}</td>
            <td>${escapeHtml(supplier.name)}</td>
            <td>${escapeHtml(supplier.email)}</td>
            <td>${escapeHtml(supplier.phone)}</td>
            <td>
                <span class="balance-amount ${getBalanceClass(supplier.balance)}">
                    ${formatCurrency(supplier.balance)}
                </span>
            </td>
            <td>${formatDate(supplier.last_transaction_date)}</td>
        </tr>
    `).join('');
}

/**
 * Render transaction history table
 */
function renderTransactionHistory(transactions) {
    const tbody = document.getElementById('transaction-history-body');
    
    if (!transactions || transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="empty-state">No transactions found</td></tr>';
        return;
    }
    
    tbody.innerHTML = transactions.map(transaction => `
        <tr>
            <td>${transaction.transaction_id}</td>
            <td>${formatDate(transaction.transaction_date)}</td>
            <td>
                <span class="status-badge ${getTransactionTypeClass(transaction.transaction_type)}">
                    ${transaction.transaction_type}
                </span>
            </td>
            <td>${escapeHtml(transaction.description)}</td>
            <td>
                <span class="balance-amount ${getAmountClass(transaction.amount)}">
                    ${formatCurrency(transaction.amount)}
                </span>
            </td>
            <td>${escapeHtml(transaction.customer_supplier_name || 'N/A')}</td>
            <td>
                <span class="status-badge ${getStatusClass(transaction.status)}">
                    ${transaction.status}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-primary" onclick="viewTransactionDetails(${transaction.transaction_id})">
                        <i class="fas fa-eye"></i> Details
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

/**
 * Generate financial report
 */
async function generateReport() {
    const period = document.getElementById('report-period').value;
    console.log('Generating report for period:', period);
    
    // Show loading state
    document.getElementById('revenue-summary').innerHTML = '<div class="loading">Loading...</div>';
    document.getElementById('expense-summary').innerHTML = '<div class="loading">Loading...</div>';
    document.getElementById('profit-analysis').innerHTML = '<div class="loading">Loading...</div>';
    
    try {
        const response = await fetch(`/backend/api/financial/report.php?period=${period}`);
        console.log('Report API response:', response);
        
        const data = await response.json();
        console.log('Report API data:', data);
        
        if (data.success) {
            console.log('Report data received successfully:', data.data);
            renderFinancialReport(data.data);
        } else {
            console.error('Failed to generate report:', data.message);
            document.getElementById('revenue-summary').innerHTML = '<div class="error">Failed to load report</div>';
            document.getElementById('expense-summary').innerHTML = '<div class="error">Failed to load report</div>';
            document.getElementById('profit-analysis').innerHTML = '<div class="error">Failed to load report</div>';
        }
    } catch (error) {
        console.error('Error generating report:', error);
        document.getElementById('revenue-summary').innerHTML = '<div class="error">Error loading report</div>';
        document.getElementById('expense-summary').innerHTML = '<div class="error">Error loading report</div>';
        document.getElementById('profit-analysis').innerHTML = '<div class="error">Error loading report</div>';
    }
}

/**
 * Render financial report
 */
function renderFinancialReport(data) {
    // Revenue summary
    document.getElementById('revenue-summary').innerHTML = `
        <div class="summary-item">
            <span class="label">Total Revenue:</span>
            <span class="value positive">${formatCurrency(data.revenue.total)}</span>
        </div>
        <div class="summary-item">
            <span class="label">Sales Orders:</span>
            <span class="value">${data.revenue.sales_count}</span>
        </div>
        <div class="summary-item">
            <span class="label">Average Order:</span>
            <span class="value">${formatCurrency(data.revenue.average_order)}</span>
        </div>
    `;
    
    // Expense summary
    document.getElementById('expense-summary').innerHTML = `
        <div class="summary-item">
            <span class="label">Total Expenses:</span>
            <span class="value negative">${formatCurrency(data.expenses.total)}</span>
        </div>
        <div class="summary-item">
            <span class="label">Purchase Orders:</span>
            <span class="value">${data.expenses.purchase_count}</span>
        </div>
        <div class="summary-item">
            <span class="label">Salaries:</span>
            <span class="value">${formatCurrency(data.expenses.salaries)}</span>
        </div>
    `;
    
    // Profit analysis
    document.getElementById('profit-analysis').innerHTML = `
        <div class="summary-item">
            <span class="label">Net Profit:</span>
            <span class="value ${data.profit.net >= 0 ? 'positive' : 'negative'}">${formatCurrency(data.profit.net)}</span>
        </div>
        <div class="summary-item">
            <span class="label">Profit Margin:</span>
            <span class="value">${data.profit.margin}%</span>
        </div>
        <div class="summary-item">
            <span class="label">Growth Rate:</span>
            <span class="value ${data.profit.growth >= 0 ? 'positive' : 'negative'}">${data.profit.growth}%</span>
        </div>
    `;
    
    // Generate charts
    generateCharts(data);
}

/**
 * Generate charts for financial report
 */
function generateCharts(data) {
    console.log('Generating charts with data:', data);
    
    // Monthly trends chart
    const monthlyCtx = document.getElementById('monthly-chart')?.getContext('2d');
    console.log('Monthly chart canvas:', document.getElementById('monthly-chart'));
    console.log('Monthly chart context:', monthlyCtx);
    
    if (monthlyCtx && data.monthly_trends) {
        console.log('Monthly trends data:', data.monthly_trends);
        
        if (window.charts?.monthly) {
            window.charts.monthly.destroy();
        }
        
        if (!window.charts) window.charts = {};
        
        window.charts.monthly = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: data.monthly_trends.labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: data.monthly_trends.revenue,
                        borderColor: '#4facfe',
                        backgroundColor: 'rgba(79, 172, 254, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Expenses',
                        data: data.monthly_trends.expenses,
                        borderColor: '#fa709a',
                        backgroundColor: 'rgba(250, 112, 154, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        console.log('Monthly chart created successfully');
    } else {
        console.log('Cannot create monthly chart - missing context or data');
    }
    
    // Transaction distribution chart
    const distributionCtx = document.getElementById('distribution-chart')?.getContext('2d');
    console.log('Distribution chart canvas:', document.getElementById('distribution-chart'));
    console.log('Distribution chart context:', distributionCtx);
    
    if (distributionCtx && data.transaction_distribution) {
        console.log('Transaction distribution data:', data.transaction_distribution);
        
        if (window.charts?.distribution) {
            window.charts.distribution.destroy();
        }
        
        if (!window.charts) window.charts = {};
        
        window.charts.distribution = new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: data.transaction_distribution.labels,
                datasets: [{
                    data: data.transaction_distribution.counts,
                    backgroundColor: [
                        '#4facfe',
                        '#fa709a',
                        '#a8edea',
                        '#ffecd2',
                        '#667eea',
                        '#764ba2'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
        console.log('Distribution chart created successfully');
    } else {
        console.log('Cannot create distribution chart - missing context or data');
    }
}

/**
 * View transaction details
 */
async function viewTransactionDetails(transactionId) {
    console.log('Viewing transaction details for ID:', transactionId);
    try {
        const response = await fetch(`/backend/api/financial/transaction-details.php?id=${transactionId}`);
        const data = await response.json();
        
        console.log('Transaction details response:', data);
        
        if (data.success) {
            showTransactionModal(data.data);
        } else {
            console.error('Failed to load transaction details:', data.message);
            alert('Failed to load transaction details: ' + data.message);
        }
    } catch (error) {
        console.error('Error loading transaction details:', error);
        alert('Error loading transaction details');
    }
}

/**
 * Show transaction details modal
 */
function showTransactionModal(transaction) {
    console.log('Showing transaction modal with data:', transaction);
    
    const modal = document.getElementById('transaction-modal');
    const details = document.getElementById('transaction-details');
    
    console.log('Modal element:', modal);
    console.log('Details element:', details);
    
    if (!modal || !details) {
        console.error('Modal or details element not found');
        alert('Error: Modal elements not found');
        return;
    }
    
    details.innerHTML = `
        <div class="transaction-detail">
            <div class="detail-item">
                <div class="detail-label">Transaction ID</div>
                <div class="detail-value">${transaction.transaction_id}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Date</div>
                <div class="detail-value">${formatDate(transaction.transaction_date)}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Type</div>
                <div class="detail-value">
                    <span class="status-badge ${getTransactionTypeClass(transaction.transaction_type)}">
                        ${transaction.transaction_type}
                    </span>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Amount</div>
                <div class="detail-value">
                    <span class="balance-amount ${getAmountClass(transaction.amount)}">
                        ${formatCurrency(transaction.amount)}
                    </span>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Description</div>
                <div class="detail-value">${escapeHtml(transaction.description)}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                    <span class="status-badge ${getStatusClass(transaction.status)}">
                        ${transaction.status}
                    </span>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Customer/Supplier</div>
                <div class="detail-value">${escapeHtml(transaction.customer_supplier_name || 'N/A')}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Reference</div>
                <div class="detail-value">${escapeHtml(transaction.reference || 'N/A')}</div>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
    console.log('Modal displayed successfully');
}

/**
 * Setup modal functionality
 */
function setupModal() {
    const modal = document.getElementById('transaction-modal');
    const closeBtn = modal.querySelector('.close');
    
    closeBtn.onclick = () => {
        modal.style.display = 'none';
    }
    
    window.onclick = (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
}

/**
 * Setup search functionality
 */
function setupSearch() {
    // Search functionality is handled by individual filter functions
}

/**
 * Setup filters
 */
function setupFilters() {
    // Filter functionality is handled by individual filter functions
}

/**
 * Filter customer balances
 */
function filterCustomerBalances() {
    const searchTerm = document.getElementById('customer-search').value.toLowerCase();
    const rows = document.querySelectorAll('#customer-balances-table tbody tr');
    
    for (const row of rows) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    }
}

function filterSupplierBalances() {
    const searchTerm = document.getElementById('supplier-search').value.toLowerCase();
    const rows = document.querySelectorAll('#supplier-balances-table tbody tr');
    
    for (const row of rows) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    }
}

function filterTransactions() {
    const searchTerm = document.getElementById('transaction-search').value.toLowerCase();
    const typeFilter = document.getElementById('transaction-type-filter').value;
    const rows = document.querySelectorAll('#transaction-history-table tbody tr');
    
    for (const row of rows) {
        const text = row.textContent.toLowerCase();
        const type = row.querySelector('td:nth-child(3)').textContent.toLowerCase();

        const matchesSearch = text.includes(searchTerm);
        const matchesType = !typeFilter || type.includes(typeFilter);

        row.style.display = (matchesSearch && matchesType) ? '' : 'none';
    }
}

/**
 * Handle report period change
 */
function handleReportPeriodChange() {
    if (currentSection === 'financial-report') {
        generateReport();
    }
}

/**
 * Export functions
 */
function exportCustomerBalances() {
    exportTableToCSV('customer-balances-table', 'customer-balances.csv');
}

function exportSupplierBalances() {
    exportTableToCSV('supplier-balances-table', 'supplier-balances.csv');
}

function exportTransactions() {
    exportTableToCSV('transaction-history-table', 'transactions.csv');
}

function exportReport() {
    // Implementation for exporting financial report
    alert('Export functionality will be implemented');
}

/**
 * Export table to CSV
 */
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        
        for (let j = 0; j < cols.length - 1; j++) { // Skip last column (actions)
            let text = cols[j].textContent || cols[j].innerText;
            text = text.replace(/"/g, '""');
            rowData.push(`"${text}"`);
        }
        
        csv.push(rowData.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

/**
 * Utility functions
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0);
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getBalanceClass(balance) {
    if (balance > 0) return 'balance-positive';
    if (balance < 0) return 'balance-negative';
    return 'balance-zero';
}

function getAmountClass(amount) {
    if (amount > 0) return 'balance-positive';
    if (amount < 0) return 'balance-negative';
    return 'balance-zero';
}

function getTransactionTypeClass(type) {
    const typeMap = {
        'sale': 'completed',
        'purchase': 'pending',
        'payment': 'completed',
        'salary': 'pending'
    };
    return typeMap[type] || 'neutral';
}

function getStatusClass(status) {
    const statusMap = {
        'completed': 'completed',
        'pending': 'pending',
        'cancelled': 'cancelled'
    };
    return statusMap[status] || 'neutral';
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Additional functions for customer/supplier transaction views
async function viewCustomerTransactions(customerId) {
    try {
        const response = await fetch(`../api/financial/financial.php?action=getCustomerTransactions&customerId=${customerId}`);
        const data = await response.json();
        
        if (data.success) {
            showTransactionModal({
                title: `Customer Transactions - ${data.customerName || 'Customer'}`,
                transactions: data.data,
                type: 'customer'
            });
        } else {
            alert('Failed to load customer transactions');
        }
    } catch (error) {
        console.error('Error loading customer transactions:', error);
        alert('Failed to load customer transactions');
    }
}

async function viewSupplierTransactions(supplierId) {
    try {
        const response = await fetch(`../api/financial/financial.php?action=getSupplierTransactions&supplierId=${supplierId}`);
        const data = await response.json();
        
        if (data.success) {
            showTransactionModal({
                title: `Supplier Transactions - ${data.supplierName || 'Supplier'}`,
                transactions: data.data,
                type: 'supplier'
            });
        } else {
            alert('Failed to load supplier transactions');
        }
    } catch (error) {
        console.error('Error loading supplier transactions:', error);
        alert('Failed to load supplier transactions');
    }
}

/**
 * Payment Recording Functions
 */

// Customer Payment Functions
function showCustomerPaymentModal() {
    const modal = document.getElementById('customer-payment-modal');
    modal.style.display = 'block';
    loadCustomersForPayment();
    setDefaultPaymentDate('customer-payment-date');
}

function closeCustomerPaymentModal() {
    const modal = document.getElementById('customer-payment-modal');
    modal.style.display = 'none';
    document.getElementById('customer-payment-form').reset();
}

async function loadCustomersForPayment() {
    try {
        const response = await fetch('../api/customers/customers.php?action=getCustomers');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('customer-payment-customer');
            select.innerHTML = '<option value="">Select Customer</option>';
            
            for (const customer of data.data) {
                const option = document.createElement('option');
                // Handle both CustomerID and id formats
                option.value = customer.CustomerID || customer.id;
                option.textContent = `${customer.CustomerName || customer.Name} (${customer.Email})`;
                select.appendChild(option);
            }
        }
    } catch (error) {
        console.error('Error loading customers:', error);
        alert('Failed to load customers');
    }
}

// Supplier Payment Functions
function showSupplierPaymentModal() {
    const modal = document.getElementById('supplier-payment-modal');
    modal.style.display = 'block';
    loadSuppliersForPayment();
    setDefaultPaymentDate('supplier-payment-date');
}

function closeSupplierPaymentModal() {
    const modal = document.getElementById('supplier-payment-modal');
    modal.style.display = 'none';
    document.getElementById('supplier-payment-form').reset();
}

async function loadSuppliersForPayment() {
    try {
        const response = await fetch('../api/suppliers/suppliers.php?action=getSuppliers');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('supplier-payment-supplier');
            select.innerHTML = '<option value="">Select Supplier</option>';
            
            for (const supplier of data.data) {
                const option = document.createElement('option');
                // Use supplierId field from API response
                option.value = supplier.supplierId || supplier.SupplierID || supplier.id;
                option.textContent = `${supplier.name || supplier.SupplierName} (${supplier.email || supplier.Email})`;
                select.appendChild(option);
            }
        }
    } catch (error) {
        console.error('Error loading suppliers:', error);
        alert('Failed to load suppliers');
    }
}

// Utility function to set default payment date
function setDefaultPaymentDate(dateInputId) {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById(dateInputId).value = today;
}

// Form submission handlers
document.addEventListener('DOMContentLoaded', () => {
    // Customer payment form submission
    document.getElementById('customer-payment-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        await recordCustomerPayment();
    });
    
    // Supplier payment form submission
    document.getElementById('supplier-payment-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        await recordSupplierPayment();
    });
});

async function recordCustomerPayment() {
    const customerIdValue = document.getElementById('customer-payment-customer').value;
    // Extract numeric ID from 'CUST-X' format
    const customerId = customerIdValue.replace('CUST-', '');
    
    const formData = {
        transactionType: 'SALES_PAYMENT',
        referenceId: `PAY-${Date.now()}`,
        referenceType: 'payment',
        amount: -Number.parseFloat(document.getElementById('customer-payment-amount').value), // Negative to reduce receivable
        paymentMethod: document.getElementById('customer-payment-method').value,
        status: 'Completed',
        transactionDate: document.getElementById('customer-payment-date').value,
        customerId: Number.parseInt(customerId), // Convert to integer
        description: document.getElementById('customer-payment-description').value || 'Customer Payment',
        notes: document.getElementById('customer-payment-notes').value
    };
    
    try {
        const response = await fetch('../api/financial/financial.php?action=addTransaction', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Customer payment recorded successfully!');
            closeCustomerPaymentModal();
            // Refresh customer balances
            if (currentSection === 'customer-balances') {
                showCustomerBalances();
            }
        } else {
            alert(`Failed to record payment: ${data.message}`);
        }
    } catch (error) {
        console.error('Error recording customer payment:', error);
        alert('Failed to record payment');
    }
}

async function recordSupplierPayment() {
    const supplierIdValue = document.getElementById('supplier-payment-supplier').value;
    // Extract numeric ID from 'SUP-X' format
    const supplierId = supplierIdValue.replace('SUP-', '');
    
    const formData = {
        transactionType: 'PURCHASE_PAYMENT',
        referenceId: `PAY-${Date.now()}`,
        referenceType: 'payment',
        amount: Number.parseFloat(document.getElementById('supplier-payment-amount').value), // Positive to reduce payable
        paymentMethod: document.getElementById('supplier-payment-method').value,
        status: 'Completed',
        transactionDate: document.getElementById('supplier-payment-date').value,
        supplierId: Number.parseInt(supplierId), // Convert to integer
        description: document.getElementById('supplier-payment-description').value || 'Supplier Payment',
        notes: document.getElementById('supplier-payment-notes').value
    };
    
    try {
        const response = await fetch('../api/financial/financial.php?action=addTransaction', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Supplier payment recorded successfully!');
            closeSupplierPaymentModal();
            // Refresh supplier balances
            if (currentSection === 'supplier-balances') {
                showSupplierBalances();
            }
        } else {
            alert(`Failed to record payment: ${data.message}`);
        }
    } catch (error) {
        console.error('Error recording supplier payment:', error);
        alert('Failed to record payment');
    }
} 