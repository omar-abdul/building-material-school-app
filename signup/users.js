// DOM Elements
const addUserBtn = document.getElementById('addUserBtn');
const signUpBtn = document.getElementById('signUpBtn');
const userModal = document.getElementById('userModal');
const viewModal = document.getElementById('viewModal');
const deleteModal = document.getElementById('deleteModal');
const signUpModal = document.getElementById('signUpModal');
const closeModal = document.getElementById('closeModal');
const closeViewModal = document.getElementById('closeViewModal');
const closeDeleteModal = document.getElementById('closeDeleteModal');
const closeSignUpModal = document.getElementById('closeSignUpModal');
const cancelBtn = document.getElementById('cancelBtn');
const closeViewBtn = document.getElementById('closeViewBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const cancelSignUpBtn = document.getElementById('cancelSignUpBtn');
const userForm = document.getElementById('userForm');
const signUpForm = document.getElementById('signUpForm');
const searchInput = document.getElementById('searchInput');
const roleFilter = document.getElementById('roleFilter');

// Current user to be deleted
let currentUserToDelete = null;

// Event Listeners
addUserBtn.addEventListener('click', openAddUserModal);
signUpBtn.addEventListener('click', openSignUpModal);
closeModal.addEventListener('click', closeModals);
closeViewModal.addEventListener('click', closeModals);
closeDeleteModal.addEventListener('click', closeModals);
closeSignUpModal.addEventListener('click', closeModals);
cancelBtn.addEventListener('click', closeModals);
closeViewBtn.addEventListener('click', closeModals);
cancelDeleteBtn.addEventListener('click', closeModals);
confirmDeleteBtn.addEventListener('click', confirmDelete);
cancelSignUpBtn.addEventListener('click', closeModals);
userForm.addEventListener('submit', saveUser);
signUpForm.addEventListener('submit', signUpUser);
searchInput.addEventListener('input', filterUsers);
roleFilter.addEventListener('change', filterUsers);

// Load users when page loads
document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
});

// Functions
function openAddUserModal() {
    document.getElementById('modalTitle').textContent = "Add New User";
    document.getElementById('userId').value = "";
    document.getElementById('username').value = "";
    document.getElementById('password').value = "";
    document.getElementById('confirmPassword').value = "";
    document.getElementById('role').value = "user";
    userModal.style.display = "flex";
}

function openSignUpModal(e) {
    e.preventDefault();
    document.getElementById('signUpUsername').value = "";
    document.getElementById('signUpPassword').value = "";
    document.getElementById('signUpConfirmPassword').value = "";
    signUpModal.style.display = "flex";
}

function editUser(id) {
    fetchUserById(id, (user) => {
        if (user) {
            document.getElementById('modalTitle').textContent = "Edit User";
            document.getElementById('userId').value = user.id;
            document.getElementById('username').value = user.username;
            document.getElementById('password').value = "";
            document.getElementById('confirmPassword').value = "";
            document.getElementById('role').value = user.role;
            userModal.style.display = "flex";
        }
    });
}

// function viewUser(id) {
//     fetchUserById(id, (user) => {
//         if (user) {
//             document.getElementById('viewId').textContent = user.id;
//             document.getElementById('viewUsername').textContent = user.username;
//             document.getElementById('viewRole').textContent = user.role === "admin" ? "Admin" : "User";
//             document.getElementById('viewPassword').textContent = "********"; // Masked password for security
//             document.getElementById('viewRole').className = user.role === "admin" ? "role-admin" : "role-user";
//             document.getElementById('viewCreatedAt').textContent = user.created_at;
//             viewModal.style.display = "flex";
//         }
//     });
// }

function deleteUser(id) {
    fetchUserById(id, (user) => {
        if (user) {
            currentUserToDelete = id;
            document.getElementById('deleteUserId').textContent = user.id;
            document.getElementById('deleteUsername').textContent = user.username;
            deleteModal.style.display = "flex";
        }
    });
}

function confirmDelete() {
    if (currentUserToDelete) {
        const formData = new FormData();
        formData.append('action', 'delete_user');
        formData.append('id', currentUserToDelete);
        
        fetch('/backend/api/auth/users.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully');
                loadUsers();
            } else {
                alert(`Failed to delete user: ${data.message}`);
            }
            closeModals();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting user');
            closeModals();
        });
    }
}

// function saveUser(e) {
//     e.preventDefault();
    
//     const id = document.getElementById('userId').value;
//     const username = document.getElementById('username').value;
//     const password = document.getElementById('password').value;
//     const confirmPassword = document.getElementById('confirmPassword').value;
//     const role = document.getElementById('role').value;
    
//     if (!username || !role) {
//         alert("Please fill in all required fields");
//         return;
//     }
    
//     if ((!id && (!password || !confirmPassword)) || (password !== confirmPassword)) {
//         alert("Passwords do not match or are empty");
//         return;
//     }
    
//     const formData = new FormData();
//     formData.append('action', id ? 'edit_user' : 'add_user');
//     formData.append('id', id);
//     formData.append('username', username);
//     if (password) formData.append('password', password);
//     if (confirmPassword) formData.append('confirmPassword', confirmPassword);
//     formData.append('role', role);
    
//     fetch('backend.php', {
//         method: 'POST',
//         body: formData
//     })
//     .then(response => response.json())
//     .then(data => {
//         if (data.success) {
//             alert(id ? 'User updated successfully' : 'User added successfully');
//             loadUsers();
//             closeModals();
//         } else {
//             alert('Operation failed: ' + data.message);
//         }
//     })
//     .catch(error => {
//         console.error('Error:', error);
//         alert('An error occurred');
//     });
// }







