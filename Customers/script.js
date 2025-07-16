// DOM Elements
const addCustomerBtn = document.getElementById('addCustomerBtn');
const customerModal = document.getElementById('customerModal');
const viewModal = document.getElementById('viewModal');
const deleteModal = document.getElementById('deleteModal');
const historyModal = document.getElementById('historyModal');
const closeModal = document.getElementById('closeModal');
const closeViewModal = document.getElementById('closeViewModal');
const closeDeleteModal = document.getElementById('closeDeleteModal');
const closeHistoryModal = document.getElementById('closeHistoryModal');
const cancelBtn = document.getElementById('cancelBtn');
const closeViewBtn = document.getElementById('closeViewBtn');
const closeHistoryBtn = document.getElementById('closeHistoryBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const customerForm = document.getElementById('customerForm');
const searchInput = document.getElementById('searchInput');
const customersTable = document.querySelector('.customers-table tbody');

// Current customer to be deleted or viewed
let currentCustomerToDelete = null;
let currentCustomerToViewHistory = null;

// Event Listeners
addCustomerBtn.addEventListener('click', openAddCustomerModal);
closeModal.addEventListener('click', closeModals);
closeViewModal.addEventListener('click', closeModals);
closeDeleteModal.addEventListener('click', closeModals);
closeHistoryModal.addEventListener('click', closeModals);
cancelBtn.addEventListener('click', closeModals);
closeViewBtn.addEventListener('click', closeModals);
closeHistoryBtn.addEventListener('click', closeModals);
cancelDeleteBtn.addEventListener('click', closeModals);
confirmDeleteBtn.addEventListener('click', confirmDelete);
customerForm.addEventListener('submit', saveCustomer);
searchInput.addEventListener('input', filterCustomers);

// Load customers when page loads
document.addEventListener('DOMContentLoaded', loadCustomers);

// Functions
function loadCustomers() {
    fetch('/backend/api/customers/customers.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderCustomersTable(data.data);
            } else {
                console.error('Error loading customers:', data.message);
                alert('Failed to load customers');
            }
        })
        .catch(error => {
            console.error('Error loading customers:', error);
            alert('Failed to load customers');
        });
}

function renderCustomersTable(customers) {
    customersTable.innerHTML = '';
    
    for (const customer of customers) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${customer.CustomerID}</td>
            <td>${customer.Name}</td>
            <td>${customer.Phone}</td>
            <td>${customer.Email || ''}</td>
            <td>${customer.Address || ''}</td>
            <td>${customer.DateAdded}</td>
            <td class="action-cell">
                <button class="action-btn view-btn" onclick="viewCustomer('${customer.CustomerID}')">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="action-btn edit-btn" onclick="editCustomer('${customer.CustomerID}')">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="action-btn delete-btn" onclick="deleteCustomer('${customer.CustomerID}')">
                    <i class="fas fa-trash"></i> Delete
                </button>
                <button class="action-btn history-btn" onclick="viewOrderHistory('${customer.CustomerID}')">
                    <i class="fas fa-history"></i> History
                </button>
            </td>
        `;
        customersTable.appendChild(row);
    }
}

function openAddCustomerModal() {
    document.getElementById('modalTitle').textContent = "Add New Customer";
    document.getElementById('customerId').value = "";
    document.getElementById('customerName').value = "";
    document.getElementById('phone').value = "";
    document.getElementById('email').value = "";
    document.getElementById('address').value = "";
    customerModal.style.display = "flex";
}

function editCustomer(id) {
    fetch(`/backend/api/customers/customers.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const customer = data.data;
                document.getElementById('modalTitle').textContent = "Edit Customer";
                document.getElementById('customerId').value = customer.CustomerID;
                document.getElementById('customerName').value = customer.Name;
                document.getElementById('phone').value = customer.Phone;
                document.getElementById('email').value = customer.Email || "";
                document.getElementById('address').value = customer.Address || "";
                customerModal.style.display = "flex";
            } else {
                console.error('Error loading customer:', data.message);
                alert('Failed to load customer details');
            }
        })
        .catch(error => {
            console.error('Error loading customer:', error);
            alert('Failed to load customer details');
        });
}

