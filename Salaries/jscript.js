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
const employeeFilter = document.getElementById('employeeFilter');
const employeeIdSelect = document.getElementById('employeeId');
const employeeNameInput = document.getElementById('employeeName');
const BasesalaryInput = document.getElementById('Basesalary');
const advanceSalaryInput = document.getElementById('advanceSalary');
const calculateBtn = document.getElementById('calculateBtn');
const netSalaryInput = document.getElementById('netSalary');
const paymentMethodInput = document.getElementById('paymentMethod');
const paymentDateInput = document.getElementById('paymentDate');
const salariesTable = document.querySelector('.salaries-table tbody');

// Current salary to be deleted
let currentSalaryToDelete = null;

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
employeeFilter.addEventListener('change', filterSalaries);
employeeIdSelect.addEventListener('change', fetchEmployeeDetails);
calculateBtn.addEventListener('click', calculateNetSalary);

// Load salaries when page loads
document.addEventListener('DOMContentLoaded', loadSalaries);

// Functions
function loadSalaries() {
    fetch('backend.php?action=getSalaries')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (Array.isArray(data)) {
                renderSalaries(data);
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
        row.innerHTML = `<td colspan="9" class="no-salaries">No salaries found</td>`;
        salariesTable.appendChild(row);
        return;
    }
    
    salaries.forEach(salary => {
        const row = document.createElement('tr');
        
        // Format payment date
        const paymentDate = new Date(salary.PaymentDate);
        const formattedDate = paymentDate.toISOString().split('T')[0];
        
        row.innerHTML = `
            <td>SAL-${salary.SalaryID}</td>
            <td>EMP-${salary.EmployeeID}</td>
            <td>${salary.EmployeeName}</td>
            <td>$${parseFloat(salary.Amount).toFixed(2)}</td>
            <td>$${parseFloat(salary.AdvanceSalary).toFixed(2)}</td>
            <td>${salary.PaymentMethod}</td>
            <td>${formattedDate}</td>
            <td><span class="status-paid">Paid</span></td>
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
    });
}

function openAddSalaryModal() {
    document.getElementById('modalTitle').textContent = "Add New Salary";
    document.getElementById('salaryId').value = "";
    document.getElementById('employeeId').value = "";
    document.getElementById('employeeName').value = "";
    document.getElementById('Basesalary').value = "";
    document.getElementById('amount').value = "";
    document.getElementById('advanceSalary').value = "0.00";
    document.getElementById('netSalary').value = "";
    document.getElementById('paymentMethod').value = "";
    document.getElementById('paymentDate').value = new Date().toISOString().split('T')[0];
    salaryModal.style.display = "flex";
}

function editSalary(id) {
    fetch(`backend.php?action=getSalary&salaryId=${id}`)
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
            
            document.getElementById('modalTitle').textContent = "Edit Salary";
            document.getElementById('salaryId').value = data.SalaryID;
            document.getElementById('employeeId').value = data.EmployeeID;
            document.getElementById('employeeName').value = data.EmployeeName;
            document.getElementById('amount').value = data.Amount;
            document.getElementById('advanceSalary').value = data.AdvanceSalary;
            document.getElementById('netSalary').value = data.NetSalary;
            document.getElementById('paymentMethod').value = data.PaymentMethod;
            document.getElementById('paymentDate').value = data.PaymentDate.split(' ')[0];
            salaryModal.style.display = "flex";
        })
        .catch(error => {
            console.error('Error fetching salary:', error);
            alert('Error fetching salary details');
        });
}

function viewSalary(id) {
    fetch(`backend.php?action=getSalary&salaryId=${id}`)
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
            
            // Format payment date
            const paymentDate = new Date(data.PaymentDate);
            const formattedDate = paymentDate.toISOString().split('T')[0];
            
            document.getElementById('viewId').textContent = `SAL-${data.SalaryID}`;
            document.getElementById('viewEmployeeId').textContent = `EMP-${data.EmployeeID}`;
            document.getElementById('viewEmployeeName').textContent = data.EmployeeName;
            document.getElementById('viewAmount').textContent = `$${parseFloat(data.Amount).toFixed(2)}`;
            document.getElementById('viewAdvance').textContent = `$${parseFloat(data.AdvanceSalary).toFixed(2)}`;
            document.getElementById('viewNetSalary').textContent = `$${parseFloat(data.NetSalary).toFixed(2)}`;
            document.getElementById('viewPaymentMethod').textContent = data.PaymentMethod;
            document.getElementById('viewPaymentDate').textContent = formattedDate;
            document.getElementById('viewStatus').textContent = "Paid";
            viewModal.style.display = "flex";
        })
        .catch(error => {
            console.error('Error fetching salary:', error);
            alert('Error fetching salary details');
        });
}

function deleteSalary(id) {
    currentSalaryToDelete = id;
    fetch(`backend.php?action=getSalary&salaryId=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('deleteSalaryId').textContent = `SAL-${data.SalaryID}`;
            document.getElementById('deleteEmployeeName').textContent = data.EmployeeName;
            deleteModal.style.display = "flex";
        })
        .catch(error => {
            console.error('Error fetching salary for delete:', error);
            alert('Error fetching salary details');
        });
}

