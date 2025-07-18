<?php

/**
 * Financial Helper Class
 * Provides methods for other modules to create financial transactions
 */

require_once __DIR__ . '/../config/database.php';

class FinancialHelper
{
    private static $db;

    public static function init()
    {
        if (!self::$db) {
            self::$db = Database::getInstance();
        }
    }

    /**
     * Create a sales order transaction
     */
    public static function createSalesOrderTransaction($orderId, $customerId, $totalAmount, $status = 'Pending')
    {
        self::init();

        return self::createTransaction([
            'transactionType' => 'SALES_ORDER',
            'referenceId' => $orderId,
            'referenceType' => 'order',
            'amount' => $totalAmount, // Positive for income
            'status' => $status,
            'customerId' => $customerId,
            'description' => "Sales Order #$orderId",
            'createdBy' => 1 // Default to admin
        ]);
    }

    /**
     * Create a sales payment transaction
     */
    public static function createSalesPaymentTransaction($orderId, $customerId, $paymentAmount, $paymentMethod, $status = 'Completed')
    {
        self::init();

        return self::createTransaction([
            'transactionType' => 'SALES_PAYMENT',
            'referenceId' => $orderId,
            'referenceType' => 'order',
            'amount' => -$paymentAmount, // Negative to reduce receivable
            'paymentMethod' => $paymentMethod,
            'status' => $status,
            'customerId' => $customerId,
            'description' => "Payment for Order #$orderId",
            'createdBy' => 1
        ]);
    }

    /**
     * Create a purchase order transaction
     */
    public static function createPurchaseOrderTransaction($purchaseId, $supplierId, $totalAmount, $status = 'Pending')
    {
        self::init();

        return self::createTransaction([
            'transactionType' => 'PURCHASE_ORDER',
            'referenceId' => $purchaseId,
            'referenceType' => 'purchase',
            'amount' => -$totalAmount, // Negative for expense
            'status' => $status,
            'supplierId' => $supplierId,
            'description' => "Purchase Order #$purchaseId",
            'createdBy' => 1
        ]);
    }

    /**
     * Create a purchase payment transaction
     */
    public static function createPurchasePaymentTransaction($purchaseId, $supplierId, $paymentAmount, $paymentMethod, $status = 'Completed')
    {
        self::init();

        return self::createTransaction([
            'transactionType' => 'PURCHASE_PAYMENT',
            'referenceId' => $purchaseId,
            'referenceType' => 'purchase',
            'amount' => $paymentAmount, // Positive to reduce payable
            'paymentMethod' => $paymentMethod,
            'status' => $status,
            'supplierId' => $supplierId,
            'description' => "Payment for Purchase #$purchaseId",
            'createdBy' => 1
        ]);
    }

    /**
     * Create a salary payment transaction
     */
    public static function createSalaryTransaction($salaryId, $employeeId, $amount, $paymentMethod, $status = 'Completed')
    {
        self::init();

        return self::createTransaction([
            'transactionType' => 'SALARY_PAYMENT',
            'referenceId' => $salaryId,
            'referenceType' => 'salary',
            'amount' => -$amount, // Negative for expense
            'paymentMethod' => $paymentMethod,
            'status' => $status,
            'employeeId' => $employeeId,
            'description' => "Salary Payment #$salaryId",
            'createdBy' => 1
        ]);
    }

    /**
     * Create an inventory purchase transaction
     */
    public static function createInventoryPurchaseTransaction($itemId, $supplierId, $quantity, $unitPrice, $status = 'Completed')
    {
        self::init();

        $totalAmount = $quantity * $unitPrice;

        return self::createTransaction([
            'transactionType' => 'INVENTORY_PURCHASE',
            'referenceId' => $itemId,
            'referenceType' => 'inventory',
            'amount' => -$totalAmount, // Negative for expense
            'status' => $status,
            'supplierId' => $supplierId,
            'description' => "Inventory Purchase - Item #$itemId (Qty: $quantity)",
            'createdBy' => 1
        ]);
    }

