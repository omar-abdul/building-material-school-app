<?php

/**
 * Transactions API
 * Provides transaction history with filtering and pagination
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/FinancialHelper.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getInstance()->getConnection();
    $financialHelper = new FinancialHelper($pdo);

    // Get query parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50;
    $offset = ($page - 1) * $limit;

    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

    // Build WHERE clause
    $whereConditions = [];
    $params = [];

    if ($type) {
        $whereConditions[] = "ft.TransactionType = ?";
        $params[] = $type;
    }

    if ($status) {
        $whereConditions[] = "ft.Status = ?";
        $params[] = $status;
    }

    if ($search) {
        $whereConditions[] = "(ft.Description LIKE ? OR ft.ReferenceID LIKE ? OR cs.Name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($startDate) {
        $whereConditions[] = "ft.TransactionDate >= ?";
        $params[] = $startDate;
    }

    if ($endDate) {
        $whereConditions[] = "ft.TransactionDate <= ?";
        $params[] = $endDate . ' 23:59:59';
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    // Get total count for pagination
    $countQuery = "
        SELECT COUNT(*) as total
        FROM financial_transactions ft
        LEFT JOIN customers c ON ft.CustomerID = c.CustomerID
        LEFT JOIN suppliers s ON ft.SupplierID = s.SupplierID
        LEFT JOIN (
            SELECT CustomerID, CustomerName as Name FROM customers
            UNION ALL
            SELECT SupplierID, SupplierName as Name FROM suppliers
        ) cs ON (ft.CustomerID = cs.CustomerID OR ft.SupplierID = cs.CustomerID)
        $whereClause
    ";

    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get transactions with pagination
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
            COALESCE(c.CustomerName, s.SupplierName) as customer_supplier_name
        FROM financial_transactions ft
        LEFT JOIN customers c ON ft.CustomerID = c.CustomerID
        LEFT JOIN suppliers s ON ft.SupplierID = s.SupplierID
        LEFT JOIN (
            SELECT CustomerID, CustomerName as Name FROM customers
            UNION ALL
            SELECT SupplierID, SupplierName as Name FROM suppliers
        ) cs ON (ft.CustomerID = cs.CustomerID OR ft.SupplierID = cs.CustomerID)
        $whereClause
        ORDER BY ft.TransactionDate DESC, ft.TransactionID DESC
        LIMIT ? OFFSET ?
    ";

    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    $formattedTransactions = [];
    foreach ($transactions as $transaction) {
        $formattedTransactions[] = [
            'transaction_id' => (int)$transaction['transaction_id'],
            'transaction_date' => $transaction['transaction_date'],
            'transaction_type' => $transaction['transaction_type'],
            'description' => $transaction['description'],
            'amount' => (float)$transaction['amount'],
            'status' => $transaction['status'],
            'reference' => $transaction['reference'],
            'customer_id' => $transaction['customer_id'] ? (int)$transaction['customer_id'] : null,
            'supplier_id' => $transaction['supplier_id'] ? (int)$transaction['supplier_id'] : null,
            'customer_supplier_name' => $transaction['customer_supplier_name']
        ];
    }

    // Calculate summary statistics
    $summaryQuery = "
        SELECT 
            COUNT(*) as total_transactions,
            COALESCE(SUM(CASE WHEN TransactionType IN ('SALES_ORDER', 'DIRECT_INCOME', 'INVENTORY_SALE') THEN Amount ELSE 0 END), 0) as total_revenue,
            COALESCE(SUM(CASE WHEN TransactionType IN ('PURCHASE_ORDER', 'SALARY_PAYMENT', 'DIRECT_EXPENSE', 'INVENTORY_PURCHASE') THEN ABS(Amount) ELSE 0 END), 0) as total_expenses,
            COALESCE(SUM(CASE WHEN Status = 'Pending' THEN ABS(Amount) ELSE 0 END), 0) as pending_amount
        FROM financial_transactions ft
        LEFT JOIN customers c ON ft.CustomerID = c.CustomerID
        LEFT JOIN suppliers s ON ft.SupplierID = s.SupplierID
        LEFT JOIN (
            SELECT CustomerID, CustomerName as Name FROM customers
            UNION ALL
            SELECT SupplierID, SupplierName as Name FROM suppliers
        ) cs ON (ft.CustomerID = cs.CustomerID OR ft.SupplierID = cs.CustomerID)
        $whereClause
    ";

    $summaryStmt = $pdo->prepare($summaryQuery);
    $summaryStmt->execute(array_slice($params, 0, -2)); // Remove limit and offset
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'data' => $formattedTransactions,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$totalCount,
            'pages' => ceil($totalCount / $limit)
        ],
        'summary' => [
            'total_transactions' => (int)$summary['total_transactions'],
            'total_revenue' => (float)$summary['total_revenue'],
            'total_expenses' => (float)$summary['total_expenses'],
            'pending_amount' => (float)$summary['pending_amount']
        ]
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error loading transactions: ' . $e->getMessage()
    ]);
}
