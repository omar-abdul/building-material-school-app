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
    fetch('/backend/api/items/items.php?action=getItems')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (Array.isArray(data)) {
                renderItems(data);
            } else {
                console.error('Invalid data format:', data);
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
    
    // biome-ignore lint/complexity/noForEach: <explanation>
        items.forEach(item => {
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td>ITM-${item.ItemID}</td>
            <td>${item.ItemName}</td>
            <td>${item.Price ? `$${item.Price}` : 'N/A'}</td>
            <td>${item.CategoryName}</td>
            <td>${item.SupplierName}</td>
            <td>${item.EmployeeName}</td>
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
    });
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
    fetch(`/backend/api/items/items.php?action=getItem&itemId=${id}`)
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
            
            document.getElementById('modalTitle').textContent = "Edit Item";
            document.getElementById('itemId').value = data.ItemID;
            document.getElementById('itemName').value = data.ItemName;
            document.getElementById('itemPrice').value = data.Price || '';
            document.getElementById('categoryId').value = data.CategoryID;
            document.getElementById('categoryName').value = data.CategoryName;
            document.getElementById('supplierId').value = data.SupplierID;
            document.getElementById('supplierName').value = data.SupplierName;
            document.getElementById('employeeId').value = data.RegisteredByEmployeeID;
            document.getElementById('employeeName').value = data.EmployeeName;
            document.getElementById('note').value = data.Note || '';
            document.getElementById('description').value = data.Description || '';
            document.getElementById('createdDate').value = data.CreatedDate.split(' ')[0];
            itemModal.style.display = "flex";
        })
        .catch(error => {
            console.error('Error fetching item:', error);
            alert('Error fetching item details');
        });
}

function viewItem(id) {
    fetch(`/backend/api/items/items.php?action=getItem&itemId=${id}`)
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
            
            document.getElementById('viewId').textContent = `ITM-${data.ItemID}`;
            document.getElementById('viewName').textContent = data.ItemName;
            document.getElementById('viewPrice').textContent = data.Price ? `$${data.Price}` : 'N/A';
            document.getElementById('viewCategory').textContent = `${data.CategoryName} (CAT-${data.CategoryID})`;
            document.getElementById('viewSupplier').textContent = `${data.SupplierName} (SUP-${data.SupplierID})`;
            document.getElementById('viewEmployee').textContent = `${data.EmployeeName} (EMP-${data.RegisteredByEmployeeID})`;
            document.getElementById('viewNote').textContent = data.Note || 'N/A';
            document.getElementById('viewDescription').textContent = data.Description || 'N/A';
            document.getElementById('viewDate').textContent = formatDate(data.CreatedDate);
            viewModal.style.display = "flex";
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

    fetch(`/backend/api/items/items.php?action=deleteItem&itemId=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network error');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
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
    
    fetch(`/backend/api/items/items.php?action=getCategoryDetails&categoryId=${categoryId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                categoryNameInput.value = '';
                alert(data.error);
            } else {
                categoryNameInput.value = data.categoryName || '';
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
    
    fetch(`/backend/api/items/items.php?action=getSupplierDetails&supplierId=${supplierId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                supplierNameInput.value = '';
                alert(data.error);
            } else {
                supplierNameInput.value = data.supplierName || '';
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
    
    fetch(`/backend/api/items/items.php?action=getEmployeeDetails&employeeId=${employeeId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                employeeNameInput.value = '';
                alert(data.error);
            } else {
                employeeNameInput.value = data.employeeName || '';
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
        Price: document.getElementById('itemPrice').value, // Added Price field
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
    const url = `/backend/api/items/items.php?action=${id ? 'updateItem' : 'addItem'}`;
    
    // Send request
    fetch(url, {
        method: 'POST',
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
        if (data.status === 'error') {
            throw new Error(data.message);
        }
        if (data.status === 'success') {
            loadItems();
            closeModals();
            alert(data.message);
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
    
    fetch(`/backend/api/items/items.php?action=getItems&search=${encodeURIComponent(searchTerm)}&categoryFilter=${encodeURIComponent(categoryFilterValue)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            renderItems(data);
        })
        .catch(error => {
            console.error('Error filtering items:', error);
        });
}

function confirmDelete() {
    if (!currentItemToDelete) return;
    
    fetch(`/backend/api/items/items.php?action=deleteItem&itemId=${currentItemToDelete}`)
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
                alert(data.error || 'Failed to delete item');
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
        // biome-ignore lint/complexity/noForEach: <explanation>
        document.querySelectorAll('.report-dropdown').forEach(dropdown => {
            dropdown.classList.remove('active');
        });
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
