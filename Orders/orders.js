// DOM Elements
const addOrderBtn = document.getElementById('addOrderBtn');
const orderModal = document.getElementById('orderModal');
const viewModal = document.getElementById('viewModal');
const deleteModal = document.getElementById('deleteModal');
const closeModal = document.getElementById('closeModal');
const closeViewModal = document.getElementById('closeViewModal');
const closeDeleteModal = document.getElementById('closeDeleteModal');
const cancelBtn = document.getElementById('cancelBtn');
const closeViewBtn = document.getElementById('closeViewBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const orderForm = document.getElementById('orderForm');
const addItemBtn = document.getElementById('addItemBtn');
const itemsList = document.getElementById('itemsList');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');

// Autocomplete elements
const customerSelect = document.getElementById('customerSelect');
const customerDropdown = document.getElementById('customerDropdown');
const employeeSelect = document.getElementById('employeeSelect');
const employeeDropdown = document.getElementById('employeeDropdown');
const itemSelect = document.getElementById('itemSelect');
const itemDropdown = document.getElementById('itemDropdown');

// Dynamic data storage
let customersData = [];
let employeesData = [];
let itemsData = [];

// Current order items
let currentOrderItems = [];

// Load data on page load
document.addEventListener('DOMContentLoaded', () => {
    loadCustomers();
    loadEmployees();
    loadItems();
    filterOrders();
    setupAutocomplete();
});

// Load customers from API
async function loadCustomers() {
    try {
        const response = await fetch('/backend/api/customers/customers.php');
        const data = await response.json();
        if (data.success) {
            customersData = data.data;
        }
    } catch (error) {
        console.error('Error loading customers:', error);
    }
}

// Load employees from API
async function loadEmployees() {
    try {
        const response = await fetch('/backend/api/employees/employees.php');
        const data = await response.json();
        if (data.success) {
            employeesData = data.data;
        }
    } catch (error) {
        console.error('Error loading employees:', error);
    }
}

// Load items from API
async function loadItems() {
    try {
        const response = await fetch('/backend/api/items/items.php');
        const data = await response.json();
        if (data.success) {
            itemsData = data.data;
        }
    } catch (error) {
        console.error('Error loading items:', error);
    }
}

// Setup autocomplete functionality
function setupAutocomplete() {
    // Customer autocomplete
    customerSelect.addEventListener('input', () => {
        const searchTerm = customerSelect.value.toLowerCase();
        const filteredCustomers = customersData.filter(customer => 
            customer.Name.toLowerCase().includes(searchTerm) ||
            customer.CustomerID.toLowerCase().includes(searchTerm)
        );
        showAutocompleteDropdown(customerDropdown, filteredCustomers, 'customer');
    });

    // Employee autocomplete
    employeeSelect.addEventListener('input', () => {
        const searchTerm = employeeSelect.value.toLowerCase();
        const filteredEmployees = employeesData.filter(employee => 
            employee.name.toLowerCase().includes(searchTerm) ||
            employee.id.toLowerCase().includes(searchTerm)
        );
        showAutocompleteDropdown(employeeDropdown, filteredEmployees, 'employee');
    });

    // Item autocomplete
    itemSelect.addEventListener('input', () => {
        const searchTerm = itemSelect.value.toLowerCase();
        const filteredItems = itemsData.filter(item => 
            item.ItemName.toLowerCase().includes(searchTerm) ||
            item.ItemID.toString().includes(searchTerm)
        );
        showAutocompleteDropdown(itemDropdown, filteredItems, 'item');
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.autocomplete-container')) {
            hideAllDropdowns();
        }
    });
}

