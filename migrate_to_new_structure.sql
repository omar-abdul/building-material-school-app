-- Migration Script: Move from old structure to new structure
-- Run this after creating the new tables

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ==============================================
-- MIGRATE PURCHASE ORDERS
-- ==============================================

-- Create main purchase orders from existing data
INSERT INTO purchase_orders_main (PurchaseOrderID, SupplierID, EmployeeID, OrderDate, Status, TotalAmount)
SELECT DISTINCT 
    po.PurchaseOrderID,
    po.SupplierID,
    po.EmployeeID,
    po.OrderDate,
    po.Status,
    SUM(po.TotalAmount) as TotalAmount
FROM purchase_orders po
GROUP BY po.PurchaseOrderID, po.SupplierID, po.EmployeeID, po.OrderDate, po.Status;

-- Create purchase order items
INSERT INTO purchase_order_items (PurchaseOrderID, ItemID, Quantity, UnitPrice, TotalAmount)
SELECT 
    po.PurchaseOrderID,
    po.ItemID,
    po.Quantity,
    po.UnitPrice,
    po.TotalAmount
FROM purchase_orders po;

-- ==============================================
-- MIGRATE SALES ORDERS
-- ==============================================

-- Create main sales orders from existing data
INSERT INTO sales_orders_main (OrderID, CustomerID, EmployeeID, OrderDate, Status, TotalAmount)
SELECT DISTINCT 
    o.OrderID,
    o.CustomerID,
    o.EmployeeID,
    o.OrderDate,
    o.Status,
    SUM(o.TotalAmount) as TotalAmount
FROM orders o
GROUP BY o.OrderID, o.CustomerID, o.EmployeeID, o.OrderDate, o.Status;

-- Create sales order items
INSERT INTO sales_order_items (OrderID, ItemID, Quantity, UnitPrice, TotalAmount)
SELECT 
    o.OrderID,
    o.ItemID,
    o.Quantity,
    o.UnitPrice,
    o.TotalAmount
FROM orders o;

COMMIT;
