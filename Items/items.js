// DOM Elements
const addItemBtn = document.getElementById('addItemBtn');
const itemModal = document.getElementById('itemModal');
const viewModal = document.getElementById('viewModal');
const deleteModal = document.getElementById('deleteModal');
const closeModal = document.getElementById('closeModal');
const closeViewModal = document.getElementById('closeViewModal');
const closeDeleteModal = document.getElementById('closeDeleteModal');
const cancelBtn = document.getElementById('cancelBtn');
const closeViewBtn = document.getElementById('closeViewBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const itemForm = document.getElementById('itemForm');
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const itemsTable = document.querySelector('.items-table tbody');

// Autocomplete elements
const categoryIdInput = document.getElementById('categoryId');
const categoryDropdown = document.getElementById('categoryDropdown');
const employeeIdInput = document.getElementById('employeeId');
const employeeDropdown = document.getElementById('employeeDropdown');

// Current item to be deleted
let currentItemToDelete = null;

// Event Listeners
console.log('Setting up event listeners');
console.log('addItemBtn element:', addItemBtn);

addItemBtn.addEventListener('click', () => {
    console.log('Add Item button clicked');
    openAddItemModal();
});

closeModal.addEventListener('click', closeModals);
closeViewModal.addEventListener('click', closeModals);
closeDeleteModal.addEventListener('click', closeModals);
cancelBtn.addEventListener('click', closeModals);
closeViewBtn.addEventListener('click', closeModals);
cancelDeleteBtn.addEventListener('click', closeModals);
confirmDeleteBtn.addEventListener('click', confirmDelete);
itemForm.addEventListener('submit', saveItem);
searchInput.addEventListener('input', filterItems);
categoryFilter.addEventListener('change', filterItems);

// Autocomplete event listeners
categoryIdInput.addEventListener('input', () => searchCategories(categoryIdInput.value));
employeeIdInput.addEventListener('input', () => searchEmployees(employeeIdInput.value));

// Close dropdowns when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.autocomplete-container')) {
        categoryDropdown.style.display = 'none';
        employeeDropdown.style.display = 'none';
    }
});

// Load items when page loads
document.addEventListener('DOMContentLoaded', () => {
    loadItems();
    loadCategoriesForFilter();
});

// Functions
function loadItems() {
    console.log('Loading items...');
    fetch(buildApiUrl('items/items.php'))
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Items data received:', data);
            if (data.success) {
                console.log('Rendering items:', data.data);
                renderItems(data.data);
            } else {
                console.error('Error loading items:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading items:', error);
        });
}

function renderItems(items) {
    itemsTable.innerHTML = '';
    
    if (!items || items.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="7" class="no-items">No items found</td>`;
        itemsTable.appendChild(row);
        return;
    }
    
    for (const item of items) {
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td>ITM-${item.ItemID}</td>
            <td>${item.ItemName}</td>
            <td>${item.Price ? `$${item.Price}` : 'N/A'}</td>
            <td>${item.CategoryName}</td>
            <td>${item.Quantity || 0}</td>
            <td>${formatDate(item.CreatedDate)}</td>
            <td class="action-cell">
                <button class="action-btn view-btn" onclick="viewItem(${item.ItemID})">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="action-btn edit-btn" onclick="editItem(${item.ItemID})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="action-btn delete-btn" onclick="deleteItem(${item.ItemID})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        `;
        
        itemsTable.appendChild(row);
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
}

function openAddItemModal() {
    console.log('openAddItemModal called');
    try {
        document.getElementById('modalTitle').textContent = "Add New Item";
        document.getElementById('itemId').value = "";
        document.getElementById('itemName').value = "";
        document.getElementById('itemPrice').value = "";
        document.getElementById('itemQuantity').value = "0";
        document.getElementById('categoryId').value = "";
        document.getElementById('employeeId').value = "";
        document.getElementById('note').value = "";
        document.getElementById('description').value = "";
        document.getElementById('createdDate').value = new Date().toISOString().split('T')[0];
        
        // Clear autocomplete dropdowns
        categoryDropdown.style.display = 'none';
        employeeDropdown.style.display = 'none';
        
        console.log('Setting modal display to flex');
        itemModal.style.display = "flex";
        console.log('Modal display set to:', itemModal.style.display);
    } catch (error) {
        console.error('Error in openAddItemModal:', error);
    }
}

function editItem(id) {
            fetch(buildApiUrl(`items/items.php?itemId=${id}`))
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const item = data.data;
                document.getElementById('modalTitle').textContent = "Edit Item";
                document.getElementById('itemId').value = item.ItemID;
                document.getElementById('itemName').value = item.ItemName;
                document.getElementById('itemPrice').value = item.Price || '';
                document.getElementById('itemQuantity').value = item.Quantity || 0;
                document.getElementById('categoryId').value = item.CategoryID;
                document.getElementById('employeeId').value = item.RegisteredByEmployeeID;
                document.getElementById('note').value = item.Note || '';
                document.getElementById('description').value = item.Description || '';
                document.getElementById('createdDate').value = item.CreatedDate.split(' ')[0];
                
                // Clear autocomplete dropdowns
                categoryDropdown.style.display = 'none';
                employeeDropdown.style.display = 'none';
                
                itemModal.style.display = "flex";
            } else {
                console.error('Error loading item:', data.message);
                alert('Error loading item details');
            }
        })
        .catch(error => {
            console.error('Error fetching item:', error);
            alert('Error fetching item details');
        });
}

