// DOM Elements (same as before)
const addInventoryBtn = document.getElementById('addInventoryBtn');
const inventoryModal = document.getElementById('inventoryModal');
const viewModal = document.getElementById('viewModal');
const deleteModal = document.getElementById('deleteModal');
const closeModal = document.getElementById('closeModal');
const closeViewModal = document.getElementById('closeViewModal');
const closeDeleteModal = document.getElementById('closeDeleteModal');
const cancelBtn = document.getElementById('cancelBtn');
const closeViewBtn = document.getElementById('closeViewBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const inventoryForm = document.getElementById('inventoryForm');
const searchInput = document.getElementById('searchInput');

// Current inventory to be deleted
let currentInventoryToDelete = null;

// Event Listeners (same as before)
addInventoryBtn.addEventListener('click', openAddInventoryModal);
closeModal.addEventListener('click', closeModals);
closeViewModal.addEventListener('click', closeModals);
closeDeleteModal.addEventListener('click', closeModals);
cancelBtn.addEventListener('click', closeModals);
closeViewBtn.addEventListener('click', closeModals);
cancelDeleteBtn.addEventListener('click', closeModals);
confirmDeleteBtn.addEventListener('click', confirmDelete);
inventoryForm.addEventListener('submit', saveInventory);
searchInput.addEventListener('input', filterInventory);

// Load inventory when page loads
document.addEventListener('DOMContentLoaded', loadInventory);

// Functions
function loadInventory() {
    fetch('backend.php?action=get_inventory')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                renderInventoryTable(data.data);
            } else {
                console.error('Error loading inventory:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}




// Ku dar event listener inputka Item ID
document.getElementById('itemId').addEventListener('change', function() {
    const itemId = this.value; // Hel qiimaha ID-ka
    if (itemId) {
        // Ka saar "ITM-" haddii la jiro
        const cleanItemId = itemId.replace('ITM-', '');
        
        // U dir backend query si aad u hesho details-ka alaabta
        fetch(`backend.php?action=getItemDetails&item_id=${cleanItemId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Ku dar magaca alaabta iyo qiimaha
                    document.getElementById('itemName').value = data.data.ItemName;
                    document.getElementById('price').value = data.data.Price;
                } else {
                    alert(data.message || 'Alaabta lama helin');
                    // Empty fields haddii alaabta la heli waayo
                    document.getElementById('itemName').value = '';
                    document.getElementById('price').value = '';
                }
            })
            .catch(error => {
                console.error('Khalad:', error);
                alert('Khalad ayaa dhacay marka la raadinayay alaabta');
            });
    } else {
        // Empty fields haddii inputka aan la gelin
        document.getElementById('itemName').value = '';
        document.getElementById('price').value = '';
    }
});



$('#item_id_input').on('change', function () {
    const itemId = $(this).val();

    $.ajax({
        url: 'backend.php',
        type: 'GET',
        data: { item_id: itemId },
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                alert(response.error);
            } else {
                $('#item_name_input').val(response.item_name);
                $('#price_input').val(response.price);
            }
        }
    });
});









// Marka alaab lagu darayo dalabka
async function addItemToOrder(itemId, quantity) {
    // Soo qaad qadarka inventory-ga (API call)
    const response = await fetch(`/api/getInventory.php?item_id=${itemId}`);
    const inventory = await response.json();

    // Hubi in alaabtu ku filan tahay
    if (inventory.quantity < quantity) {
        alert(`❌ Alaab ma filnayn! Waxaad haysaa ${inventory.quantity} oo kaliya.`);
        return false;
    }

    // Haddii ay ku filan tahay, ku dar dalabka
    const orderResult = await saveOrder(itemId, quantity);
    return orderResult.success;
}

// Ku dar dalabka database-ka
async function saveOrder(itemId, quantity) {
    const response = await fetch('/api/saveOrder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ item_id: itemId, quantity: quantity })
    });
    return await response.json();
}






// ==============================================
// INVENTORY MANAGEMENT SYSTEM (Automatic Updates)
// ==============================================

/**
 * Marka alaab lagu darayo dalabka (order)
 * @param {string} itemId - ID-ga alaabta
 * @param {number} quantity - Qadarka la dalabayo
 * @returns {boolean} - True haddii ay ku guulaysato
 */
async function addItemToOrder(itemId, quantity) {
    try {
        // 1. Soo qaad qadarka inventory-ga (API call)
        const inventory = await getInventory(itemId);

        // 2. Hubi in alaabtu ku filan tahay
        if (inventory.quantity < quantity) {
            alert(`❌ ${inventory.itemName} ma filnayn! Waxaad haysaa ${inventory.quantity} oo kaliya.`);
            return false;
        }

        // 3. Ku dar dalabka haddii ay ku filan tahay
        const orderResult = await saveOrder(itemId, quantity);
        
        if (orderResult.success) {
            alert(`✅ ${quantity}x ${inventory.itemName} si guul leh ayaa loo iibiyay!`);
            return true;
        } else {
            alert(`❌ Khalad: ${orderResult.error}`);
            return false;
        }
    } catch (error) {
        console.error("Khalad:", error);
        alert("❌ Qalad ayaa dhacay marka lagu darayo dalabka!");
        return false;
    }
}

/**
 * Soo qaad macluumaadka inventory-ga (API)
 * @param {string} itemId - ID-ga alaabta
 * @returns {object} - { itemName, quantity }
 */
async function getInventory(itemId) {
    const response = await fetch(`/api/getInventory.php?item_id=${itemId}`);
    if (!response.ok) throw new Error("Network error");
    return await response.json();
}

/**
 * Ku kaydi dalabka & cusboonaysii inventory-ga
 * @param {string} itemId - ID-ga alaabta
 * @param {number} quantity - Qadarka la dalabayo
 * @returns {object} - { success, error? }
 */
async function saveOrder(itemId, quantity) {
    const response = await fetch('/api/saveOrder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ item_id: itemId, quantity })
    });
    return await response.json();
}

// ==============================================
// EXAMPLE: Sida loo isticmaalo function-yahan
// ==============================================

// 1. Marka button lagu click-garo (e.g., "Add to Order")
document.getElementById("addToCartBtn").addEventListener("click", async () => {
    const itemId = "ITM-001"; // ID-ga alaabta
    const quantity = 2;        // Qadarka la rabo
    
    await addItemToOrder(itemId, quantity);
    
    // Optional: Dib u cusboonaysii UI-ka
    updateInventoryDisplay();
});

// 2. Dib u cusboonaysii UI inventory-ka
async function updateInventoryDisplay() {
    const inventoryList = document.getElementById("inventoryList");
    const response = await fetch("/api/getAllInventory.php");
    const inventory = await response.json();
    
    inventoryList.innerHTML = inventory.map(item => `
        <div class="inventory-item">
            <span>${item.itemName}</span>
            <span>Qadarka: ${item.quantity}</span>
        </div>
    `).join("");
}











// // Auto-fill item details when ItemID is entered
// document.getElementById('itemId').addEventListener('change', function() {
//     const itemId = this.value;
//     if (itemId) {
//         fetch(`backend.php?action=getItemDetails&item_id=${itemId}`)
//             .then(response => response.json())
//             .then(data => {
//                 if (data.status === 'success') {
//                     document.getElementById('itemName').value = data.data.ItemName;
//                     document.getElementById('price').value = data.data.Price;
//                 }
//             })
//             .catch(error => alert(error.message));
//     }
// });


fetch('api.php?action=get_item_price&item_id=ITM-123')
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
        document.getElementById('itemName').value = data.data.ItemName;
        document.getElementById('itemPrice').value = data.data.Price;
    }
  });




// Add new inventory item
function addInventoryItem() {
    const formData = {
        ItemID: document.getElementById('itemId').value,
        Quantity: document.getElementById('quantity').value
    };
    
    fetch('backend.php?action=addInventory', {
        method: 'POST',
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Inventory item added!');
            // Refresh inventory list
        }
    })
    .catch(error => alert(error.message));
}



function renderInventoryTable(inventoryItems) {
    const tbody = document.querySelector('.inventory-table tbody');
    tbody.innerHTML = '';
    
    inventoryItems.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.InventoryID}</td>
            <td>${item.ItemID}</td>
            <td>${item.ItemName}</td>
             <td>${item.Price}</td> <!-- Added Price Column -->
            <td>${item.Quantity}</td>
            <td>${item.LastUpdated}</td>
            <td class="action-cell">
                <button class="action-btn view-btn" onclick="viewInventory('${item.InventoryID}')">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="action-btn edit-btn" onclick="editInventory('${item.InventoryID}')">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="action-btn delete-btn" onclick="deleteInventory('${item.InventoryID}')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}





function openAddInventoryModal() {
    document.getElementById('modalTitle').textContent = "Add New Inventory Item";
    document.getElementById('inventoryId').value = "";
    document.getElementById('itemId').value = "";
    document.getElementById('itemName').value = "";
    document.getElementById('price').value = "";  // Clear price field
    document.getElementById('quantity').value = "";
    
    // Set current date and time as default
    const now = new Date();
    const formattedDateTime = now.toISOString().slice(0, 16);
    document.getElementById('lastUpdated').value = formattedDateTime;
    
    inventoryModal.style.display = "flex";
}

function editInventory(id) {
    fetch(`backend.php?action=get_inventory_item&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const item = data.data;
                document.getElementById('modalTitle').textContent = "Edit Inventory Item";
                document.getElementById('inventoryId').value = item.InventoryID;
                document.getElementById('itemId').value = item.ItemID;
                document.getElementById('itemName').value = item.ItemName;
                document.getElementById('price').value = item.Price || ''; // Added price field
                document.getElementById('quantity').value = item.Quantity;
                
                // Convert the displayed date to a format compatible with datetime-local input
                const dateParts = item.LastUpdated.split(' ')[0].split('-');
                const timeParts = item.LastUpdated.split(' ')[1];
                const ampm = item.LastUpdated.split(' ')[2];
                const [hours, minutes] = timeParts.split(':');
                let hour24 = ampm === 'PM' ? parseInt(hours) + 12 : hours;
                if (hour24 === 24) hour24 = 12;
                if (ampm === 'AM' && hours === '12') hour24 = '00';
                
                const formattedDate = `${dateParts[0]}-${dateParts[1]}-${dateParts[2]}T${hour24}:${minutes}`;
                document.getElementById('lastUpdated').value = formattedDate;
                inventoryModal.style.display = "flex";
            } else {
                alert('Error loading inventory item: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function viewInventory(id) {
    fetch(`backend.php?action=get_inventory_item&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const item = data.data;
                document.getElementById('viewId').textContent = item.InventoryID;
                document.getElementById('viewItemId').textContent = item.ItemID;
                document.getElementById('viewItemName').textContent = item.ItemName;
                const formattedPrice = item.Price ? 
                    `$${parseFloat(item.Price).toFixed(2)}` : 'N/A';
                document.getElementById('viewPrice').textContent = formattedPrice;
                document.getElementById('viewQuantity').textContent = item.Quantity;
                document.getElementById('viewLastUpdated').textContent = item.LastUpdated;
                viewModal.style.display = "flex";
            } else {
                alert('Error loading inventory item: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function deleteInventory(id) {
    currentInventoryToDelete = id;
    const itemName = document.querySelector(`tr td:nth-child(3)`).textContent;
    document.getElementById('deleteInventoryId').textContent = id;
    document.getElementById('deleteItemName').textContent = itemName;
    deleteModal.style.display = "flex";
}

function confirmDelete() {
    if (currentInventoryToDelete) {
        fetch(`backend.php?action=delete_inventory&id=${currentInventoryToDelete}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    loadInventory(); // Refresh the inventory list
                    closeModals();
                } else {
                    alert('Error deleting inventory item: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }
}

function saveInventory(e) {
    e.preventDefault();
    
    const id = document.getElementById('inventoryId').value;
    const itemId = document.getElementById('itemId').value;
    const itemName = document.getElementById('itemName').value;
    price: document.getElementById('price').value
    const quantity = document.getElementById('quantity').value;
    
    if (!itemId || !itemName || !quantity) {
        alert("Please fill in all required fields");
        return;
    }
    
    const inventoryData = {
        inventoryId: id,
        itemId: itemId,
        quantity: quantity
    };
    
    const url = id ? 'backend.php?action=update_inventory' : 'backend.php?action=add_inventory';
    const method = 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(inventoryData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadInventory(); // Refresh the inventory list
            closeModals();
        } else {
            alert('Error saving inventory: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}











function filterInventory() {
    const searchTerm = searchInput.value.toLowerCase();
    
    fetch(`backend.php?action=get_inventory&search=${searchTerm}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                renderInventoryTable(data.data);
            } else {
                console.error('Error filtering inventory:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function closeModals() {
    inventoryModal.style.display = "none";
    viewModal.style.display = "none";
    deleteModal.style.display = "none";
}

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === inventoryModal) inventoryModal.style.display = "none";
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





