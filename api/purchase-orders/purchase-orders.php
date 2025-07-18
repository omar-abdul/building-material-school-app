<?php

/**
 * Purchase Orders Backend API
 * Uses centralized database and utilities
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/utils.php';
require_once __DIR__ . '/../../includes/FinancialHelper.php';

// Set headers for API
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$db = Database::getInstance();

// Helper function to get the next PurchaseOrderID
function getNextPurchaseOrderID()
{
    global $db;
    $result = $db->fetchOne("SELECT MAX(PurchaseOrderID) as max_id FROM purchase_orders");
    return ($result['max_id'] ? $result['max_id'] + 1 : 1);
}

// ==============================================
// INVENTORY MANAGEMENT FUNCTIONS
// ==============================================

/**
 * Update inventory after purchase order (increase stock for purchases)
 */
function updateInventory($itemId, $quantity)
{
    global $db;

    // Check if inventory record exists
    $existing = $db->fetchOne("SELECT InventoryID FROM inventory WHERE ItemID = ?", [$itemId]);

    if ($existing) {
        // Update existing inventory
        return $db->query("UPDATE inventory SET Quantity = Quantity + ? WHERE ItemID = ?", [$quantity, $itemId]);
    } else {
        // Create new inventory record
        return $db->query("INSERT INTO inventory (ItemID, Quantity, LastUpdated) VALUES (?, ?, NOW())", [$itemId, $quantity]);
    }
}

// ==============================================
// FINANCIAL TRANSACTION FUNCTIONS
// ==============================================

/**
 * Create or update financial transaction for purchase order
 */
function createPurchaseOrderFinancialTransaction($purchaseOrderId, $supplierId, $totalAmount, $status)
{
    try {
        // Determine transaction status based on purchase order status
        $transactionStatus = 'Pending';
        if ($status === 'Received' || $status === 'Completed') {
            $transactionStatus = 'Completed';
        } elseif ($status === 'Cancelled') {
            $transactionStatus = 'Cancelled';
        }

        // Create purchase order transaction
        FinancialHelper::createPurchaseOrderTransaction($purchaseOrderId, $supplierId, $totalAmount, $transactionStatus);

        return true;
    } catch (Exception $e) {
        error_log("Failed to create financial transaction for purchase order $purchaseOrderId: " . $e->getMessage());
        return false;
    }
}

/**
 * Update financial transaction when purchase order is updated
 */