    /**
     * Create an inventory sale transaction
     */
    public static function createInventorySaleTransaction($itemId, $customerId, $quantity, $unitPrice, $status = 'Completed')
    {
        self::init();

        $totalAmount = $quantity * $unitPrice;

        return self::createTransaction([
            'transactionType' => 'INVENTORY_SALE',
            'referenceId' => $itemId,
            'referenceType' => 'inventory',
            'amount' => $totalAmount, // Positive for income
            'status' => $status,
            'customerId' => $customerId,
            'description' => "Inventory Sale - Item #$itemId (Qty: $quantity)",
            'createdBy' => 1
        ]);
    }

    /**
     * Create a direct expense transaction
     */
    public static function createDirectExpenseTransaction($description, $amount, $paymentMethod, $status = 'Completed')
    {
        self::init();

        return self::createTransaction([
            'transactionType' => 'DIRECT_EXPENSE',
            'referenceId' => 'EXP-' . time(),
            'referenceType' => 'expense',
            'amount' => -$amount, // Negative for expense
            'paymentMethod' => $paymentMethod,
            'status' => $status,
            'description' => $description,
            'createdBy' => 1
        ]);
    }

    /**
     * Create a direct income transaction
     */
    public static function createDirectIncomeTransaction($description, $amount, $paymentMethod, $status = 'Completed')
    {
        self::init();

        return self::createTransaction([
            'transactionType' => 'DIRECT_INCOME',
            'referenceId' => 'INC-' . time(),
            'referenceType' => 'income',
            'amount' => $amount, // Positive for income
            'paymentMethod' => $paymentMethod,
            'status' => $status,
            'description' => $description,
            'createdBy' => 1
        ]);
    }

    /**
     * Update transaction status and recalculate balances
     */
    public static function updateTransactionStatus($referenceId, $referenceType, $newStatus)
    {
        self::init();

        // Get the transaction to find customer/supplier IDs
        $transaction = self::$db->fetchOne(
            "SELECT CustomerID, SupplierID FROM financial_transactions 
             WHERE ReferenceID = ? AND ReferenceType = ?",
            [$referenceId, $referenceType]
        );

        // Update transaction status
        $query = "UPDATE financial_transactions 
                  SET Status = ?, UpdatedAt = CURRENT_TIMESTAMP 
                  WHERE ReferenceID = ? AND ReferenceType = ?";

        $result = self::$db->query($query, [$newStatus, $referenceId, $referenceType]);

        // Recalculate balances if transaction was found
        if ($transaction) {
            if ($transaction['CustomerID']) {
                self::calculateCustomerBalance($transaction['CustomerID']);
            }
            if ($transaction['SupplierID']) {
                self::calculateSupplierBalance($transaction['SupplierID']);
            }
        }

        return $result;
    }

    /**
     * Get customer balance
     */
    public static function getCustomerBalance($customerId)
    {
        self::init();

        self::calculateCustomerBalance($customerId);

        $balance = self::$db->fetchOne("SELECT * FROM customer_balances WHERE CustomerID = ?", [$customerId]);
        return $balance ? $balance['CurrentBalance'] : 0;
    }

    /**
     * Get supplier balance
     */
    public static function getSupplierBalance($supplierId)
    {
        self::init();

        self::calculateSupplierBalance($supplierId);

        $balance = self::$db->fetchOne("SELECT * FROM supplier_balances WHERE SupplierID = ?", [$supplierId]);
        return $balance ? $balance['CurrentBalance'] : 0;
    }