// Show autocomplete dropdown
function showAutocompleteDropdown(dropdown, items, type) {
    if (items.length === 0) {
        dropdown.innerHTML = '<div class="autocomplete-item">No results found</div>';
        dropdown.classList.add('show');
        return;
    }

    dropdown.innerHTML = items.map(item => {
        let displayText;
        let id;
        let name;
        
        switch(type) {
            case 'customer': {
                displayText = `${item.Name} (${item.CustomerID})`;
                id = item.CustomerID;
                name = item.Name;
                break;
            }
            case 'employee': {
                displayText = `${item.name} (${item.id})`;
                id = item.id;
                name = item.name;
                break;
            }
            case 'item': {
                displayText = `${item.ItemName} (ITM-${item.ItemID.toString().padStart(3, '0')}) - $${item.Price}`;
                id = item.ItemID;
                name = item.ItemName;
                break;
            }
        }

        return `<div class="autocomplete-item" data-id="${id}" data-name="${name}" data-type="${type}">${displayText}</div>`;
    }).join('');

    dropdown.classList.add('show');

    // Add click handlers to dropdown items
    const dropdownItems = dropdown.querySelectorAll('.autocomplete-item');
    for (const item of dropdownItems) {
        item.addEventListener('click', () => {
            const id = item.dataset.id;
            const name = item.dataset.name;
            const type = item.dataset.type;
            
            selectAutocompleteItem(type, id, name);
        });
    }
}

// Select autocomplete item
function selectAutocompleteItem(type, id, name) {
    switch(type) {
        case 'customer': {
            customerSelect.value = `${name} (${id})`;
            document.getElementById('customerId').value = id;
            break;
        }
        case 'employee': {
            employeeSelect.value = `${name} (${id})`;
            document.getElementById('employeeId').value = id;
            break;
        }
        case 'item': {
            itemSelect.value = `${name} (ITM-${id.toString().padStart(3, '0')})`;
            document.getElementById('itemId').value = id;
            // Set unit price
            const item = itemsData.find(i => i.ItemID === Number.parseInt(id, 10));
            if (item) {
                document.getElementById('unitPrice').value = item.Price;
            }
            break;
        }
    }
    
    hideAllDropdowns();
}

// Hide all dropdowns
function hideAllDropdowns() {
    const dropdowns = document.querySelectorAll('.autocomplete-dropdown');
    for (const dropdown of dropdowns) {
        dropdown.classList.remove('show');
    }
}

// Event Listeners
addOrderBtn.addEventListener('click', openAddOrderModal);
closeModal.addEventListener('click', closeModals);
closeViewModal.addEventListener('click', closeModals);
closeDeleteModal.addEventListener('click', closeModals);
cancelBtn.addEventListener('click', closeModals);
closeViewBtn.addEventListener('click', closeModals);
cancelDeleteBtn.addEventListener('click', closeModals);
confirmDeleteBtn.addEventListener('click', confirmDelete);
orderForm.addEventListener('submit', saveOrder);
addItemBtn.addEventListener('click', addItemToOrder);
searchInput.addEventListener('input', filterOrders);
statusFilter.addEventListener('change', filterOrders);

// Functions
function openAddOrderModal() {
    document.getElementById('modalTitle').textContent = "Add New Order";
    document.getElementById('orderId').value = "";
    customerSelect.value = "";
    document.getElementById('customerId').value = "";
    employeeSelect.value = "";
    document.getElementById('employeeId').value = "";
    document.getElementById('status').value = "Pending";
    document.getElementById('orderDate').value = new Date().toISOString().split('T')[0];
    itemSelect.value = "";
    document.getElementById('itemId').value = "";
    document.getElementById('quantity').value = "1";
    document.getElementById('unitPrice').value = "";
    
    // Reset items list
    currentOrderItems = [];
    renderOrderItems();
    
    orderModal.style.display = "flex";
}

function addItemToOrder() {
    const itemId = document.getElementById('itemId').value;
    const itemName = itemSelect.value.split(' (')[0]; // Extract name from autocomplete value
    const quantity = Number.parseInt(document.getElementById('quantity').value);
    const unitPrice = Number.parseFloat(document.getElementById('unitPrice').value);
    
    if (!itemId || !itemName || Number.isNaN(quantity) || Number.isNaN(unitPrice)) {
        alert("Please fill in all item details");
        return;
    }
    
    if (quantity <= 0) {
        alert("Quantity must be greater than 0");
        return;
    }
    
    if (unitPrice <= 0) {
        alert("Unit price must be greater than 0");
        return;
    }
    
    const total = quantity * unitPrice;
    
    // Add item to current order
    currentOrderItems.push({
        itemId,
        itemName,
        quantity,
        unitPrice,
        total
    });
    
    // Clear item form
    itemSelect.value = "";
    document.getElementById('itemId').value = "";
    document.getElementById('quantity').value = "1";
    document.getElementById('unitPrice').value = "";
    
    // Update items list display
    renderOrderItems();
}

