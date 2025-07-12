<?php
header('Content-Type: application/json');
require_once 'connection.php';

// Get the action from the request
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_inventory':
            getInventory($conn);
            break;
        case 'add_inventory':
            addInventory($conn);
            break;
        case 'update_inventory':
            updateInventory($conn);
            break;
        case 'delete_inventory':
            deleteInventory($conn);
            break;
        case 'get_inventory_item':
            getInventoryItem($conn);
            break;
            case 'get_item_price':
                getItemPrice($conn);
                break;
           case 'getItemDetails':
                getItemDetails($conn);
                break;
                // if ($_GET['action'] === 'getItemDetails' && isset($_GET['item_id'])) {
                //     $itemId = $_GET['item_id'];
                    
                //     // Tusaale: xiriir DB samee
                //     include 'db.php'; // Hubi inaad leedahay file-kan ama ku dar xidhiidh DB
                //     $stmt = $pdo->prepare("SELECT ItemName, Price FROM items WHERE ItemID = ?");
                //     $stmt->execute([$itemId]);
                //     $item = $stmt->fetch(PDO::FETCH_ASSOC);
                
                //     if ($item) {
                //         echo json_encode([
                //             'status' => 'success',
                //             'data' => [
                //                 'ItemName' => $item['ItemName'],
                //                 'Price' => $item['Price']
                //             ]
                //         ]);
                //     } else {
                //         echo json_encode([
                //             'status' => 'error',
                //             'message' => 'Item not found'
                //         ]);
                //     }
                //     exit;
                // }
                
              
            

                
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;


            
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}














function getItemPrice($conn) {
    $itemId = str_replace('ITM-', '', $_GET['item_id']);

    $sql = "SELECT ItemID, ItemName, Price FROM Items WHERE ItemID = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $itemId);
    $stmt->execute();

    $item = $stmt->fetch();

    if ($item) {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'ItemID' => 'ITM-' . $item['ItemID'],
                'ItemName' => $item['ItemName'],
                'Price' => number_format($item['Price'], 2)
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
    }
}





// // Get all inventory items
// function getInventory($conn) {
//     $search = $_GET['search'] ?? '';
    
//     $sql = "SELECT i.InventoryID, i.ItemID, i.ItemName, i.Price, i.Quantity, i.LastUpdated 
//             FROM Inventory i
//             JOIN Items it ON i.ItemID = it.ItemID
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
//             FROM Inventory i
//             JOIN Items it ON i.ItemID = it.ItemID
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





function getInventoryItem($conn) {
    $inventoryId = str_replace('INV-', '', $_GET['id']);
    
    // Corrected SQL query - get ItemName from Items table (it) not Inventory table (i)
    $sql = "SELECT i.InventoryID, i.ItemID, it.ItemName, it.Price, i.Quantity, i.LastUpdated 
            FROM Inventory i
            JOIN Items it ON i.ItemID = it.ItemID
            WHERE i.InventoryID = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $inventoryId);
    $stmt->execute();
    
    $item = $stmt->fetch();
    
    if ($item) {
        $formattedItem = [
            'InventoryID' => 'INV-' . $item['InventoryID'],
            'ItemID' => 'ITM-' . $item['ItemID'],
            'ItemName' => $item['ItemName'],
            'Price' => number_format($item['Price'], 2),
            'Quantity' => $item['Quantity'],
            'LastUpdated' => date('Y-m-d h:i A', strtotime($item['LastUpdated']))
        ];
        echo json_encode(['status' => 'success', 'data' => $formattedItem]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
    }
}











// Get all inventory items with more details
function getInventory($conn) {
    $search = $_GET['search'] ?? '';
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    $offset = ($page - 1) * $limit;
    
    // Count total items for pagination
    $countSql = "SELECT COUNT(*) as total FROM Inventory i
                JOIN Items it ON i.ItemID = it.ItemID
                WHERE it.ItemName LIKE :search";
    
    $countStmt = $conn->prepare($countSql);
    $countStmt->bindValue(':search', '%' . $search . '%');
    $countStmt->execute();
    $totalItems = $countStmt->fetch()['total'];
    
    // Get inventory data
    $sql = "SELECT i.InventoryID, i.ItemID, it.ItemName, it.Price, 
                   i.Quantity, i.LastUpdated, cat.CategoryName
            FROM Inventory i
            JOIN Items it ON i.ItemID = it.ItemID
            LEFT JOIN Categories cat ON it.CategoryID = cat.CategoryID
            WHERE it.ItemName LIKE :search
            ORDER BY i.LastUpdated DESC
            LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':search', '%' . $search . '%');
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $inventory = $stmt->fetchAll();
    
    // Format the data for the frontend
    $formattedInventory = array_map(function($item) {
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
    
    echo json_encode([
        'status' => 'success',
        'data' => $formattedInventory,
        'pagination' => [
            'totalItems' => $totalItems,
            'currentPage' => $page,
            'itemsPerPage' => $limit,
            'totalPages' => ceil($totalItems / $limit)
        ]
    ]);
}




// Add a new inventory item
function addInventory($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $itemId = str_replace('ITM-', '', $data['itemId']);
    $quantity = $data['quantity'];
    
    $sql = "INSERT INTO Inventory (ItemID, Quantity) VALUES (:itemId, :quantity)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':itemId', $itemId);
    $stmt->bindParam(':quantity', $quantity);
    
    if ($stmt->execute()) {
        $newId = $conn->lastInsertId();
        echo json_encode(['status' => 'success', 'message' => 'Inventory item added', 'id' => 'INV-' . $newId]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add inventory item']);
    }
}

// Update an inventory item
function updateInventory($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $inventoryId = str_replace('INV-', '', $data['inventoryId']);
    $quantity = $data['quantity'];
    
    $sql = "UPDATE Inventory SET Quantity = :quantity WHERE InventoryID = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':id', $inventoryId);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Inventory item updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update inventory item']);
    }
}






















// Delete an inventory item
function deleteInventory($conn) {
    $inventoryId = str_replace('INV-', '', $_GET['id']);
    
    $sql = "DELETE FROM Inventory WHERE InventoryID = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $inventoryId);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Inventory item deleted']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete inventory item']);
    }
}


function getItemDetails($conn) {
    $itemIdRaw = $_GET['item_id'] ?? ''; // Hel ID-ka alaabta
    $itemId = str_replace('ITM-', '', $itemIdRaw); // Ka saar "ITM-" haddii la jiro

    if (!$itemId) {
        echo json_encode(['status' => 'error', 'message' => 'Waa inaad gelisaa ID-ka alaabta']);
        return;
    }

    // Query database-ka si aad u hesho magaca alaabta iyo qiimaha
    $sql = "SELECT ItemName, Price FROM Items WHERE ItemID = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $itemId);
    $stmt->execute();

    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        // Soo celi magaca iyo qiimaha
        echo json_encode([
            'status' => 'success',
            'data' => [
                'ItemName' => $item['ItemName'],
                'Price' => number_format($item['Price'], 2) // Qiimaha laba jajab
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Alaabta lama helin']);
    }
}








?>