function toggleDropdown() {
    const dropdown = document.querySelector('.dropdown-menu');
    dropdown.classList.toggle('show');
  }

// Hide loader when page is loaded
window.addEventListener('load', function() {
    document.querySelector('.loader').style.display = 'none';
});

// Toggle sidebar
document.querySelector('.toggle-sidebar').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('collapsed');
});

// Toggle sidebar from header button
document.querySelector('.bar-item').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('collapsed');
});

// Like button functionality
document.querySelector('.heart').addEventListener('click', function() {
    this.classList.toggle('fa-regular');
    this.classList.toggle('fa-solid');
    this.classList.toggle('text-red-500');
});

// Toggle Salary Form
document.getElementById('add-salary-btn').addEventListener('click', function() {
    const salaryForm = document.getElementById('salary-form');
    salaryForm.style.display = salaryForm.style.display === 'none' ? 'block' : 'none';
});

// Dynamic salary data
let salaryData = [];

// Load salary data from API
async function loadSalaryData() {
    try {
        const response = await fetch('/backend/api/salaries/salaries.php?action=getSalaries');
        const data = await response.json();
        if (data.success) {
            salaryData = data.data;
            renderSalaryTable();
        }
    } catch (error) {
        console.error('Error loading salary data:', error);
    }
}

// Render Salary Table
function renderSalaryTable() {
    const tableBody = document.querySelector('#salary-table tbody');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';

    if (salaryData.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No salary data available</td></tr>';
        return;
    }

    for (const salary of salaryData) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${salary.SalaryID || salary.id}</td>
            <td>${salary.EmployeeName || salary.employee}</td>
            <td>$${Number.parseFloat(salary.Amount || salary.amount).toFixed(2)}</td>
            <td>${salary.PaymentDate || salary.date}</td>
            <td class="${(salary.Status || salary.status).toLowerCase()}">${salary.Status || salary.status}</td>
            <td>
                <button class="edit-btn"><i class="fas fa-edit"></i></button>
                <button class="delete-btn"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tableBody.appendChild(row);
    }
}

// Initialize Table on Load
document.addEventListener('DOMContentLoaded', function() {
    loadSalaryData();
});

// Tooltip functionality
const tipTops = document.querySelectorAll('.tipTop');
tipTops.forEach(tip => {
    tip.addEventListener('mouseover', function() {
        this.setAttribute('data-tooltip', this.style.width);
    });
});


 // Toggle dropdown when clicking the report button
 document.querySelector('.sidebar-report-btn').addEventListener('click', function(e) {
    e.preventDefault();
    const dropdown = this.closest('.report-dropdown');
    dropdown.classList.toggle('active');
});

// Close dropdown when clicking outside 
document.addEventListener('click', function(e) {
    // Check if the click was outside the dropdown menu
    if (!e.target.closest('.sidebar-report-btn') && !e.target.closest('.report-dropdown-content')) {
        // If click was outside, remove the 'active' class to hide the dropdown
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



