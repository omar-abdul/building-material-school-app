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

// Get all items
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['itemId']) && !isset($_GET['categoryId']) && !isset($_GET['supplierId']) && !isset($_GET['employeeId'])) {
    try {
        $search = $_GET['search'] ?? '';
        $categoryFilter = $_GET['categoryFilter'] ?? '';

        $query = "SELECT i.ItemID, i.ItemName, i.Price,
                         i.CategoryID, c.CategoryName, 
                         i.SupplierID, s.SupplierName, 
                         i.RegisteredByEmployeeID, e.EmployeeName,
                         i.Note, i.Description, i.CreatedDate,
                         COALESCE(inv.Quantity, 0) as Quantity
                  FROM items i
                  JOIN categories c ON i.CategoryID = c.CategoryID
                  JOIN suppliers s ON i.SupplierID = s.SupplierID
                  JOIN employees e ON i.RegisteredByEmployeeID = e.EmployeeID
                  LEFT JOIN inventory inv ON i.ItemID = inv.ItemID
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

        $items = $db->fetchAll($query, $params);
        Utils::sendSuccessResponse('Items retrieved successfully', $items);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve items: ' . $e->getMessage());
    }
}

// Get single item
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['itemId'])) {
    try {
        $itemId = $_GET['itemId'];

        if (empty($itemId)) {
            Utils::sendErrorResponse('Item ID is required');
            return;
        }

        $query = "SELECT i.*, c.CategoryName, s.SupplierName, e.EmployeeName,
                         COALESCE(inv.Quantity, 0) as Quantity
                  FROM items i
                  JOIN categories c ON i.CategoryID = c.CategoryID
                  JOIN suppliers s ON i.SupplierID = s.SupplierID
                  JOIN employees e ON i.RegisteredByEmployeeID = e.EmployeeID
                  LEFT JOIN inventory inv ON i.ItemID = inv.ItemID
                  WHERE i.ItemID = ?";

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

// Get category details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['categoryId'])) {
    try {
        $categoryId = $_GET['categoryId'];

        if (empty($categoryId)) {
            Utils::sendErrorResponse('Category ID is required');
            return;
        }

        $query = "SELECT CategoryName FROM categories WHERE CategoryID = ?";
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

// Get supplier details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['supplierId'])) {
    try {
        $supplierId = $_GET['supplierId'];

        if (empty($supplierId)) {
            Utils::sendErrorResponse('Supplier ID is required');
            return;
        }

        $query = "SELECT SupplierName FROM suppliers WHERE SupplierID = ?";
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

// Get employee details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['employeeId'])) {
    try {
        $employeeId = $_GET['employeeId'];

        if (empty($employeeId)) {
            Utils::sendErrorResponse('Employee ID is required');
            return;
        }

        $query = "SELECT EmployeeName FROM employees WHERE EmployeeID = ?";
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

// Add new item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

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
        $quantity = (int) ($data['Quantity'] ?? 0);
        $categoryId = (int) $data['CategoryID'];
        $supplierId = (int) $data['SupplierID'];
        $employeeId = (int) $data['RegisteredByEmployeeID'];
        $note = isset($data['Note']) ? trim($data['Note']) : '';
        $description = isset($data['Description']) ? trim($data['Description']) : '';
        $createdDate = isset($data['CreatedDate']) ? $data['CreatedDate'] : date('Y-m-d H:i:s');

        // Start transaction
        $db->beginTransaction();

        try {
            // Insert item
            $query = "INSERT INTO items (ItemName, Price, CategoryID, SupplierID, RegisteredByEmployeeID, Note, Description, CreatedDate) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

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

            // Insert inventory record if quantity > 0
            if ($quantity > 0) {
                $inventoryQuery = "INSERT INTO inventory (ItemID, Quantity, LastUpdated) VALUES (?, ?, ?)";
                $db->query($inventoryQuery, [$itemId, $quantity, $createdDate]);
            }

            $db->commit();
            Utils::sendSuccessResponse('Item added successfully', ['item_id' => $itemId]);
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to add item: ' . $e->getMessage());
    }
}

// Update item
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

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
        $quantity = (int) ($data['Quantity'] ?? 0);
        $categoryId = (int) $data['CategoryID'];
        $supplierId = (int) $data['SupplierID'];
        $employeeId = (int) $data['RegisteredByEmployeeID'];
        $note = isset($data['Note']) ? trim($data['Note']) : '';
        $description = isset($data['Description']) ? trim($data['Description']) : '';
        $createdDate = isset($data['CreatedDate']) ? $data['CreatedDate'] : date('Y-m-d H:i:s');

        // Start transaction
        $db->beginTransaction();

        try {
            // Update item
            $query = "UPDATE items 
                      SET ItemName = ?, Price = ?, CategoryID = ?, SupplierID = ?, 
                          RegisteredByEmployeeID = ?, Note = ?, Description = ?, 
                          CreatedDate = ?
                      WHERE ItemID = ?";

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

            // Update or insert inventory record
            $checkInventoryQuery = "SELECT InventoryID FROM inventory WHERE ItemID = ?";
            $existingInventory = $db->fetchOne($checkInventoryQuery, [$itemId]);

            if ($existingInventory) {
                // Update existing inventory record
                $updateInventoryQuery = "UPDATE inventory SET Quantity = ?, LastUpdated = ? WHERE ItemID = ?";
                $db->query($updateInventoryQuery, [$quantity, date('Y-m-d H:i:s'), $itemId]);
            } else {
                // Insert new inventory record
                $insertInventoryQuery = "INSERT INTO inventory (ItemID, Quantity, LastUpdated) VALUES (?, ?, ?)";
                $db->query($insertInventoryQuery, [$itemId, $quantity, date('Y-m-d H:i:s')]);
            }

            $db->commit();
            Utils::sendSuccessResponse('Item updated successfully');
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update item: ' . $e->getMessage());
    }
}

// Delete item
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $itemId = isset($data['itemId']) ? $data['itemId'] : ($_GET['itemId'] ?? '');

        if (empty($itemId)) {
            Utils::sendErrorResponse('Item ID is required');
            return;
        }

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
