/**
 * Cash/Wallet Management JavaScript
 * Handles cash and wallet balance tracking and expense payments
 */

// Global variables
let currentBalances = {
	cash: 0,
	wallet: 0,
	total: 0,
};

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
	initializeCashModule();
	setupEventListeners();
	loadBalances();
	showTransactionHistory();
});

/**
 * Initialize cash module
 */
function initializeCashModule() {
	console.log("Initializing Cash/Wallet Module...");

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
	// Search inputs
	document
		.getElementById("transaction-search")
		?.addEventListener("input", debounce(filterTransactions, 300));

	// Filter selects
	document
		.getElementById("transaction-type-filter")
		?.addEventListener("change", filterTransactions);

	// Form submissions
	document
		.getElementById("expense-form")
		?.addEventListener("submit", async (e) => {
			e.preventDefault();
			await recordExpense();
		});
}

/**
 * Load cash and wallet balances
 */
async function loadBalances() {
	try {
		const response = await fetch(
			buildApiUrl("cash/cash.php?action=getBalances"),
		);
		const data = await response.json();

		if (data.success) {
			currentBalances = data.data;
			updateBalanceCards();
		} else {
			console.error("Failed to load balances:", data.message);
		}
	} catch (error) {
		console.error("Error loading balances:", error);
	}
}

/**
 * Update balance cards with current data
 */
function updateBalanceCards() {
	document.getElementById("cash-balance").textContent = formatCurrency(
		currentBalances.cash || 0,
	);
	document.getElementById("wallet-balance").textContent = formatCurrency(
		currentBalances.wallet || 0,
	);
	document.getElementById("total-balance").textContent = formatCurrency(
		currentBalances.total || 0,
	);
}

/**
 * Show transaction history
 */
async function showTransactionHistory() {
	const tbody = document.getElementById("transaction-history-body");
	tbody.innerHTML =
		'<tr><td colspan="8" class="loading">Loading transaction history...</td></tr>';

	try {
		const response = await fetch(
			buildApiUrl("cash/cash.php?action=getTransactions"),
		);
		const data = await response.json();

		if (data.success) {
			renderTransactionHistory(data.data);
		} else {
			tbody.innerHTML =
				'<tr><td colspan="8" class="empty-state">No transactions found</td></tr>';
		}
	} catch (error) {
		console.error("Error loading transaction history:", error);
		tbody.innerHTML =
			'<tr><td colspan="8" class="empty-state">Error loading transaction history</td></tr>';
	}
}

/**
 * Render transaction history
 */
function renderTransactionHistory(transactions) {
	const tbody = document.getElementById("transaction-history-body");

	if (!transactions || transactions.length === 0) {
		tbody.innerHTML =
			'<tr><td colspan="8" class="empty-state">No transactions found</td></tr>';
		return;
	}

	tbody.innerHTML = transactions
		.map(
			(transaction) => `
        <tr>
            <td>${transaction.TransactionID}</td>
            <td>${formatDate(transaction.TransactionDate)}</td>
            <td>
                <span class="status-badge ${getTransactionTypeClass(transaction.TransactionType)}">
                    ${transaction.TransactionType}
                </span>
            </td>
            <td>${escapeHtml(transaction.Description || "N/A")}</td>
            <td class="${getAmountClass(transaction.Amount)}">
                ${formatCurrency(Math.abs(transaction.Amount))}
            </td>
            <td>${escapeHtml(transaction.PaymentMethod || "N/A")}</td>
            <td class="${getBalanceClass(transaction.NewBalance)}">
                ${formatCurrency(transaction.NewBalance)}
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-primary btn-sm" onclick="viewTransactionDetails(${transaction.TransactionID})">
                        <i class="fas fa-eye"></i> View
                    </button>
                </div>
            </td>
        </tr>
    `,
		)
		.join("");
}

/**
 * Show expense modal
 */
