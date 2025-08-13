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

        // Check if transaction already exists to prevent duplicates
        $existingTransaction = self::$db->fetchOne(
            "SELECT TransactionID FROM financial_transactions 
             WHERE ReferenceID = ? AND ReferenceType = 'order' AND TransactionType = 'SALES_ORDER'",
            [$orderId]
        );

        if ($existingTransaction) {
            // Update existing transaction instead of creating new one
            $query = "UPDATE financial_transactions 
                     SET Amount = ?, Status = ?, UpdatedAt = CURRENT_TIMESTAMP 
                     WHERE ReferenceID = ? AND ReferenceType = 'order' AND TransactionType = 'SALES_ORDER'";
            self::$db->query($query, [$totalAmount, $status, $orderId]);
            return $existingTransaction['TransactionID'];
        }

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
     * Create a purchase order transaction (creates payable, not expense)
     */
    public static function createPurchaseOrderTransaction($purchaseId, $supplierId, $totalAmount, $status = 'Pending')
    {
        self::init();

        // Check if transaction already exists to prevent duplicates
        $existingTransaction = self::$db->fetchOne(
            "SELECT TransactionID FROM financial_transactions 
             WHERE ReferenceID = ? AND ReferenceType = 'purchase' AND TransactionType = 'PURCHASE_ORDER'",
            [$purchaseId]
        );

        if ($existingTransaction) {
            // Update existing transaction instead of creating new one
            $query = "UPDATE financial_transactions 
                     SET Amount = ?, Status = ?, UpdatedAt = CURRENT_TIMESTAMP 
                     WHERE ReferenceID = ? AND ReferenceType = 'purchase' AND TransactionType = 'PURCHASE_ORDER'";
            self::$db->query($query, [$totalAmount, $status, $purchaseId]);
            return $existingTransaction['TransactionID'];
        }

        return self::createTransaction([
            'transactionType' => 'PURCHASE_ORDER',
            'referenceId' => $purchaseId,
            'referenceType' => 'purchase',
            'amount' => $totalAmount, // Positive for payable (not expense)
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

        // Calculate totals from transactions with proper logic
        $query = "SELECT 
                    COALESCE(SUM(CASE 
                        WHEN TransactionType = 'SALES_ORDER' THEN ABS(Amount)
                        ELSE 0 
                    END), 0) as totalPurchases,
                    COALESCE(SUM(CASE 
                        WHEN TransactionType = 'SALES_PAYMENT' THEN ABS(Amount)
                        ELSE 0 
                    END), 0) as totalPayments,
                    MAX(TransactionDate) as lastTransactionDate
                  FROM financial_transactions 
                  WHERE CustomerID = ? AND Status = 'Completed'";

        $result = self::$db->fetchOne($query, [$customerId]);

        $totalPurchases = $result['totalPurchases'] ?? 0;
        $totalPayments = $result['totalPayments'] ?? 0;
        $lastTransactionDate = $result['lastTransactionDate'] ?? null;

        // Calculate current balance (sales - payments)
        // Positive balance means customer owes us money
        // Negative balance means we owe the customer money (overpayment)
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

        error_log("Customer $customerId balance calculated: Purchases=$totalPurchases, Payments=$totalPayments, Balance=$currentBalance");
    }

    /**
     * Calculate supplier balance using direct SQL
     */
    public static function calculateSupplierBalance($supplierId)
    {
        self::init();

        // Calculate totals from transactions with proper logic
        $query = "SELECT 
                    COALESCE(SUM(CASE 
                        WHEN TransactionType = 'PURCHASE_ORDER' THEN ABS(Amount)
                        ELSE 0 
                    END), 0) as totalPurchases,
                    COALESCE(SUM(CASE 
                        WHEN TransactionType = 'PURCHASE_PAYMENT' THEN ABS(Amount)
                        ELSE 0 
                    END), 0) as totalPayments,
                    MAX(TransactionDate) as lastTransactionDate
                  FROM financial_transactions 
                  WHERE SupplierID = ? AND Status = 'Completed'";

        $result = self::$db->fetchOne($query, [$supplierId]);

        $totalPurchases = $result['totalPurchases'] ?? 0;
        $totalPayments = $result['totalPayments'] ?? 0;
        $lastTransactionDate = $result['lastTransactionDate'] ?? null;

        // Calculate current balance (purchases - payments)
        // Positive balance means we owe the supplier money
        // Negative balance means the supplier owes us money (overpayment)
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

        error_log("Supplier $supplierId balance calculated: Purchases=$totalPurchases, Payments=$totalPayments, Balance=$currentBalance");
    }

    /**
     * Calculate average cost for inventory using weighted average method
     */
    public static function calculateAverageCost($itemId, $newQuantity, $newCost)
    {
        self::init();

        // Get current inventory
        $currentInventory = self::$db->fetchOne(
            "SELECT Quantity, Cost FROM inventory WHERE ItemID = ?",
            [$itemId]
        );

        if (!$currentInventory) {
            // First time adding this item to inventory
            return $newCost;
        }

        $currentQuantity = $currentInventory['Quantity'];
        $currentCost = $currentInventory['Cost'];

        // Calculate weighted average cost
        $totalQuantity = $currentQuantity + $newQuantity;
        $totalValue = ($currentQuantity * $currentCost) + ($newQuantity * $newCost);

        return $totalQuantity > 0 ? $totalValue / $totalQuantity : 0;
    }

    /**
     * Update inventory with average costing
     */
    public static function updateInventoryWithCost($itemId, $quantity, $unitCost)
    {
        self::init();

        $averageCost = self::calculateAverageCost($itemId, $quantity, $unitCost);

        // Check if inventory record exists
        $existingInventory = self::$db->fetchOne(
            "SELECT InventoryID, Cost FROM inventory WHERE ItemID = ?",
            [$itemId]
        );

        $costChanged = false;
        if ($existingInventory) {
            // Check if cost has changed
            if (abs($existingInventory['Cost'] - $averageCost) > 0.01) { // Allow for small floating point differences
                $costChanged = true;
            }

            // Update existing inventory
            $query = "UPDATE inventory 
                     SET Quantity = Quantity + ?, Cost = ?, LastUpdated = CURRENT_TIMESTAMP 
                     WHERE ItemID = ?";
            self::$db->query($query, [$quantity, $averageCost, $itemId]);
        } else {
            // Insert new inventory record
            $query = "INSERT INTO inventory (ItemID, Quantity, Cost, LastUpdated) 
                     VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
            self::$db->query($query, [$itemId, $quantity, $averageCost]);
        }

        // If cost changed, trigger COGS recalculation for all delivered orders using this item
        if ($costChanged) {
            self::recalculateCOGSForItem($itemId);
        }

        return $averageCost;
    }

    /**
     * Recalculate COGS for all delivered orders that use a specific item
     * This is called when inventory costs change
     */
    public static function recalculateCOGSForItem($itemId)
    {
        self::init();

        try {
            // Find all delivered sales orders that use this item
            $ordersToRecalculate = self::$db->fetchAll("
                SELECT DISTINCT som.OrderID
                FROM sales_orders_main som
                JOIN sales_order_items soi ON som.OrderID = soi.OrderID
                WHERE som.Status = 'Delivered' 
                AND soi.ItemID = ?
            ", [$itemId]);

            if (empty($ordersToRecalculate)) {
                return 0; // No orders to recalculate
            }

            $recalculatedCount = 0;
            foreach ($ordersToRecalculate as $order) {
                try {
                    // Delete existing COGS transactions for this order
                    self::$db->query("DELETE FROM financial_transactions 
                                    WHERE TransactionType = 'INVENTORY_SALE' 
                                    AND ReferenceID = ? 
                                    AND ReferenceType = 'order'", [$order['OrderID']]);

                    // Get current order items with updated costs
                    $items = self::$db->fetchAll("
                        SELECT soi.ItemID, soi.Quantity, inv.Cost 
                        FROM sales_order_items soi 
                        LEFT JOIN inventory inv ON soi.ItemID = inv.ItemID 
                        WHERE soi.OrderID = ?
                    ", [$order['OrderID']]);

                    // Recreate COGS transactions with updated costs
                    foreach ($items as $item) {
                        if ($item['Cost'] && $item['Cost'] > 0) {
                            $cogsResult = self::createCOGSTransaction(
                                $order['OrderID'],
                                $item['ItemID'],
                                $item['Quantity'],
                                $item['Cost']
                            );

                            if ($cogsResult) {
                                $recalculatedCount++;
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Failed to recalculate COGS for OrderID: {$order['OrderID']}: " . $e->getMessage());
                }
            }

            if ($recalculatedCount > 0) {
                error_log("Recalculated COGS for $recalculatedCount items across " . count($ordersToRecalculate) . " orders due to cost change for ItemID: $itemId");
            }

            return $recalculatedCount;
        } catch (Exception $e) {
            error_log("Error recalculating COGS for ItemID: $itemId: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Create COGS transaction for sales
     */
    public static function createCOGSTransaction($orderId, $itemId, $quantity, $unitCost, $status = 'Completed')
    {
        self::init();

        $cogsAmount = $quantity * $unitCost;

        return self::createTransaction([
            'transactionType' => 'INVENTORY_SALE',
            'referenceId' => $orderId,
            'referenceType' => 'order',
            'amount' => -$cogsAmount, // Negative for expense (COGS)
            'status' => 'Completed', // COGS transactions are always 'Completed' regardless of order status
            'paymentMethod' => 'Cash', // Default to Cash for COGS transactions
            'description' => "COGS for Order #$orderId - Item #$itemId",
            'createdBy' => 1
        ]);
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
        // For COGS and other non-customer/supplier transactions, PreviousBalance stays 0

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
            $params = [
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
            ];

            self::$db->query($query, $params);

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

    /**
     * Update cash/wallet balances after financial transaction
     * This ensures the cash module reflects the latest balances
     */
    public static function updateCashBalances()
    {
        self::init();

        try {
            // This method can be called after any financial transaction
            // to ensure cash/wallet balances are up to date
            // The actual balance calculation is done in the cash API

            // For now, we just log that balances should be refreshed
            error_log("Cash balances should be refreshed after financial transaction");

            return true;
        } catch (Exception $e) {
            error_log("Failed to update cash balances: " . $e->getMessage());
            return false;
        }
    }
}
