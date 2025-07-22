// DOM Elements
const addSupplierBtn = document.getElementById('addSupplierBtn');
const supplierModal = document.getElementById('supplierModal');
const viewModal = document.getElementById('viewModal');
const deleteModal = document.getElementById('deleteModal');
const closeModal = document.getElementById('closeModal');
const closeViewModal = document.getElementById('closeViewModal');
const closeDeleteModal = document.getElementById('closeDeleteModal');
const cancelBtn = document.getElementById('cancelBtn');
const closeViewBtn = document.getElementById('closeViewBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const supplierForm = document.getElementById('supplierForm');
const searchInput = document.getElementById('searchInput');
const viewItemsBtn = document.getElementById('viewItemsBtn');
const sendEmailBtn = document.getElementById('sendEmailBtn');

// Current supplier to be deleted
let currentSupplierToDelete = null;

// Event Listeners
addSupplierBtn.addEventListener('click', openAddSupplierModal);
closeModal.addEventListener('click', closeModals);
closeViewModal.addEventListener('click', closeModals);
closeDeleteModal.addEventListener('click', closeModals);
cancelBtn.addEventListener('click', closeModals);
closeViewBtn.addEventListener('click', closeModals);
cancelDeleteBtn.addEventListener('click', closeModals);
confirmDeleteBtn.addEventListener('click', confirmDelete);
supplierForm.addEventListener('submit', saveSupplier);
searchInput.addEventListener('input', filterSuppliers);
viewItemsBtn.addEventListener('click', viewSuppliedItems);
sendEmailBtn.addEventListener('click', sendEmailToSupplier);

// Load suppliers when page loads
document.addEventListener('DOMContentLoaded', loadSuppliers);

// Functions
function loadSuppliers() {
    fetch(buildApiUrl('suppliers/suppliers.php'))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderSuppliers(data.data);
            } else {
                console.error('Error loading suppliers:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function renderSuppliers(suppliers) {
    const tbody = document.querySelector('.suppliers-table tbody');
    tbody.innerHTML = '';
    
    for (const supplier of suppliers) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${supplier.supplierId}</td>
            <td>${supplier.name}</td>
            <td>${supplier.contactPerson}</td>
            <td>${supplier.phone}</td>
            <td>${supplier.email || 'N/A'}</td>
            <td>${supplier.address || 'N/A'}</td>
            <td>${supplier.dateAdded}</td>
            <td class="action-cell">
                <button class="action-btn view-btn" onclick="viewSupplier(${supplier.id})">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="action-btn edit-btn" onclick="editSupplier(${supplier.id})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="action-btn delete-btn" onclick="deleteSupplier(${supplier.id})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        `;
        tbody.appendChild(row);
    }
}

function openAddSupplierModal() {
    document.getElementById('modalTitle').textContent = "Add New Supplier";
    document.getElementById('supplierId').value = "";
    document.getElementById('supplierName').value = "";
    document.getElementById('contactPerson').value = "";
    document.getElementById('phone').value = "";
    document.getElementById('email').value = "";
    document.getElementById('address').value = "";
    supplierModal.style.display = "flex";
}

function editSupplier(id) {
    fetch(`buildApiUrl('suppliers/suppliers.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const supplier = data.data;
                document.getElementById(')modalTitle').textContent = "Edit Supplier";
                document.getElementById('supplierId').value = supplier.id;
                document.getElementById('supplierName').value = supplier.name;
                document.getElementById('contactPerson').value = supplier.contactPerson;
                document.getElementById('phone').value = supplier.phone;
                document.getElementById('email').value = supplier.email || '';
                document.getElementById('address').value = supplier.address || '';
                supplierModal.style.display = "flex";
            } else {
                alert(`Error loading supplier: ${data.message}`);
            }
        })
        .catch(error => console.error('Error:', error));
}