function saveUser(e) {
    e.preventDefault();
    
    const id = document.getElementById('userId').value;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const role = document.getElementById('role').value;
    
    if (!username || !role) {
        alert("Username iyo role waa lagama maarmaanka ah!");
        return;
    }
    
    // Haddii password cusub la geliyo, isku hubi
    if (password && password !== confirmPassword) {
        alert("Password-ka kuma mid aha!");
        return;
    }
    
    const formData = new FormData();
    formData.append('action', id ? 'edit_user' : 'add_user');
    formData.append('id', id);
    formData.append('username', username);
    formData.append('role', role);
    
    // Haddii password la geliyo, ku dar formData
    if (password) {
        formData.append('password', password);
        formData.append('confirmPassword', confirmPassword);
    }
    
    fetch('/backend/api/auth/users.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(id ? 'Isticmaale waa la cusboonaysiiyay!' : 'Isticmaale cusub ayaa lagu daray!');
            loadUsers();
            closeModals();
        } else {
            alert(`Khalad: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Khalad:', error);
        alert('Khalad ayaa dhacay!');
    });
}





function signUpUser(e) {
    e.preventDefault();
    
    const username = document.getElementById('signUpUsername').value;
    const password = document.getElementById('signUpPassword').value;
    const confirmPassword = document.getElementById('signUpConfirmPassword').value;
    
    if (!username || !password || !confirmPassword) {
        alert("Please fill in all required fields");
        return;
    }
    
    if (password !== confirmPassword) {
        alert("Passwords do not match");
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'signup_user');
    formData.append('username', username);
    formData.append('password', password);
    formData.append('confirmPassword', confirmPassword);
    
    fetch('/backend/api/auth/signup.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User signed up successfully');
            loadUsers();
            closeModals();
        } else {
            alert(`Sign up failed: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during sign up');
    });
}

function loadUsers() {
    const search = searchInput.value;
    const role = roleFilter.value;
    
    const formData = new FormData();
    formData.append('action', 'get_users');
    formData.append('search', search);
    formData.append('roleFilter', role);
    
    fetch('/backend/api/auth/users.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderUsers(data.users);
        } else {
            console.error('Failed to load users:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// function renderUsers(users) {
//     const tbody = document.querySelector('.users-table tbody');
//     tbody.innerHTML = '';
    
//     users.forEach(user => {
//         const tr = document.createElement('tr');
        
//         tr.innerHTML = `
//             <td>${user.id}</td>
//             <td>${user.username}</td>
//             <td><span class="role-${user.role}">${user.role === 'admin' ? 'Admin' : 'User'}</span></td>
//             <td>********</td>
//             <td>${user.created_at}</td>
//             <td class="action-cell">
//                 <button class="action-btn view-btn" onclick="viewUser('${user.id}')">
//                     <i class="fas fa-eye"></i> View
//                 </button>
//                 <button class="action-btn edit-btn" onclick="editUser('${user.id}')">
//                     <i class="fas fa-edit"></i> Edit
//                 </button>
//                 <button class="action-btn delete-btn" onclick="deleteUser('${user.id}')">
//                     <i class="fas fa-trash"></i> Delete
//                 </button>
//             </td>
//         `;
        
//         tbody.appendChild(tr);
//     });
// }

function fetchUserById(id, callback) {
    const formData = new FormData();
    formData.append('action', 'get_user');
    formData.append('id', id);
    
    fetch('/backend/api/auth/users.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            callback(data.user);
        } else {
            alert(`User not found: ${data.message}`);
            callback(null);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while fetching user');
        callback(null);
    });
}

function filterUsers() {
    loadUsers();
}

function closeModals() {
    userModal.style.display = "none";
    viewModal.style.display = "none";
    deleteModal.style.display = "none";
    signUpModal.style.display = "none";
}

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === userModal) userModal.style.display = "none";
    if (e.target === viewModal) viewModal.style.display = "none";
    if (e.target === deleteModal) deleteModal.style.display = "none";
    if (e.target === signUpModal) signUpModal.style.display = "none";
});











function viewUser(id) {
    fetchUserById(id, (user) => {
        if (user) {
            document.getElementById('viewId').textContent = user.id;
            document.getElementById('viewUsername').textContent = user.username;
            document.getElementById('viewRole').textContent = user.role === "admin" ? "Admin" : "User";
            document.getElementById('viewPassword').textContent = user.password; // Show actual password
            document.getElementById('viewRole').className = user.role === "admin" ? "role-admin" : "role-user";
            document.getElementById('viewCreatedAt').textContent = user.created_at;
            viewModal.style.display = "flex";
        }
    });
}




function renderUsers(users) {
    const tbody = document.querySelector('.users-table tbody');
    tbody.innerHTML = '';
    
    // biome-ignore lint/complexity/noForEach: <explanation>
        users.forEach(user => {
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td>${user.id}</td>
            <td>${user.username}</td>
            <td><span class="role-${user.role}">${user.role === 'admin' ? 'Admin' : 'User'}</span></td>
            <td>${user.password}</td> <!-- Show actual password -->
            <td>${user.created_at}</td>
            <td class="action-cell">
                <button class="action-btn view-btn" onclick="viewUser('${user.id}')">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="action-btn edit-btn" onclick="editUser('${user.id}')">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="action-btn delete-btn" onclick="deleteUser('${user.id}')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
}