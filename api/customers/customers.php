<?php
require_once 'connection.php';

header('Content-Type: application/json');

// Handle different actions
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getCustomers':
            getCustomers();
            break;
        case 'addCustomer':
            addCustomer();
            break;
        case 'updateCustomer':
            updateCustomer();
            break;
        case 'deleteCustomer':
            deleteCustomer();
            break;
        case 'getCustomer':
            getCustomer();
            break;
        case 'getOrderHistory':
            getOrderHistory();
            break;
        case 'searchCustomers':
            searchCustomers();
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function getCustomers() {
    global $conn;
    
    $sql = "SELECT 
                CONCAT('CUST-', CustomerID) as CustomerID,
                CustomerName as Name,
                Phone,
                Email,
                Address,
                DATE_FORMAT(CreatedDate, '%Y-%m-%d') as DateAdded
            FROM Customers
            ORDER BY CreatedDate DESC";
    
    $result = $conn->query($sql);
    
    $customers = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
    
    echo json_encode($customers);
}

function addCustomer() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = $data['name'] ?? '';
    $phone = $data['phone'] ?? '';
    $email = $data['email'] ?? '';
    $address = $data['address'] ?? '';
    
    if (empty($name) || empty($phone)) {
        throw new Exception('Name and phone are required');
    }
    
    $stmt = $conn->prepare("INSERT INTO Customers (CustomerName, Phone, Email, Address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $email, $address);
    
    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'customerId' => 'CUST-' . $newId
        ]);
    } else {
        throw new Exception('Failed to add customer');
    }
}

function updateCustomer() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = str_replace('CUST-', '', $data['id'] ?? '');
    $name = $data['name'] ?? '';
    $phone = $data['phone'] ?? '';
    $email = $data['email'] ?? '';
    $address = $data['address'] ?? '';
    
    if (empty($id) || empty($name) || empty($phone)) {
        throw new Exception('ID, name and phone are required');
    }
    
    $stmt = $conn->prepare("UPDATE Customers SET CustomerName=?, Phone=?, Email=?, Address=? WHERE CustomerID=?");
    $stmt->bind_param("ssssi", $name, $phone, $email, $address, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to update customer');
    }
}

function deleteCustomer() {
    global $conn;
    
    $id = str_replace('CUST-', '', $_GET['id'] ?? '');
    
    if (empty($id)) {
        throw new Exception('Customer ID is required');
    }
    
    $stmt = $conn->prepare("DELETE FROM Customers WHERE CustomerID=?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to delete customer');
    }
}

function getCustomer() {
    global $conn;
    
    $id = str_replace('CUST-', '', $_GET['id'] ?? '');
    
    if (empty($id)) {
        throw new Exception('Customer ID is required');
    }
    
    $stmt = $conn->prepare("SELECT 
                                CONCAT('CUST-', CustomerID) as CustomerID,
                                CustomerName as Name,
                                Phone,
                                Email,
                                Address,
                                DATE_FORMAT(CreatedDate, '%Y-%m-%d') as DateAdded
                            FROM Customers
                            WHERE CustomerID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        throw new Exception('Customer not found');
    }
}

// function getOrderHistory() {
//     global $conn;
    
//     $customerId = str_replace('CUST-', '', $_GET['id'] ?? '');
    
//     if (empty($customerId)) {
//         throw new Exception('Customer ID is required');
//     }
    
//     // This is a simplified example - you would need to join with your Orders table
//     $sql = "SELECT 
//                 CONCAT('ORD-', OrderID) as OrderID,
//                 DATE_FORMAT(OrderDate, '%Y-%m-%d') as OrderDate,
//                 (SELECT COUNT(*) FROM OrderItems WHERE OrderID = Orders.OrderID) as ItemsCount,
//                 TotalAmount as Total,
//                 Status
//             FROM Orders
//             WHERE CustomerID = ?
//             ORDER BY OrderDate DESC";
    
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("i", $customerId);
//     $stmt->execute();
//     $result = $stmt->get_result();
    
//     $orders = [];
//     if ($result->num_rows > 0) {
//         while($row = $result->fetch_assoc()) {
//             $orders[] = $row;
//         }
//     }
    
//     echo json_encode($orders);
// }

function searchCustomers() {
    global $conn;
    
    $searchTerm = $_GET['term'] ?? '';
    
    $searchTerm = '%' . $searchTerm . '%';
    
    $stmt = $conn->prepare("SELECT 
                                CONCAT('CUST-', CustomerID) as CustomerID,
                                CustomerName as Name,
                                Phone,
                                Email,
                                Address,
                                DATE_FORMAT(CreatedDate, '%Y-%m-%d') as DateAdded
                            FROM Customers
                            WHERE CustomerName LIKE ? OR Phone LIKE ? OR Email LIKE ?
                            ORDER BY CustomerName");
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $customers = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
    
    echo json_encode($customers);
}

function getOrderHistory() {
    global $conn;
    
    $customerId = str_replace('CUST-', '', $_GET['id'] ?? '');
    
    if (empty($customerId)) {
        throw new Exception('Customer ID is required');
    }
    
    // Updated query to match your database schema
    $sql = "SELECT 
                CONCAT('ORD-', o.OrderID) as OrderID,
                DATE_FORMAT(o.OrderDate, '%Y-%m-%d') as OrderDate,
                o.TotalAmount as Total,
                o.Status,
                COUNT(t.TransactionID) as TransactionsCount,
                IFNULL(SUM(t.Amount), 0) as PaidAmount
            FROM Orders o
            LEFT JOIN Transactions t ON o.OrderID = t.OrderID
            WHERE o.CustomerID = ?
            GROUP BY o.OrderID
            ORDER BY o.OrderDate DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    
    echo json_encode($orders);
}












?>