    /**
     * Calculate customer balance using direct SQL
     */
    public static function calculateCustomerBalance($customerId)
    {
        self::init();

        // Calculate totals from transactions
        $query = "SELECT 
                    COALESCE(SUM(CASE WHEN Amount > 0 THEN Amount ELSE 0 END), 0) as totalPurchases,
                    COALESCE(SUM(CASE WHEN Amount < 0 THEN ABS(Amount) ELSE 0 END), 0) as totalPayments,
                    MAX(TransactionDate) as lastTransactionDate
                  FROM financial_transactions 
                  WHERE CustomerID = ? AND Status = 'Completed'";

        $result = self::$db->fetchOne($query, [$customerId]);

        $totalPurchases = $result['totalPurchases'] ?? 0;
        $totalPayments = $result['totalPayments'] ?? 0;
        $lastTransactionDate = $result['lastTransactionDate'] ?? null;

        // Calculate current balance (purchases - payments)
        $currentBalance = $totalPurchases - $totalPayments;

        // Update customer balance
        $updateQuery = "UPDATE customer_balances 
                       SET CurrentBalance = ?, TotalPurchases = ?, TotalPayments = ?, 
                           LastTransactionDate = ?, UpdatedAt = CURRENT_TIMESTAMP
                       WHERE CustomerID = ?";

        $stmt = self::$db->query($updateQuery, [
            $currentBalance,
            $totalPurchases,
            $totalPayments,
            $lastTransactionDate,
            $customerId
        ]);

        // Insert if not exists
        if ($stmt->rowCount() === 0) {
            $insertQuery = "INSERT INTO customer_balances 
                           (CustomerID, CurrentBalance, TotalPurchases, TotalPayments, LastTransactionDate)
                           VALUES (?, ?, ?, ?, ?)";
            self::$db->query($insertQuery, [
                $customerId,
                $currentBalance,
                $totalPurchases,
                $totalPayments,
                $lastTransactionDate
            ]);
        }
    }

    /**
     * Calculate supplier balance using direct SQL
     */
    public static function calculateSupplierBalance($supplierId)
    {
        self::init();

        // Calculate totals from transactions
        $query = "SELECT 
                    COALESCE(SUM(CASE WHEN Amount < 0 THEN ABS(Amount) ELSE 0 END), 0) as totalPurchases,
                    COALESCE(SUM(CASE WHEN Amount > 0 THEN Amount ELSE 0 END), 0) as totalPayments,
                    MAX(TransactionDate) as lastTransactionDate
                  FROM financial_transactions 
                  WHERE SupplierID = ? AND Status = 'Completed'";

        $result = self::$db->fetchOne($query, [$supplierId]);

        $totalPurchases = $result['totalPurchases'] ?? 0;
        $totalPayments = $result['totalPayments'] ?? 0;
        $lastTransactionDate = $result['lastTransactionDate'] ?? null;

        // Calculate current balance (purchases - payments)
        $currentBalance = $totalPurchases - $totalPayments;

        // Update supplier balance
        $updateQuery = "UPDATE supplier_balances 
                       SET CurrentBalance = ?, TotalPurchases = ?, TotalPayments = ?, 
                           LastTransactionDate = ?, UpdatedAt = CURRENT_TIMESTAMP
                       WHERE SupplierID = ?";

        $stmt = self::$db->query($updateQuery, [
            $currentBalance,
            $totalPurchases,
            $totalPayments,
            $lastTransactionDate,
            $supplierId
        ]);

        // Insert if not exists
        if ($stmt->rowCount() === 0) {
            $insertQuery = "INSERT INTO supplier_balances 
                           (SupplierID, CurrentBalance, TotalPurchases, TotalPayments, LastTransactionDate)
                           VALUES (?, ?, ?, ?, ?)";
            self::$db->query($insertQuery, [
                $supplierId,
                $currentBalance,
                $totalPurchases,
                $totalPayments,
                $lastTransactionDate
            ]);
        }
    }

    /**
     * Core transaction creation method
     */
    private static function createTransaction($data)
    {
        self::init();

        // Get current balance for customer/supplier
        $previousBalance = 0;
        if (!empty($data['customerId'])) {
            $balance = self::$db->fetchOne("SELECT CurrentBalance FROM customer_balances WHERE CustomerID = ?", [$data['customerId']]);
            $previousBalance = $balance ? $balance['CurrentBalance'] : 0;
        } elseif (!empty($data['supplierId'])) {
            $balance = self::$db->fetchOne("SELECT CurrentBalance FROM supplier_balances WHERE SupplierID = ?", [$data['supplierId']]);
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
            self::$db->query($query, [
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
                $data['createdBy'] ?? 1
            ]);

            $transactionId = self::$db->lastInsertId();

            // Update balances
            if (!empty($data['customerId'])) {
                self::calculateCustomerBalance($data['customerId']);
            }
            if (!empty($data['supplierId'])) {
                self::calculateSupplierBalance($data['supplierId']);
            }

            return $transactionId;
        } catch (Exception $e) {
            error_log("Failed to create financial transaction: " . $e->getMessage());
            return false;
        }
    }
}
