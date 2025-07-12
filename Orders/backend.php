<?php
include 'connection.php';
header('Content-Type: application/json');

// Helper function to get the next OrderID
function getNextOrderID($conn) {
    $stmt = $conn->query("SELECT MAX(OrderID) as max_id FROM Orders");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($result['max_id'] ? $result['max_id'] + 1 : 1);
}

// ==============================================
// INVENTORY MANAGEMENT FUNCTIONS
// ==============================================

/**
 * Check if item has sufficient inventory
 */
function checkInventory($conn, $itemId, $quantity) {
    $stmt = $conn->prepare("SELECT Quantity FROM Inventory WHERE ItemID = ?");
    $stmt->execute([$itemId]);
    $inventory = $stmt->fetch(PDO::FETCH_ASSOC);
    
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
function updateInventory($conn, $itemId, $quantity) {
    $stmt = $conn->prepare("UPDATE Inventory SET Quantity = Quantity - ? WHERE ItemID = ?");
    return $stmt->execute([$quantity, $itemId]);
}

// ==============================================
// API ENDPOINTS
// ==============================================

// Get customer details by ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['customer_id'])) {
    $customerId = $_GET['customer_id'];
    $stmt = $conn->prepare("SELECT * FROM Customers WHERE CustomerID = ?");
    $stmt->execute([$customerId]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($customer ?: ['error' => 'Customer not found']);
    exit;
}

// Get employee details by ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['employee_id'])) {
    $employeeId = $_GET['employee_id'];
    $stmt = $conn->prepare("SELECT * FROM Employees WHERE EmployeeID = ?");
    $stmt->execute([$employeeId]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($employee ?: ['error' => 'Employee not found']);
    exit;
}

// Get item details by ID (including inventory)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['item_id'])) {
    $itemId = $_GET['item_id'];
    
    $stmt = $conn->prepare("
        SELECT i.*, inv.Quantity 
        FROM Items i
        LEFT JOIN Inventory inv ON i.ItemID = inv.ItemID
        WHERE i.ItemID = ?
    ");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($item ?: ['error' => 'Item not found']);
    exit;
}

// Create or update an order (WITH INVENTORY MANAGEMENT)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    try {
        $conn->beginTransaction();

        // 1. Validate all items have sufficient inventory FIRST
        foreach ($data['items'] as $item) {
            checkInventory($conn, $item['item_id'], $item['quantity']);
        }

        // 2. Process order creation/update
        if (empty($data['order_id'])) {
            $orderId = getNextOrderID($conn);
        } else {
            $orderId = $data['order_id'];
            $stmt = $conn->prepare("DELETE FROM Orders WHERE OrderID = ?");
            $stmt->execute([$orderId]);
        }

        // 3. Insert items and update inventory
        $totalAmount = 0;
        foreach ($data['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unitPrice'];
            $totalAmount += $itemTotal;

            // Insert order item
            $stmt = $conn->prepare("INSERT INTO Orders 
                (OrderID, CustomerID, EmployeeID, ItemID, Quantity, UnitPrice, OrderDate, TotalAmount, Status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
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
            updateInventory($conn, $item['item_id'], $item['quantity']);
        }

        $conn->commit();
        echo json_encode([
            'success' => true, 
            'order_id' => $orderId,
            'message' => 'Dalabka si guul leh ayaa loo kaydiyay oo inventory-ga waa la cusboonaysiiyay'
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
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
        $conn->beginTransaction();
        
        // 1. Get all items from the order first
        $stmt = $conn->prepare("SELECT ItemID, Quantity FROM Orders WHERE OrderID = ?");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 2. Restore inventory for each item
        foreach ($items as $item) {
            $stmt = $conn->prepare("UPDATE Inventory SET Quantity = Quantity + ? WHERE ItemID = ?");
            $stmt->execute([$item['Quantity'], $item['ItemID']]);
        }
        
        // 3. Now delete the order
        $stmt = $conn->prepare("DELETE FROM Orders WHERE OrderID = ?");
        $stmt->execute([$orderId]);
        
        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Dalabka si guul leh ayaa loo tirtiray oo inventory-ga waa la soo celiyay'
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Get order details by OrderID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];

    $stmt = $conn->prepare("
        SELECT o.OrderID, o.CustomerID, c.CustomerName, o.EmployeeID, e.EmployeeName,
               o.OrderDate, o.Status, SUM(o.Quantity * o.UnitPrice) as TotalAmount
        FROM Orders o
        LEFT JOIN Customers c ON o.CustomerID = c.CustomerID
        LEFT JOIN Employees e ON o.EmployeeID = e.EmployeeID
        WHERE o.OrderID = ?
        GROUP BY o.OrderID
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        $stmt = $conn->prepare("
            SELECT o.ItemID, i.ItemName, o.Quantity, o.UnitPrice, 
                   (o.Quantity * o.UnitPrice) as TotalAmount,
                   inv.Quantity as CurrentInventory
            FROM Orders o
            JOIN Items i ON o.ItemID = i.ItemID
            LEFT JOIN Inventory inv ON i.ItemID = inv.ItemID
            WHERE o.OrderID = ?
        ");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
               SUM(o.Quantity * o.UnitPrice) as TotalAmount,
               o.Status, o.OrderDate
        FROM Orders o
        LEFT JOIN Customers c ON o.CustomerID = c.CustomerID
        LEFT JOIN Employees e ON o.EmployeeID = e.EmployeeID
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

    $query .= " GROUP BY o.OrderID ORDER BY o.OrderDate DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($orders);
    exit;
}

echo json_encode(['error' => 'Invalid request']);
?>