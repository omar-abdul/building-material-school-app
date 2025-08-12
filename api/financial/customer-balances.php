<?php

/**
 * Customer Balances API
 * Provides customer balance information
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/utils.php';
require_once __DIR__ . '/../../includes/FinancialHelper.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();

    // Get customer balances with their information
    $query = "
        SELECT 
            c.CustomerID as customer_id,
            c.CustomerName as name,
            c.Email as email,
            c.Phone as phone,
            COALESCE(cb.CurrentBalance, 0) as balance,
            cb.LastTransactionDate as last_transaction_date
        FROM customers c
        LEFT JOIN customer_balances cb ON c.CustomerID = cb.CustomerID
        ORDER BY c.CustomerName ASC
    ";

    $customers = $db->fetchAll($query);

    // Format the data
    $formattedCustomers = [];
    foreach ($customers as $customer) {
        $formattedCustomers[] = [
            'customer_id' => (int)$customer['customer_id'],
            'name' => $customer['name'],
            'email' => $customer['email'],
            'phone' => $customer['phone'],
            'balance' => (float)$customer['balance'],
            'last_transaction_date' => $customer['last_transaction_date']
        ];
    }

    $response = [
        'success' => true,
        'data' => $formattedCustomers,
        'summary' => [
            'total_customers' => count($formattedCustomers),
            'customers_with_balance' => count(array_filter($formattedCustomers, function ($c) {
                return $c['balance'] != 0;
            })),
            'total_balance' => array_sum(array_column($formattedCustomers, 'balance'))
        ]
    ];

    Utils::sendSuccessResponse('Customer balances retrieved successfully', $formattedCustomers);
} catch (Exception $e) {
    Utils::sendErrorResponse('Error loading customer balances: ' . $e->getMessage());
}