function showExpenseModal() {
	const modal = document.getElementById("expense-modal");
	modal.style.display = "block";
	setDefaultExpenseDate();
}

/**
 * Close expense modal
 */
function closeExpenseModal() {
	const modal = document.getElementById("expense-modal");
	modal.style.display = "none";
	document.getElementById("expense-form").reset();
}

/**
 * Set default expense date to today
 */
function setDefaultExpenseDate() {
	const today = new Date().toISOString().split("T")[0];
	document.getElementById("expense-date").value = today;
}

/**
 * Record expense payment
 */
async function recordExpense() {
	const amount = Number.parseFloat(
		document.getElementById("expense-amount").value,
	);
	const paymentMethod = document.getElementById("expense-payment-method").value;
	const date = document.getElementById("expense-date").value;
	const category = document.getElementById("expense-category").value;
	const description = document.getElementById("expense-description").value;
	const notes = document.getElementById("expense-notes").value;

	if (!amount || amount <= 0) {
		alert("Please enter a valid amount");
		return;
	}

	if (!paymentMethod) {
		alert("Please select a payment method");
		return;
	}

	if (!date) {
		alert("Please select a date");
		return;
	}

	if (!description) {
		alert("Please enter a description");
		return;
	}

	const formData = {
		transactionType: "DIRECT_EXPENSE",
		referenceId: `EXP-${Date.now()}`,
		referenceType: "expense",
		amount: -amount, // Negative for expense
		paymentMethod: paymentMethod,
		status: "Completed",
		transactionDate: date,
		description: description,
		notes: notes,
		category: category,
	};

	try {
		const response = await fetch(
			buildApiUrl("cash/cash.php?action=addTransaction"),
			{
				method: "POST",
				headers: {
					"Content-Type": "application/json",
				},
				body: JSON.stringify(formData),
			},
		);

		const data = await response.json();

		if (data.success) {
			alert("Expense recorded successfully!");
			closeExpenseModal();
			loadBalances();
			showTransactionHistory();
		} else {
			alert(`Failed to record expense: ${data.message}`);
		}
	} catch (error) {
		console.error("Error recording expense:", error);
		alert("Failed to record expense");
	}
}

/**
 * View transaction details
 */
async function viewTransactionDetails(transactionId) {
	try {
		const response = await fetch(
			buildApiUrl(
				`cash/cash.php?action=getTransaction&transactionId=${transactionId}`,
			),
		);
		const data = await response.json();

		if (data.success) {
			showTransactionModal(data.data);
		} else {
			alert("Failed to load transaction details");
		}
	} catch (error) {
		console.error("Error loading transaction details:", error);
		alert("Error loading transaction details");
	}
}

/**
 * Show transaction modal
 */
function showTransactionModal(transaction) {
	const modal = document.getElementById("transaction-modal");
	const details = document.getElementById("transaction-details");

	details.innerHTML = `
        <div class="transaction-detail">
            <div class="detail-item">
                <div class="detail-label">Transaction ID</div>
                <div class="detail-value">${transaction.TransactionID}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Date</div>
                <div class="detail-value">${formatDate(transaction.TransactionDate)}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Type</div>
                <div class="detail-value">${transaction.TransactionType}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Description</div>
                <div class="detail-value">${escapeHtml(transaction.Description || "N/A")}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Amount</div>
                <div class="detail-value ${getAmountClass(transaction.Amount)}">
                    ${formatCurrency(Math.abs(transaction.Amount))}
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Payment Method</div>
                <div class="detail-value">${escapeHtml(transaction.PaymentMethod || "N/A")}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Balance</div>
                <div class="detail-value ${getBalanceClass(transaction.NewBalance)}">
                    ${formatCurrency(transaction.NewBalance)}
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                    <span class="status-badge ${getStatusClass(transaction.Status)}">
                        ${transaction.Status}
                    </span>
                </div>
            </div>
            ${
							transaction.Notes
								? `
            <div class="detail-item">
                <div class="detail-label">Notes</div>
                <div class="detail-value">${escapeHtml(transaction.Notes)}</div>
            </div>
            `
								: ""
						}
        </div>
    `;

	modal.style.display = "block";
}

