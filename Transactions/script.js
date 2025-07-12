// DOM Elements
const addTransactionBtn = document.getElementById('addTransactionBtn');
const transactionModal = document.getElementById('transactionModal');
const viewModal = document.getElementById('viewModal');
const deleteModal = document.getElementById('deleteModal');
const closeModal = document.getElementById('closeModal');
const closeViewModal = document.getElementById('closeViewModal');
const closeDeleteModal = document.getElementById('closeDeleteModal');
const cancelBtn = document.getElementById('cancelBtn');
const closeViewBtn = document.getElementById('closeViewBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const transactionForm = document.getElementById('transactionForm');
const applyDiscountBtn = document.getElementById('applyDiscountBtn');
const calculateBalanceBtn = document.getElementById('calculateBalanceBtn');
const markAsPaidBtn = document.getElementById('markAsPaidBtn');
const searchInput = document.getElementById('searchInput');
const paymentMethodFilter = document.getElementById('paymentMethodFilter');
const orderIdSelect = document.getElementById('orderId');
const transactionsTable = document.querySelector('.transactions-table tbody');

// Current transaction to be deleted
let currentTransactionToDelete = null;

// Event Listeners
addTransactionBtn.addEventListener('click', openAddTransactionModal);
closeModal.addEventListener('click', closeModals);
closeViewModal.addEventListener('click', closeModals);
closeDeleteModal.addEventListener('click', closeModals);
cancelBtn.addEventListener('click', closeModals);
closeViewBtn.addEventListener('click', closeModals);
cancelDeleteBtn.addEventListener('click', closeModals);
confirmDeleteBtn.addEventListener('click', confirmDelete);
transactionForm.addEventListener('submit', saveTransaction);
applyDiscountBtn.addEventListener('click', applyDiscount);
calculateBalanceBtn.addEventListener('click', calculateBalance);
markAsPaidBtn.addEventListener('click', markAsPaid);
orderIdSelect.addEventListener('change', fetchOrderDetails);
searchInput.addEventListener('input', filterTransactions);
paymentMethodFilter.addEventListener('change', filterTransactions);

// Load transactions when page loads
document.addEventListener('DOMContentLoaded', loadTransactions);

// Functions
function loadTransactions() {
    fetch('backend.php?action=getTransactions')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (Array.isArray(data)) {
                renderTransactions(data);
            } else {
                console.error('Invalid data format:', data);
            }
        })
        .catch(error => {
            console.error('Error loading transactions:', error);
        });
}

function renderTransactions(transactions) {
    transactionsTable.innerHTML = '';
    
    if (!transactions || transactions.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="11" class="no-transactions">No transactions found</td>`;
        transactionsTable.appendChild(row);
        return;
    }
    
    transactions.forEach(transaction => {
        const row = document.createElement('tr');
        
        // Calculate discount if any
        let discountPercentage = 0;
        let discountAmount = 0;
        if (transaction.Balance < 0) {
            discountAmount = Math.abs(transaction.Balance);
            discountPercentage = (discountAmount / (parseFloat(transaction.AmountPaid) + discountAmount)) * 100;
        }
        
        row.innerHTML = `
            <td>TRX-${transaction.TransactionID}</td>
            <td>ORD-${transaction.OrderID}</td>
            <td>CUST-${transaction.CustomerID}</td>
            <td>${transaction.CustomerName}</td>
            <td>${transaction.PaymentMethod}</td>
            <td>$${parseFloat(transaction.AmountPaid).toFixed(2)}</td>
            <td>$${Math.max(0, parseFloat(transaction.Balance)).toFixed(2)}</td>
            <td>${discountPercentage.toFixed(2)}% ($${discountAmount.toFixed(2)})</td>
            <td>${formatDate(transaction.TransactionDate)}</td>
            <td><span class="status-${transaction.Status.toLowerCase()}">${transaction.Status}</span></td>
            <td class="action-cell">
                <button class="action-btn view-btn" onclick="viewTransaction(${transaction.TransactionID})">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="action-btn edit-btn" onclick="editTransaction(${transaction.TransactionID})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="action-btn delete-btn" onclick="deleteTransaction(${transaction.TransactionID})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        `;
        
        transactionsTable.appendChild(row);
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
}

function openAddTransactionModal() {
    document.getElementById('modalTitle').textContent = "Add New Transaction";
    document.getElementById('transactionId').value = "";
    document.getElementById('orderId').value = "";
    document.getElementById('totalAmount').value = "";
    document.getElementById('amountPaid').value = "";
    document.getElementById('paymentMethod').value = "";
    document.getElementById('balance').value = "";
    document.getElementById('discountPercentage').value = "0";
    document.getElementById('discountAmount').value = "";
    document.getElementById('transactionStatus').value = "Paid";
    document.getElementById('transactionDate').value = new Date().toISOString().split('T')[0];
    transactionModal.style.display = "flex";
}

function fetchOrderDetails() {
    const orderId = document.getElementById('orderId').value;
    if (!orderId) {
        document.getElementById('totalAmount').value = "";
        return;
    }
    
    fetch(`backend.php?action=getOrderDetails&orderId=${orderId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            
            document.getElementById('totalAmount').value = data.TotalAmount;
            document.getElementById('customerId').value = data.CustomerID;
            document.getElementById('customerName').value = data.CustomerName;
        })
        .catch(error => {
            console.error('Error fetching order:', error);
        });
}