function viewSupplier(id) {
    fetch(`buildApiUrl('suppliers/suppliers.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const supplier = data.data;
                document.getElementById(')viewId').textContent = supplier.supplierId;
                document.getElementById('viewName').textContent = supplier.name;
                document.getElementById('viewContact').textContent = supplier.contactPerson;
                document.getElementById('viewPhone').textContent = supplier.phone;
                document.getElementById('viewEmail').textContent = supplier.email || "N/A";
                document.getElementById('viewAddress').textContent = supplier.address || "N/A";
                viewModal.style.display = "flex";
            } else {
                alert(`Error loading supplier: ${data.message}`);
            }
        })
        .catch(error => console.error('Error:', error));
}

function deleteSupplier(id) {
    currentSupplierToDelete = id;
    fetch(`buildApiUrl('suppliers/suppliers.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const supplier = data.data;
                document.getElementById(')deleteSupplierId').textContent = supplier.supplierId;
                document.getElementById('deleteSupplierName').textContent = supplier.name;
                deleteModal.style.display = "flex";
            } else {
                alert(`Error loading supplier: ${data.message}`);
            }
        })
        .catch(error => console.error('Error:', error));
}

function confirmDelete() {
    if (currentSupplierToDelete) {
        fetch(`buildApiUrl('suppliers/suppliers.php?id=${currentSupplierToDelete}`, {
            method: ')DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadSuppliers(); // Refresh the list
                closeModals();
            } else {
                alert(`Error deleting supplier: ${data.message}`);
            }
        })
        .catch(error => console.error('Error:', error));
        
        currentSupplierToDelete = null;
    }
}

function saveSupplier(e) {
    e.preventDefault();
    
    const id = document.getElementById('supplierId').value;
    const name = document.getElementById('supplierName').value;
    const contactPerson = document.getElementById('contactPerson').value;
    const phone = document.getElementById('phone').value;
    const email = document.getElementById('email').value;
    const address = document.getElementById('address').value;
    
    if (!name || !contactPerson || !phone) {
        alert("Please fill in all required fields");
        return;
    }
    
    const supplierData = {
        id: id,
        name: name,
        contactPerson: contactPerson,
        phone: phone,
        email: email,
        address: address
    };
    
    const method = id ? 'PUT' : 'POST';
    
    fetch(buildApiUrl('suppliers/suppliers.php'), {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(supplierData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadSuppliers(); // Refresh the list
            closeModals();
        } else {
            alert(`Error saving supplier: ${data.message}`);
        }
    })
    .catch(error => console.error('Error:', error));
}

function filterSuppliers() {
    const searchTerm = searchInput.value.trim();
    
    const url = searchTerm 
        ? `buildApiUrl('suppliers/suppliers.php?search=${encodeURIComponent(searchTerm)}`
        : ')buildApiUrl('suppliers/suppliers.php');
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderSuppliers(data.data);
            } else {
                console.error('Error filtering suppliers:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function viewSuppliedItems() {
    const supplierId = document.getElementById('viewId').textContent;
    alert(`Would show items supplied by ${supplierId} in a real application`);
    console.log(`Viewing items supplied by: ${supplierId}`);
}

function sendEmailToSupplier() {
    const supplierEmail = document.getElementById('viewEmail').textContent;
    if (supplierEmail && supplierEmail !== "N/A") {
        window.location.href = `mailto:${supplierEmail}`;
    } else {
        alert("This supplier doesn't have an email address");
    }
}

function closeModals() {
    supplierModal.style.display = "none";
    viewModal.style.display = "none";
    deleteModal.style.display = "none";
}

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === supplierModal) supplierModal.style.display = "none";
    if (e.target === viewModal) viewModal.style.display = "none";
    if (e.target === deleteModal) deleteModal.style.display = "none";
});

// Sign up modal functionality
function openSignUpModal(e) {
    e.preventDefault();
    // Add your sign up modal logic here
    console.log('Opening sign up modal...');
}

