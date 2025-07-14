<?php

/**
 * Orders Backend API
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

// Helper function to get the next OrderID
function getNextOrderID()
{
    global $db;
    $result = $db->fetchOne("SELECT MAX(OrderID) as max_id FROM orders");
    return ($result['max_id'] ? $result['max_id'] + 1 : 1);
}

// ==============================================
// INVENTORY MANAGEMENT FUNCTIONS
// ==============================================

/**
 * Check if item has sufficient inventory
 */
function checkInventory($itemId, $quantity)
{
    global $db;
    $inventory = $db->fetchOne("SELECT Quantity FROM inventory WHERE ItemID = ?", [$itemId]);

    if (!$inventory) {
        throw new Exception("Alaabta lama helin (ID: $itemId)");
    }

    if ($inventory['Quantity'] < $quantity) {
        throw new Exception("Alaab ma filnayn! ID: $itemId, Hadda: {$inventory['Quantity']}, La baahay: $quantity");
    }

    return true;
}

/**
 * Update inventory after order
 */
function updateInventory($itemId, $quantity)
{
    global $db;
    return $db->query("UPDATE inventory SET Quantity = Quantity - ? WHERE ItemID = ?", [$quantity, $itemId]);
}

// ==============================================
// API ENDPOINTS
// ==============================================

// Get customer details by ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['customer_id'])) {
    $customerId = $_GET['customer_id'];
    $customer = $db->fetchOne("SELECT * FROM customers WHERE CustomerID = ?", [$customerId]);

    echo json_encode($customer ?: ['error' => 'Customer not found']);
    exit;
}

// Get employee details by ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['employee_id'])) {
    $employeeId = $_GET['employee_id'];
    $employee = $db->fetchOne("SELECT * FROM employees WHERE EmployeeID = ?", [$employeeId]);

    echo json_encode($employee ?: ['error' => 'Employee not found']);
    exit;
}

// Get item details by ID (including inventory)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['item_id'])) {
    $itemId = $_GET['item_id'];

    $item = $db->fetchOne("
        SELECT i.*, inv.Quantity 
        FROM items i
        LEFT JOIN inventory inv ON i.ItemID = inv.ItemID
        WHERE i.ItemID = ?
    ", [$itemId]);

    echo json_encode($item ?: ['error' => 'Item not found']);
    exit;
}

// Create or update an order (WITH INVENTORY MANAGEMENT)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    try {
        $db->getConnection()->beginTransaction();

        // 1. Validate all items have sufficient inventory FIRST
        foreach ($data['items'] as $item) {
            checkInventory($item['item_id'], $item['quantity']);
        }

        // 2. Process order creation/update
        if (empty($data['order_id'])) {
            $orderId = getNextOrderID();
        } else {
            $orderId = $data['order_id'];
            // Delete existing order entries for this OrderID
            $db->query("DELETE FROM orders WHERE OrderID = ?", [$orderId]);
        }

        // 3. Insert items and update inventory
        $totalAmount = 0;
        foreach ($data['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unitPrice'];
            $totalAmount += $itemTotal;

            // Insert order item (OrderEntryID is auto-increment)
            $db->query("INSERT INTO orders 
                (OrderID, CustomerID, EmployeeID, ItemID, Quantity, UnitPrice, OrderDate, TotalAmount, Status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $orderId,
                $data['customer_id'],
                $data['employee_id'],
                $item['item_id'],
                $item['quantity'],
                $item['unitPrice'],
                $data['order_date'],
                $itemTotal,
                $data['status']
            ]);

            // Update inventory
            updateInventory($item['item_id'], $item['quantity']);
        }

        $db->getConnection()->commit();
        echo json_encode([
            'success' => true,
            'order_id' => $orderId,
            'message' => 'Dalabka si guul leh ayaa loo kaydiyay oo inventory-ga waa la cusboonaysiiyay'
        ]);
    } catch (Exception $e) {
        $db->getConnection()->rollBack();
        echo json_encode([
            'error' => $e->getMessage(),
            'code' => 'INVENTORY_ERROR'
        ]);
    }
    exit;
}

// Delete an order (WITH INVENTORY RESTORATION)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];

    try {
        $db->getConnection()->beginTransaction();

        // 1. Get all items from the order first
        $items = $db->fetchAll("SELECT ItemID, Quantity FROM orders WHERE OrderID = ?", [$orderId]);

        // 2. Restore inventory for each item
        foreach ($items as $item) {
            $db->query("UPDATE inventory SET Quantity = Quantity + ? WHERE ItemID = ?", [$item['Quantity'], $item['ItemID']]);
        }

        // 3. Now delete the order entries
        $db->query("DELETE FROM orders WHERE OrderID = ?", [$orderId]);

        $db->getConnection()->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Dalabka si guul leh ayaa loo tirtiray oo inventory-ga waa la soo celiyay'
        ]);
    } catch (Exception $e) {
        $db->getConnection()->rollBack();
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Get order details by OrderID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];

    // Get order summary
    $order = $db->fetchOne("
        SELECT o.OrderID, o.CustomerID, c.CustomerName, o.EmployeeID, e.EmployeeName,
               o.OrderDate, o.Status, SUM(o.TotalAmount) as TotalAmount
        FROM orders o
        LEFT JOIN customers c ON o.CustomerID = c.CustomerID
        LEFT JOIN employees e ON o.EmployeeID = e.EmployeeID
        WHERE o.OrderID = ?
        GROUP BY o.OrderID, o.CustomerID, c.CustomerName, o.EmployeeID, e.EmployeeName, o.OrderDate, o.Status
    ", [$orderId]);

    if ($order) {
        // Get individual order items
        $items = $db->fetchAll("
            SELECT o.ItemID, i.ItemName, o.Quantity, o.UnitPrice, 
                   o.TotalAmount,
                   inv.Quantity as CurrentInventory
            FROM orders o
            JOIN items i ON o.ItemID = i.ItemID
            LEFT JOIN inventory inv ON i.ItemID = inv.ItemID
            WHERE o.OrderID = ?
        ", [$orderId]);

        $order['items'] = $items;
        $order['total_items'] = count($items);
    }

    echo json_encode($order ?: ['error' => 'Order not found']);
    exit;
}

// Get all orders (for listing)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $statusFilter = isset($_GET['status']) ? $_GET['status'] : null;
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : null;

    $query = "
        SELECT o.OrderID, c.CustomerName, e.EmployeeName,
               COUNT(o.ItemID) as ItemsCount,
               SUM(o.TotalAmount) as TotalAmount,
               o.Status, o.OrderDate
        FROM orders o
        LEFT JOIN customers c ON o.CustomerID = c.CustomerID
        LEFT JOIN employees e ON o.EmployeeID = e.EmployeeID
        WHERE 1=1
    ";

    $params = [];

    if ($statusFilter) {
        $query .= " AND o.Status = ?";
        $params[] = $statusFilter;
    }

    if ($searchTerm) {
        $query .= " AND (c.CustomerName LIKE ? OR e.EmployeeName LIKE ? OR o.OrderID LIKE ?)";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
    }

    $query .= " GROUP BY o.OrderID, c.CustomerName, e.EmployeeName, o.Status, o.OrderDate ORDER BY o.OrderDate DESC";

    $orders = $db->fetchAll($query, $params);

    echo json_encode($orders);
    exit;
}

echo json_encode(['error' => 'Invalid request']);
