// DOM Elements
const addEmployeeBtn = document.getElementById('addEmployeeBtn');
const employeeModal = document.getElementById('employeeModal');
const viewModal = document.getElementById('viewModal');
const deleteModal = document.getElementById('deleteModal');
const closeModal = document.getElementById('closeModal');
const closeViewModal = document.getElementById('closeViewModal');
const closeDeleteModal = document.getElementById('closeDeleteModal');
const cancelBtn = document.getElementById('cancelBtn');
const closeViewBtn = document.getElementById('closeViewBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const employeeForm = document.getElementById('employeeForm');
const calculateBtn = document.getElementById('calculateBtn');
const searchInput = document.getElementById('searchInput');
const positionFilter = document.getElementById('positionFilter');
const employeesTable = document.querySelector('.employees-table tbody');

// Form elements for dynamic calculation
const baseSalaryInput = document.getElementById('baseSalary');
const salaryIncreaseInput = document.getElementById('salaryIncrease');
const expectedSalaryInput = document.getElementById('expectedSalary');

// Current employee to be deleted
let currentEmployeeToDelete = null;

// Event Listeners
addEmployeeBtn.addEventListener('click', openAddEmployeeModal);
closeModal.addEventListener('click', closeModals);
closeViewModal.addEventListener('click', closeModals);
closeDeleteModal.addEventListener('click', closeModals);
cancelBtn.addEventListener('click', closeModals);
closeViewBtn.addEventListener('click', closeModals);
cancelDeleteBtn.addEventListener('click', closeModals);
confirmDeleteBtn.addEventListener('click', confirmDelete);
employeeForm.addEventListener('submit', saveEmployee);
calculateBtn.addEventListener('click', calculateExpectedSalary);
searchInput.addEventListener('input', filterEmployees);
positionFilter.addEventListener('change', filterEmployees);

// Dynamic calculation event listeners
baseSalaryInput.addEventListener('input', calculateExpectedSalary);
salaryIncreaseInput.addEventListener('input', calculateExpectedSalary);

// Load employees when page loads
document.addEventListener('DOMContentLoaded', loadEmployees);

// Functions
function loadEmployees() {
    fetch(buildApiUrl('employees/employees.php'))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                employeesTable.innerHTML = '';
                for (const employee of data.data) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${employee.id}</td>
                        <td>${employee.name}</td>
                        <td>${employee.position}</td>
                        <td>$${Number.parseFloat(employee.baseSalary).toFixed(2)}</td>
                        <td>$${Number.parseFloat(employee.expectedSalary).toFixed(2)}</td>
                        <td>${employee.phone}</td>
                        <td>${employee.email}</td>
                        <td>${employee.guarantor}</td>
                        <td>${employee.address}</td>
                        <td>${employee.dateAdded.split(' ')[0]}</td>
                        <td><span class="status-active">${employee.status}</span></td>
                        <td class="action-cell">
                            <button class="action-btn view-btn" onclick="viewEmployee('${employee.id}')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn edit-btn" onclick="editEmployee('${employee.id}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteEmployee('${employee.id}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    `;
                    employeesTable.appendChild(row);
                }
            } else {
                console.error('Error loading employees:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function openAddEmployeeModal() {
    document.getElementById('modalTitle').textContent = "Add New Employee";
    document.getElementById('employeeId').value = "";
    document.getElementById('employeeName').value = "";
    document.getElementById('position').value = "";
    document.getElementById('status').value = "Active";
    document.getElementById('baseSalary').value = "";
    document.getElementById('salaryIncrease').value = "";
    document.getElementById('expectedSalary').value = "";
    document.getElementById('phone').value = "";
    document.getElementById('email').value = "";
    document.getElementById('guarantor').value = "";
    document.getElementById('address').value = "";
    employeeModal.style.display = "flex";
}

function editEmployee(id) {
    fetch(`buildApiUrl('employees/employees.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const employee = data.data;
                document.getElementById(')modalTitle').textContent = "Edit Employee";
                document.getElementById('employeeId').value = employee.id;
                document.getElementById('employeeName').value = employee.name;
                document.getElementById('position').value = employee.position;
                document.getElementById('status').value = employee.status;
                document.getElementById('baseSalary').value = employee.baseSalary;
                
                // Calculate and set the percentage increase
                const baseSalary = Number.parseFloat(employee.baseSalary);
                const expectedSalary = Number.parseFloat(employee.expectedSalary);
                const percentageIncrease = ((expectedSalary - baseSalary) / baseSalary) * 100;
                document.getElementById('salaryIncrease').value = percentageIncrease.toFixed(1);
                
                document.getElementById('expectedSalary').value = employee.expectedSalary;
                document.getElementById('phone').value = employee.phone;
                document.getElementById('email').value = employee.email;
                document.getElementById('guarantor').value = employee.guarantor;
                document.getElementById('address').value = employee.address;
                employeeModal.style.display = "flex";
            } else {
                console.error('Error loading employee:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function viewEmployee(id) {
    fetch(`buildApiUrl('employees/employees.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const employee = data.data;
                document.getElementById(')viewId').textContent = employee.id;
                document.getElementById('viewName').textContent = employee.name;
                document.getElementById('viewPosition').textContent = employee.position;
                document.getElementById('viewBaseSalary').textContent = `$${Number.parseFloat(employee.baseSalary).toFixed(2)}`;
                document.getElementById('viewExpectedSalary').textContent = `$${Number.parseFloat(employee.expectedSalary).toFixed(2)}`;
                document.getElementById('viewPhone').textContent = employee.phone;
                document.getElementById('viewEmail').textContent = employee.email;
                document.getElementById('viewGuarantor').textContent = employee.guarantor;
                document.getElementById('viewStatus').textContent = employee.status;
                document.getElementById('viewStatus').className = employee.status === "Active" ? "status-active" : "status-inactive";
                document.getElementById('viewAddress').textContent = employee.address;
                viewModal.style.display = "flex";
            } else {
                console.error('Error loading employee:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function deleteEmployee(id) {
    currentEmployeeToDelete = id;
    
    // Find the row with this employee ID and get the name
    const rows = document.querySelectorAll('.employees-table tbody tr');
    let employeeName = '';
    for (const row of rows) {
        if (row.cells[0].textContent === id) {
            employeeName = row.cells[1].textContent;
            break;
        }
    }
    
    document.getElementById('deleteEmployeeId').textContent = id;
    document.getElementById('deleteEmployeeName').textContent = employeeName;
    deleteModal.style.display = "flex";
}

function confirmDelete() {
    console.log(currentEmployeeToDelete);
    if (currentEmployeeToDelete) {
        fetch(buildApiUrl('employees/employees.php'), {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: currentEmployeeToDelete })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadEmployees(); // Refresh the table
                closeModals();
            } else {
                alert(`Error deleting employee: ${data.message}`);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function calculateExpectedSalary() {
    const baseSalary = Number.parseFloat(baseSalaryInput.value) || 0;
    const salaryIncrease = Number.parseFloat(salaryIncreaseInput.value) || 0;
    
    if (baseSalary <= 0) {
        expectedSalaryInput.value = "";
        return;
    }
    
    const expectedSalary = baseSalary + (baseSalary * (salaryIncrease / 100));
    expectedSalaryInput.value = expectedSalary.toFixed(2);
}

function saveEmployee(e) {
    e.preventDefault();
    
    const id = document.getElementById('employeeId').value;
    const name = document.getElementById('employeeName').value;
    const position = document.getElementById('position').value;
    const status = document.getElementById('status').value;
    const baseSalary = Number.parseFloat(document.getElementById('baseSalary').value);
    const salaryIncrease = Number.parseFloat(document.getElementById('salaryIncrease').value);
    const expectedSalary = Number.parseFloat(document.getElementById('expectedSalary').value);
    const phone = document.getElementById('phone').value;
    const email = document.getElementById('email').value;
    const guarantor = document.getElementById('guarantor').value;
    const address = document.getElementById('address').value;
    
    if (!name || !position || !baseSalary || !salaryIncrease || !expectedSalary || !phone) {
        alert("Please fill in all required fields");
        return;
    }
    
    const employeeData = {
        employeeId: id,
        name: name,
        position: position,
        baseSalary: baseSalary,
        expectedSalary: expectedSalary,
        phone: phone,
        email: email,
        guarantor: guarantor,
        address: address
    };
    
    const method = id ? 'PUT' : 'POST';
    const url = buildApiUrl('employees/employees.php');
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(employeeData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadEmployees(); // Refresh the table
            closeModals();
        } else {
            alert(`Error saving employee: ${data.message}`);
        }
    })
    .catch(error => console.error('Error:', error));
}

function filterEmployees() {
    const searchTerm = searchInput.value.toLowerCase();
    const positionFilterValue = positionFilter.value.toLowerCase();
    
    const rows = document.querySelectorAll('.employees-table tbody tr');
    
    for (const row of rows) {
        const id = row.cells[0].textContent.toLowerCase();
        const name = row.cells[1].textContent.toLowerCase();
        const position = row.cells[2].textContent.toLowerCase();
        const phone = row.cells[5].textContent.toLowerCase();
        const email = row.cells[6].textContent.toLowerCase();
        const status = row.cells[10].textContent.toLowerCase();
        
        const matchesSearch = id.includes(searchTerm) || 
                            name.includes(searchTerm) || 
                            position.includes(searchTerm) || 
                            phone.includes(searchTerm) || 
                            email.includes(searchTerm) || 
                            status.includes(searchTerm);
        
        const matchesPositionFilter = positionFilterValue === '' || position === positionFilterValue;
        
        if (matchesSearch && matchesPositionFilter) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    }
}

function closeModals() {
    employeeModal.style.display = "none";
    viewModal.style.display = "none";
    deleteModal.style.display = "none";
}

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === employeeModal) employeeModal.style.display = "none";
    if (e.target === viewModal) viewModal.style.display = "none";
    if (e.target === deleteModal) deleteModal.style.display = "none";
});

// Sign up modal functionality
function openSignUpModal(e) {
    e.preventDefault();
    // Add your sign up modal logic here
    console.log('Opening sign up modal...');
}
