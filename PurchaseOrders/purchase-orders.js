// DOM Elements
const addPurchaseOrderBtn = document.getElementById('addPurchaseOrderBtn');
const purchaseOrderModal = document.getElementById('purchaseOrderModal');
const viewModal = document.getElementById('viewModal');
const deleteModal = document.getElementById('deleteModal');
const closeModal = document.getElementById('closeModal');
const closeViewModal = document.getElementById('closeViewModal');
const closeDeleteModal = document.getElementById('closeDeleteModal');
const cancelBtn = document.getElementById('cancelBtn');
const closeViewBtn = document.getElementById('closeViewBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const purchaseOrderForm = document.getElementById('purchaseOrderForm');
const addItemBtn = document.getElementById('addItemBtn');
const itemsList = document.getElementById('itemsList');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');

// Autocomplete elements
const supplierSelect = document.getElementById('supplierSelect');
const supplierDropdown = document.getElementById('supplierDropdown');
const employeeSelect = document.getElementById('employeeSelect');
const employeeDropdown = document.getElementById('employeeDropdown');
const itemSelect = document.getElementById('itemSelect');
const itemDropdown = document.getElementById('itemDropdown');

// Dynamic data storage
let suppliersData = [];
let employeesData = [];
let itemsData = [];

// Current purchase order items
let currentPurchaseOrderItems = [];

// Load data on page load
document.addEventListener('DOMContentLoaded', () => {
    loadSuppliers();
    loadEmployees();
    loadItems();
    filterPurchaseOrders();
    setupAutocomplete();
    
    // Add event delegation for action buttons
    setupActionButtonListeners();
});

// Setup event delegation for action buttons
function setupActionButtonListeners() {
    // Find the table body element
    const tableBody = document.querySelector('.purchase-orders-table tbody');
    
    if (tableBody) {
        tableBody.addEventListener('click', (e) => {
            const target = e.target.closest('.action-btn');
            if (!target) return;
            
            const purchaseOrderId = target.dataset.purchaseOrderId;
            if (!purchaseOrderId) return;
            
            if (target.classList.contains('view-btn')) {
                viewPurchaseOrder(purchaseOrderId);
            } else if (target.classList.contains('edit-btn')) {
                editPurchaseOrder(purchaseOrderId);
            } else if (target.classList.contains('delete-btn')) {
                deletePurchaseOrder(purchaseOrderId);
            }
        });
    }
}

// Load suppliers from API
async function loadSuppliers() {
    try {
        const response = await fetch(buildApiUrl('suppliers/suppliers.php'));
        const data = await response.json();
        if (data.success) {
            suppliersData = data.data;
        }
    } catch (error) {
        console.error('Error loading suppliers:', error);
    }
}

// Load employees from API
async function loadEmployees() {
    try {
        const response = await fetch(buildApiUrl('employees/employees.php'));
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
        const response = await fetch(buildApiUrl('items/items.php'));
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
    // Supplier autocomplete
    supplierSelect.addEventListener('input', () => {
        const searchTerm = supplierSelect.value.toLowerCase();
        const filteredSuppliers = suppliersData.filter(supplier => 
            supplier.name.toLowerCase().includes(searchTerm) ||
            supplier.id.toString().includes(searchTerm)
        );
        showAutocompleteDropdown(supplierDropdown, filteredSuppliers, 'supplier');
    });

    // Employee autocomplete
    employeeSelect.addEventListener('input', () => {
        const searchTerm = employeeSelect.value.toLowerCase();
        const filteredEmployees = employeesData.filter(employee => 
            employee.name.toLowerCase().includes(searchTerm) ||
            employee.id.toString().includes(searchTerm)
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
            case 'supplier': {
                displayText = `${item.name} (SUP-${item.id.toString().padStart(3, '0')})`;
                id = item.id;
                name = item.name;
                break;
            }
            case 'employee': {
                displayText = `${item.name} (EMP-${item.id.toString().padStart(3, '0')})`;
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
        case 'supplier': {
            supplierSelect.value = `${name} (SUP-${id.toString().padStart(3, '0')})`;
            document.getElementById('supplierId').value = id;
            break;
        }
        case 'employee': {
            employeeSelect.value = `${name} (EMP-${id.toString().padStart(3, '0')})`;
            document.getElementById('employeeId').value = id;
            break;
        }
        case 'item': {
            itemSelect.value = `${name} (ITM-${id.toString().padStart(3, '0')})`;
            document.getElementById('itemId').value = id;
            // Set unit price as placeholder/suggestion, but keep it editable
            const item = itemsData.find(i => i.ItemID === Number.parseInt(id, 10));
            if (item) {
                const unitPriceInput = document.getElementById('unitPrice');
                unitPriceInput.value = ''; // Clear first
                unitPriceInput.placeholder = `Suggested: $${item.Price}`;
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
addPurchaseOrderBtn.addEventListener('click', openAddPurchaseOrderModal);
closeModal.addEventListener('click', closeModals);
closeViewModal.addEventListener('click', closeModals);
closeDeleteModal.addEventListener('click', closeModals);
cancelBtn.addEventListener('click', closeModals);
closeViewBtn.addEventListener('click', closeModals);
cancelDeleteBtn.addEventListener('click', closeModals);
confirmDeleteBtn.addEventListener('click', confirmDelete);
purchaseOrderForm.addEventListener('submit', savePurchaseOrder);
addItemBtn.addEventListener('click', addItemToPurchaseOrder);
searchInput.addEventListener('input', filterPurchaseOrders);
statusFilter.addEventListener('change', filterPurchaseOrders);

// Functions
function openAddPurchaseOrderModal() {
    document.getElementById('modalTitle').textContent = "Add New Purchase Order";
    document.getElementById('purchaseOrderId').value = "";
    supplierSelect.value = "";
    document.getElementById('supplierId').value = "";
    employeeSelect.value = "";
    document.getElementById('employeeId').value = "";
    document.getElementById('status').value = "Pending";
    document.getElementById('orderDate').value = new Date().toISOString().split('T')[0];
    itemSelect.value = "";
    document.getElementById('itemId').value = "";
    document.getElementById('quantity').value = "1";
    document.getElementById('unitPrice').value = "";
    
    // Reset items list
    currentPurchaseOrderItems = [];
    renderPurchaseOrderItems();
    
    purchaseOrderModal.style.display = "flex";
}

function addItemToPurchaseOrder() {
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
    
    // Add item to current purchase order
    currentPurchaseOrderItems.push({
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
    renderPurchaseOrderItems();
}

function renderPurchaseOrderItems() {
    if (currentPurchaseOrderItems.length === 0) {
        itemsList.innerHTML = '<div class="no-items">No items added to this purchase order</div>';
        document.getElementById('totalItems').textContent = '0';
        document.getElementById('totalAmount').textContent = '$0.00';
        return;
    }
    
    itemsList.innerHTML = '';
    
    let totalItems = 0;
    let totalAmount = 0;
    
    for (const [index, item] of currentPurchaseOrderItems.entries()) {
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
    currentPurchaseOrderItems.splice(index, 1);
    renderPurchaseOrderItems();
}

// API Functions
async function getSupplierDetails(supplierId) {
    const response = await fetch(buildApiUrl(`purchase-orders/purchase-orders.php?supplierId=${supplierId}`));
    const data = await response.json();
    if (data.success) {
        return data.data;
    }
    alert(data.message);
    return null;
}

async function getEmployeeDetails(employeeId) {
    const response = await fetch(buildApiUrl(`purchase-orders/purchase-orders.php?employeeId=${employeeId}`));
    const data = await response.json();
    if (data.success) {
        return data.data;
    }
    alert(data.message);
    return null;
}

async function getItemDetails(itemId) {
    const response = await fetch(buildApiUrl(`purchase-orders/purchase-orders.php?itemId=${itemId}`));
    const data = await response.json();
    if (data.success) {
        return data.data;
    }
    alert(data.message);
    return null;
}

async function savePurchaseOrderToServer(purchaseOrderData) {
    const method = purchaseOrderData.purchase_order_id ? 'PUT' : 'POST';
    const response = await fetch(buildApiUrl('purchase-orders/purchase-orders.php'), {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(purchaseOrderData)
    });
    return await response.json();
}

async function deletePurchaseOrderFromServer(purchaseOrderId) {
    const response = await fetch(buildApiUrl('purchase-orders/purchase-orders.php'), {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ purchaseOrderId: purchaseOrderId })
    });
    return await response.json();
}

async function getPurchaseOrderDetails(purchaseOrderId) {
    const response = await fetch(buildApiUrl(`purchase-orders/purchase-orders.php?purchaseOrderId=${purchaseOrderId}`));
    const data = await response.json();
    if (data.success) {
        return data.data;
    }
    return { error: data.message };
}

async function getPurchaseOrders(searchTerm = '', statusFilter = '') {
    let url = buildApiUrl('purchase-orders/purchase-orders.php');
    const params = new URLSearchParams();

    if (searchTerm) params.append('search', searchTerm);
    if (statusFilter) params.append('status', statusFilter);

    if (params.toString()) url += `?${params.toString()}`;

    const response = await fetch(url);
    const data = await response.json();
    if (data.success) {
        return data.data;
    }
    return [];
}

// Save Purchase Order Handler
async function savePurchaseOrder(e) {
    e.preventDefault();

    const supplierId = document.getElementById('supplierId').value;
    const orderDate = document.getElementById('orderDate').value;
    const status = document.getElementById('status').value;
    const employeeId = document.getElementById('employeeId').value;
    const purchaseOrderId = document.getElementById('purchaseOrderId').value;

    if (!supplierId || !orderDate || !status) {
        alert("Please fill in all required fields");
        return;
    }

    if (currentPurchaseOrderItems.length === 0) {
        alert("Please add at least one item to the purchase order");
        return;
    }

    const purchaseOrderData = {
        purchase_order_id: purchaseOrderId || null,
        supplier_id: supplierId,
        employee_id: employeeId || null,
        order_date: orderDate,
        status: status,
        items: currentPurchaseOrderItems.map(item => ({
            item_id: item.itemId,
            quantity: item.quantity,
            unitPrice: item.unitPrice
        }))
    };

    try {
        const result = await savePurchaseOrderToServer(purchaseOrderData);
        if (result.success) {
            alert("Purchase order saved successfully!");
            closeModals();
            filterPurchaseOrders();
        } else {
            alert(`Error saving purchase order: ${result.message || 'Unknown error'}`);
        }
    } catch (error) {
        alert(`Error saving purchase order: ${error.message}`);
    }
}

// Confirm Delete
async function confirmDelete() {
    const purchaseOrderId = document.getElementById('deletePurchaseOrderId').textContent;

    try {
        // Show loading state
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const originalText = confirmDeleteBtn.innerHTML;
        confirmDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        confirmDeleteBtn.disabled = true;

        const result = await deletePurchaseOrderFromServer(purchaseOrderId);
        if (result.success) {
            // Show success message
            alert("Purchase order deleted successfully!");
            
            // Close the modal
            closeModals();
            
            // Automatically refresh the data
            await filterPurchaseOrders();
        } else {
            alert(`Error deleting purchase order: ${result.message || 'Unknown error'}`);
        }
    } catch (error) {
        alert(`Error deleting purchase order: ${error.message}`);
    } finally {
        // Restore button state
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        confirmDeleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
        confirmDeleteBtn.disabled = false;
    }
}

// Filter Purchase Orders
async function filterPurchaseOrders() {
    const searchTerm = searchInput.value.toLowerCase();
    const statusFilterValue = statusFilter.value;

    try {
        const purchaseOrders = await getPurchaseOrders(searchTerm, statusFilterValue);
        updatePurchaseOrdersTable(purchaseOrders);
    } catch (error) {
        console.error("Error filtering purchase orders:", error);
    }
}

// Update Purchase Orders Table
function updatePurchaseOrdersTable(purchaseOrders) {
    const tbody = document.querySelector('.purchase-orders-table tbody');
    
    // First validate the input data
    if (!Array.isArray(purchaseOrders)) {
        console.error('Invalid purchase orders data:', purchaseOrders);
        tbody.innerHTML = '<tr><td colspan="8">Error loading purchase orders</td></tr>';
        return;
    }

    if (purchaseOrders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8">No purchase orders found</td></tr>';
        return;
    }

    tbody.innerHTML = purchaseOrders.map(purchaseOrder => {
        // Safely handle all properties with defaults
        const purchaseOrderId = purchaseOrder.PurchaseOrderID ? purchaseOrder.PurchaseOrderID.toString().padStart(4, '0') : '0000';
        const supplierId = purchaseOrder.SupplierID ? purchaseOrder.SupplierID.toString().padStart(3, '0') : '000';
        const supplierName = purchaseOrder.SupplierName || 'Unknown Supplier';
        const itemsCount = purchaseOrder.ItemsCount || 0;
        const employeeName = purchaseOrder.EmployeeName || 'N/A';
        const employeeId = purchaseOrder.EmployeeID ? purchaseOrder.EmployeeID.toString().padStart(3, '0') : '000';
        
        // Safely handle numeric values
        const totalAmount = typeof purchaseOrder.TotalAmount === 'number' ? purchaseOrder.TotalAmount :
                           typeof purchaseOrder.TotalAmount === 'string' ? Number.parseFloat(purchaseOrder.TotalAmount) :
                           0;
        const formattedAmount = !Number.isNaN(totalAmount) ? totalAmount.toFixed(2) : '0.00';
        
        const status = purchaseOrder.Status || 'Unknown';
        const orderDate = purchaseOrder.OrderDate ? purchaseOrder.OrderDate.split(' ')[0] : 'N/A';

        return `
            <tr>
                <td>PO-${purchaseOrderId}</td>
                <td>${supplierName} (SUP-${supplierId})</td>
                <td>${itemsCount} items</td>
                <td>${employeeName} ${purchaseOrder.EmployeeID ? `(EMP-${employeeId})` : ''}</td>
                <td>$${formattedAmount}</td>
                <td><span class="status-${status.toLowerCase()}">${status}</span></td>
                <td>${orderDate}</td>
                <td class="action-cell">
                    <button class="action-btn view-btn" data-purchase-order-id="${purchaseOrder.PurchaseOrderID || ''}">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <button class="action-btn edit-btn" data-purchase-order-id="${purchaseOrder.PurchaseOrderID || ''}">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="action-btn delete-btn" data-purchase-order-id="${purchaseOrder.PurchaseOrderID || ''}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

// View Purchase Order
async function viewPurchaseOrder(id) {
    try {
        const purchaseOrder = await getPurchaseOrderDetails(id);
        if (purchaseOrder.error) {
            alert(purchaseOrder.error);
            return;
        }

        document.getElementById('viewId').textContent = purchaseOrder.PurchaseOrderID;
        document.getElementById('viewSupplier').textContent = 
            `${purchaseOrder.SupplierName} (SUP-${purchaseOrder.SupplierID.toString().padStart(3, '0')})`;

        if (purchaseOrder.EmployeeID) {
            document.getElementById('viewEmployee').textContent = 
                `${purchaseOrder.EmployeeName} (EMP-${purchaseOrder.EmployeeID.toString().padStart(3, '0')})`;
        } else {
            document.getElementById('viewEmployee').textContent = 'N/A';
        }

        document.getElementById('viewStatus').textContent = purchaseOrder.Status;
        document.getElementById('viewStatus').className = `status-${purchaseOrder.Status.toLowerCase()}`;
        document.getElementById('viewOrderDate').textContent = purchaseOrder.OrderDate;

        const viewItemsList = document.getElementById('viewItemsList');
        let totalAmount = 0;
        
        viewItemsList.innerHTML = purchaseOrder.items.map(item => {
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
        alert(`Error loading purchase order details: ${error.message}`);
    }
}

// Edit Purchase Order
async function editPurchaseOrder(id) {
    try {
        const purchaseOrder = await getPurchaseOrderDetails(id);
        if (purchaseOrder.error) {
            alert(purchaseOrder.error);
            return;
        }

        document.getElementById('modalTitle').textContent = "Edit Purchase Order";
        document.getElementById('purchaseOrderId').value = purchaseOrder.PurchaseOrderID;
        
        // Set supplier autocomplete field
        document.getElementById('supplierId').value = purchaseOrder.SupplierID;
        document.getElementById('supplierSelect').value = `${purchaseOrder.SupplierName} (SUP-${purchaseOrder.SupplierID.toString().padStart(3, '0')})`;

        // Set employee autocomplete field
        if (purchaseOrder.EmployeeID) {
            document.getElementById('employeeId').value = purchaseOrder.EmployeeID;
            document.getElementById('employeeSelect').value = `${purchaseOrder.EmployeeName} (EMP-${purchaseOrder.EmployeeID.toString().padStart(3, '0')})`;
        } else {
            document.getElementById('employeeId').value = '';
            document.getElementById('employeeSelect').value = '';
        }

        document.getElementById('status').value = purchaseOrder.Status;
        document.getElementById('orderDate').value = purchaseOrder.OrderDate.split(' ')[0];

        // Populate items
        currentPurchaseOrderItems = purchaseOrder.items.map(item => ({
            itemId: item.ItemID,
            itemName: item.ItemName,
            quantity: typeof item.Quantity === 'number' ? item.Quantity : Number.parseInt(item.Quantity) || 1,
            unitPrice: typeof item.UnitPrice === 'number' ? item.UnitPrice : Number.parseFloat(item.UnitPrice) || 0,
            total: typeof item.TotalAmount === 'number' ? item.TotalAmount : Number.parseFloat(item.TotalAmount) || 0
        }));

        renderPurchaseOrderItems();
        purchaseOrderModal.style.display = "flex";
    } catch (error) {
        alert(`Error loading purchase order for editing: ${error.message}`);
    }
}

// Delete Purchase Order
async function deletePurchaseOrder(id) {
    try {
        const purchaseOrder = await getPurchaseOrderDetails(id);
        if (purchaseOrder.error) {
            alert(purchaseOrder.error);
            return;
        }

        document.getElementById('deletePurchaseOrderId').textContent = purchaseOrder.PurchaseOrderID;
        document.getElementById('deleteSupplierName').textContent = purchaseOrder.SupplierName;
        deleteModal.style.display = "flex";
    } catch (error) {
        alert(`Error loading purchase order for deletion: ${error.message}`);
    }
}

// Close Modals
function closeModals() {
    purchaseOrderModal.style.display = "none";
    viewModal.style.display = "none";
    deleteModal.style.display = "none";
    currentPurchaseOrderItems = [];
}

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === purchaseOrderModal) purchaseOrderModal.style.display = "none";
    if (e.target === viewModal) viewModal.style.display = "none";
    if (e.target === deleteModal) deleteModal.style.display = "none";
}); 