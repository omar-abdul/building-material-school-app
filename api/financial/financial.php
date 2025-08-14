<?php

/**
 * Comprehensive Financial System API
 * Handles all financial transactions and balance calculations
 */

// Suppress warnings to prevent them from corrupting JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

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
        case 'getFinancialOverview':
            getFinancialOverview();
            break;
        case 'recalculateCOGS':
            recalculateAllCOGS();
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

    // Get period filter parameters
    $period = $_GET['period'] ?? 'current-month';
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';

    // Build date filter
    $dateFilter = buildDateFilter($period, $startDate, $endDate);

    $query = "SELECT ft.*, 
                     c.CustomerName,
                     s.SupplierName,
                     e.EmployeeName
              FROM financial_transactions ft
              LEFT JOIN customers c ON ft.CustomerID = c.CustomerID
              LEFT JOIN suppliers s ON ft.SupplierID = s.SupplierID
              LEFT JOIN employees e ON ft.EmployeeID = e.EmployeeID
              WHERE 1=1" . $dateFilter['where'];

    $params = $dateFilter['params'];

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

    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            Utils::sendErrorResponse('Invalid input data');
            return;
        }

        // Validate required fields
        $requiredFields = ['transactionType', 'referenceId', 'referenceType', 'amount', 'status'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                Utils::sendErrorResponse("Missing required field: $field");
                return;
            }
        }

        // Insert the transaction
        $sql = "INSERT INTO financial_transactions (
                    TransactionType, ReferenceID, ReferenceType, Amount, 
                    PaymentMethod, Status, TransactionDate, DueDate,
                    CustomerID, SupplierID, EmployeeID, Description, Notes, CreatedBy
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $input['transactionType'],
            $input['referenceId'],
            $input['referenceType'],
            $input['amount'],
            $input['paymentMethod'] ?? null,
            $input['status'],
            $input['transactionDate'] ?? date('Y-m-d H:i:s'),
            $input['dueDate'] ?? null,
            $input['customerId'] ?? null,
            $input['supplierId'] ?? null,
            $input['employeeId'] ?? null,
            $input['description'] ?? null,
            $input['notes'] ?? null,
            $input['createdBy'] ?? 1
        ];

        $db->query($sql, $params);
        $transactionId = $db->lastInsertId();

        // Update relevant balances based on transaction type
        if (isset($input['customerId']) && $input['customerId']) {
            FinancialHelper::calculateCustomerBalance($input['customerId']);
        }
        if (isset($input['supplierId']) && $input['supplierId']) {
            FinancialHelper::calculateSupplierBalance($input['supplierId']);
        }

        // Update cash/wallet balances to ensure synchronization
        FinancialHelper::updateCashBalances();

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

/**
 * Recalculate all customer and supplier balances
 * This ensures consistency after COGS or other financial changes
 */
function recalculateAllBalances()
{
    global $db;
    try {
        require_once __DIR__ . '/../../includes/FinancialHelper.php';

        // Get all customers and suppliers
        $customers = $db->fetchAll("SELECT CustomerID FROM customers");
        $suppliers = $db->fetchAll("SELECT SupplierID FROM suppliers");

        $customerCount = 0;
        $supplierCount = 0;

        // Recalculate customer balances
        foreach ($customers as $customer) {
            try {
                FinancialHelper::calculateCustomerBalance($customer['CustomerID']);
                $customerCount++;
            } catch (Exception $e) {
                error_log("Failed to recalculate customer balance for CustomerID: {$customer['CustomerID']}: " . $e->getMessage());
            }
        }

        // Recalculate supplier balances
        foreach ($suppliers as $supplier) {
            try {
                FinancialHelper::calculateSupplierBalance($supplier['SupplierID']);
                $supplierCount++;
            } catch (Exception $e) {
                error_log("Failed to recalculate supplier balance for SupplierID: {$supplier['SupplierID']}: " . $e->getMessage());
            }
        }

        error_log("Recalculated balances for $customerCount customers and $supplierCount suppliers");
    } catch (Exception $e) {
        error_log("Error recalculating all balances: " . $e->getMessage());
    }
}

/**
 * Manually recalculate all COGS transactions
 * This is useful for maintenance and testing
 */
