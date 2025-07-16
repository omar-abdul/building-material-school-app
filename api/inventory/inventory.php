<?php

/**
 * Inventory Backend API
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

// Get the action from the request
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getInventory':
            getInventory();
            break;
        case 'addInventory':
            addInventory();
            break;
        case 'updateInventory':
            updateInventory();
            break;
        case 'deleteInventory':
            deleteInventory();
            break;
        case 'getInventoryItem':
            getInventoryItem();
            break;
        case 'getItemPrice':
            getItemPrice();
            break;
        case 'getItemDetails':
            getItemDetails();
            break;
        default:
            Utils::sendErrorResponse('Invalid action');
            break;
    }
} catch (Exception $e) {
    Utils::sendErrorResponse('Database error: ' . $e->getMessage());
}














function getItemPrice()
{
    global $db;

    $itemId = str_replace('ITM-', '', $_GET['item_id'] ?? '');

    if (empty($itemId)) {
        Utils::sendErrorResponse('Item ID is required');
        return;
    }

    $sql = "SELECT ItemID, ItemName, Price FROM items WHERE ItemID = ?";

    try {
        $item = $db->fetchOne($sql, [$itemId]);

        if ($item) {
            Utils::sendSuccessResponse('Item price retrieved successfully', [
                'ItemID' => 'ITM-' . $item['ItemID'],
                'ItemName' => $item['ItemName'],
                'Price' => number_format($item['Price'], 2)
            ]);
        } else {
            Utils::sendErrorResponse('Item not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve item price: ' . $e->getMessage());
    }
}





// // Get all inventory items
// function getInventory($conn) {
//     $search = $_GET['search'] ?? '';

//     $sql = "SELECT i.InventoryID, i.ItemID, i.ItemName, i.Price, i.Quantity, i.LastUpdated 
//             FROM inventory i
//             JOIN items it ON i.ItemID = it.ItemID
//             WHERE it.ItemName LIKE :search
//             ORDER BY i.LastUpdated DESC";

//     $stmt = $conn->prepare($sql);
//     $stmt->bindValue(':search', '%' . $search . '%');
//     $stmt->execute();

//     $inventory = $stmt->fetchAll();

//     // Format the data for the frontend
//     $formattedInventory = array_map(function($item) {
//         return [
//             'InventoryID' => 'INV-' . $item['InventoryID'],
//             'ItemID' => 'ITM-' . $item['ItemID'],
//             'ItemName' => $item['ItemName'],
//             'Quantity' => $item['Quantity'],
//             'LastUpdated' => date('Y-m-d h:i A', strtotime($item['LastUpdated']))
//         ];
//     }, $inventory);

//     echo json_encode(['status' => 'success', 'data' => $formattedInventory]);
// }

// Get a single inventory item
// function getInventoryItem($conn) {
//     $inventoryId = str_replace('INV-', '', $_GET['id']);

//     $sql = "SELECT i.InventoryID, i.ItemID, i.ItemName, i.Price, i.Quantity, i.LastUpdated 
//             FROM inventory i
//             JOIN items it ON i.ItemID = it.ItemID
//             WHERE i.InventoryID = :id";

//     $stmt = $conn->prepare($sql);
//     $stmt->bindParam(':id', $inventoryId);
//     $stmt->execute();

//     $item = $stmt->fetch();

//     if ($item) {
//         $formattedItem = [
//             'InventoryID' => 'INV-' . $item['InventoryID'],
//             'ItemID' => 'ITM-' . $item['ItemID'],
//             'ItemName' => $item['ItemName'],
//                 'Price' => number_format($item['Price'], 2),
//             'Quantity' => $item['Quantity'],
//             'LastUpdated' => date('Y-m-d h:i A', strtotime($item['LastUpdated']))
//         ];
//         echo json_encode(['status' => 'success', 'data' => $formattedItem]);
//     } else {
//         echo json_encode(['status' => 'error', 'message' => 'Item not found']);
//     }
// }





function getInventoryItem()
{
    global $db;

    $inventoryId = str_replace('INV-', '', $_GET['id'] ?? '');

    if (empty($inventoryId)) {
        Utils::sendErrorResponse('Inventory ID is required');
        return;
    }

    // Corrected SQL query - get ItemName from Items table (it) not Inventory table (i)
    $sql = "SELECT i.InventoryID, i.ItemID, it.ItemName, it.Price, i.Quantity, i.LastUpdated 
            FROM inventory i
            JOIN items it ON i.ItemID = it.ItemID
            WHERE i.InventoryID = ?";

    try {
        $item = $db->fetchOne($sql, [$inventoryId]);

        if ($item) {
            $formattedItem = [
                'InventoryID' => 'INV-' . $item['InventoryID'],
                'ItemID' => 'ITM-' . $item['ItemID'],
                'ItemName' => $item['ItemName'],
                'Price' => number_format($item['Price'], 2),
                'Quantity' => $item['Quantity'],
                'LastUpdated' => date('Y-m-d h:i A', strtotime($item['LastUpdated']))
            ];
            Utils::sendSuccessResponse('Inventory item retrieved successfully', $formattedItem);
        } else {
            Utils::sendErrorResponse('Item not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve inventory item: ' . $e->getMessage());
    }
}











// Get all inventory items with more details
function getInventory()
{
    global $db;

    $search = $_GET['search'] ?? '';
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    $offset = ($page - 1) * $limit;

    try {
        // Count total items for pagination
        $countSql = "SELECT COUNT(*) as total FROM inventory i
                    JOIN items it ON i.ItemID = it.ItemID";
        $params = [];

        if (!empty($search)) {
            $countSql .= " WHERE it.ItemName LIKE ?";
            $params[] = '%' . $search . '%';
        }

        $totalResult = $db->fetchOne($countSql, $params);
        $totalItems = $totalResult['total'];

        // Get inventory data
        $sql = "SELECT i.InventoryID, i.ItemID, it.ItemName, it.Price, 
                       i.Quantity, i.LastUpdated, cat.CategoryName
                FROM inventory i
                JOIN items it ON i.ItemID = it.ItemID
                LEFT JOIN categories cat ON it.CategoryID = cat.CategoryID";

        if (!empty($search)) {
            $sql .= " WHERE it.ItemName LIKE ?";
            $params = ['%' . $search . '%'];
        } else {
            $params = [];
        }

        $sql .= " ORDER BY i.LastUpdated DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $inventory = $db->fetchAll($sql, $params);

        // Format the data for the frontend
        $formattedInventory = array_map(function ($item) {
            return [
                'InventoryID' => 'INV-' . $item['InventoryID'],
                'ItemID' => 'ITM-' . $item['ItemID'],
                'ItemName' => $item['ItemName'],
                'Price' => number_format($item['Price'], 2),
                'Quantity' => $item['Quantity'],
                'Category' => $item['CategoryName'],
                'TotalValue' => number_format($item['Price'] * $item['Quantity'], 2),
                'LastUpdated' => date('Y-m-d h:i A', strtotime($item['LastUpdated']))
            ];
        }, $inventory);

        Utils::sendSuccessResponse('Inventory retrieved successfully', [
            'data' => $formattedInventory,
            'pagination' => [
                'totalItems' => $totalItems,
                'currentPage' => $page,
                'itemsPerPage' => $limit,
                'totalPages' => ceil($totalItems / $limit)
            ]
        ]);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve inventory: ' . $e->getMessage());
    }
}




// Add a new inventory item
function addInventory()
{
    global $db;

    $data = json_decode(file_get_contents('php://input'), true);

    $itemId = str_replace('ITM-', '', $data['itemId'] ?? '');
    $quantity = $data['quantity'] ?? 0;

    if (empty($itemId) || $quantity <= 0) {
        Utils::sendErrorResponse('Item ID and valid quantity are required');
        return;
    }

    $sql = "INSERT INTO inventory (ItemID, Quantity) VALUES (?, ?)";

    try {
        $db->query($sql, [$itemId, $quantity]);
        $newId = $db->lastInsertId();
        Utils::sendSuccessResponse('Inventory item added successfully', ['id' => 'INV-' . $newId]);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to add inventory item: ' . $e->getMessage());
    }
}

// Update an inventory item
function updateInventory()
{
    global $db;

    $data = json_decode(file_get_contents('php://input'), true);

    $inventoryId = str_replace('INV-', '', $data['inventoryId'] ?? '');
    $quantity = $data['quantity'] ?? 0;

    if (empty($inventoryId) || $quantity < 0) {
        Utils::sendErrorResponse('Inventory ID and valid quantity are required');
        return;
    }

    $sql = "UPDATE inventory SET Quantity = ? WHERE InventoryID = ?";

    try {
        $db->query($sql, [$quantity, $inventoryId]);
        Utils::sendSuccessResponse('Inventory item updated successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update inventory item: ' . $e->getMessage());
    }
}






















// Delete an inventory item
function deleteInventory()
{
    global $db;

    $inventoryId = str_replace('INV-', '', $_GET['id'] ?? '');

    if (empty($inventoryId)) {
        Utils::sendErrorResponse('Inventory ID is required');
        return;
    }

    $sql = "DELETE FROM inventory WHERE InventoryID = ?";

    try {
        $db->query($sql, [$inventoryId]);
        Utils::sendSuccessResponse('Inventory item deleted successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete inventory item: ' . $e->getMessage());
    }
}


function getItemDetails()
{
    global $db;

    $itemIdRaw = $_GET['item_id'] ?? '';
    $itemId = str_replace('ITM-', '', $itemIdRaw);

    if (empty($itemId)) {
        Utils::sendErrorResponse('Item ID is required');
        return;
    }

    $sql = "SELECT ItemName, Price FROM items WHERE ItemID = ?";

    try {
        $item = $db->fetchOne($sql, [$itemId]);

        if ($item) {
            Utils::sendSuccessResponse('Item details retrieved successfully', [
                'ItemName' => $item['ItemName'],
                'Price' => number_format($item['Price'], 2)
            ]);
        } else {
            Utils::sendErrorResponse('Item not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve item details: ' . $e->getMessage());
    }
}
