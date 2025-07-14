<?php

/**
 * Items Backend API
 * Uses centralized database and utilities
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/utils.php';

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
            getItems();
            break;
        case 'getItem':
            getItem();
            break;
        case 'getCategoryDetails':
            getCategoryDetails();
            break;
        case 'getSupplierDetails':
            getSupplierDetails();
            break;
        case 'getEmployeeDetails':
            getEmployeeDetails();
            break;
        case 'addItem':
            addItem();
            break;
        case 'updateItem':
            updateItem();
            break;
        case 'deleteItem':
            deleteItem();
            break;
        default:
            Utils::sendErrorResponse('Invalid action');
            break;
    }
} catch (Exception $e) {
    Utils::logError('Items API Error: ' . $e->getMessage());
    Utils::sendErrorResponse($e->getMessage());
}

function getItems()
{
    global $db;

    $search = $_GET['search'] ?? '';
    $categoryFilter = $_GET['categoryFilter'] ?? '';

    $query = "SELECT i.ItemID, i.ItemName, i.Price,
                     i.CategoryID, c.CategoryName, 
                     i.SupplierID, s.SupplierName, 
                     i.RegisteredByEmployeeID, e.EmployeeName,
                     i.Note, i.Description, i.CreatedDate 
              FROM items i
              JOIN categories c ON i.CategoryID = c.CategoryID
              JOIN suppliers s ON i.SupplierID = s.SupplierID
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

function getItem()
{
    global $db;

    $itemId = $_GET['itemId'] ?? '';

    if (empty($itemId)) {
        Utils::sendErrorResponse('Item ID is required');
        return;
    }

    $query = "SELECT i.*, c.CategoryName, s.SupplierName, e.EmployeeName 
              FROM items i
              JOIN categories c ON i.CategoryID = c.CategoryID
              JOIN suppliers s ON i.SupplierID = s.SupplierID
              JOIN Employees e ON i.RegisteredByEmployeeID = e.EmployeeID
              WHERE i.ItemID = ?";

    try {
        $item = $db->fetchOne($query, [$itemId]);

        if ($item) {
            Utils::sendSuccessResponse('Item retrieved successfully', $item);
        } else {
            Utils::sendErrorResponse('Item not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve item: ' . $e->getMessage());
    }
}

function getCategoryDetails()
{
    global $db;

    $categoryId = $_GET['categoryId'] ?? '';

    if (empty($categoryId)) {
        Utils::sendErrorResponse('Category ID is required');
        return;
    }

    $query = "SELECT CategoryName FROM categories WHERE CategoryID = ?";

    try {
        $category = $db->fetchOne($query, [$categoryId]);

        if ($category) {
            Utils::sendSuccessResponse('Category details retrieved successfully', ['categoryName' => $category['CategoryName']]);
        } else {
            Utils::sendErrorResponse('Category not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve category details: ' . $e->getMessage());
    }
}

function getSupplierDetails()
{
    global $db;

    $supplierId = $_GET['supplierId'] ?? '';

    if (empty($supplierId)) {
        Utils::sendErrorResponse('Supplier ID is required');
        return;
    }

    $query = "SELECT SupplierName FROM suppliers WHERE SupplierID = ?";

    try {
        $supplier = $db->fetchOne($query, [$supplierId]);

        if ($supplier) {
            Utils::sendSuccessResponse('Supplier details retrieved successfully', ['supplierName' => $supplier['SupplierName']]);
        } else {
            Utils::sendErrorResponse('Supplier not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve supplier details: ' . $e->getMessage());
    }
}

function getEmployeeDetails()
{
    global $db;

    $employeeId = $_GET['employeeId'] ?? '';

    if (empty($employeeId)) {
        Utils::sendErrorResponse('Employee ID is required');
        return;
    }

    $query = "SELECT EmployeeName FROM employees WHERE EmployeeID = ?";

    try {
        $employee = $db->fetchOne($query, [$employeeId]);

        if ($employee) {
            Utils::sendSuccessResponse('Employee details retrieved successfully', ['employeeName' => $employee['EmployeeName']]);
        } else {
            Utils::sendErrorResponse('Employee not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve employee details: ' . $e->getMessage());
    }
}

function addItem()
{
    global $db;

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $data = $_POST;
    }

    $required = ['ItemName', 'Price', 'CategoryID', 'SupplierID', 'RegisteredByEmployeeID'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            Utils::sendErrorResponse("$field is required");
            return;
        }
    }

    $itemName = trim($data['ItemName']);
    $price = (float) $data['Price'];
    $categoryId = (int) $data['CategoryID'];
    $supplierId = (int) $data['SupplierID'];
    $employeeId = (int) $data['RegisteredByEmployeeID'];
    $note = isset($data['Note']) ? trim($data['Note']) : '';
    $description = isset($data['Description']) ? trim($data['Description']) : '';
    $createdDate = isset($data['CreatedDate']) ? $data['CreatedDate'] : date('Y-m-d H:i:s');

    $query = "INSERT INTO tems (ItemName, Price, CategoryID, SupplierID, RegisteredByEmployeeID, Note, Description, CreatedDate) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    try {
        $db->query($query, [
            $itemName,
            $price,
            $categoryId,
            $supplierId,
            $employeeId,
            $note,
            $description,
            $createdDate
        ]);

        $itemId = $db->lastInsertId();
        Utils::sendSuccessResponse('Item added successfully', ['item_id' => $itemId]);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to add item: ' . $e->getMessage());
    }
}

function updateItem()
{
    global $db;

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $data = $_POST;
    }

    if (empty($data['ItemID'])) {
        Utils::sendErrorResponse('Item ID is required');
        return;
    }

    $required = ['ItemName', 'Price', 'CategoryID', 'SupplierID', 'RegisteredByEmployeeID'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            Utils::sendErrorResponse("$field is required");
            return;
        }
    }

    $itemId = (int) $data['ItemID'];
    $itemName = trim($data['ItemName']);
    $price = (float) $data['Price'];
    $categoryId = (int) $data['CategoryID'];
    $supplierId = (int) $data['SupplierID'];
    $employeeId = (int) $data['RegisteredByEmployeeID'];
    $note = isset($data['Note']) ? trim($data['Note']) : '';
    $description = isset($data['Description']) ? trim($data['Description']) : '';
    $createdDate = isset($data['CreatedDate']) ? $data['CreatedDate'] : date('Y-m-d H:i:s');

    $query = "UPDATE tems 
              SET ItemName = ?, Price = ?, CategoryID = ?, SupplierID = ?, 
                  RegisteredByEmployeeID = ?, Note = ?, Description = ?, 
                  CreatedDate = ?
              WHERE ItemID = ?";

    try {
        $db->query($query, [
            $itemName,
            $price,
            $categoryId,
            $supplierId,
            $employeeId,
            $note,
            $description,
            $createdDate,
            $itemId
        ]);

        Utils::sendSuccessResponse('Item updated successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update item: ' . $e->getMessage());
    }
}

function deleteItem()
{
    global $db;

    // Read JSON data if sent
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // If no JSON, use GET
    if (json_last_error() !== JSON_ERROR_NONE) {
        $itemId = $_GET['itemId'] ?? '';
    } else {
        $itemId = $data['itemId'] ?? '';
    }

    // Ensure itemID is provided
    if (empty($itemId)) {
        Utils::sendErrorResponse('Item ID is required');
        return;
    }

    try {
        // 1. First check if item exists
        $checkQuery = "SELECT ItemID FROM items WHERE ItemID = ?";
        $item = $db->fetchOne($checkQuery, [$itemId]);

        if (!$item) {
            Utils::sendErrorResponse('Item not found in database');
            return;
        }

        // 2. Check foreign key constraints
        $fkQuery = "SELECT COUNT(*) as count FROM inventory WHERE ItemID = ?";
        $fkResult = $db->fetchOne($fkQuery, [$itemId]);

        if ($fkResult && $fkResult['count'] > 0) {
            Utils::sendErrorResponse('This item is linked to inventory data, remove it first');
            return;
        }

        // 3. Delete the item
        $deleteQuery = "DELETE FROM items WHERE ItemID = ?";
        $db->query($deleteQuery, [$itemId]);

        Utils::sendSuccessResponse('Item deleted successfully', ['itemId' => $itemId]);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete item: ' . $e->getMessage());
    }
}