function viewCustomer(id) {
    fetch(`/backend/api/customers/customers.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const customer = data.data;
                document.getElementById('viewId').textContent = customer.CustomerID;
                document.getElementById('viewName').textContent = customer.Name;
                document.getElementById('viewPhone').textContent = customer.Phone;
                document.getElementById('viewEmail').textContent = customer.Email || "N/A";
                document.getElementById('viewAddress').textContent = customer.Address || "N/A";
                viewModal.style.display = "flex";
            } else {
                console.error('Error loading customer:', data.message);
                alert('Failed to load customer details');
            }
        })
        .catch(error => {
            console.error('Error loading customer:', error);
            alert('Failed to load customer details');
        });
}

function deleteCustomer(id) {
    currentCustomerToDelete = id;
    // Get customer name for display in confirmation
    fetch(`/backend/api/customers/customers.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const customer = data.data;
                document.getElementById('deleteCustomerId').textContent = customer.CustomerID;
                document.getElementById('deleteCustomerName').textContent = customer.Name;
                deleteModal.style.display = "flex";
            } else {
                console.error('Error loading customer:', data.message);
                alert('Failed to load customer details');
            }
        })
        .catch(error => {
            console.error('Error loading customer:', error);
            alert('Failed to load customer details');
        });
}

function confirmDelete() {
    if (currentCustomerToDelete) {
        fetch('/backend/api/customers/customers.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: currentCustomerToDelete })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Customer deleted successfully');
                loadCustomers(); // Refresh the table
            } else {
                alert(`Failed to delete customer: ${data.message}`);
            }
            currentCustomerToDelete = null;
            closeModals();
        })
        .catch(error => {
            console.error('Error deleting customer:', error);
            alert('Failed to delete customer');
            currentCustomerToDelete = null;
            closeModals();
        });
    }
}

function viewOrderHistory(id) {
    currentCustomerToViewHistory = id;
    
    // Get customer name for display
    fetch(`/backend/api/customers/customers.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const customer = data.data;
                document.getElementById('historyCustomerName').textContent = `${customer.Name} (${customer.CustomerID})`;
                
                // Get order history
                return fetch(`/backend/api/customers/customers.php?id=${id}&history=true`);
            }
            throw new Error(data.message);
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const orderHistoryBody = document.getElementById('orderHistoryBody');
                orderHistoryBody.innerHTML = "";
                
                if (data.data.length === 0) {
                    orderHistoryBody.innerHTML = `<tr><td colspan="5" style="text-align: center;">No order history found</td></tr>`;
                } else {
                    for (const order of data.data) {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${order.OrderID}</td>
                            <td>${order.OrderDate}</td>
                            <td>${order.TransactionsCount || 0}</td>
                            <td>$${Number.parseFloat(order.Total).toFixed(2)}</td>
                            <td>${order.Status}</td>
                        `;
                        orderHistoryBody.appendChild(row);
                    }
                }
                
                historyModal.style.display = "flex";
            }
            throw new Error(data.message);
        })
        .catch(error => {
            console.error('Error loading order history:', error);
            alert('Failed to load order history');
        });
}

function saveCustomer(e) {
    e.preventDefault();
    
    const id = document.getElementById('customerId').value;
    const name = document.getElementById('customerName').value;
    const phone = document.getElementById('phone').value;
    const email = document.getElementById('email').value;
    const address = document.getElementById('address').value;
    
    if (!name || !phone) {
        alert("Please fill in all required fields");
        return;
    }
    
    // Simple phone validation
    if (!phone.match(/^\+?\d{8,15}$/)) {
        alert("Please enter a valid phone number");
        return;
    }
    
    // Simple email validation if provided
    if (email && !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        alert("Please enter a valid email address");
        return;
    }
    
    const customerData = {
        name: name,
        phone: phone,
        email: email,
        address: address
    };
    
    const method = id ? 'PUT' : 'POST';
    const url = '/backend/api/customers/customers.php';
    
    if (id) {
        customerData.id = id;
    }
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(customerData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(id ? 'Customer updated successfully' : 'Customer added successfully');
            loadCustomers(); // Refresh the table
            closeModals();
        } else {
            throw new Error(data.message || 'Operation failed');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Failed to save customer');
    });
}

function filterCustomers() {
    const searchTerm = searchInput.value.trim();
    
    if (searchTerm.length === 0) {
        loadCustomers();
        return;
    }
    
    fetch(`/backend/api/customers/customers.php?term=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderCustomersTable(data.data);
            } else {
                console.error('Error searching customers:', data.message);
                alert('Failed to search customers');
            }
        })
        .catch(error => {
            console.error('Error searching customers:', error);
            alert('Failed to search customers');
        });
}

function closeModals() {
    customerModal.style.display = "none";
    viewModal.style.display = "none";
    deleteModal.style.display = "none";
    historyModal.style.display = "none";
}

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === customerModal) customerModal.style.display = "none";
    if (e.target === viewModal) viewModal.style.display = "none";
    if (e.target === deleteModal) deleteModal.style.display = "none";
    if (e.target === historyModal) historyModal.style.display = "none";
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

document.getElementById("signUpBtn").addEventListener("click", (e) => {
    console.log("Button clicked");
});
