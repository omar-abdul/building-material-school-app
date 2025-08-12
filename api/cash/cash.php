<?php

/**
 * Cash/Wallet Management API
 * Handles cash and wallet balance tracking and expense payments
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../config/base_url.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/utils.php';

$auth = new Auth();
$auth->requireAuth();

$db = Database::getInstance();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getBalances':
            getBalances();
            break;
        case 'getTransactions':
            getTransactions();
            break;
        case 'getTransaction':
            getTransaction();
            break;
        case 'addTransaction':
            addTransaction();
            break;
        default:
            Utils::sendErrorResponse('Invalid action');
    }
} catch (Exception $e) {
    Utils::sendErrorResponse('Server error: ' . $e->getMessage());
}

/**
 * Get current cash and wallet balances
 */
function getBalances()
{
    global $db;

    try {
        // Calculate cash balance - consider transaction types for proper balance
        $cashQuery = "
            SELECT 
                COALESCE(SUM(
                    CASE 
                        -- Money coming IN (positive for cash balance)
                        WHEN TransactionType IN ('SALES_PAYMENT', 'DIRECT_INCOME', 'PURCHASE_REFUND') THEN ABS(Amount)
                        -- Money going OUT (negative for cash balance)  
                        WHEN TransactionType IN ('PURCHASE_PAYMENT', 'SALARY_PAYMENT', 'DIRECT_EXPENSE', 'SALES_REFUND') THEN -ABS(Amount)
                        -- For inventory transactions, consider the direction
                        WHEN TransactionType = 'INVENTORY_SALE' AND Amount > 0 THEN ABS(Amount)
                        WHEN TransactionType = 'INVENTORY_PURCHASE' AND Amount < 0 THEN -ABS(Amount)
                        ELSE 0
                    END
                ), 0) as cash_balance 
            FROM financial_transactions 
            WHERE PaymentMethod = 'Cash' AND Status = 'Completed'
        ";
        $cashResult = $db->fetchOne($cashQuery);
        $cashBalance = $cashResult['cash_balance'] ?? 0;

        // Calculate wallet balance - same logic for wallet transactions
        $walletQuery = "
            SELECT 
                COALESCE(SUM(
                    CASE 
                        -- Money coming IN (positive for wallet balance)
                        WHEN TransactionType IN ('SALES_PAYMENT', 'DIRECT_INCOME', 'PURCHASE_REFUND') THEN ABS(Amount)
                        -- Money going OUT (negative for wallet balance)
                        WHEN TransactionType IN ('PURCHASE_PAYMENT', 'SALARY_PAYMENT', 'DIRECT_EXPENSE', 'SALES_REFUND') THEN -ABS(Amount)
                        -- For inventory transactions, consider the direction
                        WHEN TransactionType = 'INVENTORY_SALE' AND Amount > 0 THEN ABS(Amount)
                        WHEN TransactionType = 'INVENTORY_PURCHASE' AND Amount < 0 THEN -ABS(Amount)
                        ELSE 0
                    END
                ), 0) as wallet_balance 
            FROM financial_transactions 
            WHERE PaymentMethod = 'Wallet' AND Status = 'Completed'
        ";
        $walletResult = $db->fetchOne($walletQuery);
        $walletBalance = $walletResult['wallet_balance'] ?? 0;

        // Total balance
        $totalBalance = $cashBalance + $walletBalance;

        $balances = [
            'cash' => $cashBalance,
            'wallet' => $walletBalance,
            'total' => $totalBalance
        ];

        Utils::sendSuccessResponse('Balances retrieved successfully', $balances);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to get balances: ' . $e->getMessage());
    }
}

/**
 * Get transaction history
 */
