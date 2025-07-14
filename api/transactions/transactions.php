<?php

/**
 * Transactions Backend API
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
        case 'getTransactions':
            getTransactions();
            break;
        case 'getTransaction':
            getTransaction();
            break;
        case 'getOrderDetails':
            getOrderDetails();
            break;
        case 'addTransaction':
            addTransaction();
            break;
        case 'updateTransaction':
            updateTransaction();
            break;
        case 'deleteTransaction':
            deleteTransaction();
            break;
        default:
            Utils::sendErrorResponse('Invalid action');
            break;
    }
} catch (Exception $e) {
    Utils::sendErrorResponse($e->getMessage());
}

function getTransactions()
{
    global $db;

    $search = $_GET['search'] ?? '';
    $paymentMethod = $_GET['paymentMethod'] ?? '';

    $query = "SELECT t.TransactionID, t.OrderID, o.CustomerID, c.CustomerName, 
                     t.PaymentMethod, t.Amount AS AmountPaid, 
                     (orderTotal.TotalAmount - t.Amount) AS Balance,
                     t.TransactionDate, t.Status
              FROM transactions t
              JOIN (
                  SELECT OrderID, SUM(TotalAmount) as TotalAmount 
                  FROM orders 
                  GROUP BY OrderID
              ) orderTotal ON t.OrderID = orderTotal.OrderID
              JOIN orders o ON t.OrderID = o.OrderID
              JOIN customers c ON o.CustomerID = c.CustomerID
              WHERE 1=1";

    $params = [];

    if (!empty($search)) {
        $query .= " AND (t.TransactionID LIKE ? OR o.OrderID LIKE ? OR c.CustomerName LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    }

    if (!empty($paymentMethod)) {
        $query .= " AND t.PaymentMethod = ?";
        $params[] = $paymentMethod;
    }

    $query .= " GROUP BY t.TransactionID, t.OrderID, o.CustomerID, c.CustomerName, t.PaymentMethod, t.Amount, orderTotal.TotalAmount, t.TransactionDate, t.Status ORDER BY t.TransactionDate DESC";

    try {
        $transactions = $db->fetchAll($query, $params);
        Utils::sendSuccessResponse('Transactions retrieved successfully', $transactions);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve transactions: ' . $e->getMessage());
    }
}

function getTransaction()
{
    global $db;

    $transactionId = $_GET['transactionId'] ?? '';

    if (empty($transactionId)) {
        throw new Exception('Transaction ID is required');
    }

    $query = "SELECT t.*, o.CustomerID, c.CustomerName, orderTotal.TotalAmount
              FROM transactions t
              JOIN (
                  SELECT OrderID, SUM(TotalAmount) as TotalAmount 
                  FROM orders 
                  GROUP BY OrderID
              ) orderTotal ON t.OrderID = orderTotal.OrderID
              JOIN orders o ON t.OrderID = o.OrderID
              JOIN customers c ON o.CustomerID = c.CustomerID
              WHERE t.TransactionID = ?";

    $transaction = $db->fetchOne($query, [$transactionId]);

    if ($transaction) {
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

function getOrderDetails()
{
    global $db;

    $orderId = $_GET['orderId'] ?? '';

    if (empty($orderId)) {
        throw new Exception('Order ID is required');
    }

    $query = "SELECT o.OrderID, o.CustomerID, c.CustomerName, SUM(o.TotalAmount) as TotalAmount
              FROM orders o
              JOIN customers c ON o.CustomerID = c.CustomerID
              WHERE o.OrderID = ?
              GROUP BY o.OrderID, o.CustomerID, c.CustomerName";

    $order = $db->fetchOne($query, [$orderId]);

    if ($order) {
        echo json_encode($order);
    } else {
        throw new Exception('Order not found');
    }
}

function addTransaction()
{
    global $db;

    $data = $_POST;

    // Validate required fields
    $required = ['orderId', 'paymentMethod', 'amount', 'status'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(['error' => "$field is required"]);
            return;
        }
    }

    // Calculate balance (get order total first)
    $order = $db->fetchOne("SELECT SUM(TotalAmount) as TotalAmount FROM orders WHERE OrderID = ?", [$data['orderId']]);

    if (!$order) {
        echo json_encode(['error' => 'Order not found']);
        return;
    }

    $totalAmount = $order['TotalAmount'];
    $amountPaid = $data['amount'];
    $balance = $totalAmount - $amountPaid;

    // Set transaction date (use current date if not provided)
    $transactionDate = !empty($data['transactionDate']) ? $data['transactionDate'] : date('Y-m-d H:i:s');

    // Insert transaction
    $query = "INSERT INTO transactions (OrderID, PaymentMethod, Amount, Balance, Status, TransactionDate) 
              VALUES (?, ?, ?, ?, ?, ?)";

    try {
        $db->query($query, [
            $data['orderId'],
            $data['paymentMethod'],
            $amountPaid,
            $balance,
            $data['status'],
            $transactionDate
        ]);

        echo json_encode(['success' => true, 'transactionId' => $db->lastInsertId()]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to add transaction: ' . $e->getMessage()]);
    }
}

function updateTransaction()
{
    global $db;

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

    // Calculate balance (get order total first)
    $order = $db->fetchOne("SELECT SUM(TotalAmount) as TotalAmount FROM orders WHERE OrderID = ?", [$data['orderId']]);

    if (!$order) {
        echo json_encode(['error' => 'Order not found']);
        return;
    }

    $totalAmount = $order['TotalAmount'];
    $amountPaid = $data['amount'];
    $balance = $totalAmount - $amountPaid;

    // Update transaction
    $query = "UPDATE transactions 
              SET OrderID = ?, PaymentMethod = ?, Amount = ?, Balance = ?, Status = ?, TransactionDate = ?
              WHERE TransactionID = ?";

    try {
        $db->query($query, [
            $data['orderId'],
            $data['paymentMethod'],
            $amountPaid,
            $balance,
            $data['status'],
            $data['transactionDate'],
            $data['transactionId']
        ]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to update transaction: ' . $e->getMessage()]);
    }
}

function deleteTransaction()
{
    global $db;

    $transactionId = $_GET['transactionId'] ?? '';

    if (empty($transactionId)) {
        throw new Exception('Transaction ID is required');
    }

    try {
        $db->query("DELETE FROM transactions WHERE TransactionID = ?", [$transactionId]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        throw new Exception('Failed to delete transaction: ' . $e->getMessage());
    }
}