function editTransaction(id) {
    fetch(`backend.php?action=getTransaction&transactionId=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            
            document.getElementById('modalTitle').textContent = "Edit Transaction";
            document.getElementById('transactionId').value = data.TransactionID;
            document.getElementById('orderId').value = data.OrderID;
            document.getElementById('totalAmount').value = data.TotalAmount;
            document.getElementById('amountPaid').value = data.Amount;
            document.getElementById('paymentMethod').value = data.PaymentMethod;
            document.getElementById('balance').value = data.Balance;
            document.getElementById('discountPercentage').value = data.DiscountPercentage || "0";
            document.getElementById('discountAmount').value = data.DiscountAmount || "0";
            document.getElementById('transactionStatus').value = data.Status;
            document.getElementById('transactionDate').value = data.TransactionDate.split(' ')[0];
            transactionModal.style.display = "flex";
        })
        .catch(error => {
            console.error('Error fetching transaction:', error);
            alert('Error fetching transaction details');
        });
}

function viewTransaction(id) {
    fetch(`backend.php?action=getTransaction&transactionId=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            
            // Calculate discount if any
            let discountPercentage = 0;
            let discountAmount = 0;
            if (data.Amount < data.TotalAmount) {
                discountAmount = data.TotalAmount - data.Amount;
                discountPercentage = (discountAmount / data.TotalAmount) * 100;
            }
            
            document.getElementById('viewId').textContent = `TRX-${data.TransactionID}`;
            document.getElementById('viewOrderId').textContent = `ORD-${data.OrderID}`;
            document.getElementById('viewCustomerId').textContent = `CUST-${data.CustomerID}`;
            document.getElementById('viewCustomerName').textContent = data.CustomerName;
            document.getElementById('viewPaymentMethod').textContent = data.PaymentMethod;
            document.getElementById('viewTotalAmount').textContent = `$${parseFloat(data.TotalAmount).toFixed(2)}`;
            document.getElementById('viewAmountPaid').textContent = `$${parseFloat(data.Amount).toFixed(2)}`;
            document.getElementById('viewBalance').textContent = `$${Math.max(0, parseFloat(data.Balance)).toFixed(2)}`;
            document.getElementById('viewDiscount').textContent = `${discountPercentage.toFixed(2)}% ($${discountAmount.toFixed(2)})`;
            document.getElementById('viewStatus').textContent = data.Status;
            document.getElementById('viewDate').textContent = formatDate(data.TransactionDate);
            
            // Set status class
            const statusElement = document.getElementById('viewStatus');
            statusElement.className = '';
            statusElement.classList.add(`status-${data.Status.toLowerCase()}`);
            
            viewModal.style.display = "flex";
        })
        .catch(error => {
            console.error('Error fetching transaction:', error);
            alert('Error fetching transaction details');
        });
}