function getTransactions()
{
    global $db;

    try {
        $query = "
            SELECT 
                ft.TransactionID,
                ft.TransactionDate,
                ft.TransactionType,
                ft.ReferenceID,
                ft.ReferenceType,
                ft.Amount,
                ft.PaymentMethod,
                ft.Status,
                ft.Description,
                ft.Notes,
                ft.CreatedAt,
                -- Calculate running balance for each payment method
                CASE 
                    WHEN ft.PaymentMethod = 'Cash' THEN
                        (SELECT COALESCE(SUM(
                            CASE 
                                -- Money coming IN (positive for cash balance)
                                WHEN ft2.TransactionType IN ('SALES_PAYMENT', 'DIRECT_INCOME', 'PURCHASE_REFUND') THEN ABS(ft2.Amount)
                                -- Money going OUT (negative for cash balance)  
                                WHEN ft2.TransactionType IN ('PURCHASE_PAYMENT', 'SALARY_PAYMENT', 'DIRECT_EXPENSE', 'SALES_REFUND') THEN -ABS(ft2.Amount)
                                -- For inventory transactions, consider the direction
                                WHEN ft2.TransactionType = 'INVENTORY_SALE' AND ft2.Amount > 0 THEN ABS(ft2.Amount)
                                WHEN ft2.TransactionType = 'INVENTORY_PURCHASE' AND ft2.Amount < 0 THEN -ABS(ft2.Amount)
                                ELSE 0
                            END
                        ), 0) 
                         FROM financial_transactions ft2 
                         WHERE ft2.PaymentMethod = 'Cash' 
                         AND ft2.Status = 'Completed'
                         AND ft2.TransactionID <= ft.TransactionID)
                    WHEN ft.PaymentMethod = 'Wallet' THEN
                        (SELECT COALESCE(SUM(
                            CASE 
                                -- Money coming IN (positive for wallet balance)
                                WHEN ft2.TransactionType IN ('SALES_PAYMENT', 'DIRECT_INCOME', 'PURCHASE_REFUND') THEN ft2.Amount
                                -- Money going OUT (negative for wallet balance)
                                WHEN ft2.TransactionType IN ('PURCHASE_PAYMENT', 'SALARY_PAYMENT', 'DIRECT_EXPENSE', 'SALES_REFUND') THEN -ABS(ft2.Amount)
                                -- For inventory transactions, consider the direction
                                WHEN ft2.TransactionType = 'INVENTORY_SALE' AND ft2.Amount > 0 THEN ft2.Amount
                                WHEN ft2.TransactionType = 'INVENTORY_PURCHASE' AND ft2.Amount < 0 THEN -ABS(ft2.Amount)
                                ELSE 0
                            END
                        ), 0) 
                         FROM financial_transactions ft2 
                         WHERE ft2.PaymentMethod = 'Wallet' 
                         AND ft2.Status = 'Completed'
                         AND ft2.TransactionID <= ft.TransactionID)
                    ELSE 0
                END as RunningBalance
            FROM financial_transactions ft
            WHERE ft.PaymentMethod IN ('Cash', 'Wallet')
            ORDER BY ft.TransactionDate DESC, ft.TransactionID DESC
        ";

        $transactions = $db->fetchAll($query);

        Utils::sendSuccessResponse('Transactions retrieved successfully', $transactions);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to get transactions: ' . $e->getMessage());
    }
}

/**
 * Get specific transaction details
 */
