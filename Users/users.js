/**
 * Users Management JavaScript
 * Handles user CRUD operations and UI interactions
 */

// Global variables
let users = [];
let currentUser = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initializeUsers();
    setupEventListeners();
});

/**
 * Initialize users management
 */
function initializeUsers() {
    loadUsers();
    setupSearch();
    setupFilters();
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // User form submission
    document.getElementById('user-form').addEventListener('submit', handleUserSubmit);
    
    // Search input
    document.getElementById('user-search').addEventListener('input', debounce(filterUsers, 300));
    
    // Role filter
    document.getElementById('role-filter').addEventListener('change', filterUsers);

    // Modal close events
    for (const closeBtn of document.querySelectorAll('.close')) {
        closeBtn.addEventListener('click', (e) => {
            const modal = e.target.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    });
}

/**
 * Load users from API
 */
async function loadUsers() {
    try {
        showLoading('users-table-body');
        
        const response = await fetch('../api/users/users.php?action=getUsers');
        const data = await response.json();
        
        if (data.success) {
            users = data.data;
            renderUsers(users);
        } else {
            showError(`Failed to load users: ${data.message}`);
        }
    } catch (error) {
        console.error('Error loading users:', error);
        showError('Failed to load users');
    }
}

/**
 * Render users in table
 */
function renderUsers(usersToRender) {
    const tbody = document.getElementById('users-table-body');
    
    if (usersToRender.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No Users Found</h3>
                    <p>No users match your current search criteria.</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = usersToRender.map(user => `
        <tr>
            <td>${user.id}</td>
            <td>${escapeHtml(user.username)}</td>
            <td><span class="role-badge ${user.role}">${user.role}</span></td>
            <td>${formatDate(user.created_at)}</td>
            <td class="action-buttons">
                <button class="btn btn-sm btn-primary" onclick="viewUser(${user.id})">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-secondary" onclick="editUser(${user.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

/**
 * Show add user modal
 */
function showAddUserModal() {
    currentUser = null;
    document.getElementById('modal-title').innerHTML = '<i class="fas fa-user-plus"></i> Add New User';
    document.getElementById('user-form').reset();
    document.getElementById('user-id').value = '';
    document.getElementById('user-modal').style.display = 'block';
}

/**
 * Show edit user modal
 */
function editUser(userId) {
    const user = users.find(u => u.id === userId);
    if (!user) {
        showError('User not found');
        return;
    }
    
    currentUser = user;
    document.getElementById('modal-title').innerHTML = '<i class="fas fa-user-edit"></i> Edit User';
    document.getElementById('user-id').value = user.id;
    document.getElementById('username').value = user.username;
    document.getElementById('role').value = user.role;
    
    // Clear password fields for edit
    document.getElementById('password').value = '';
    document.getElementById('confirm-password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('confirm-password').required = false;
    
    document.getElementById('user-modal').style.display = 'block';
}

/**
 * Close user modal
 */
function closeUserModal() {
    document.getElementById('user-modal').style.display = 'none';
    document.getElementById('user-form').reset();
    document.getElementById('password').required = true;
    document.getElementById('confirm-password').required = true;
}

/**
 * Handle user form submission
 */
async function handleUserSubmit(e) {
    e.preventDefault();
    
    let formData = {
        username: document.getElementById('username').value,
        password: document.getElementById('password').value,
        confirmPassword: document.getElementById('confirm-password').value,
        role: document.getElementById('role').value
    };
    
    // Validate password match
    if (formData.password !== formData.confirmPassword) {
        showError('Passwords do not match');
        return;
    }
    
    // Validate password strength for new users
    if (!currentUser && formData.password.length < 6) {
        showError('Password must be at least 6 characters long');
        return;
    }
    
    try {
        const action = currentUser ? 'updateUser' : 'addUser';
        const url = `../api/users/users.php?action=${action}`;
        
        if (currentUser) {
            formData.userId = currentUser.id;
            // Remove password if not provided for updates
            if (!formData.password) {
                const { password, confirmPassword, ...updateData } = formData;
                formData = updateData;
            }
        }
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message);
            closeUserModal();
            loadUsers();
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('Error saving user:', error);
        showError('Failed to save user');
    }
}

/**
 * Delete user
 */
async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch('../api/users/users.php?action=deleteUser', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('User deleted successfully');
            loadUsers();
        } else {
            showError(`Failed to delete user: ${data.message}`);
        }
    } catch (error) {
        console.error('Error deleting user:', error);
        showError('Failed to delete user');
    }
}

/**
 * View user details
 */
function viewUser(userId) {
    const user = users.find(u => u.id === userId);
    if (!user) {
        showError('User not found');
        return;
    }
    
    document.getElementById('view-user-id').textContent = user.id;
    document.getElementById('view-username').textContent = user.username;
    document.getElementById('view-role').textContent = user.role;
    document.getElementById('view-created-at').textContent = formatDate(user.created_at);
    
    document.getElementById('view-user-modal').style.display = 'block';
}

/**
 * Filter users
 */
function filterUsers() {
    const searchTerm = document.getElementById('user-search').value.toLowerCase();
    const roleFilter = document.getElementById('role-filter').value;
    
    const filteredUsers = users.filter(user => {
        const matchesSearch = user.username.toLowerCase().includes(searchTerm);
        const matchesRole = !roleFilter || user.role === roleFilter;
        
        return matchesSearch && matchesRole;
    });
    
    renderUsers(filteredUsers);
}

/**
 * Export users
 */
function exportUsers() {
    const filteredUsers = users.filter(user => {
        const searchTerm = document.getElementById('user-search').value.toLowerCase();
        const roleFilter = document.getElementById('role-filter').value;
        
        const matchesSearch = user.username.toLowerCase().includes(searchTerm);
        const matchesRole = !roleFilter || user.role === roleFilter;
        
        return matchesSearch && matchesRole;
    });
    
    exportTableToCSV('users-table', 'users.csv');
}

/**
 * Show backup modal
 */
function showBackupModal() {
    document.getElementById('backup-modal').style.display = 'block';
}

/**
 * Close backup modal
 */
function closeBackupModal() {
    document.getElementById('backup-modal').style.display = 'none';
}

/**
 * Create backup
 */
async function createBackup() {
    const backupOptions = {
        users: document.getElementById('backup-users').checked,
        customers: document.getElementById('backup-customers').checked,
        suppliers: document.getElementById('backup-suppliers').checked,
        items: document.getElementById('backup-items').checked,
        orders: document.getElementById('backup-orders').checked,
        financial: document.getElementById('backup-financial').checked
    };
    
    try {
        const response = await fetch('../api/users/backup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(backupOptions)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Backup created successfully');
            closeBackupModal();
            
            // Download backup file if provided
            if (data.downloadUrl) {
                window.open(data.downloadUrl, '_blank');
            }
        } else {
            showError(`Failed to create backup: ${data.message}`);
        }
    } catch (error) {
        console.error('Error creating backup:', error);
        showError('Failed to create backup');
    }
}

/**
 * Setup search functionality
 */
function setupSearch() {
    // Search functionality is handled by filterUsers function
}

/**
 * Setup filters
 */
function setupFilters() {
    // Filter functionality is handled by filterUsers function
}

/**
 * Show loading state
 */
function showLoading(elementId) {
    const element = document.getElementById(elementId);
    element.innerHTML = `
        <tr>
            <td colspan="5" class="loading">
                Loading users...
            </td>
        </tr>
    `;
}

/**
 * Show success message
 */
function showSuccess(message) {
    alert(message); // Replace with better notification system
}

/**
 * Show error message
 */
function showError(message) {
    alert(`Error: ${message}`); // Replace with better notification system
}

/**
 * Export table to CSV
 */
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        
        for (let j = 0; j < cols.length - 1; j++) { // Skip last column (actions)
            let text = cols[j].textContent || cols[j].innerText;
            text = text.replace(/"/g, '""');
            rowData.push(`"${text}"`);
        }
        
        csv.push(rowData.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

/**
 * Utility functions
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
} 