/**
 * Close transaction modal
 */
function closeTransactionModal() {
	const modal = document.getElementById("transaction-modal");
	modal.style.display = "none";
}

/**
 * Setup modal functionality
 */
function setupModal() {
	const modals = document.querySelectorAll(".modal");

	for (const modal of modals) {
		const closeBtn = modal.querySelector(".close");

		if (closeBtn) {
			closeBtn.onclick = () => {
				modal.style.display = "none";
			};
		}

		window.onclick = (event) => {
			if (event.target === modal) {
				modal.style.display = "none";
			}
		};
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
 * Filter transactions
 */
function filterTransactions() {
	const searchTerm = document
		.getElementById("transaction-search")
		.value.toLowerCase();
	const typeFilter = document.getElementById("transaction-type-filter").value;
	const rows = document.querySelectorAll("#transaction-history-table tbody tr");

	for (const row of rows) {
		const text = row.textContent.toLowerCase();
		const type = row.querySelector("td:nth-child(3)").textContent.toLowerCase();

		const matchesSearch = text.includes(searchTerm);
		const matchesType = !typeFilter || type.includes(typeFilter);

		row.style.display = matchesSearch && matchesType ? "" : "none";
	}
}

/**
 * Export transactions
 */
function exportTransactions() {
	exportTableToCSV("transaction-history-table", "cash-transactions.csv");
}

/**
 * Export table to CSV
 */
function exportTableToCSV(tableId, filename) {
	const table = document.getElementById(tableId);
	const rows = table.querySelectorAll("tr");
	const csv = [];

	for (let i = 0; i < rows.length; i++) {
		const row = rows[i];
		const cols = row.querySelectorAll("td, th");
		const rowData = [];

		for (let j = 0; j < cols.length - 1; j++) {
			// Skip last column (actions)
			let text = cols[j].textContent || cols[j].innerText;
			text = text.replace(/"/g, '""');
			rowData.push(`"${text}"`);
		}

		csv.push(rowData.join(","));
	}

	const csvContent = csv.join("\n");
	const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
	const link = document.createElement("a");

	if (link.download !== undefined) {
		const url = URL.createObjectURL(blob);
		link.setAttribute("href", url);
		link.setAttribute("download", filename);
		link.style.visibility = "hidden";
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	}
}

/**
 * Utility functions
 */
function formatCurrency(amount) {
	return new Intl.NumberFormat("en-US", {
		style: "currency",
		currency: "USD",
	}).format(amount || 0);
}

function formatDate(dateString) {
	if (!dateString) return "N/A";
	return new Date(dateString).toLocaleDateString("en-US", {
		year: "numeric",
		month: "short",
		day: "numeric",
	});
}

function escapeHtml(text) {
	const div = document.createElement("div");
	div.textContent = text;
	return div.innerHTML;
}

function getBalanceClass(balance) {
	if (balance > 0) return "balance-positive";
	if (balance < 0) return "balance-negative";
	return "balance-zero";
}

function getAmountClass(amount) {
	if (amount > 0) return "balance-positive";
	if (amount < 0) return "balance-negative";
	return "balance-zero";
}

function getTransactionTypeClass(type) {
	const typeMap = {
		DIRECT_EXPENSE: "cancelled",
		DIRECT_INCOME: "completed",
		SALES_PAYMENT: "completed",
		PURCHASE_PAYMENT: "completed",
	};
	return typeMap[type] || "neutral";
}

function getStatusClass(status) {
	const statusMap = {
		Completed: "completed",
		Pending: "pending",
		Cancelled: "cancelled",
	};
	return statusMap[status] || "neutral";
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