function viewItem(id) {
            fetch(buildApiUrl(`items/items.php?itemId=${id}`))
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const item = data.data;
                document.getElementById('viewId').textContent = `ITM-${item.ItemID}`;
                document.getElementById('viewName').textContent = item.ItemName;
                document.getElementById('viewPrice').textContent = item.Price ? `$${item.Price}` : 'N/A';
                document.getElementById('viewCategory').textContent = `${item.CategoryName} (CAT-${item.CategoryID})`;
                document.getElementById('viewQuantity').textContent = item.Quantity || 0;
                document.getElementById('viewSupplier').textContent = item.SupplierName ? `${item.SupplierName} (SUP-${item.SupplierID})` : 'N/A';
                document.getElementById('viewEmployee').textContent = item.EmployeeName ? `${item.EmployeeName} (EMP-${item.RegisteredByEmployeeID})` : 'N/A';
                document.getElementById('viewNote').textContent = item.Note || 'N/A';
                document.getElementById('viewDescription').textContent = item.Description || 'N/A';
                document.getElementById('viewDate').textContent = formatDate(item.CreatedDate);
                viewModal.style.display = "flex";
            } else {
                console.error('Error loading item:', data.message);
                alert('Error loading item details');
            }
        })
        .catch(error => {
            console.error('Error fetching item:', error);
            alert('Error fetching item details');
        });
}

function deleteItem(id) {
    if (!confirm('Ma hubtaa inaad masaxdo item-kan?')) {
        return; // User clicked cancel
    }
    
            fetch(buildApiUrl(`items/items.php?itemId=${id}`), {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ itemId: id })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network error');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            loadItems(); // Reload the items list
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert(`Delete failed: ${error.message}`);
    });
}

function saveItem(e) {
    e.preventDefault();
    
    // Get form values
    const id = document.getElementById('itemId').value;
    const formData = {
        ItemName: document.getElementById('itemName').value,
        Price: document.getElementById('itemPrice').value,
        Quantity: document.getElementById('itemQuantity').value,
        CategoryID: document.getElementById('categoryId').value,
        RegisteredByEmployeeID: document.getElementById('employeeId').value,
        Note: document.getElementById('note').value,
        Description: document.getElementById('description').value,
        CreatedDate: document.getElementById('createdDate').value
    };
    
    console.log('Form data being sent:', formData);
    
    // Add ItemID if we're updating
    if (id) {
        formData.ItemID = id;
    }
    
    // Validate required fields
    if (!formData.ItemName || !formData.CategoryID) {
        alert("Please fill in all required fields");
        console.log('Validation failed - missing required fields');
        return;
    }
    
    // Determine URL and method
    const method = id ? 'PUT' : 'POST';
    const url = buildApiUrl('items/items.php');
    
    console.log('Sending request to:', url, 'with method:', method);
    console.log('Final form data:', formData);
    
    // Send request
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('API response:', data);
        if (data.success) {
            loadItems();
            closeModals();
            alert(data.message);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(`Error: ${error.message}`);
    });
}