function renderOrderItems() {
    if (currentOrderItems.length === 0) {
        itemsList.innerHTML = '<div class="no-items">No items added to this order</div>';
        document.getElementById('totalItems').textContent = '0';
        document.getElementById('totalAmount').textContent = '$0.00';
        return;
    }
    
    itemsList.innerHTML = '';
    
    let totalItems = 0;
    let totalAmount = 0;
    
    for (const [index, item] of currentOrderItems.entries()) {
        totalItems += item.quantity;
        totalAmount += item.total;
        
        const itemRow = document.createElement('div');
        itemRow.className = 'item-row';
        itemRow.innerHTML = `
            <div class="item-info">
                <span>${item.itemName} (${item.itemId})</span>
                <span>Qty: ${item.quantity}</span>
                <span>Price: $${item.unitPrice.toFixed(2)}</span>
                <span>Total: $${item.total.toFixed(2)}</span>
            </div>
            <button class="remove-item" onclick="removeItem(${index})">
                <i class="fas fa-times"></i>
            </button>
        `;
        itemsList.appendChild(itemRow);
    }
    
    // Update summary
    document.getElementById('totalItems').textContent = totalItems;
    document.getElementById('totalAmount').textContent = `$${totalAmount.toFixed(2)}`;
}

function removeItem(index) {
    currentOrderItems.splice(index, 1);
    renderOrderItems();
}

// API Functions
async function getCustomerDetails(customerId) {
    const response = await fetch(`/backend/api/orders/orders.php?customerId=${customerId}`);
    const data = await response.json();
    if (data.success) {
        return data.data;
    }
    alert(data.message);
    return null;
}

async function getEmployeeDetails(employeeId) {
    const response = await fetch(`/backend/api/orders/orders.php?employeeId=${employeeId}`);
    const data = await response.json();
    if (data.success) {
        return data.data;
    }
    alert(data.message);
    return null;
}

async function getItemDetails(itemId) {
    const response = await fetch(`/backend/api/orders/orders.php?itemId=${itemId}`);
    const data = await response.json();
    if (data.success) {
        return data.data;
    }
    alert(data.message);
    return null;
}

async function saveOrderToServer(orderData) {
    const method = orderData.order_id ? 'PUT' : 'POST';
    const response = await fetch('/backend/api/orders/orders.php', {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData)
    });
    return await response.json();
}

async function deleteOrderFromServer(orderId) {
    const response = await fetch('/backend/api/orders/orders.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ orderId: orderId })
    });
    return await response.json();
}

async function getOrderDetails(orderId) {
    const response = await fetch(`/backend/api/orders/orders.php?orderId=${orderId}`);
    const data = await response.json();
    if (data.success) {
        return data.data;
    }
    return { error: data.message };
}

async function getOrders(searchTerm = '', statusFilter = '') {
    let url = '/backend/api/orders/orders.php';
    const params = new URLSearchParams();

    if (searchTerm) params.append('search', searchTerm);
    if (statusFilter) params.append('status', statusFilter);

    if (params.toString()) url += `?${params.toString()}`;

    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        if (data.success) {
            return data.data || [];
        }
        throw new Error(data.message || 'Unknown error');
    } catch (error) {
        console.error('Error in getOrders:', error);
        throw error;
    }
}

// Save Order Handler
async function saveOrder(e) {
    e.preventDefault();

    const customerId = document.getElementById('customerId').value;
    const orderDate = document.getElementById('orderDate').value;
    const status = document.getElementById('status').value;
    const employeeId = document.getElementById('employeeId').value;
    const orderId = document.getElementById('orderId').value;

    if (!customerId || !orderDate || !status) {
        alert("Please fill in all required fields");
        return;
    }

    if (currentOrderItems.length === 0) {
        alert("Please add at least one item to the order");
        return;
    }

    const orderData = {
        order_id: orderId || null,
        customer_id: customerId,
        employee_id: employeeId || null,
        order_date: orderDate,
        status: status,
        items: currentOrderItems.map(item => ({
            item_id: item.itemId,
            quantity: item.quantity,
            unitPrice: item.unitPrice
        }))
    };

    try {
        const result = await saveOrderToServer(orderData);
        if (result.success) {
            alert("Order saved successfully!");
            closeModals();
            filterOrders();
        } else {
            alert(`Error saving order: ${result.message || 'Unknown error'}`);
        }
    } catch (error) {
        alert(`Error saving order: ${error.message}`);
    }
}

