function toggleDropdown() {
    const dropdown = document.querySelector('.dropdown-menu');
    dropdown.classList.toggle('show');
  }
  
  function loadDefault() {
    const title = document.getElementById('main-title');
    const content = document.getElementById('main-content');
    title.innerText = "Dashboard";
    content.innerHTML = `<p>Welcome to BMMS Dashboard!</p>`;
  }
 
 function loadReportForm(formType) {
  const title = document.getElementById('main-title');
  const content = document.getElementById('main-content');
  title.innerText = "Reports Management";

  let url = "";

  switch (formType) {
  case 'items':
    url = 'backend/reports/items.php';
    break;
  case 'inventory':
    url = 'backend/reports/inventory.php';
    break;
  case 'orders':
    url = 'backend/reports/orders.php';
    break;
  case 'transactions':
    url = 'backend/reports/transactions.php';
    break;
  case 'salaries':
    url = 'backend/reports/salaries.php';
    break;
  default:
    content.innerHTML = "<p>Invalid report selected.</p>";
    return;
}


  fetch(url)
    .then(response => response.text())
    .then(html => {
      content.innerHTML = html;
    })
    .catch(error => {
      content.innerHTML = `<p>Error loading report: ${error}</p>`;
    });
}

    $(document).ready(function () {
    $('#inventoryTable').DataTable({
      dom: 'Bflrtip',
      buttons: [
        'copyHtml5',
        'csvHtml5',
        'excelHtml5',
        'pdfHtml5',
        'print',
        {
          extend: 'colvis',
          text: 'Toggle Columns'
        }
      ],
      lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });
  });