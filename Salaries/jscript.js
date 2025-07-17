// Global functions that need to be accessible from onclick handlers
function viewSalary(id) {
    fetch(`/backend/api/salaries/salaries.php?salaryId=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data) {
                const salary = data.data;
                
                // Format payment date
                const paymentDate = new Date(salary.PaymentDate);
                const formattedDate = paymentDate.toISOString().split('T')[0];
                
                document.getElementById('viewId').textContent = `SAL-${salary.SalaryID}`;
                document.getElementById('viewEmployeeId').textContent = `EMP-${salary.EmployeeID}`;
                document.getElementById('viewEmployeeName').textContent = salary.EmployeeName;
                document.getElementById('viewAmount').textContent = `$${Number.parseFloat(salary.Amount).toFixed(2)}`;
                document.getElementById('viewAdvance').textContent = `$${Number.parseFloat(salary.AdvanceSalary).toFixed(2)}`;
                document.getElementById('viewNetSalary').textContent = `$${Number.parseFloat(salary.NetSalary).toFixed(2)}`;
                document.getElementById('viewPaymentMethod').textContent = salary.PaymentMethod;
                document.getElementById('viewPaymentDate').textContent = formattedDate;
                document.getElementById('viewStatus').textContent = salary.Status;
                document.getElementById('viewModal').style.display = "flex";
            } else {
                alert(data.message || 'Error fetching salary details');
            }
        })
        .catch(error => {
            console.error('Error fetching salary:', error);
            alert('Error fetching salary details');
        });
}

function editSalary(id) {
    fetch(`/backend/api/salaries/salaries.php?salaryId=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data) {
                const salary = data.data;
                
                document.getElementById('modalTitle').textContent = "Edit Salary";
                document.getElementById('salaryId').value = salary.SalaryID;
                document.getElementById('employeeId').value = salary.EmployeeID;
                document.getElementById('employeeName').value = salary.EmployeeName;
                document.getElementById('employeeSearch').value = `${salary.EmployeeName} (EMP-${salary.EmployeeID})`;
                document.getElementById('baseSalary').value = salary.BaseSalary || "";
                document.getElementById('amount').value = salary.Amount;
                document.getElementById('advanceSalary').value = salary.AdvanceSalary;
                document.getElementById('netSalary').value = salary.NetSalary;
                document.getElementById('paymentMethod').value = salary.PaymentMethod;
                document.getElementById('paymentDate').value = salary.PaymentDate.split(' ')[0];
                document.getElementById('status').value = salary.Status;
                document.getElementById('salaryModal').style.display = "flex";
            } else {
                alert(data.message || 'Error fetching salary details');
            }
        })
        .catch(error => {
            console.error('Error fetching salary:', error);
            alert('Error fetching salary details');
        });
}