function deleteTransaction(id) {
    currentTransactionToDelete = id;
    fetch(`backend.php?action=getTransaction&transactionId=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('deleteTransactionId').textContent = `TRX-${data.TransactionID}`;
            document.getElementById('deleteTransactionOrder').textContent = `ORD-${data.OrderID}`;
            deleteModal.style.display = "flex";
        })
        .catch(error => {
            console.error('Error fetching transaction for delete:', error);
            alert('Error fetching transaction details');
        });
}

function applyDiscount() {
    const totalAmount = parseFloat(document.getElementById('totalAmount').value) || 0;
    const discountPercentage = parseFloat(document.getElementById('discountPercentage').value) || 0;
    
    if (!totalAmount) {
        alert("Please select an order first");
        return;
    }
    
    if (discountPercentage < 0 || discountPercentage > 100) {
        alert("Discount percentage must be between 0 and 100");
        return;
    }
    
    const discountAmount = totalAmount * (discountPercentage / 100);
    document.getElementById('discountAmount').value = discountAmount.toFixed(2);
    
    // Recalculate total amount after discount
    const newTotal = totalAmount - discountAmount;
    document.getElementById('totalAmount').value = newTotal.toFixed(2);
    
    // If amount paid was already entered, recalculate balance
    const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
    if (amountPaid > 0) {
        const balance = newTotal - amountPaid;
        document.getElementById('balance').value = balance.toFixed(2);
    }
}

function calculateBalance() {
    const totalAmount = parseFloat(document.getElementById('totalAmount').value) || 0;
    const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
    
    if (!totalAmount) {
        alert("Please select an order first");
        return;
    }
    
    if (amountPaid < 0) {
        alert("Amount paid cannot be negative");
        return;
    }
    
    const balance = totalAmount - amountPaid;
    document.getElementById('balance').value = balance.toFixed(2);
    
    // Update status based on payment
    if (amountPaid >= totalAmount) {
        document.getElementById('transactionStatus').value = "Paid";
    } else if (amountPaid > 0) {
        document.getElementById('transactionStatus').value = "Partial";
    } else {
        document.getElementById('transactionStatus').value = "Unpaid";
    }
}

function markAsPaid() {
    const totalAmount = parseFloat(document.getElementById('totalAmount').value) || 0;
    
    if (!totalAmount) {
        alert("Please select an order first");
        return;
    }
    
    document.getElementById('amountPaid').value = totalAmount.toFixed(2);
    document.getElementById('balance').value = "0.00";
    document.getElementById('transactionStatus').value = "Paid";
}

function saveTransaction(e) {
    e.preventDefault();
    
    // Get form values
    const id = document.getElementById('transactionId').value;
    const orderId = document.getElementById('orderId').value;
    const paymentMethod = document.getElementById('paymentMethod').value;
    const totalAmount = parseFloat(document.getElementById('totalAmount').value);
    const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
    const balance = parseFloat(document.getElementById('balance').value) || 0;
    const discountPercentage = parseFloat(document.getElementById('discountPercentage').value) || 0;
    const discountAmount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const status = document.getElementById('transactionStatus').value;
    const transactionDate = document.getElementById('transactionDate').value;
    
    // Validate required fields
    if (!orderId || !paymentMethod || isNaN(totalAmount) || !status || !transactionDate) {
        alert("Please fill in all required fields with valid values");
        return;
    }
    
    // Prepare data
    const transactionData = {
        orderId: orderId,
        paymentMethod: paymentMethod,
        amount: amountPaid,
        status: status,
        transactionDate: transactionDate
    };
    
    if (id) {
        transactionData.transactionId = id;
    }
    
    // Determine URL and method
    const url = `backend.php?action=${id ? 'updateTransaction' : 'addTransaction'}`;
    
    // Send request
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(transactionData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        if (data.success) {
            loadTransactions();
            closeModals();
            alert(`Transaction ${id ? 'updated' : 'added'} successfully!`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(`Error saving transaction: ${error.message}`);
    });
}

function filterTransactions() {
    const searchTerm = searchInput.value;
    const paymentMethodFilterValue = paymentMethodFilter.value;
    
    fetch(`backend.php?action=getTransactions&search=${encodeURIComponent(searchTerm)}&paymentMethod=${encodeURIComponent(paymentMethodFilterValue)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            renderTransactions(data);
        })
        .catch(error => {
            console.error('Error filtering transactions:', error);
        });
}

function confirmDelete() {
    if (!currentTransactionToDelete) return;
    
    fetch(`backend.php?action=deleteTransaction&transactionId=${currentTransactionToDelete}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                loadTransactions();
                closeModals();
            } else {
                alert(data.error || 'Failed to delete transaction');
            }
        })
        .catch(error => {
            console.error('Error deleting transaction:', error);
            alert('Error deleting transaction');
        });
}

function closeModals() {
    transactionModal.style.display = "none";
    viewModal.style.display = "none";
    deleteModal.style.display = "none";
    currentTransactionToDelete = null;
}

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === transactionModal) transactionModal.style.display = "none";
    if (e.target === viewModal) viewModal.style.display = "none";
    if (e.target === deleteModal) deleteModal.style.display = "none";
});








// These elements and event listeners are already in your code
const signUpBtn = document.getElementById('signUpBtn');
const signUpModal = document.getElementById('signUpModal');
const closeSignUpModal = document.getElementById('closeSignUpModal');
const cancelSignUpBtn = document.getElementById('cancelSignUpBtn');
const signUpForm = document.getElementById('signUpForm');

signUpBtn.addEventListener('click', openSignUpModal);
closeSignUpModal.addEventListener('click', closeModals);
cancelSignUpBtn.addEventListener('click', closeModals);
signUpForm.addEventListener('submit', signUpUser);

function openSignUpModal(e) {
    e.preventDefault(); // Prevent default link behavior
    document.getElementById('signUpUsername').value = "";
    document.getElementById('signUpPassword').value = "";
    document.getElementById('signUpConfirmPassword').value = "";
    signUpModal.style.display = "flex";
}