function fetchEmployeeDetails() {
    const employeeId = employeeIdSelect.value;
    if (!employeeId) {
        employeeNameInput.value = '';
        BasesalaryInput.value = '';
        return;
    }
    
    fetch(`backend.php?action=getEmployeeDetails&employeeId=${employeeId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                employeeNameInput.value = '';
                BasesalaryInput.value = '';
                alert(data.error);
            } else {
                employeeNameInput.value = data.employeeName || '';
                BasesalaryInput.value = data.baseSalary || '';
                // Set amount to base salary by default
                document.getElementById('amount').value = data.baseSalary || '';
            }
        })
        .catch(error => {
            console.error('Error fetching employee:', error);
            employeeNameInput.value = '';
            BasesalaryInput.value = '';
        });
}

function calculateNetSalary() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const advance = parseFloat(document.getElementById('advanceSalary').value) || 0;
    
    if (amount <= 0) {
        alert("Amount must be greater than 0");
        return;
    }
    
    if (advance < 0) {
        alert("Advance cannot be negative");
        return;
    }
    
    if (advance > amount) {
        alert("Advance cannot be greater than amount");
        return;
    }
    
    const netSalary = amount - advance;
    document.getElementById('netSalary').value = netSalary.toFixed(2);
}

function saveSalary(e) {
    e.preventDefault();
    
    // Get form values
    const id = document.getElementById('salaryId').value;
    const employeeId = document.getElementById('employeeId').value;
    const amount = parseFloat(document.getElementById('amount').value);
    const advance = parseFloat(document.getElementById('advanceSalary').value) || 0;
    const netSalary = parseFloat(document.getElementById('netSalary').value);
    const paymentMethod = document.getElementById('paymentMethod').value;
    const paymentDate = document.getElementById('paymentDate').value;
    
    // Validate required fields
    if (!employeeId || isNaN(amount) || isNaN(netSalary) || !paymentMethod || !paymentDate) {
        alert("Please fill in all required fields with valid values");
        return;
    }
    
    // Prepare data
    const salaryData = {
        employeeId: employeeId,
        amount: amount,
        advanceSalary: advance,
        paymentMethod: paymentMethod,
        paymentDate: paymentDate
    };
    
    if (id) {
        salaryData.salaryId = id;
    }
    
    // Determine URL and method
    const url = `backend.php?action=${id ? 'updateSalary' : 'addSalary'}`;
    
    // Send request
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(salaryData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        if (data.success) {
            loadSalaries();
            closeModals();
            alert(`Salary ${id ? 'updated' : 'added'} successfully!`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(`Error saving salary: ${error.message}`);
    });
}

function filterSalaries() {
    const searchTerm = searchInput.value;
    const employeeFilterValue = employeeFilter.value;
    
    fetch(`backend.php?action=getSalaries&search=${encodeURIComponent(searchTerm)}&employeeFilter=${encodeURIComponent(employeeFilterValue)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            renderSalaries(data);
        })
        .catch(error => {
            console.error('Error filtering salaries:', error);
        });
}

function confirmDelete() {
    if (!currentSalaryToDelete) return;
    
    fetch(`backend.php?action=deleteSalary&salaryId=${currentSalaryToDelete}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                loadSalaries();
                closeModals();
            } else {
                alert(data.error || 'Failed to delete salary');
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
    currentSalaryToDelete = null;
}

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === salaryModal) salaryModal.style.display = "none";
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
