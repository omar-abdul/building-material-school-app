<?php
require_once 'connection.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getTransactions':
            getTransactions($conn);
            break;
        case 'getTransaction':
            getTransaction($conn);
            break;
        case 'getOrderDetails':
            getOrderDetails($conn);
            break;
        case 'addTransaction':
            addTransaction($conn);
            break;
        case 'updateTransaction':
            updateTransaction($conn);
            break;
        case 'deleteTransaction':
            deleteTransaction($conn);
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function getTransactions($conn) {
    $search = $_GET['search'] ?? '';
    $paymentMethod = $_GET['paymentMethod'] ?? '';
    
    $query = "SELECT t.TransactionID, t.OrderID, o.CustomerID, c.CustomerName, 
                     t.PaymentMethod, t.Amount AS AmountPaid, 
                     (o.TotalAmount - t.Amount) AS Balance,
                     t.TransactionDate, t.Status
              FROM Transactions t
              JOIN Orders o ON t.OrderID = o.OrderID
              JOIN Customers c ON o.CustomerID = c.CustomerID
              WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $query .= " AND (t.TransactionID LIKE ? OR o.OrderID LIKE ? OR c.CustomerName LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        $types .= 'sss';
    }
    
    if (!empty($paymentMethod)) {
        $query .= " AND t.PaymentMethod = ?";
        $params[] = $paymentMethod;
        $types .= 's';
    }
    
    $query .= " ORDER BY t.TransactionDate DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    echo json_encode($transactions);
}

function getTransaction($conn) {
    $transactionId = $_GET['transactionId'] ?? '';
    
    if (empty($transactionId)) {
        throw new Exception('Transaction ID is required');
    }
    
    $query = "SELECT t.*, o.CustomerID, c.CustomerName, o.TotalAmount
              FROM Transactions t
              JOIN Orders o ON t.OrderID = o.OrderID
              JOIN Customers c ON o.CustomerID = c.CustomerID
              WHERE t.TransactionID = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $transactionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $transaction = $result->fetch_assoc();
        
        // Calculate discount if any
        $discountPercentage = 0;
        $discountAmount = 0;
        if ($transaction['Amount'] < $transaction['TotalAmount']) {
            $discountAmount = $transaction['TotalAmount'] - $transaction['Amount'];
            $discountPercentage = ($discountAmount / $transaction['TotalAmount']) * 100;
        }
        
        $transaction['DiscountPercentage'] = round($discountPercentage, 2);
        $transaction['DiscountAmount'] = round($discountAmount, 2);
        
        echo json_encode($transaction);
    } else {
        throw new Exception('Transaction not found');
    }
}

function getOrderDetails($conn) {
    $orderId = $_GET['orderId'] ?? '';
    
    if (empty($orderId)) {
        throw new Exception('Order ID is required');
    }
    
    $query = "SELECT o.OrderID, o.CustomerID, c.CustomerName, o.TotalAmount
              FROM Orders o
              JOIN Customers c ON o.CustomerID = c.CustomerID
              WHERE o.OrderID = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        echo json_encode($order);
    } else {
        throw new Exception('Order not found');
    }
}

function addTransaction($conn) {
    $data = $_POST;
    
    // Validate required fields
    $required = ['orderId', 'paymentMethod', 'amount', 'status'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(['error' => "$field is required"]);
            return;
        }
    }

    // Prepare the query
    $query = "INSERT INTO Transactions (OrderID, PaymentMethod, Amount, Balance, Status, TransactionDate) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    
    // Calculate balance (get order total first)
    $orderQuery = "SELECT TotalAmount FROM Orders WHERE OrderID = ?";
    $orderStmt = $conn->prepare($orderQuery);
    $orderStmt->bind_param('i', $data['orderId']);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();
    
    if ($orderResult->num_rows === 0) {
        echo json_encode(['error' => 'Order not found']);
        return;
    }
    
    $order = $orderResult->fetch_assoc();
    $totalAmount = $order['TotalAmount'];
    $amountPaid = $data['amount'];
    $balance = $totalAmount - $amountPaid;
    
    // Set transaction date (use current date if not provided)
    $transactionDate = !empty($data['transactionDate']) ? $data['transactionDate'] : date('Y-m-d H:i:s');
    
    // Bind parameters
    $stmt->bind_param(
        "isdsss",
        $data['orderId'],
        $data['paymentMethod'],
        $amountPaid,
        $balance,
        $data['status'],
        $transactionDate
    );
    
    // Execute and respond
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'transactionId' => $conn->insert_id]);
    } else {
        echo json_encode(['error' => 'Failed to add transaction: ' . $conn->error]);
    }
}

function updateTransaction($conn) {
    $data = $_POST;
    
    // Validate required fields
    if (empty($data['transactionId'])) {
        echo json_encode(['error' => 'Transaction ID is required']);
        return;
    }
    
    $required = ['orderId', 'paymentMethod', 'amount', 'status'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(['error' => "$field is required"]);
            return;
        }
    }

    // Prepare the query
    $query = "UPDATE Transactions 
              SET OrderID = ?, PaymentMethod = ?, Amount = ?, Balance = ?, Status = ?, TransactionDate = ?
              WHERE TransactionID = ?";
    
    $stmt = $conn->prepare($query);
    
    // Calculate balance (get order total first)
    $orderQuery = "SELECT TotalAmount FROM Orders WHERE OrderID = ?";
    $orderStmt = $conn->prepare($orderQuery);
    $orderStmt->bind_param('i', $data['orderId']);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();
    
    if ($orderResult->num_rows === 0) {
        echo json_encode(['error' => 'Order not found']);
        return;
    }
    
    $order = $orderResult->fetch_assoc();
    $totalAmount = $order['TotalAmount'];
    $amountPaid = $data['amount'];
    $balance = $totalAmount - $amountPaid;
    
    // Bind parameters
    $stmt->bind_param(
        "isdsssi",
        $data['orderId'],
        $data['paymentMethod'],
        $amountPaid,
        $balance,
        $data['status'],
        $data['transactionDate'],
        $data['transactionId']
    );
    
    // Execute and respond
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to update transaction: ' . $conn->error]);
    }
}

function deleteTransaction($conn) {
    $transactionId = $_GET['transactionId'] ?? '';
    
    if (empty($transactionId)) {
        throw new Exception('Transaction ID is required');
    }
    
    $query = "DELETE FROM Transactions WHERE TransactionID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $transactionId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to delete transaction: ' . $conn->error);
    }
}
?>