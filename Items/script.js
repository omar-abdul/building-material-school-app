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
const categoryIdInput = document.getElementById('categoryId');
const categoryNameInput = document.getElementById('categoryName');
const supplierIdInput = document.getElementById('supplierId');
const supplierNameInput = document.getElementById('supplierName');
const employeeIdInput = document.getElementById('employeeId');
const employeeNameInput = document.getElementById('employeeName');

// Current item to be deleted
let currentItemToDelete = null;

// Event Listeners
addItemBtn.addEventListener('click', openAddItemModal);
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
categoryIdInput.addEventListener('change', fetchCategoryDetails);
supplierIdInput.addEventListener('change', fetchSupplierDetails);
employeeIdInput.addEventListener('change', fetchEmployeeDetails);

// Load items when page loads
document.addEventListener('DOMContentLoaded', loadItems);

// Functions
function loadItems() {
    fetch('/backend/api/items/items.php')
        .then(response => {
            console.log(response);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
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
    document.getElementById('modalTitle').textContent = "Add New Item";
    document.getElementById('itemId').value = "";
    document.getElementById('itemName').value = "";
    document.getElementById('itemPrice').value = "";
    document.getElementById('itemQuantity').value = "0";
    document.getElementById('categoryId').value = "";
    document.getElementById('categoryName').value = "";
    document.getElementById('supplierId').value = "";
    document.getElementById('supplierName').value = "";
    document.getElementById('employeeId').value = "";
    document.getElementById('employeeName').value = "";
    document.getElementById('note').value = "";
    document.getElementById('description').value = "";
    document.getElementById('createdDate').value = new Date().toISOString().split('T')[0];
    itemModal.style.display = "flex";
}

function editItem(id) {
    fetch(`/backend/api/items/items.php?itemId=${id}`)
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
                document.getElementById('categoryName').value = item.CategoryName;
                document.getElementById('supplierId').value = item.SupplierID;
                document.getElementById('supplierName').value = item.SupplierName;
                document.getElementById('employeeId').value = item.RegisteredByEmployeeID;
                document.getElementById('employeeName').value = item.EmployeeName;
                document.getElementById('note').value = item.Note || '';
                document.getElementById('description').value = item.Description || '';
                document.getElementById('createdDate').value = item.CreatedDate.split(' ')[0];
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
    fetch(`/backend/api/items/items.php?itemId=${id}`)
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
                document.getElementById('viewSupplier').textContent = `${item.SupplierName} (SUP-${item.SupplierID})`;
                document.getElementById('viewEmployee').textContent = `${item.EmployeeName} (EMP-${item.RegisteredByEmployeeID})`;
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

    fetch('/backend/api/items/items.php', {
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

function fetchCategoryDetails() {
    const categoryId = categoryIdInput.value;
    if (!categoryId) {
        categoryNameInput.value = '';
        return;
    }
    
    fetch(`/backend/api/items/items.php?categoryId=${categoryId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                categoryNameInput.value = data.data.categoryName || '';
            } else {
                categoryNameInput.value = '';
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching category:', error);
            categoryNameInput.value = '';
        });
}

function fetchSupplierDetails() {
    const supplierId = supplierIdInput.value;
    if (!supplierId) {
        supplierNameInput.value = '';
        return;
    }
    
    fetch(`/backend/api/items/items.php?supplierId=${supplierId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                supplierNameInput.value = data.data.supplierName || '';
            } else {
                supplierNameInput.value = '';
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching supplier:', error);
            supplierNameInput.value = '';
        });
}

function fetchEmployeeDetails() {
    const employeeId = employeeIdInput.value;
    if (!employeeId) {
        employeeNameInput.value = '';
        return;
    }
    
    fetch(`/backend/api/items/items.php?employeeId=${employeeId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                employeeNameInput.value = data.data.employeeName || '';
            } else {
                employeeNameInput.value = '';
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching employee:', error);
            employeeNameInput.value = '';
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
        SupplierID: document.getElementById('supplierId').value,
        RegisteredByEmployeeID: document.getElementById('employeeId').value,
        Note: document.getElementById('note').value,
        Description: document.getElementById('description').value,
        CreatedDate: document.getElementById('createdDate').value
    };
    
    // Add ItemID if we're updating
    if (id) {
        formData.ItemID = id;
    }
    
    // Validate required fields
    if (!formData.ItemName || !formData.CategoryID || !formData.SupplierID || !formData.RegisteredByEmployeeID) {
        alert("Please fill in all required fields");
        return;
    }
    
    // Determine URL and method
    const method = id ? 'PUT' : 'POST';
    const url = '/backend/api/items/items.php';
    
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
    
    let url = '/backend/api/items/items.php';
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
    
    fetch('/backend/api/items/items.php', {
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

// Toggle dropdown when clicking the report button
document.querySelector('.sidebar-report-btn').addEventListener('click', function(e) {
    e.preventDefault();
    const dropdown = this.closest('.report-dropdown');
    dropdown.classList.toggle('active');
});

// Close dropdown when clicking outside 
document.addEventListener('click', (e) => {
    if (!e.target.closest('.sidebar-report-btn') && !e.target.closest('.report-dropdown-content')) {
        const dropdowns = document.querySelectorAll('.report-dropdown');
        for (const dropdown of dropdowns) {
            dropdown.classList.remove('active');
        }
    }
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
