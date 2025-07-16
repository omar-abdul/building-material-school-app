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
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
 * Update inventory after order (decrease stock for sales)
 */
function updateInventory($itemId, $quantity)
{
    global $db;
    return $db->query("UPDATE inventory SET Quantity = Quantity - ? WHERE ItemID = ?", [$quantity, $itemId]);
}

// ==============================================
// API ENDPOINTS
// ==============================================

// Get all orders
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['orderId']) && !isset($_GET['customerId']) && !isset($_GET['employeeId']) && !isset($_GET['itemId'])) {
    try {
        $statusFilter = $_GET['status'] ?? '';
        $searchTerm = $_GET['search'] ?? '';

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
        Utils::sendSuccessResponse('Orders retrieved successfully', $orders);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve orders: ' . $e->getMessage());
    }
}

// Get single order
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['orderId'])) {
    try {
        $orderId = $_GET['orderId'];

        if (empty($orderId)) {
            Utils::sendErrorResponse('Order ID is required');
            return;
        }

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
            Utils::sendSuccessResponse('Order retrieved successfully', $order);
        } else {
            Utils::sendErrorResponse('Order not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve order: ' . $e->getMessage());
    }
}

// Get customer details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['customerId'])) {
    try {
        $customerId = $_GET['customerId'];

        if (empty($customerId)) {
            Utils::sendErrorResponse('Customer ID is required');
            return;
        }

        $customer = $db->fetchOne("SELECT * FROM customers WHERE CustomerID = ?", [$customerId]);

        if ($customer) {
            Utils::sendSuccessResponse('Customer details retrieved successfully', $customer);
        } else {
            Utils::sendErrorResponse('Customer not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve customer details: ' . $e->getMessage());
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

        $employee = $db->fetchOne("SELECT * FROM employees WHERE EmployeeID = ?", [$employeeId]);

        if ($employee) {
            Utils::sendSuccessResponse('Employee details retrieved successfully', $employee);
        } else {
            Utils::sendErrorResponse('Employee not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve employee details: ' . $e->getMessage());
    }
}

// Get item details (including inventory)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['itemId'])) {
    try {
        $itemId = $_GET['itemId'];

        if (empty($itemId)) {
            Utils::sendErrorResponse('Item ID is required');
            return;
        }

        $item = $db->fetchOne("
            SELECT i.*, inv.Quantity 
            FROM items i
            LEFT JOIN inventory inv ON i.ItemID = inv.ItemID
            WHERE i.ItemID = ?
        ", [$itemId]);

        if ($item) {
            Utils::sendSuccessResponse('Item details retrieved successfully', $item);
        } else {
            Utils::sendErrorResponse('Item not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve item details: ' . $e->getMessage());
    }
}

// Create or update an order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }

        if (empty($data['customer_id']) || empty($data['order_date']) || empty($data['status'])) {
            Utils::sendErrorResponse('Customer ID, order date, and status are required');
            return;
        }

        if (empty($data['items']) || !is_array($data['items'])) {
            Utils::sendErrorResponse('At least one item is required');
            return;
        }

        // Clean up IDs - remove prefixes and convert to integers
        $customerId = is_string($data['customer_id']) ? (int)str_replace('CUST-', '', $data['customer_id']) : (int)$data['customer_id'];
        $employeeId = !empty($data['employee_id']) ? (is_string($data['employee_id']) ? (int)str_replace('EMP-', '', $data['employee_id']) : (int)$data['employee_id']) : null;

        $db->beginTransaction();

        try {
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

                // Insert order item
                $db->query("INSERT INTO orders 
                    (OrderID, CustomerID, EmployeeID, ItemID, Quantity, UnitPrice, OrderDate, TotalAmount, Status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                    $orderId,
                    $customerId,
                    $employeeId,
                    $item['item_id'],
                    $item['quantity'],
                    $item['unitPrice'],
                    $data['order_date'],
                    $itemTotal,
                    $data['status']
                ]);

                // Update inventory (decrease stock for sales)
                updateInventory($item['item_id'], $item['quantity']);
            }

            $db->commit();
            Utils::sendSuccessResponse('Order saved successfully', ['order_id' => $orderId]);
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to save order: ' . $e->getMessage());
    }
}

// Update order
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }

        if (empty($data['order_id'])) {
            Utils::sendErrorResponse('Order ID is required');
            return;
        }

        if (empty($data['customer_id']) || empty($data['order_date']) || empty($data['status'])) {
            Utils::sendErrorResponse('Customer ID, order date, and status are required');
            return;
        }

        if (empty($data['items']) || !is_array($data['items'])) {
            Utils::sendErrorResponse('At least one item is required');
            return;
        }

        // Clean up IDs - remove prefixes and convert to integers
        $customerId = is_string($data['customer_id']) ? (int)str_replace('CUST-', '', $data['customer_id']) : (int)$data['customer_id'];
        $employeeId = !empty($data['employee_id']) ? (is_string($data['employee_id']) ? (int)str_replace('EMP-', '', $data['employee_id']) : (int)$data['employee_id']) : null;
        $orderId = $data['order_id'];

        $db->beginTransaction();

        try {
            // 1. Validate all items have sufficient inventory FIRST
            foreach ($data['items'] as $item) {
                checkInventory($item['item_id'], $item['quantity']);
            }

            // 2. Delete existing order entries for this OrderID
            $db->query("DELETE FROM orders WHERE OrderID = ?", [$orderId]);

            // 3. Insert items and update inventory
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unitPrice'];
                $totalAmount += $itemTotal;

                // Insert order item
                $db->query("INSERT INTO orders 
                    (OrderID, CustomerID, EmployeeID, ItemID, Quantity, UnitPrice, OrderDate, TotalAmount, Status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                    $orderId,
                    $customerId,
                    $employeeId,
                    $item['item_id'],
                    $item['quantity'],
                    $item['unitPrice'],
                    $data['order_date'],
                    $itemTotal,
                    $data['status']
                ]);

                // Update inventory (decrease stock for sales)
                updateInventory($item['item_id'], $item['quantity']);
            }

            $db->commit();
            Utils::sendSuccessResponse('Order updated successfully', ['order_id' => $orderId]);
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update order: ' . $e->getMessage());
    }
}

// Delete an order
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = isset($data['orderId']) ? $data['orderId'] : ($_GET['orderId'] ?? '');

        if (empty($orderId)) {
            Utils::sendErrorResponse('Order ID is required');
            return;
        }

        $db->beginTransaction();

        try {
            // 1. Get all items from the order first
            $items = $db->fetchAll("SELECT ItemID, Quantity FROM orders WHERE OrderID = ?", [$orderId]);

            if (empty($items)) {
                Utils::sendErrorResponse('Order not found');
                return;
            }

            // 2. Restore inventory for each item
            foreach ($items as $item) {
                $db->query("UPDATE inventory SET Quantity = Quantity + ? WHERE ItemID = ?", [$item['Quantity'], $item['ItemID']]);
            }

            // 3. Now delete the order entries
            $db->query("DELETE FROM orders WHERE OrderID = ?", [$orderId]);

            $db->commit();
            Utils::sendSuccessResponse('Order deleted successfully');
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete order: ' . $e->getMessage());
    }
}