// Confirm Delete
async function confirmDelete() {
    const orderId = document.getElementById('deleteOrderId').textContent;

    try {
        // Show loading state
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const originalText = confirmDeleteBtn.innerHTML;
        confirmDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        confirmDeleteBtn.disabled = true;

        const result = await deleteOrderFromServer(orderId);
        if (result.success) {
            // Show success message as requested
            alert("Waad ku gulaysatay in aad order delete garayso!");
            
            // Close the modal
            closeModals();
            
            // Automatically refresh the data as requested
            await filterOrders();
        } else {
            alert(`Error deleting order: ${result.message || 'Unknown error'}`);
        }
    } catch (error) {
        alert(`Error deleting order: ${error.message}`);
    } finally {
        // Restore button state
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        confirmDeleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
        confirmDeleteBtn.disabled = false;
    }
}

// Filter Orders
async function filterOrders() {
    const searchTerm = searchInput.value.toLowerCase();
    const statusFilterValue = statusFilter.value;

    try {
        const orders = await getOrders(searchTerm, statusFilterValue);
        updateOrdersTable(orders);
    } catch (error) {
        console.error("Error filtering orders:", error);
        // Show error message in table
        const tbody = document.querySelector('.sales-orders-table tbody');
        tbody.innerHTML = '<tr><td colspan="8">Error loading orders</td></tr>';
    }
}