function recalculateAllCOGS()
{
    global $db;

    try {
        // Find all delivered orders
        $deliveredOrders = $db->fetchAll("
            SELECT OrderID FROM sales_orders_main WHERE Status = 'Delivered'
        ");

        if (empty($deliveredOrders)) {
            Utils::sendSuccessResponse('No delivered orders found to recalculate COGS for');
            return;
        }

        $totalRecalculated = 0;
        foreach ($deliveredOrders as $order) {
            try {
                // Delete existing COGS transactions for this order
                $db->query("DELETE FROM financial_transactions 
                            WHERE TransactionType = 'INVENTORY_SALE' 
                            AND ReferenceID = ? 
                            AND ReferenceType = 'order'", [$order['OrderID']]);

                // Get current order items with costs
                $items = $db->fetchAll("
                    SELECT soi.ItemID, soi.Quantity, inv.Cost 
                    FROM sales_order_items soi 
                    LEFT JOIN inventory inv ON soi.ItemID = inv.ItemID 
                    WHERE soi.OrderID = ?
                ", [$order['OrderID']]);

                $orderRecalculated = 0;
                foreach ($items as $item) {
                    if ($item['Cost'] && $item['Cost'] > 0) {
                        try {
                            $cogsResult = FinancialHelper::createCOGSTransaction(
                                $order['OrderID'],
                                $item['ItemID'],
                                $item['Quantity'],
                                $item['Cost']
                            );

                            if ($cogsResult) {
                                $orderRecalculated++;
                                $totalRecalculated++;
                            }
                        } catch (Exception $e) {
                            error_log("Failed to create COGS transaction for OrderID: {$order['OrderID']}, ItemID: {$item['ItemID']}: " . $e->getMessage());
                        }
                    }
                }

                if ($orderRecalculated > 0) {
                    error_log("Recalculated $orderRecalculated COGS transactions for OrderID: {$order['OrderID']}");
                }
            } catch (Exception $e) {
                error_log("Error recalculating COGS for OrderID: {$order['OrderID']}: " . $e->getMessage());
            }
        }

        Utils::sendSuccessResponse("COGS recalculation completed. Recalculated $totalRecalculated transactions across " . count($deliveredOrders) . " orders.");

        // After COGS recalculation, recalculate all balances to ensure consistency
        recalculateAllBalances();
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to recalculate COGS: ' . $e->getMessage());
    }
}

/**
 * Ensure all delivered orders have COGS transactions
 */
function createMissingCOGSTransactionsIfNeeded()
{
    global $db;

    try {
        // Find delivered orders that don't have COGS transactions
        $ordersWithoutCOGS = $db->fetchAll("
            SELECT DISTINCT som.OrderID, som.Status
            FROM sales_orders_main som
            LEFT JOIN financial_transactions ft ON ft.ReferenceID = som.OrderID 
                AND ft.TransactionType = 'INVENTORY_SALE' 
                AND ft.ReferenceType = 'order'
            WHERE som.Status = 'Delivered' 
            AND ft.TransactionID IS NULL
        ");

        if (empty($ordersWithoutCOGS)) {
            return; // No missing COGS transactions
        }

        $createdCount = 0;
        foreach ($ordersWithoutCOGS as $order) {
            // Get items for this order
            $items = $db->fetchAll("
                SELECT soi.ItemID, soi.Quantity, inv.Cost 
                FROM sales_order_items soi 
                LEFT JOIN inventory inv ON soi.ItemID = inv.ItemID 
                WHERE soi.OrderID = ?
            ", [$order['OrderID']]);

            foreach ($items as $item) {
                if ($item['Cost'] && $item['Cost'] > 0) {
                    try {
                        // Create COGS transaction using FinancialHelper
                        require_once __DIR__ . '/../../includes/FinancialHelper.php';
                        $cogsResult = FinancialHelper::createCOGSTransaction(
                            $order['OrderID'],
                            $item['ItemID'],
                            $item['Quantity'],
                            $item['Cost']
                        );

                        if ($cogsResult) {
                            $createdCount++;
                            error_log("Created missing COGS transaction for OrderID: {$order['OrderID']}, ItemID: {$item['ItemID']}");
                        }
                    } catch (Exception $e) {
                        error_log("Failed to create missing COGS transaction for OrderID: {$order['OrderID']}, ItemID: {$item['ItemID']}: " . $e->getMessage());
                    }
                }
            }
        }

        if ($createdCount > 0) {
            error_log("Created $createdCount missing COGS transactions in financial overview");

            // After creating COGS transactions, recalculate all balances to ensure consistency
            recalculateAllBalances();
        }
    } catch (Exception $e) {
        error_log("Error creating missing COGS transactions: " . $e->getMessage());
    }
}

function getFinancialOverview()
{
    global $db;

    try {
        // Ensure all delivered orders have COGS transactions
        createMissingCOGSTransactionsIfNeeded();

        // Get period filter parameters
        $period = $_GET['period'] ?? 'current-month';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        // Build date filter
        $dateFilter = buildDateFilter($period, $startDate, $endDate);

        // Calculate total revenue (positive sales transactions)
        $revenueQuery = "SELECT COALESCE(SUM(Amount), 0) as total_revenue 
                        FROM financial_transactions 
                        WHERE TransactionType = 'SALES_ORDER' AND Status = 'Completed' AND Amount > 0" . $dateFilter['where'];
        $revenue = $db->fetchOne($revenueQuery, $dateFilter['params']);
        $totalRevenue = $revenue['total_revenue'] ?? 0;

        // Calculate total COGS (cost of goods sold from inventory sales)
        $cogsQuery = "SELECT COALESCE(SUM(ABS(Amount)), 0) as total_cogs 
                     FROM financial_transactions 
                     WHERE TransactionType = 'INVENTORY_SALE' AND Status = 'Completed' AND Amount < 0" . $dateFilter['where'];
        $cogs = $db->fetchOne($cogsQuery, $dateFilter['params']);
        $totalCOGS = $cogs['total_cogs'] ?? 0;

        // Calculate other expenses (salary payments, direct expenses, and purchase orders that are completed)
        $expensesQuery = "SELECT COALESCE(SUM(ABS(Amount)), 0) as total_expenses 
                         FROM financial_transactions 
                         WHERE TransactionType IN ('SALARY_PAYMENT', 'DIRECT_EXPENSE', 'PURCHASE_ORDER') 
                         AND Status = 'Completed' AND Amount < 0" . $dateFilter['where'];
        $expenses = $db->fetchOne($expensesQuery, $dateFilter['params']);
        $totalExpenses = $expenses['total_expenses'] ?? 0;

        // Calculate net profit (revenue - COGS - other expenses)
        $netProfit = $totalRevenue - $totalCOGS - $totalExpenses;

        // Calculate pending payments (all pending transactions with positive amounts)
        $pendingQuery = "SELECT COALESCE(SUM(Amount), 0) as pending_amount, COUNT(*) as pending_count 
                        FROM financial_transactions 
                        WHERE Status = 'Pending' AND Amount > 0" . $dateFilter['where'];
        $pending = $db->fetchOne($pendingQuery, $dateFilter['params']);
        $pendingAmount = $pending['pending_amount'] ?? 0;
        $pendingCount = $pending['pending_count'] ?? 0;

        $overview = [
            'total_revenue' => $totalRevenue,
            'total_cogs' => $totalCOGS,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'pending_payments' => $pendingAmount,
            'pending_count' => $pendingCount
        ];

        Utils::sendSuccessResponse('Financial overview retrieved successfully', $overview);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to get financial overview: ' . $e->getMessage());
    }
}

function buildDateFilter($period, $startDate, $endDate)
{
    $where = '';
    $params = [];

    if ($period === 'custom' && $startDate && $endDate) {
        $where = " AND TransactionDate >= ? AND TransactionDate <= ?";
        $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
    } else {
        $now = new DateTime();

        switch ($period) {
            case 'current-month':
                $start = new DateTime('first day of this month');
                $end = new DateTime('last day of this month');
                break;
            case 'current-quarter':
                $quarter = ceil($now->format('n') / 3);
                $start = new DateTime($now->format('Y') . '-' . (($quarter - 1) * 3 + 1) . '-01');
                $end = new DateTime($now->format('Y') . '-' . ($quarter * 3) . '-' . date('t', strtotime($now->format('Y') . '-' . ($quarter * 3) . '-01')));
                break;
            case 'current-year':
                $start = new DateTime($now->format('Y') . '-01-01');
                $end = new DateTime($now->format('Y') . '-12-31');
                break;
            default:
                $start = new DateTime('first day of this month');
                $end = new DateTime('last day of this month');
        }

        $where = " AND TransactionDate >= ? AND TransactionDate <= ?";
        $params = [$start->format('Y-m-d 00:00:00'), $end->format('Y-m-d 23:59:59')];
    }

    return ['where' => $where, 'params' => $params];
}