function deleteSalary(id) {
    fetch(`/backend/api/salaries/salaries.php?salaryId=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const salary = data.data;
                document.getElementById('deleteSalaryId').textContent = `SAL-${salary.SalaryID}`;
                document.getElementById('deleteEmployeeName').textContent = salary.EmployeeName;
                document.getElementById('deleteModal').style.display = "flex";
                // Set the current salary to delete
                window.currentSalaryToDelete = id;
            } else {
                alert(data.message || 'Error fetching salary details');
            }
        })
        .catch(error => {
            console.error('Error fetching salary:', error);
            alert('Error fetching salary details');
        });
}

document.addEventListener('DOMContentLoaded', () => {
// DOM Elements
const addSalaryBtn = document.getElementById('addSalaryBtn');
const salaryModal = document.getElementById('salaryModal');
const viewModal = document.getElementById('viewModal');
const deleteModal = document.getElementById('deleteModal');
const closeModal = document.getElementById('closeModal');
const closeViewModal = document.getElementById('closeViewModal');
const closeDeleteModal = document.getElementById('closeDeleteModal');
const cancelBtn = document.getElementById('cancelBtn');
const closeViewBtn = document.getElementById('closeViewBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const salaryForm = document.getElementById('salaryForm');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');

// Form elements
const employeeSearch = document.getElementById('employeeSearch');
const employeeDropdown = document.getElementById('employeeDropdown');
const employeeId = document.getElementById('employeeId');
const employeeName = document.getElementById('employeeName');
const baseSalary = document.getElementById('baseSalary');
const amount = document.getElementById('amount');
const advanceSalary = document.getElementById('advanceSalary');
const calculateBtn = document.getElementById('calculateBtn');
const netSalary = document.getElementById('netSalary');
const paymentMethod = document.getElementById('paymentMethod');
const paymentDate = document.getElementById('paymentDate');
const status = document.getElementById('status');
const salaryId = document.getElementById('salaryId');

const salariesTable = document.querySelector('.salaries-table tbody');

// Current salary to be deleted
let currentSalaryToDelete = null;
let employees = [];

// Event Listeners
addSalaryBtn.addEventListener('click', openAddSalaryModal);
closeModal.addEventListener('click', closeModals);
closeViewModal.addEventListener('click', closeModals);
closeDeleteModal.addEventListener('click', closeModals);
cancelBtn.addEventListener('click', closeModals);
closeViewBtn.addEventListener('click', closeModals);
cancelDeleteBtn.addEventListener('click', closeModals);
confirmDeleteBtn.addEventListener('click', confirmDelete);
salaryForm.addEventListener('submit', saveSalary);
searchInput.addEventListener('input', filterSalaries);
statusFilter.addEventListener('change', filterSalaries);
calculateBtn.addEventListener('click', calculateNetSalary);

// Employee search autocomplete
employeeSearch.addEventListener('input', handleEmployeeSearch);
employeeSearch.addEventListener('focus', handleEmployeeSearch);
document.addEventListener('click', (e) => {
    if (!e.target.closest('.autocomplete-container')) {
        employeeDropdown.style.display = 'none';
    }
});

// Load salaries when page loads
loadSalaries();

// Functions
function loadSalaries() {
    fetch('/backend/api/salaries/salaries.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && Array.isArray(data.data)) {
                renderSalaries(data.data);
            } else {
                console.error('Invalid data format:', data);
            }
        })
        .catch(error => {
            console.error('Error loading salaries:', error);
        });
}

function renderSalaries(salaries) {
    salariesTable.innerHTML = '';
    
    if (!salaries || salaries.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="10" class="no-salaries">No salaries found</td>`;
        salariesTable.appendChild(row);
        return;
    }
    
    for (const salary of salaries) {
        const row = document.createElement('tr');
        
        // Format payment date
        const paymentDate = new Date(salary.PaymentDate);
        const formattedDate = paymentDate.toISOString().split('T')[0];
        
        row.innerHTML = `
            <td>SAL-${salary.SalaryID}</td>
            <td>EMP-${salary.EmployeeID}</td>
            <td>${salary.EmployeeName}</td>
            <td>$${Number.parseFloat(salary.Amount).toFixed(2)}</td>
            <td>$${Number.parseFloat(salary.AdvanceSalary).toFixed(2)}</td>
            <td>$${Number.parseFloat(salary.NetSalary).toFixed(2)}</td>
            <td>${salary.PaymentMethod}</td>
            <td>${formattedDate}</td>
            <td><span class="status-${salary.Status.toLowerCase()}">${salary.Status}</span></td>
            <td class="action-cell">
                <button class="action-btn view-btn" onclick="viewSalary(${salary.SalaryID})">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="action-btn edit-btn" onclick="editSalary(${salary.SalaryID})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="action-btn delete-btn" onclick="deleteSalary(${salary.SalaryID})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        `;
        
        salariesTable.appendChild(row);
    }
}

function openAddSalaryModal() {
    document.getElementById('modalTitle').textContent = "Add New Salary";
    clearForm();
    salaryModal.style.display = "flex";
}

function clearForm() {
    salaryId.value = "";
    employeeSearch.value = "";
    employeeId.value = "";
    employeeName.value = "";
    baseSalary.value = "";
    amount.value = "";
    advanceSalary.value = "0.00";
    netSalary.value = "";
    paymentMethod.value = "";
    paymentDate.value = new Date().toISOString().split('T')[0];
    status.value = "Paid";
    employeeDropdown.style.display = 'none';
}