// Update Orders Table
function updateOrdersTable(orders) {
    const tbody = document.querySelector('.orders-table tbody');
    
    // First validate the input data
    if (!Array.isArray(orders)) {
        console.error('Invalid orders data:', orders);
        tbody.innerHTML = '<tr><td colspan="8">Error loading orders</td></tr>';
        return;
    }

    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8">No orders found</td></tr>';
        return;
    }

    tbody.innerHTML = orders.map(order => {
        // Safely handle all properties with defaults
        const orderId = order.OrderID ? order.OrderID.toString().padStart(4, '0') : '0000';
        const customerId = order.CustomerID ? order.CustomerID.toString().padStart(3, '0') : '000';
        const customerName = order.CustomerName || 'Unknown Customer';
        const itemsCount = order.ItemsCount || 0;
        const employeeName = order.EmployeeName || 'N/A';
        const employeeId = order.EmployeeID ? order.EmployeeID.toString().padStart(3, '0') : '000';
        
        // Safely handle numeric values
        const totalAmount = typeof order.TotalAmount === 'number' ? order.TotalAmount :
                           typeof order.TotalAmount === 'string' ? Number.parseFloat(order.TotalAmount) :
                           0;
        const formattedAmount = !Number.isNaN(totalAmount) ? totalAmount.toFixed(2) : '0.00';
        
        const status = order.Status || 'Unknown';
        const orderDate = order.OrderDate ? order.OrderDate.split(' ')[0] : 'N/A';

        return `
            <tr>
                <td>ORD-${orderId}</td>
                <td>${customerName} (CUST-${customerId})</td>
                <td>${itemsCount} items</td>
                <td>${employeeName} ${order.EmployeeID ? `(EMP-${employeeId})` : ''}</td>
                <td>$${formattedAmount}</td>
                <td><span class="status-${status.toLowerCase()}">${status}</span></td>
                <td>${orderDate}</td>
                <td class="action-cell">
                    <button class="action-btn view-btn" onclick="viewOrder('${order.OrderID || ''}')">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <button class="action-btn edit-btn" onclick="editOrder('${order.OrderID || ''}')">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="action-btn delete-btn" onclick="deleteOrder('${order.OrderID || ''}')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

// View Order
async function viewOrder(id) {
    try {
        const order = await getOrderDetails(id);
        if (order.error) {
            alert(order.error);
            return;
        }

        document.getElementById('viewId').textContent = order.OrderID;
        document.getElementById('viewCustomer').textContent = 
            `${order.CustomerName} (CUST-${order.CustomerID.toString().padStart(3, '0')})`;

        if (order.EmployeeID) {
            document.getElementById('viewEmployee').textContent = 
                `${order.EmployeeName} (EMP-${order.EmployeeID.toString().padStart(3, '0')})`;
        } else {
            document.getElementById('viewEmployee').textContent = 'N/A';
        }

        document.getElementById('viewStatus').textContent = order.Status;
        document.getElementById('viewStatus').className = `status-${order.Status.toLowerCase()}`;
        document.getElementById('viewOrderDate').textContent = order.OrderDate;

        const viewItemsList = document.getElementById('viewItemsList');
        let totalAmount = 0;
        
        viewItemsList.innerHTML = order.items.map(item => {
            // Safely handle numeric values
            const unitPrice = typeof item.UnitPrice === 'number' ? item.UnitPrice :
                            typeof item.UnitPrice === 'string' ? Number.parseFloat(item.UnitPrice) :
                            0;
            const quantity = typeof item.Quantity === 'number' ? item.Quantity :
                           typeof item.Quantity === 'string' ? Number.parseInt(item.Quantity) :
                           1;
            const itemTotal = unitPrice * quantity;
            totalAmount += itemTotal;
            
            return `
                <tr>
                    <td>${item.ItemName} (ITM-${item.ItemID.toString().padStart(3, '0')})</td>
                    <td>${quantity}</td>
                    <td>$${!Number.isNaN(unitPrice) ? unitPrice.toFixed(2) : '0.00'}</td>
                    <td>$${!Number.isNaN(itemTotal) ? itemTotal.toFixed(2) : '0.00'}</td>
                </tr>
            `;
        }).join('');

        // Add a total row
        viewItemsList.innerHTML += `
            <tr class="total-row">
                <td colspan="3" class="text-right"><strong>Total:</strong></td>
                <td><strong>$${totalAmount.toFixed(2)}</strong></td>
            </tr>
        `;

        viewModal.style.display = "flex";
    } catch (error) {
        alert(`Error loading order details: ${error.message}`);
    }
}

// Edit Order
async function editOrder(id) {
    try {
        const order = await getOrderDetails(id);
        if (order.error) {
            alert(order.error);
            return;
        }

        document.getElementById('modalTitle').textContent = "Edit Order";
        document.getElementById('orderId').value = order.OrderID;
        
        // Set customer autocomplete field
        document.getElementById('customerId').value = order.CustomerID;
        document.getElementById('customerSelect').value = `${order.CustomerName} (${order.CustomerID})`;

        // Set employee autocomplete field
        if (order.EmployeeID) {
            document.getElementById('employeeId').value = order.EmployeeID;
            document.getElementById('employeeSelect').value = `${order.EmployeeName} (${order.EmployeeID})`;
        } else {
            document.getElementById('employeeId').value = '';
            document.getElementById('employeeSelect').value = '';
        }

        document.getElementById('status').value = order.Status;
        document.getElementById('orderDate').value = order.OrderDate.split(' ')[0];

        // Populate items
        currentOrderItems = order.items.map(item => ({
            itemId: item.ItemID,
            itemName: item.ItemName,
            quantity: typeof item.Quantity === 'number' ? item.Quantity : Number.parseInt(item.Quantity) || 1,
            unitPrice: typeof item.UnitPrice === 'number' ? item.UnitPrice : Number.parseFloat(item.UnitPrice) || 0,
            total: typeof item.TotalAmount === 'number' ? item.TotalAmount : Number.parseFloat(item.TotalAmount) || 0
        }));

        renderOrderItems();
        orderModal.style.display = "flex";
    } catch (error) {
        alert(`Error loading order for editing: ${error.message}`);
    }
}

// Delete Order
async function deleteOrder(id) {
    try {
        const order = await getOrderDetails(id);
        if (order.error) {
            alert(order.error);
            return;
        }

        document.getElementById('deleteOrderId').textContent = order.OrderID;
        document.getElementById('deleteCustomerName').textContent = order.CustomerName;
        deleteModal.style.display = "flex";
    } catch (error) {
        alert(`Error loading order for deletion: ${error.message}`);
    }
}

// Close Modals
function closeModals() {
    orderModal.style.display = "none";
    viewModal.style.display = "none";
    deleteModal.style.display = "none";
    currentOrderItems = [];
}

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === orderModal) orderModal.style.display = "none";
    if (e.target === viewModal) viewModal.style.display = "none";
    if (e.target === deleteModal) deleteModal.style.display = "none";
});






