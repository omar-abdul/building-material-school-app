<?php

/**
 * Comprehensive Financial System API
 * Handles all financial transactions and balance calculations
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/utils.php';
require_once __DIR__ . '/../../includes/FinancialHelper.php';

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
        case 'addTransaction':
            addTransaction();
            break;
        case 'updateTransaction':
            updateTransaction();
            break;
        case 'deleteTransaction':
            deleteTransaction();
            break;
        case 'getCustomerBalances':
            getCustomerBalances();
            break;
        case 'getSupplierBalances':
            getSupplierBalances();
            break;
        case 'getCustomerBalance':
            getCustomerBalance();
            break;
        case 'getSupplierBalance':
            getSupplierBalance();
            break;
        case 'getCustomerTransactions':
            getCustomerTransactions();
            break;
        case 'getSupplierTransactions':
            getSupplierTransactions();
            break;
        case 'calculateAllBalances':
            calculateAllBalances();
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
    $type = $_GET['type'] ?? '';
    $status = $_GET['status'] ?? '';
    $customerId = $_GET['customerId'] ?? '';
    $supplierId = $_GET['supplierId'] ?? '';
    $dateFrom = $_GET['dateFrom'] ?? '';
    $dateTo = $_GET['dateTo'] ?? '';

    $query = "SELECT ft.*, 
                     c.CustomerName,
                     s.SupplierName,
                     e.EmployeeName
              FROM financial_transactions ft
              LEFT JOIN customers c ON ft.CustomerID = c.CustomerID
              LEFT JOIN suppliers s ON ft.SupplierID = s.SupplierID
              LEFT JOIN employees e ON ft.EmployeeID = e.EmployeeID
              WHERE 1=1";

    $params = [];

    if (!empty($search)) {
        $query .= " AND (ft.ReferenceID LIKE ? OR ft.Description LIKE ? OR c.CustomerName LIKE ? OR s.SupplierName LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    }

    if (!empty($type)) {
        $query .= " AND ft.TransactionType = ?";
        $params[] = $type;
    }

    if (!empty($status)) {
        $query .= " AND ft.Status = ?";
        $params[] = $status;
    }

    if (!empty($customerId)) {
        $query .= " AND ft.CustomerID = ?";
        $params[] = $customerId;
    }

    if (!empty($supplierId)) {
        $query .= " AND ft.SupplierID = ?";
        $params[] = $supplierId;
    }

    if (!empty($dateFrom)) {
        $query .= " AND DATE(ft.TransactionDate) >= ?";
        $params[] = $dateFrom;
    }

    if (!empty($dateTo)) {
        $query .= " AND DATE(ft.TransactionDate) <= ?";
        $params[] = $dateTo;
    }

    $query .= " ORDER BY ft.TransactionDate DESC";

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

    $query = "SELECT ft.*, 
                     c.CustomerName,
                     s.SupplierName,
                     e.EmployeeName
              FROM financial_transactions ft
              LEFT JOIN customers c ON ft.CustomerID = c.CustomerID
              LEFT JOIN suppliers s ON ft.SupplierID = s.SupplierID
              LEFT JOIN employees e ON ft.EmployeeID = e.EmployeeID
              WHERE ft.TransactionID = ?";

    $transaction = $db->fetchOne($query, [$transactionId]);

    if ($transaction) {
        Utils::sendSuccessResponse('Transaction retrieved successfully', $transaction);
    } else {
        throw new Exception('Transaction not found');
    }
}

function addTransaction()
{
    global $db;

    // Handle both POST data and JSON data
    $data = $_POST;

    // If no POST data, try to get JSON data
    if (empty($data)) {
        $jsonData = file_get_contents('php://input');
        if ($jsonData) {
            $data = json_decode($jsonData, true);
        }
    }

    // Validate required fields
    $required = ['transactionType', 'referenceId', 'referenceType', 'amount', 'status'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            Utils::sendErrorResponse("$field is required");
            return;
        }
    }

    // Get current balance for customer/supplier
    $previousBalance = 0;
    if (!empty($data['customerId'])) {
        $balance = $db->fetchOne("SELECT CurrentBalance FROM customer_balances WHERE CustomerID = ?", [$data['customerId']]);
        $previousBalance = $balance ? $balance['CurrentBalance'] : 0;
    } elseif (!empty($data['supplierId'])) {
        $balance = $db->fetchOne("SELECT CurrentBalance FROM supplier_balances WHERE SupplierID = ?", [$data['supplierId']]);
        $previousBalance = $balance ? $balance['CurrentBalance'] : 0;
    }

    // Calculate new balance
    $newBalance = $previousBalance + floatval($data['amount']);

    // Set transaction date
    $transactionDate = !empty($data['transactionDate']) ? $data['transactionDate'] : date('Y-m-d H:i:s');

    // Insert transaction
    $query = "INSERT INTO financial_transactions (
                TransactionType, ReferenceID, ReferenceType, Amount, PaymentMethod, 
                Status, TransactionDate, DueDate, CustomerID, SupplierID, EmployeeID,
                PreviousBalance, NewBalance, Description, Notes, CreatedBy
              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    try {
        $db->query($query, [
            $data['transactionType'],
            $data['referenceId'],
            $data['referenceType'],
            $data['amount'],
            $data['paymentMethod'] ?? null,
            $data['status'],
            $transactionDate,
            $data['dueDate'] ?? null,
            $data['customerId'] ?? null,
            $data['supplierId'] ?? null,
            $data['employeeId'] ?? null,
            $previousBalance,
            $newBalance,
            $data['description'] ?? null,
            $data['notes'] ?? null,
            $data['createdBy'] ?? 1 // Default to admin user
        ]);

        $transactionId = $db->lastInsertId();

        // Update balances
        if (!empty($data['customerId'])) {
            FinancialHelper::calculateCustomerBalance($data['customerId']);
        }
        if (!empty($data['supplierId'])) {
            FinancialHelper::calculateSupplierBalance($data['supplierId']);
        }

        Utils::sendSuccessResponse('Transaction added successfully', ['transactionId' => $transactionId]);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to add transaction: ' . $e->getMessage());
    }
}

function updateTransaction()
{
    global $db;

    // Handle both POST data and JSON data
    $data = $_POST;

    // If no POST data, try to get JSON data
    if (empty($data)) {
        $jsonData = file_get_contents('php://input');
        if ($jsonData) {
            $data = json_decode($jsonData, true);
        }
    }

    $transactionId = $data['transactionId'] ?? '';

    if (empty($transactionId)) {
        Utils::sendErrorResponse('Transaction ID is required');
        return;
    }

    // Get current transaction
    $currentTransaction = $db->fetchOne("SELECT * FROM financial_transactions WHERE TransactionID = ?", [$transactionId]);
    if (!$currentTransaction) {
        Utils::sendErrorResponse('Transaction not found');
        return;
    }

    // Update transaction
    $query = "UPDATE financial_transactions SET 
                TransactionType = ?, ReferenceID = ?, ReferenceType = ?, Amount = ?, 
                PaymentMethod = ?, Status = ?, TransactionDate = ?, DueDate = ?,
                CustomerID = ?, SupplierID = ?, EmployeeID = ?, Description = ?, 
                Notes = ?, UpdatedAt = CURRENT_TIMESTAMP
              WHERE TransactionID = ?";

    try {
        $db->query($query, [
            $data['transactionType'] ?? $currentTransaction['TransactionType'],
            $data['referenceId'] ?? $currentTransaction['ReferenceID'],
            $data['referenceType'] ?? $currentTransaction['ReferenceType'],
            $data['amount'] ?? $currentTransaction['Amount'],
            $data['paymentMethod'] ?? $currentTransaction['PaymentMethod'],
            $data['status'] ?? $currentTransaction['Status'],
            $data['transactionDate'] ?? $currentTransaction['TransactionDate'],
            $data['dueDate'] ?? $currentTransaction['DueDate'],
            $data['customerId'] ?? $currentTransaction['CustomerID'],
            $data['supplierId'] ?? $currentTransaction['SupplierID'],
            $data['employeeId'] ?? $currentTransaction['EmployeeID'],
            $data['description'] ?? $currentTransaction['Description'],
            $data['notes'] ?? $currentTransaction['Notes'],
            $transactionId
        ]);

        // Recalculate balances
        if ($currentTransaction['CustomerID']) {
            FinancialHelper::calculateCustomerBalance($currentTransaction['CustomerID']);
        }
        if ($currentTransaction['SupplierID']) {
            FinancialHelper::calculateSupplierBalance($currentTransaction['SupplierID']);
        }

        Utils::sendSuccessResponse('Transaction updated successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update transaction: ' . $e->getMessage());
    }
}

function deleteTransaction()
{
    global $db;

    $transactionId = $_GET['transactionId'] ?? '';

    if (empty($transactionId)) {
        Utils::sendErrorResponse('Transaction ID is required');
        return;
    }

    // Get transaction details before deletion
    $transaction = $db->fetchOne("SELECT * FROM financial_transactions WHERE TransactionID = ?", [$transactionId]);
    if (!$transaction) {
        Utils::sendErrorResponse('Transaction not found');
        return;
    }

    try {
        $db->query("DELETE FROM financial_transactions WHERE TransactionID = ?", [$transactionId]);

        // Recalculate balances
        if ($transaction['CustomerID']) {
            FinancialHelper::calculateCustomerBalance($transaction['CustomerID']);
        }
        if ($transaction['SupplierID']) {
            FinancialHelper::calculateSupplierBalance($transaction['SupplierID']);
        }

        Utils::sendSuccessResponse('Transaction deleted successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete transaction: ' . $e->getMessage());
    }
}

function getCustomerBalances()
{
    global $db;

    $search = $_GET['search'] ?? '';

    $query = "SELECT cb.*, c.CustomerName, c.Email, c.Phone
              FROM customer_balances cb
              JOIN customers c ON cb.CustomerID = c.CustomerID
              WHERE 1=1";

    $params = [];

    if (!empty($search)) {
        $query .= " AND c.CustomerName LIKE ?";
        $params[] = "%$search%";
    }

    $query .= " ORDER BY cb.CurrentBalance DESC";

    try {
        $balances = $db->fetchAll($query, $params);
        Utils::sendSuccessResponse('Customer balances retrieved successfully', $balances);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve customer balances: ' . $e->getMessage());
    }
}

function getSupplierBalances()
{
    global $db;

    $search = $_GET['search'] ?? '';

    $query = "SELECT sb.*, s.SupplierName, s.Email, s.Phone
              FROM supplier_balances sb
              JOIN suppliers s ON sb.SupplierID = s.SupplierID
              WHERE 1=1";

    $params = [];

    if (!empty($search)) {
        $query .= " AND s.SupplierName LIKE ?";
        $params[] = "%$search%";
    }

    $query .= " ORDER BY sb.CurrentBalance DESC";

    try {
        $balances = $db->fetchAll($query, $params);
        Utils::sendSuccessResponse('Supplier balances retrieved successfully', $balances);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve supplier balances: ' . $e->getMessage());
    }
}

function getCustomerBalance()
{
    global $db;

    $customerId = $_GET['customerId'] ?? '';

    if (empty($customerId)) {
        Utils::sendErrorResponse('Customer ID is required');
        return;
    }

    try {
        FinancialHelper::calculateCustomerBalance($customerId);

        $balance = $db->fetchOne("SELECT cb.*, c.CustomerName 
                                  FROM customer_balances cb
                                  JOIN customers c ON cb.CustomerID = c.CustomerID
                                  WHERE cb.CustomerID = ?", [$customerId]);

        if ($balance) {
            Utils::sendSuccessResponse('Customer balance retrieved successfully', $balance);
        } else {
            Utils::sendErrorResponse('Customer not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to get customer balance: ' . $e->getMessage());
    }
}

function getSupplierBalance()
{
    global $db;

    $supplierId = $_GET['supplierId'] ?? '';

    if (empty($supplierId)) {
        Utils::sendErrorResponse('Supplier ID is required');
        return;
    }

    try {
        FinancialHelper::calculateSupplierBalance($supplierId);

        $balance = $db->fetchOne("SELECT sb.*, s.SupplierName 
                                  FROM supplier_balances sb
                                  JOIN suppliers s ON sb.SupplierID = s.SupplierID
                                  WHERE sb.SupplierID = ?", [$supplierId]);

        if ($balance) {
            Utils::sendSuccessResponse('Supplier balance retrieved successfully', $balance);
        } else {
            Utils::sendErrorResponse('Supplier not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to get supplier balance: ' . $e->getMessage());
    }
}

function getCustomerTransactions()
{
    global $db;

    $customerId = $_GET['customerId'] ?? '';

    if (empty($customerId)) {
        Utils::sendErrorResponse('Customer ID is required');
        return;
    }

    $query = "SELECT ft.*, c.CustomerName, s.SupplierName, e.EmployeeName
              FROM financial_transactions ft
              LEFT JOIN customers c ON ft.CustomerID = c.CustomerID
              LEFT JOIN suppliers s ON ft.SupplierID = s.SupplierID
              LEFT JOIN employees e ON ft.EmployeeID = e.EmployeeID
              WHERE ft.CustomerID = ?
              ORDER BY ft.TransactionDate DESC";

    try {
        $transactions = $db->fetchAll($query, [$customerId]);
        Utils::sendSuccessResponse('Customer transactions retrieved successfully', $transactions);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve customer transactions: ' . $e->getMessage());
    }
}

function getSupplierTransactions()
{
    global $db;

    $supplierId = $_GET['supplierId'] ?? '';

    if (empty($supplierId)) {
        Utils::sendErrorResponse('Supplier ID is required');
        return;
    }

    $query = "SELECT ft.*, c.CustomerName, s.SupplierName, e.EmployeeName
              FROM financial_transactions ft
              LEFT JOIN customers c ON ft.CustomerID = c.CustomerID
              LEFT JOIN suppliers s ON ft.SupplierID = s.SupplierID
              LEFT JOIN employees e ON ft.EmployeeID = e.EmployeeID
              WHERE ft.SupplierID = ?
              ORDER BY ft.TransactionDate DESC";

    try {
        $transactions = $db->fetchAll($query, [$supplierId]);
        Utils::sendSuccessResponse('Supplier transactions retrieved successfully', $transactions);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve supplier transactions: ' . $e->getMessage());
    }
}

function calculateAllBalances()
{
    global $db;

    try {
        // Calculate all customer balances
        $customers = $db->fetchAll("SELECT CustomerID FROM customers");
        foreach ($customers as $customer) {
            FinancialHelper::calculateCustomerBalance($customer['CustomerID']);
        }

        // Calculate all supplier balances
        $suppliers = $db->fetchAll("SELECT SupplierID FROM suppliers");
        foreach ($suppliers as $supplier) {
            FinancialHelper::calculateSupplierBalance($supplier['SupplierID']);
        }

        Utils::sendSuccessResponse('All balances calculated successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to calculate balances: ' . $e->getMessage());
    }
}
