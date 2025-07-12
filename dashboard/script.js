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

// Sample Salary Data (Replace with real data from backend)
const salaryData = [
    { id: 1, employee: "Ahmed Ali", amount: "$1,200", date: "10/09/2023", status: "Paid" },
    { id: 2, employee: "Aisha Mohamed", amount: "$1,500", date: "05/09/2023", status: "Paid" },
    { id: 3, employee: "Omar Hassan", amount: "$1,000", date: "Pending", status: "Pending" },
    { id: 4, employee: "Fatuma Abdi", amount: "$1,300", date: "01/09/2023", status: "Paid" },
];

// Render Salary Table
function renderSalaryTable() {
    const tableBody = document.querySelector('#salary-table tbody');
    tableBody.innerHTML = '';

    salaryData.forEach(salary => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${salary.id}</td>
            <td>${salary.employee}</td>
            <td>${salary.amount}</td>
            <td>${salary.date}</td>
            <td class="${salary.status.toLowerCase()}">${salary.status}</td>
            <td>
                <button class="edit-btn"><i class="fas fa-edit"></i></button>
                <button class="delete-btn"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Initialize Table on Load
document.addEventListener('DOMContentLoaded', function() {
    renderSalaryTable();
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



