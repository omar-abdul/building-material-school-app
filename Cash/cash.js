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
			renderTransactions(data.data);
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
function renderTransactions(transactions) {
	const tbody = document.getElementById("transaction-history-body");

	if (!transactions || transactions.length === 0) {
		tbody.innerHTML =
			'<tr><td colspan="7" class="empty-state">No transactions found</td></tr>';
		return;
	}

	tbody.innerHTML = transactions
		.map(
			(transaction) => `
		<tr>
			<td>${formatDate(transaction.TransactionDate)}</td>
			<td>${transaction.TransactionType}</td>
			<td>${transaction.ReferenceID}</td>
			<td>${transaction.Description || "-"}</td>
			<td class="amount ${transaction.Amount >= 0 ? "positive" : "negative"}">
				${formatCurrency(transaction.Amount)}
			</td>
			<td>${transaction.PaymentMethod}</td>
			<td class="balance">${formatCurrency(transaction.RunningBalance)}</td>
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
 * Refresh balances and transaction history
 */
async function refreshCashModule() {
	await loadBalances();
	await showTransactionHistory();
}

/**
 * Record expense transaction
 */
async function recordExpense() {
	const form = document.getElementById("expense-form");
	const formData = new FormData(form);

	// Convert FormData to JSON object
	const expenseData = {
		transactionType: formData.get("transactionType"),
		amount: -Math.abs(Number.parseFloat(formData.get("amount"))), // Make expenses negative
		paymentMethod: formData.get("paymentMethod"),
		transactionDate: formData.get("transactionDate"),
		description: formData.get("description"),
		notes: formData.get("notes") || null,
		referenceId: formData.get("referenceId") || null,
		referenceType: formData.get("referenceType") || null,
	};

	try {
		const response = await fetch(
			buildApiUrl("cash/cash.php?action=addTransaction"),
			{
				method: "POST",
				headers: {
					"Content-Type": "application/json",
				},
				body: JSON.stringify(expenseData),
			},
		);

		const data = await response.json();

		if (data.success) {
			// Close modal and refresh data
			closeModal();
			form.reset();

			// Refresh the entire module to show updated balances
			await refreshCashModule();

			// Show success message
			showNotification("Expense recorded successfully", "success");
		} else {
			showNotification(`Failed to record expense: ${data.message}`, "error");
		}
	} catch (error) {
		console.error("Error recording expense:", error);
		showNotification("Error recording expense", "error");
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

/**
 * Show notification message
 */
function showNotification(message, type = "info") {
	// Remove existing notifications
	const existingNotifications = document.querySelectorAll(".notification");
	for (const notification of existingNotifications) {
		notification.remove();
	}

	// Create notification element
	const notification = document.createElement("div");
	notification.className = `notification ${type}`;
	notification.innerHTML = `
		<span class="notification-message">${message}</span>
		<button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
	`;

	// Add to page
	document.body.appendChild(notification);

	// Auto-remove after 5 seconds
	setTimeout(() => {
		if (notification.parentElement) {
			notification.remove();
		}
	}, 5000);
}
