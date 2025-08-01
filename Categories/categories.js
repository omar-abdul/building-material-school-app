// DOM Elements
const addCategoryBtn = document.getElementById('addCategoryBtn');
const categoryModal = document.getElementById('categoryModal');
const viewItemsModal = document.getElementById('viewItemsModal');
const deleteModal = document.getElementById('deleteModal');
const closeModal = document.getElementById('closeModal');
const closeViewItemsModal = document.getElementById('closeViewItemsModal');
const closeDeleteModal = document.getElementById('closeDeleteModal');
const cancelBtn = document.getElementById('cancelBtn');
const closeViewItemsBtn = document.getElementById('closeViewItemsBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const categoryForm = document.getElementById('categoryForm');
const searchInput = document.getElementById('searchInput');
const categoriesTable = document.querySelector('.categories-table tbody');

// Current category to be deleted
let currentCategoryToDelete = null;

// Initialize the page
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
});

// Event Listeners
addCategoryBtn.addEventListener('click', openAddCategoryModal);
closeModal.addEventListener('click', closeModals);
closeViewItemsModal.addEventListener('click', closeModals);
closeDeleteModal.addEventListener('click', closeModals);
cancelBtn.addEventListener('click', closeModals);
closeViewItemsBtn.addEventListener('click', closeModals);
cancelDeleteBtn.addEventListener('click', closeModals);
confirmDeleteBtn.addEventListener('click', confirmDelete);
categoryForm.addEventListener('submit', saveCategory);
searchInput.addEventListener('input', filterCategories);

// Functions
function loadCategories(search = '') {
    console.log('Loading categories...');
    fetch(buildApiUrl('categories/categories.php'))
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('API response:', data);
            
            if (data.error) {
                alert(data.error);
                return;
            }
            
            categoriesTable.innerHTML = '';
            
            // Handle both success and direct data formats
            const categories = data.success ? data.data : data;
            console.log('Categories to display:', categories);
            
            if (!categories || categories.length === 0) {
                categoriesTable.innerHTML = '<tr><td colspan="5" style="text-align: center;">No categories found</td></tr>';
                return;
            }
            
            for (const category of categories) {
                const row = document.createElement('tr');
                row.setAttribute('data-id', category.CategoryID); // Add data-id attribute
                row.innerHTML = `
                    <td>${category.CategoryID}</td>
                    <td>${category.CategoryName}</td>
                    <td>${category.Description || ''}</td>
                    <td>${category.CreatedDate}</td>
                    <td class="action-cell">
                        <button class="action-btn view-btn" onclick="viewItems('${category.CategoryID}')">
                            <i class="fas fa-eye"></i> View Items
                        </button>
                        <button class="action-btn edit-btn" onclick="editCategory('${category.CategoryID}')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="action-btn delete-btn" onclick="deleteCategory('${category.CategoryID}')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                `;
                categoriesTable.appendChild(row);
            }
            console.log('Categories loaded successfully');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load categories');
        });
}

function openAddCategoryModal() {
    document.getElementById('modalTitle').textContent = "Add New Category";
    document.getElementById('categoryId').value = "";
    document.getElementById('categoryName').value = "";
    document.getElementById('description').value = "";
    document.getElementById('DateAdded').value = new Date().toISOString().split('T')[0];
    categoryModal.style.display = "flex";
}

function editCategory(id) {
    // Find the row with matching data-id
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (!row) return;

    // Get values from the row
    const name = row.cells[1].textContent;
    const description = row.cells[2].textContent;
    const date = row.cells[3].textContent;

    // Fill the modal
    document.getElementById('modalTitle').textContent = "Edit Category";
    document.getElementById('categoryId').value = id;
    document.getElementById('categoryName').value = name;
    document.getElementById('description').value = description;
    document.getElementById('DateAdded').value = date;
    categoryModal.style.display = "flex";
}

function viewItems(id) {
    // Find the row with matching data-id to get category name
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (!row) return;
    
    const categoryName = row.cells[1].textContent;
    document.getElementById('categoryNameTitle').textContent = categoryName;
    
    fetch(buildApiUrl(`categories/categories.php?category_id=${id}&items=true`))
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.error) });
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message);
            }
            
            const itemsListBody = document.getElementById('itemsListBody');
            itemsListBody.innerHTML = "";
            
            if (data.data.length === 0) {
                itemsListBody.innerHTML = '<tr><td colspan="4">No items found in this category</td></tr>';
            } else {
                for (const item of data.data) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.ItemID}</td>
                        <td>${item.ItemName}</td>
                        <td>$${(Number.parseFloat(item.Price) || 0).toFixed(2)}</td>
                        <td>${item.StockQuantity || 0}</td>
                    `;
                    itemsListBody.appendChild(row);
                }
            }
            
            viewItemsModal.style.display = "flex";
        })
        .catch(error => {
            console.error('Error:', error);
            alert(`Error loading items: ${error.message}`);
        });
}

function deleteCategory(id) {
    // Find the row with matching data-id to get category name
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (!row) return;
    
    const categoryName = row.cells[1].textContent;
    currentCategoryToDelete = id;
    document.getElementById('deleteCategoryId').textContent = id;
    document.getElementById('deleteCategoryName').textContent = categoryName;
    deleteModal.style.display = "flex";
}

function confirmDelete() {
    if (!currentCategoryToDelete) return;
    
    const formData = new FormData();
    formData.append('category_id', currentCategoryToDelete);
    
    fetch(buildApiUrl('categories/categories.php'), {
        method: 'DELETE',
        body: JSON.stringify({
            category_id: currentCategoryToDelete
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        
        alert(data.message);
        loadCategories();
        closeModals();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete category');
    });
}

function saveCategory(e) {
    e.preventDefault();
    
    const id = document.getElementById('categoryId').value;
    const name = document.getElementById('categoryName').value;
    const description = document.getElementById('description').value;
    
    if (!name) {
        alert("Please enter category name");
        return;
    }

    fetch(buildApiUrl('categories/categories.php'), {
        method: 'POST',
        body: JSON.stringify({
            category_name: name,
            description: description,
            category_id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        
        alert(data.message);
        loadCategories();
        closeModals();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save category');
    });
}

function filterCategories() {
    const searchTerm = searchInput.value.trim();
    loadCategories(searchTerm);
}

function closeModals() {
    categoryModal.style.display = "none";
    viewItemsModal.style.display = "none";
    deleteModal.style.display = "none";
    currentCategoryToDelete = null;
}

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === categoryModal) categoryModal.style.display = "none";
    if (e.target === viewItemsModal) viewItemsModal.style.display = "none";
    if (e.target === deleteModal) deleteModal.style.display = "none";
});