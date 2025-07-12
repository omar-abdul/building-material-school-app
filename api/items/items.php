<?php
/**
 * Items Backend API
 * Uses centralized database and utilities
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/utils.php';

// Set headers for API
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$db = Database::getInstance();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getItems':
            getItems($db);
            break;
        case 'getItem':
            getItem($db);
            break;
        case 'getCategoryDetails':
            getCategoryDetails($db);
            break;
        case 'getSupplierDetails':
            getSupplierDetails($db);
            break;
        case 'getEmployeeDetails':
            getEmployeeDetails($db);
            break;
        case 'addItem':
            addItem($db);
            break;
        case 'updateItem':
            updateItem($db);
            break;
        case 'deleteItem':
            deleteItem($db);
            break;
        default:
            Utils::sendErrorResponse('Invalid action');
            break;
    }
} catch (Exception $e) {
    Utils::logError('Items API Error: ' . $e->getMessage());
    Utils::sendErrorResponse($e->getMessage());
}

function getItems($db) {
    $search = $_GET['search'] ?? '';
    $categoryFilter = $_GET['categoryFilter'] ?? '';
    
    $query = "SELECT i.ItemID, i.ItemName, i.Price,
                     i.CategoryID, c.CategoryName, 
                     i.SupplierID, s.SupplierName, 
                     i.RegisteredByEmployeeID, e.EmployeeName,
                     i.Note, i.Description, i.CreatedDate 
              FROM Items i
              JOIN Categories c ON i.CategoryID = c.CategoryID
              JOIN Suppliers s ON i.SupplierID = s.SupplierID
              JOIN Employees e ON i.RegisteredByEmployeeID = e.EmployeeID
              WHERE 1=1";
    
    $params = [];
    
    if (!empty($search)) {
        $query .= " AND (i.ItemName LIKE ? OR i.Description LIKE ? OR c.CategoryName LIKE ? OR s.SupplierName LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    }
    
    if (!empty($categoryFilter)) {
        $query .= " AND c.CategoryName = ?";
        $params[] = $categoryFilter;
    }
    
    $query .= " ORDER BY i.CreatedDate DESC";
    
    try {
        $items = $db->fetchAll($query, $params);
        Utils::sendSuccessResponse('Items retrieved successfully', $items);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve items: ' . $e->getMessage());
    }
}

function getItem($conn) {
    $itemId = $_GET['itemId'] ?? '';
    
    if (empty($itemId)) {
        throw new Exception('Item ID is required');
    }
    
    $query = "SELECT i.*, c.CategoryName, s.SupplierName, e.EmployeeName 
              FROM Items i
              JOIN Categories c ON i.CategoryID = c.CategoryID
              JOIN Suppliers s ON i.SupplierID = s.SupplierID
              JOIN Employees e ON i.RegisteredByEmployeeID = e.EmployeeID
              WHERE i.ItemID = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        throw new Exception('Item not found');
    }
}

function getCategoryDetails($conn) {
    $categoryId = $_GET['categoryId'] ?? '';
    
    if (empty($categoryId)) {
        throw new Exception('Category ID is required');
    }
    
    $query = "SELECT CategoryName FROM Categories WHERE CategoryID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['categoryName' => $row['CategoryName']]);
    } else {
        throw new Exception('Category not found');
    }
}

function getSupplierDetails($conn) {
    $supplierId = $_GET['supplierId'] ?? '';
    
    if (empty($supplierId)) {
        throw new Exception('Supplier ID is required');
    }
    
    $query = "SELECT SupplierName FROM Suppliers WHERE SupplierID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $supplierId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['supplierName' => $row['SupplierName']]);
    } else {
        throw new Exception('Supplier not found');
    }
}

function getEmployeeDetails($conn) {
    $employeeId = $_GET['employeeId'] ?? '';
    
    if (empty($employeeId)) {
        throw new Exception('Employee ID is required');
    }
    
    $query = "SELECT EmployeeName FROM Employees WHERE EmployeeID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $employeeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['employeeName' => $row['EmployeeName']]);
    } else {
        throw new Exception('Employee not found');
    }
}

function addItem($conn) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $data = $_POST;
    }
    
    $required = ['ItemName', 'Price', 'CategoryID', 'SupplierID', 'RegisteredByEmployeeID'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(['status' => 'error', 'message' => "$field is required"]);
            return;
        }
    }

    $itemName = sanitize_input($data['ItemName'], $conn);
    $price = (float) sanitize_input($data['Price'], $conn);
    $categoryId = (int) sanitize_input($data['CategoryID'], $conn);
    $supplierId = (int) sanitize_input($data['SupplierID'], $conn);
    $employeeId = (int) sanitize_input($data['RegisteredByEmployeeID'], $conn);
    $note = isset($data['Note']) ? sanitize_input($data['Note'], $conn) : '';
    $description = isset($data['Description']) ? sanitize_input($data['Description'], $conn) : '';
    $createdDate = isset($data['CreatedDate']) ? sanitize_input($data['CreatedDate'], $conn) : date('Y-m-d H:i:s');

    $query = "INSERT INTO Items (ItemName, Price, CategoryID, SupplierID, RegisteredByEmployeeID, Note, Description, CreatedDate) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sdiissss",
        $itemName,
        $price,
        $categoryId,
        $supplierId,
        $employeeId,
        $note,
        $description,
        $createdDate
    );
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Item added successfully', 'item_id' => $conn->insert_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add item: ' . $conn->error]);
    }
}

function updateItem($conn) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $data = $_POST;
    }
    
    if (empty($data['ItemID'])) {
        echo json_encode(['status' => 'error', 'message' => 'Item ID is required']);
        return;
    }
    
    $required = ['ItemName', 'Price', 'CategoryID', 'SupplierID', 'RegisteredByEmployeeID'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(['status' => 'error', 'message' => "$field is required"]);
            return;
        }
    }

    $itemId = (int) sanitize_input($data['ItemID'], $conn);
    $itemName = sanitize_input($data['ItemName'], $conn);
    $price = (float) sanitize_input($data['Price'], $conn);
    $categoryId = (int) sanitize_input($data['CategoryID'], $conn);
    $supplierId = (int) sanitize_input($data['SupplierID'], $conn);
    $employeeId = (int) sanitize_input($data['RegisteredByEmployeeID'], $conn);
    $note = isset($data['Note']) ? sanitize_input($data['Note'], $conn) : '';
    $description = isset($data['Description']) ? sanitize_input($data['Description'], $conn) : '';
    $createdDate = isset($data['CreatedDate']) ? sanitize_input($data['CreatedDate'], $conn) : date('Y-m-d H:i:s');

    $query = "UPDATE Items 
              SET ItemName = ?, Price = ?, CategoryID = ?, SupplierID = ?, 
                  RegisteredByEmployeeID = ?, Note = ?, Description = ?, 
                  CreatedDate = ?
              WHERE ItemID = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sdiissssi",
        $itemName,
        $price,
        $categoryId,
        $supplierId,
        $employeeId,
        $note,
        $description,
        $createdDate,
        $itemId
    );
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Item updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update item: ' . $conn->error]);
    }
}

function deleteItem($conn) {
    // Akhri xogta JSON haddii la soo diriyo
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Haddii aan JSON la helin, isticmaal GET
    if (json_last_error() !== JSON_ERROR_NONE) {
        $itemId = $_GET['itemId'] ?? '';
    } else {
        $itemId = $data['itemId'] ?? '';
    }
    
    // Hubi in itemID la siiyay
    if (empty($itemId)) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Item ID ayaa loo baahan yahay',
            'xogta_la_helay' => [
                'GET' => $_GET, 
                'POST' => $_POST, 
                'JSON' => $data
            ]
        ]);
        return;
    }

    // 1. Marka hore hubi in item-ku jiro
    $checkQuery = "SELECT ItemID FROM Items WHERE ItemID = ?";
    $checkStmt = $conn->prepare($checkQuery);
    
    if (!$checkStmt) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Qalad diyaarinta hubinta: ' . $conn->error
        ]);
        return;
    }
    
    $checkStmt->bind_param('i', $itemId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Item-kaan ma jiro database-ka',
            'itemId' => $itemId
        ]);
        return;
    }

    // 2. Tijaabo foreign key constraints
    $fkQuery = "SELECT COUNT(*) as count FROM Inventory WHERE ItemID = ?";
    $fkStmt = $conn->prepare($fkQuery);
    
    if ($fkStmt) {
        $fkStmt->bind_param('i', $itemId);
        $fkStmt->execute();
        $fkResult = $fkStmt->get_result();
        $row = $fkResult->fetch_assoc();
        
        if ($row['count'] > 0) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Item-kan waxaa ku xiran xog Inventory-ga, hawl ka saar marka hore'
            ]);
            return;
        }
    }

    // 3. Delete item-ka
    $deleteQuery = "DELETE FROM Items WHERE ItemID = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    
    if (!$deleteStmt) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Qalad diyaarinta delete: ' . $conn->error
        ]);
        return;
    }
    
    $deleteStmt->bind_param('i', $itemId);
    
    if ($deleteStmt->execute()) {
        if ($deleteStmt->affected_rows > 0) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Item-ka si guul leh ayaa loo delete gareeyay',
                'itemId' => $itemId
            ]);
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Item-kaan lama delete garan karo (qalad aan la garanayn)'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Qalad delete gareynta: ' . $deleteStmt->error,
            'error_code' => $deleteStmt->errno
        ]);
    }
}
    

?>