<?php

/**
 * Supplier Balances API
 * Provides supplier balance information
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/FinancialHelper.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getInstance()->getConnection();
    $financialHelper = new FinancialHelper($pdo);

    // Get supplier balances with their information
    $query = "
        SELECT 
            s.SupplierID as supplier_id,
            s.SupplierName as name,
            s.Email as email,
            s.Phone as phone,
            COALESCE(sb.CurrentBalance, 0) as balance,
            sb.LastTransactionDate as last_transaction_date
        FROM suppliers s
        LEFT JOIN supplier_balances sb ON s.SupplierID = sb.SupplierID
        ORDER BY s.SupplierName ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    $formattedSuppliers = [];
    foreach ($suppliers as $supplier) {
        $formattedSuppliers[] = [
            'supplier_id' => (int)$supplier['supplier_id'],
            'name' => $supplier['name'],
            'email' => $supplier['email'],
            'phone' => $supplier['phone'],
            'balance' => (float)$supplier['balance'],
            'last_transaction_date' => $supplier['last_transaction_date']
        ];
    }

    $response = [
        'success' => true,
        'data' => $formattedSuppliers,
        'summary' => [
            'total_suppliers' => count($formattedSuppliers),
            'suppliers_with_balance' => count(array_filter($formattedSuppliers, function ($s) {
                return $s['balance'] != 0;
            })),
            'total_balance' => array_sum(array_column($formattedSuppliers, 'balance'))
        ]
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error loading supplier balances: ' . $e->getMessage()
    ]);
}