function getTransaction()
{
    global $db;

    $transactionId = $_GET['transactionId'] ?? null;

    if (!$transactionId) {
        Utils::sendErrorResponse('Transaction ID is required');
    }

    try {
        $query = "
            SELECT 
                ft.TransactionID,
                ft.TransactionDate,
                ft.TransactionType,
                ft.ReferenceID,
                ft.ReferenceType,
                ft.Amount,
                ft.PaymentMethod,
                ft.Status,
                ft.Description,
                ft.Notes,
                ft.CreatedAt,
                -- Calculate running balance for each payment method
                CASE 
                    WHEN ft.PaymentMethod = 'Cash' THEN
                        (SELECT COALESCE(SUM(
                            CASE 
                                -- Money coming IN (positive for cash balance)
                                WHEN ft2.TransactionType IN ('SALES_PAYMENT', 'DIRECT_INCOME', 'PURCHASE_REFUND') THEN ABS(ft2.Amount)
                                -- Money going OUT (negative for cash balance)  
                                WHEN ft2.TransactionType IN ('PURCHASE_PAYMENT', 'SALARY_PAYMENT', 'DIRECT_EXPENSE', 'SALES_REFUND') THEN -ABS(ft2.Amount)
                                -- For inventory transactions, consider the direction
                                WHEN ft2.TransactionType = 'INVENTORY_SALE' AND ft2.Amount > 0 THEN ABS(ft2.Amount)
                                WHEN ft2.TransactionType = 'INVENTORY_PURCHASE' AND ft2.Amount < 0 THEN -ABS(ft2.Amount)
                                ELSE 0
                            END
                        ), 0) 
                         FROM financial_transactions ft2 
                         WHERE ft2.PaymentMethod = 'Cash' 
                         AND ft2.Status = 'Completed'
                         AND ft2.TransactionID <= ft.TransactionID)
                    WHEN ft.PaymentMethod = 'Wallet' THEN
                        (SELECT COALESCE(SUM(
                            CASE 
                                -- Money coming IN (positive for wallet balance)
                                WHEN ft2.TransactionType IN ('SALES_PAYMENT', 'DIRECT_INCOME', 'PURCHASE_REFUND') THEN ft2.Amount
                                -- Money going OUT (negative for wallet balance)
                                WHEN ft2.TransactionType IN ('PURCHASE_PAYMENT', 'SALARY_PAYMENT', 'DIRECT_EXPENSE', 'SALES_REFUND') THEN -ABS(ft2.Amount)
                                -- For inventory transactions, consider the direction
                                WHEN ft2.TransactionType = 'INVENTORY_SALE' AND ft2.Amount > 0 THEN ft2.Amount
                                WHEN ft2.TransactionType = 'INVENTORY_PURCHASE' AND ft2.Amount < 0 THEN -ABS(ft2.Amount)
                                ELSE 0
                            END
                        ), 0) 
                         FROM financial_transactions ft2 
                         WHERE ft2.PaymentMethod = 'Wallet' 
                         AND ft2.Status = 'Completed'
                         AND ft2.TransactionID <= ft.TransactionID)
                    ELSE 0
                END as RunningBalance
            FROM financial_transactions ft
            WHERE ft.TransactionID = ? AND ft.PaymentMethod IN ('Cash', 'Wallet')
        ";

        $transaction = $db->fetchOne($query, [$transactionId]);

        if (!$transaction) {
            Utils::sendErrorResponse('Transaction not found');
        }

        Utils::sendSuccessResponse('Transaction retrieved successfully', $transaction);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to get transaction: ' . $e->getMessage());
    }
}

/**
 * Add new transaction (expense or income)
 */
function addTransaction()
{
    global $db;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Utils::sendErrorResponse('Method not allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        Utils::sendErrorResponse('Invalid JSON input');
    }

    // Validate required fields
    $requiredFields = ['transactionType', 'amount', 'paymentMethod', 'transactionDate', 'description'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            Utils::sendErrorResponse("Missing required field: $field");
        }
    }

    try {
        $db->beginTransaction();

        // Insert the transaction
        $query = "
            INSERT INTO financial_transactions (
                TransactionType,
                ReferenceID,
                ReferenceType,
                Amount,
                PaymentMethod,
                Status,
                TransactionDate,
                Description,
                Notes,
                PreviousBalance,
                NewBalance,
                CreatedBy,
                CreatedAt
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";

        // Get current user ID from session
        $currentUserId = SessionManager::getUserId() ?? 1; // Default to 1 if not set

        // Calculate balances (for now, set to 0 - this can be enhanced later)
        $previousBalance = 0.00;
        $newBalance = 0.00;

        $params = [
            $input['transactionType'],
            $input['referenceId'] ?? null,
            $input['referenceType'] ?? null,
            $input['amount'],
            $input['paymentMethod'],
            $input['status'] ?? 'Completed',
            $input['transactionDate'],
            $input['description'],
            $input['notes'] ?? null,
            $previousBalance,
            $newBalance,
            $currentUserId
        ];

        $db->query($query, $params);
        $transactionId = $db->lastInsertId();

        if (!$transactionId) {
            throw new Exception('Failed to insert transaction');
        }

        $db->commit();

        Utils::sendSuccessResponse('Transaction added successfully', ['transactionId' => $transactionId]);
    } catch (Exception $e) {
        $db->rollback();
        Utils::sendErrorResponse('Failed to add transaction: ' . $e->getMessage());
    }
}
