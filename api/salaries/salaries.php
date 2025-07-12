<?php
require_once 'connection.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getSalaries':
            getSalaries($conn);
            break;
        case 'getSalary':
            getSalary($conn);
            break;
        case 'getEmployeeDetails':
            getEmployeeDetails($conn);
            break;
        case 'addSalary':
            addSalary($conn);
            break;
        case 'updateSalary':
            updateSalary($conn);
            break;
        case 'deleteSalary':
            deleteSalary($conn);
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function getSalaries($conn) {
    $search = $_GET['search'] ?? '';
    $employeeFilter = $_GET['employeeFilter'] ?? '';
    
    $query = "SELECT s.SalaryID, s.EmployeeID, e.EmployeeName, 
                     s.Amount, s.AdvanceSalary, s.NetSalary,
                     s.PaymentMethod, s.PaymentDate
              FROM Salaries s
              JOIN Employees e ON s.EmployeeID = e.EmployeeID
              WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $query .= " AND (s.SalaryID LIKE ? OR e.EmployeeName LIKE ? OR s.PaymentMethod LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        $types .= 'sss';
    }
    
    if (!empty($employeeFilter)) {
        $query .= " AND s.EmployeeID = ?";
        $params[] = $employeeFilter;
        $types .= 'i';
    }
    
    $query .= " ORDER BY s.PaymentDate DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $salaries = [];
    while ($row = $result->fetch_assoc()) {
        $salaries[] = $row;
    }
    
    echo json_encode($salaries);
}

function getSalary($conn) {
    $salaryId = $_GET['salaryId'] ?? '';
    
    if (empty($salaryId)) {
        throw new Exception('Salary ID is required');
    }
    
    $query = "SELECT s.*, e.EmployeeName 
              FROM Salaries s
              JOIN Employees e ON s.EmployeeID = e.EmployeeID
              WHERE s.SalaryID = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $salaryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        throw new Exception('Salary not found');
    }
}

function getEmployeeDetails($conn) {
    $employeeId = $_GET['employeeId'] ?? '';
    
    if (empty($employeeId)) {
        throw new Exception('Employee ID is required');
    }
    
    // This would come from your Employees table which should have salary info
    $query = "SELECT EmployeeName, BaseSalary FROM Employees WHERE EmployeeID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $employeeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'employeeName' => $row['EmployeeName'],
            'baseSalary' => $row['BaseSalary']
        ]);
    } else {
        throw new Exception('Employee not found');
    }
}

function addSalary($conn) {
    $data = $_POST;
    
    // Validate required fields
    $required = ['employeeId', 'amount', 'paymentMethod', 'paymentDate'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(['error' => "$field is required"]);
            return;
        }
    }

    // Calculate net salary
    $advance = isset($data['advanceSalary']) ? floatval($data['advanceSalary']) : 0;
    $amount = floatval($data['amount']);
    $netSalary = $amount - $advance;
    
    // Prepare the query
    $query = "INSERT INTO Salaries (EmployeeID, Amount, AdvanceSalary, NetSalary, 
                                   PaymentMethod, PaymentDate)
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    $stmt->bind_param(
        "idddss",
        $data['employeeId'],
        $amount,
        $advance,
        $netSalary,
        $data['paymentMethod'],
        $data['paymentDate']
    );
    
    // Execute and respond
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'salaryId' => $conn->insert_id]);
    } else {
        echo json_encode(['error' => 'Failed to add salary: ' . $conn->error]);
    }
}

function updateSalary($conn) {
    $data = $_POST;
    
    // Validate required fields
    if (empty($data['salaryId'])) {
        echo json_encode(['error' => 'Salary ID is required']);
        return;
    }
    
    $required = ['employeeId', 'amount', 'paymentMethod', 'paymentDate'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(['error' => "$field is required"]);
            return;
        }
    }

    // Calculate net salary
    $advance = isset($data['advanceSalary']) ? floatval($data['advanceSalary']) : 0;
    $amount = floatval($data['amount']);
    $netSalary = $amount - $advance;
    
    // Prepare the query
    $query = "UPDATE Salaries 
              SET EmployeeID = ?, Amount = ?, AdvanceSalary = ?, NetSalary = ?,
                  PaymentMethod = ?, PaymentDate = ?
              WHERE SalaryID = ?";
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    $stmt->bind_param(
        "idddssi",
        $data['employeeId'],
        $amount,
        $advance,
        $netSalary,
        $data['paymentMethod'],
        $data['paymentDate'],
        $data['salaryId']
    );
    
    // Execute and respond
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to update salary: ' . $conn->error]);
    }
}

function deleteSalary($conn) {
    $salaryId = $_GET['salaryId'] ?? '';
    
    if (empty($salaryId)) {
        throw new Exception('Salary ID is required');
    }
    
    $query = "DELETE FROM Salaries WHERE SalaryID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $salaryId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to delete salary: ' . $conn->error);
    }
}
?>