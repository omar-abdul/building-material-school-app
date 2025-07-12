<?php
include 'connection.php';

header('Content-Type: application/json');

// Get all employees
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['id'])) {
    $sql = "SELECT * FROM Employees";
    $result = $conn->query($sql);
    
    $employees = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Calculate expected salary (10% increase from base salary)
            $expectedSalary = $row['BaseSalary'] * 1.10;
            
            $employees[] = array(
                'id' => 'EMP-' . $row['EmployeeID'],
                'employeeId' => $row['EmployeeID'],
                'name' => $row['EmployeeName'],
                'position' => $row['Position'],
                'baseSalary' => $row['BaseSalary'],
                'expectedSalary' => number_format($expectedSalary, 2),
                'phone' => $row['Phone'],
                'email' => $row['Email'],
                'guarantor' => $row['Guarantor'],
                'address' => $row['Address'],
                'dateAdded' => $row['CreatedDate'],
                'status' => 'Active' // Assuming all are active for this example
            );
        }
    }
    echo json_encode($employees);
}

// Get single employee
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = str_replace('EMP-', '', $_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM Employees WHERE EmployeeID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $expectedSalary = $row['BaseSalary'] * 1.10;
        
        $employee = array(
            'id' => 'EMP-' . $row['EmployeeID'],
            'name' => $row['EmployeeName'],
            'position' => $row['Position'],
            'baseSalary' => $row['BaseSalary'],
            'expectedSalary' => number_format($expectedSalary, 2),
            'phone' => $row['Phone'],
            'email' => $row['Email'],
            'guarantor' => $row['Guarantor'],
            'address' => $row['Address'],
            'dateAdded' => $row['CreatedDate'],
            'status' => 'Active'
        );
        echo json_encode($employee);
    } else {
        echo json_encode(array('error' => 'Employee not found'));
    }
}

// Add or Update employee
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = isset($data['employeeId']) ? str_replace('EMP-', '', $data['employeeId']) : null;
    $name = $data['name'];
    $position = $data['position'];
    $baseSalary = $data['baseSalary'];
    $phone = $data['phone'];
    $email = $data['email'];
    $guarantor = $data['guarantor'];
    $address = $data['address'];
    
    if ($id) {
        // Update existing employee
        $stmt = $conn->prepare("UPDATE Employees SET EmployeeName=?, Position=?, BaseSalary=?, Phone=?, Email=?, Guarantor=?, Address=? WHERE EmployeeID=?");
        $stmt->bind_param("ssdssssi", $name, $position, $baseSalary, $phone, $email, $guarantor, $address, $id);
    } else {
        // Add new employee
        $stmt = $conn->prepare("INSERT INTO Employees (EmployeeName, Position, BaseSalary, Phone, Email, Guarantor, Address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssss", $name, $position, $baseSalary, $phone, $email, $guarantor, $address);
    }
    
    if ($stmt->execute()) {
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('error' => $stmt->error));
    }
}

// Delete employee
// if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
//     $id = str_replace('EMP-', '', $_GET['id']);
//     $stmt = $conn->prepare("DELETE FROM Employees WHERE EmployeeID = ?");
//     $stmt->bind_param("i", $id);
    
//     if ($stmt->execute()) {
//         echo json_encode(array('success' => true));
//     } else {
//         echo json_encode(array('error' => $stmt->error));
//     }
// }

// $conn->close();




// backend.php - DELETE Handler
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $deleteParams);
    $id = isset($deleteParams['id']) ? str_replace('EMP-', '', $deleteParams['id']) : null;

    if ($id) {
        $stmt = $conn->prepare("DELETE FROM Employees WHERE EmployeeID = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $stmt->error]);
        }
    } else {
        echo json_encode(['error' => 'ID lama helin']);
    }
    exit;
}
?>