function handleEmployeeSearch() {
    const searchTerm = employeeSearch.value.trim();
    
    if (searchTerm.length < 2) {
        employeeDropdown.style.display = 'none';
        return;
    }
    
    fetch(`/backend/api/salaries/salaries.php?searchEmployees=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.data)) {
                employees = data.data;
                showEmployeeDropdown(employees);
            } else {
                employeeDropdown.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error searching employees:', error);
            employeeDropdown.style.display = 'none';
        });
}

function showEmployeeDropdown(employees) {
    if (!employees || employees.length === 0) {
        employeeDropdown.style.display = 'none';
        return;
    }
    
    employeeDropdown.innerHTML = '';
    for (const employee of employees) {
        const item = document.createElement('div');
        item.className = 'autocomplete-item';
        item.textContent = `${employee.EmployeeName} (EMP-${employee.EmployeeID})`;
        item.addEventListener('click', () => selectEmployee(employee));
        employeeDropdown.appendChild(item);
    }
    employeeDropdown.style.display = 'block';
}

function selectEmployee(employee) {
    employeeSearch.value = `${employee.EmployeeName} (EMP-${employee.EmployeeID})`;
    employeeId.value = employee.EmployeeID;
    employeeName.value = employee.EmployeeName;
    baseSalary.value = employee.BaseSalary || "";
    employeeDropdown.style.display = 'none';
    
    // Auto-calculate if amount is not set
    if (!amount.value && employee.BaseSalary) {
        amount.value = employee.BaseSalary;
        calculateNetSalary();
    }
}

function calculateNetSalary() {
    const amountValue = Number.parseFloat(amount.value) || 0;
    const advanceValue = Number.parseFloat(advanceSalary.value) || 0;
    const netValue = amountValue - advanceValue;
    netSalary.value = netValue.toFixed(2);
}

function saveSalary(e) {
    e.preventDefault();
    
    const formData = {
        employee_id: employeeId.value,
        amount: amount.value,
        advance_salary: advanceSalary.value,
        payment_method: paymentMethod.value,
        payment_date: paymentDate.value,
        status: status.value
    };
    
    if (salaryId.value) {
        // Update existing salary
        formData.salary_id = salaryId.value;
        
        fetch('/backend/api/salaries/salaries.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Salary updated successfully');
                closeModals();
                loadSalaries();
            } else {
                alert(data.message || 'Error updating salary');
            }
        })
        .catch(error => {
            console.error('Error updating salary:', error);
            alert('Error updating salary');
        });
    } else {
        // Create new salary
        fetch('/backend/api/salaries/salaries.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Salary created successfully');
                closeModals();
                loadSalaries();
            } else {
                alert(data.message || 'Error creating salary');
            }
        })
        .catch(error => {
            console.error('Error creating salary:', error);
            alert('Error creating salary');
        });
    }
}

function filterSalaries() {
    const searchTerm = searchInput.value;
    const statusFilterValue = statusFilter.value;
    
    let url = '/backend/api/salaries/salaries.php?';
    const params = [];
    
    if (searchTerm) {
        params.push(`search=${encodeURIComponent(searchTerm)}`);
    }
    
    if (statusFilterValue) {
        params.push(`status=${encodeURIComponent(statusFilterValue)}`);
    }
    
    url += params.join('&');
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.data)) {
                renderSalaries(data.data);
            }
        })
        .catch(error => {
            console.error('Error filtering salaries:', error);
        });
}

function confirmDelete() {
    if (!window.currentSalaryToDelete) return;
    
    fetch('/backend/api/salaries/salaries.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ salaryId: window.currentSalaryToDelete })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Salary deleted successfully');
            closeModals();
            loadSalaries();
        } else {
            alert(data.message || 'Error deleting salary');
        }
    })
    .catch(error => {
        console.error('Error deleting salary:', error);
        alert('Error deleting salary');
    });
}

function closeModals() {
    salaryModal.style.display = "none";
    viewModal.style.display = "none";
    deleteModal.style.display = "none";
    employeeDropdown.style.display = 'none';
    window.currentSalaryToDelete = null;
}

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
});