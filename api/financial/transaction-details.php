<?php

/**
 * Transaction Details API
 * Provides detailed information about a specific transaction
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/FinancialHelper.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getInstance()->getConnection();
    $financialHelper = new FinancialHelper($pdo);

    // Get transaction ID from request
    $transactionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (!$transactionId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Transaction ID is required'
        ]);
        exit;
    }

    // Get transaction details
    $query = "
        SELECT 
            ft.TransactionID as transaction_id,
            ft.TransactionDate as transaction_date,
            ft.TransactionType as transaction_type,
            ft.Description as description,
            ft.Amount as amount,
            ft.Status as status,
            ft.ReferenceID as reference,
            ft.CustomerID as customer_id,
            ft.SupplierID as supplier_id,
            ft.CreatedAt as created_at,
            ft.UpdatedAt as updated_at,
            COALESCE(c.CustomerName, s.SupplierName) as customer_supplier_name,
            COALESCE(c.Email, s.Email) as customer_supplier_email,
            COALESCE(c.Phone, s.Phone) as customer_supplier_phone
        FROM financial_transactions ft
        LEFT JOIN customers c ON ft.CustomerID = c.CustomerID
        LEFT JOIN suppliers s ON ft.SupplierID = s.SupplierID
        WHERE ft.TransactionID = ?
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$transactionId]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Transaction not found'
        ]);
        exit;
    }

    // Get related order information if available
    $orderInfo = null;
    if ($transaction['reference']) {
        $orderQuery = "";
        if ($transaction['transaction_type'] === 'SALES_ORDER') {
            $orderQuery = "SELECT OrderID as order_id, OrderDate as order_date, TotalAmount as total_amount FROM orders WHERE OrderID = ?";
        } elseif ($transaction['transaction_type'] === 'PURCHASE_ORDER') {
            $orderQuery = "SELECT OrderID as order_id, OrderDate as order_date, TotalAmount as total_amount FROM purchase_orders WHERE OrderID = ?";
        }

        if ($orderQuery) {
            $orderStmt = $pdo->prepare($orderQuery);
            $orderStmt->execute([$transaction['reference']]);
            $orderInfo = $orderStmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    // Format the response
    $formattedTransaction = [
        'transaction_id' => (int)$transaction['transaction_id'],
        'transaction_date' => $transaction['transaction_date'],
        'transaction_type' => $transaction['transaction_type'],
        'description' => $transaction['description'],
        'amount' => (float)$transaction['amount'],
        'status' => $transaction['status'],
        'reference' => $transaction['reference'],
        'customer_id' => $transaction['customer_id'] ? (int)$transaction['customer_id'] : null,
        'supplier_id' => $transaction['supplier_id'] ? (int)$transaction['supplier_id'] : null,
        'customer_supplier_name' => $transaction['customer_supplier_name'],
        'customer_supplier_email' => $transaction['customer_supplier_email'],
        'customer_supplier_phone' => $transaction['customer_supplier_phone'],
        'created_at' => $transaction['created_at'],
        'updated_at' => $transaction['updated_at'],
        'order_info' => $orderInfo
    ];

    $response = [
        'success' => true,
        'data' => $formattedTransaction
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error loading transaction details: ' . $e->getMessage()
    ]);
}