function updatePurchaseOrderFinancialTransaction($purchaseOrderId, $supplierId, $totalAmount, $status)
{
    try {
        // Update the existing transaction status
        $transactionStatus = 'Pending';
        if ($status === 'Received' || $status === 'Completed') {
            $transactionStatus = 'Completed';
        } elseif ($status === 'Cancelled') {
            $transactionStatus = 'Cancelled';
        }

        // Update transaction status
        FinancialHelper::updateTransactionStatus($purchaseOrderId, 'purchase', $transactionStatus);

        return true;
    } catch (Exception $e) {
        error_log("Failed to update financial transaction for purchase order $purchaseOrderId: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete financial transaction when purchase order is deleted
 */
function deletePurchaseOrderFinancialTransaction($purchaseOrderId)
{
    try {
        // Update transaction status to cancelled instead of deleting
        FinancialHelper::updateTransactionStatus($purchaseOrderId, 'purchase', 'Cancelled');

        return true;
    } catch (Exception $e) {
        error_log("Failed to delete financial transaction for purchase order $purchaseOrderId: " . $e->getMessage());
        return false;
    }
}

// ==============================================
// API ENDPOINTS
// ==============================================

// Get all purchase orders
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['purchaseOrderId']) && !isset($_GET['supplierId']) && !isset($_GET['employeeId']) && !isset($_GET['itemId'])) {
    try {
        $statusFilter = $_GET['status'] ?? '';
        $searchTerm = $_GET['search'] ?? '';

        $query = "
            SELECT po.PurchaseOrderID, s.SupplierName, e.EmployeeName,
                   COUNT(po.ItemID) as ItemsCount,
                   SUM(po.TotalAmount) as TotalAmount,
                   po.Status, po.OrderDate
            FROM purchase_orders po
            LEFT JOIN suppliers s ON po.SupplierID = s.SupplierID
            LEFT JOIN employees e ON po.EmployeeID = e.EmployeeID
            WHERE 1=1
        ";

        $params = [];

        if ($statusFilter) {
            $query .= " AND po.Status = ?";
            $params[] = $statusFilter;
        }

        if ($searchTerm) {
            $query .= " AND (s.SupplierName LIKE ? OR e.EmployeeName LIKE ? OR po.PurchaseOrderID LIKE ?)";
            $params[] = "%$searchTerm%";
            $params[] = "%$searchTerm%";
            $params[] = "%$searchTerm%";
        }

        $query .= " GROUP BY po.PurchaseOrderID, s.SupplierName, e.EmployeeName, po.Status, po.OrderDate ORDER BY po.OrderDate DESC";

        $purchaseOrders = $db->fetchAll($query, $params);
        Utils::sendSuccessResponse('Purchase orders retrieved successfully', $purchaseOrders);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve purchase orders: ' . $e->getMessage());
    }
}

// Get single purchase order
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['purchaseOrderId'])) {
    try {
        $purchaseOrderId = $_GET['purchaseOrderId'];

        if (empty($purchaseOrderId)) {
            Utils::sendErrorResponse('Purchase Order ID is required');
            return;
        }

        // Get purchase order summary
        $purchaseOrder = $db->fetchOne("
            SELECT po.PurchaseOrderID, po.SupplierID, s.SupplierName, po.EmployeeID, e.EmployeeName,
                   po.OrderDate, po.Status, SUM(po.TotalAmount) as TotalAmount
            FROM purchase_orders po
            LEFT JOIN suppliers s ON po.SupplierID = s.SupplierID
            LEFT JOIN employees e ON po.EmployeeID = e.EmployeeID
            WHERE po.PurchaseOrderID = ?
            GROUP BY po.PurchaseOrderID, po.SupplierID, s.SupplierName, po.EmployeeID, e.EmployeeName, po.OrderDate, po.Status
        ", [$purchaseOrderId]);

        if ($purchaseOrder) {
            // Get individual purchase order items
            $items = $db->fetchAll("
                SELECT po.ItemID, i.ItemName, po.Quantity, po.UnitPrice, 
                       po.TotalAmount,
                       inv.Quantity as CurrentInventory
                FROM purchase_orders po
                JOIN items i ON po.ItemID = i.ItemID
                LEFT JOIN inventory inv ON i.ItemID = inv.ItemID
                WHERE po.PurchaseOrderID = ?
            ", [$purchaseOrderId]);

            $purchaseOrder['items'] = $items;
            $purchaseOrder['total_items'] = count($items);
            Utils::sendSuccessResponse('Purchase order retrieved successfully', $purchaseOrder);
        } else {
            Utils::sendErrorResponse('Purchase order not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve purchase order: ' . $e->getMessage());
    }
}

// Get supplier details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['supplierId'])) {
    try {
        $supplierId = $_GET['supplierId'];

        if (empty($supplierId)) {
            Utils::sendErrorResponse('Supplier ID is required');
            return;
        }

        $supplier = $db->fetchOne("SELECT * FROM suppliers WHERE SupplierID = ?", [$supplierId]);

        if ($supplier) {
            Utils::sendSuccessResponse('Supplier details retrieved successfully', $supplier);
        } else {
            Utils::sendErrorResponse('Supplier not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve supplier details: ' . $e->getMessage());
    }
}

// Get employee details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['employeeId'])) {
    try {
        $employeeId = $_GET['employeeId'];

        if (empty($employeeId)) {
            Utils::sendErrorResponse('Employee ID is required');
            return;
        }

        $employee = $db->fetchOne("SELECT * FROM employees WHERE EmployeeID = ?", [$employeeId]);

        if ($employee) {
            Utils::sendSuccessResponse('Employee details retrieved successfully', $employee);
        } else {
            Utils::sendErrorResponse('Employee not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve employee details: ' . $e->getMessage());
    }
}

// Get item details (including inventory)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['itemId'])) {
    try {
        $itemId = $_GET['itemId'];

        if (empty($itemId)) {
            Utils::sendErrorResponse('Item ID is required');
            return;
        }

        $item = $db->fetchOne("
            SELECT i.*, inv.Quantity 
            FROM items i
            LEFT JOIN inventory inv ON i.ItemID = inv.ItemID
            WHERE i.ItemID = ?
        ", [$itemId]);

        if ($item) {
            Utils::sendSuccessResponse('Item details retrieved successfully', $item);
        } else {
            Utils::sendErrorResponse('Item not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve item details: ' . $e->getMessage());
    }
}

// Create or update a purchase order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }

        if (empty($data['supplier_id']) || empty($data['order_date']) || empty($data['status'])) {
            Utils::sendErrorResponse('Supplier ID, order date, and status are required');
            return;
        }

        if (empty($data['items']) || !is_array($data['items'])) {
            Utils::sendErrorResponse('At least one item is required');
            return;
        }

        // Clean up IDs - remove prefixes and convert to integers
        $supplierId = is_string($data['supplier_id']) ? (int)str_replace('SUP-', '', $data['supplier_id']) : (int)$data['supplier_id'];
        $employeeId = !empty($data['employee_id']) ? (is_string($data['employee_id']) ? (int)str_replace('EMP-', '', $data['employee_id']) : (int)$data['employee_id']) : null;

        $db->beginTransaction();

        try {
            // Process purchase order creation/update
            if (empty($data['purchase_order_id'])) {
                $purchaseOrderId = getNextPurchaseOrderID();
            } else {
                $purchaseOrderId = $data['purchase_order_id'];
                // Delete existing purchase order entries for this PurchaseOrderID
                $db->query("DELETE FROM purchase_orders WHERE PurchaseOrderID = ?", [$purchaseOrderId]);
            }

            // Insert items and update inventory
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unitPrice'];
                $totalAmount += $itemTotal;

                // Insert purchase order item
                $db->query("INSERT INTO purchase_orders 
                    (PurchaseOrderID, SupplierID, EmployeeID, ItemID, Quantity, UnitPrice, OrderDate, TotalAmount, Status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                    $purchaseOrderId,
                    $supplierId,
                    $employeeId,
                    $item['item_id'],
                    $item['quantity'],
                    $item['unitPrice'],
                    $data['order_date'],
                    $itemTotal,
                    $data['status']
                ]);

                // Update inventory (increase stock for purchases)
                updateInventory($item['item_id'], $item['quantity']);
            }

            // Create financial transaction
            createPurchaseOrderFinancialTransaction($purchaseOrderId, $supplierId, $totalAmount, $data['status']);

            $db->commit();
            Utils::sendSuccessResponse('Purchase order saved successfully', ['purchase_order_id' => $purchaseOrderId]);
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to save purchase order: ' . $e->getMessage());
    }
}

// Update purchase order
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }

        if (empty($data['purchase_order_id'])) {
            Utils::sendErrorResponse('Purchase Order ID is required');
            return;
        }

        if (empty($data['supplier_id']) || empty($data['order_date']) || empty($data['status'])) {
            Utils::sendErrorResponse('Supplier ID, order date, and status are required');
            return;
        }

        if (empty($data['items']) || !is_array($data['items'])) {
            Utils::sendErrorResponse('At least one item is required');
            return;
        }

        // Clean up IDs - remove prefixes and convert to integers
        $supplierId = is_string($data['supplier_id']) ? (int)str_replace('SUP-', '', $data['supplier_id']) : (int)$data['supplier_id'];
        $employeeId = !empty($data['employee_id']) ? (is_string($data['employee_id']) ? (int)str_replace('EMP-', '', $data['employee_id']) : (int)$data['employee_id']) : null;
        $purchaseOrderId = $data['purchase_order_id'];

        $db->beginTransaction();

        try {
            /*
             * DIFFERENTIAL INVENTORY UPDATE LOGIC:
             * 
             * When editing a purchase order, we need to handle inventory changes correctly:
             * 1. Get original quantities from the purchase order
             * 2. Calculate differences between old and new quantities
             * 3. Handle removed items (decrease their inventory)
             * 4. Update inventory based on differences only
             * 
             * Example:
             * - Original purchase order: Item A (3 qty) - inventory was increased by 3
             * - Updated purchase order: Item A (5 qty) - we only need to increase inventory by 2 more
             * - Result: inventory increased by 5 total (3 original + 2 additional)
             */

            // 1. Get original purchase order quantities before deletion
            $originalItems = $db->fetchAll("SELECT ItemID, Quantity FROM purchase_orders WHERE PurchaseOrderID = ?", [$purchaseOrderId]);
            $originalQuantities = [];
            foreach ($originalItems as $item) {
                $originalQuantities[$item['ItemID']] = $item['Quantity'];
            }

            // 2. Calculate inventory differences
            $inventoryDifferences = [];
            $newItemIds = [];

            foreach ($data['items'] as $item) {
                $itemId = $item['item_id'];
                $newQuantity = $item['quantity'];
                $oldQuantity = $originalQuantities[$itemId] ?? 0;
                $newItemIds[] = $itemId;

                // Calculate the difference (how much more/less we need)
                $quantityDifference = $newQuantity - $oldQuantity;
                $inventoryDifferences[$itemId] = $quantityDifference;
            }

            // 3. Handle items that were removed from the purchase order (decrease their inventory)
            foreach ($originalQuantities as $itemId => $oldQuantity) {
                if (!in_array($itemId, $newItemIds)) {
                    // This item was removed from the purchase order, so we need to decrease its inventory
                    // For purchase orders, removing means decreasing inventory
                    $db->query("UPDATE inventory SET Quantity = GREATEST(0, Quantity - ?) WHERE ItemID = ?", [$oldQuantity, $itemId]);
                }
            }

            // 4. Delete existing purchase order entries for this PurchaseOrderID
            $db->query("DELETE FROM purchase_orders WHERE PurchaseOrderID = ?", [$purchaseOrderId]);

            // 5. Insert items and update inventory based on differences
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unitPrice'];
                $totalAmount += $itemTotal;

                // Insert purchase order item
                $db->query("INSERT INTO purchase_orders 
                    (PurchaseOrderID, SupplierID, EmployeeID, ItemID, Quantity, UnitPrice, OrderDate, TotalAmount, Status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                    $purchaseOrderId,
                    $supplierId,
                    $employeeId,
                    $item['item_id'],
                    $item['quantity'],
                    $item['unitPrice'],
                    $data['order_date'],
                    $itemTotal,
                    $data['status']
                ]);

                // Update inventory based on the difference only
                $quantityDifference = $inventoryDifferences[$item['item_id']];
                if ($quantityDifference != 0) {
                    updateInventory($item['item_id'], $quantityDifference);
                }
            }

            // Update financial transaction
            updatePurchaseOrderFinancialTransaction($purchaseOrderId, $supplierId, $totalAmount, $data['status']);

            $db->commit();
            Utils::sendSuccessResponse('Purchase order updated successfully', ['purchase_order_id' => $purchaseOrderId]);
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update purchase order: ' . $e->getMessage());
    }
}