function filterItems() {
    const searchTerm = searchInput.value;
    const categoryFilterValue = categoryFilter.value;
    
    let url = buildApiUrl('items/items.php');
    const params = new URLSearchParams();
    
    if (searchTerm) {
        params.append('search', searchTerm);
    }
    if (categoryFilterValue) {
        params.append('categoryFilter', categoryFilterValue);
    }
    
    if (params.toString()) {
        url += `?${params.toString()}`;
    }
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderItems(data.data);
            } else {
                console.error('Error filtering items:', data.message);
            }
        })
        .catch(error => {
            console.error('Error filtering items:', error);
        });
}

function confirmDelete() {
    if (!currentItemToDelete) return;
    
    fetch(buildApiUrl('items/items.php'), {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ itemId: currentItemToDelete })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            loadItems();
            closeModals();
        } else {
            alert(data.message || 'Failed to delete item');
        }
    })
    .catch(error => {
        console.error('Error deleting item:', error);
        alert('Error deleting item');
    });
}

function closeModals() {
    itemModal.style.display = "none";
    viewModal.style.display = "none";
    deleteModal.style.display = "none";
    currentItemToDelete = null;
}

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === itemModal) itemModal.style.display = "none";
    if (e.target === viewModal) viewModal.style.display = "none";
    if (e.target === deleteModal) deleteModal.style.display = "none";
});

// Autocomplete Functions
async function searchCategories(query) {
    if (query.length < 2) {
        categoryDropdown.style.display = 'none';
        return;
    }

    try {
        const response = await fetch(buildApiUrl(`categories/categories.php?search=${encodeURIComponent(query)}`));
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            categoryDropdown.innerHTML = '';
            for (const category of data.data) {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.textContent = `${category.CategoryName} (${category.CategoryID})`;
                item.onclick = () => {
                    // Extract numeric ID from CategoryID (e.g., "CAT-1" -> "1")
                    const numericId = category.CategoryID.replace('CAT-', '');
                    console.log('Category selected:', category.CategoryName, 'Original ID:', category.CategoryID, 'Numeric ID:', numericId);
                    categoryIdInput.value = numericId;
                    console.log('Category input value set to:', categoryIdInput.value);
                    categoryDropdown.style.display = 'none';
                };
                categoryDropdown.appendChild(item);
            }
            categoryDropdown.style.display = 'block';
        } else {
            categoryDropdown.style.display = 'none';
        }
    } catch (error) {
        console.error('Error searching categories:', error);
    }
}



async function searchEmployees(query) {
    if (query.length < 2) {
        employeeDropdown.style.display = 'none';
        return;
    }

    try {
        const response = await fetch(buildApiUrl(`employees/employees.php?search=${encodeURIComponent(query)}`));
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            employeeDropdown.innerHTML = '';
            for (const employee of data.data) {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.textContent = `${employee.EmployeeName} (${employee.EmployeeID})`;
                item.onclick = () => {
                    console.log('Employee selected:', employee.EmployeeName, 'ID:', employee.EmployeeID);
                    employeeIdInput.value = employee.EmployeeID;
                    console.log('Employee input value set to:', employeeIdInput.value);
                    employeeDropdown.style.display = 'none';
                };
                employeeDropdown.appendChild(item);
            }
            employeeDropdown.style.display = 'block';
        } else {
            employeeDropdown.style.display = 'none';
        }
    } catch (error) {
        console.error('Error searching employees:', error);
    }
}

async function loadCategoriesForFilter() {
    try {
        const response = await fetch(buildApiUrl('categories/categories.php'));
        const data = await response.json();
        
        if (data.success) {
            categoryFilter.innerHTML = '<option value="">Filter by Category</option>';
            for (const category of data.data) {
                const option = document.createElement('option');
                option.value = category.CategoryName;
                option.textContent = category.CategoryName;
                categoryFilter.appendChild(option);
            }
        }
    } catch (error) {
        console.error('Error loading categories for filter:', error);
    }
}