// Delete a purchase order
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $purchaseOrderId = isset($data['purchaseOrderId']) ? $data['purchaseOrderId'] : ($_GET['purchaseOrderId'] ?? '');

        if (empty($purchaseOrderId)) {
            Utils::sendErrorResponse('Purchase Order ID is required');
            return;
        }

        $db->beginTransaction();

        try {
            // 1. Get all items from the purchase order first
            $items = $db->fetchAll("SELECT ItemID, Quantity FROM purchase_orders WHERE PurchaseOrderID = ?", [$purchaseOrderId]);

            if (empty($items)) {
                Utils::sendErrorResponse('Purchase order not found');
                return;
            }

            // 2. Decrease inventory for each item (reverse the purchase)
            foreach ($items as $item) {
                $db->query("UPDATE inventory SET Quantity = GREATEST(0, Quantity - ?) WHERE ItemID = ?", [$item['Quantity'], $item['ItemID']]);
            }

            // 3. Now delete the purchase order entries
            $db->query("DELETE FROM purchase_orders WHERE PurchaseOrderID = ?", [$purchaseOrderId]);

            // Delete financial transaction
            deletePurchaseOrderFinancialTransaction($purchaseOrderId);

            $db->commit();
            Utils::sendSuccessResponse('Purchase order deleted successfully');
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete purchase order: ' . $e->getMessage());
    }
}
