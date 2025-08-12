-- Fixed Database Structure for BMMS
-- This separates orders from order items for proper multi-item support

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ==============================================
-- PURCHASE ORDERS (Main Order Table)
-- ==============================================
CREATE TABLE IF NOT EXISTS `purchase_orders_main` (
  `PurchaseOrderID` int(11) NOT NULL AUTO_INCREMENT,
  `SupplierID` int(11) NOT NULL,
  `EmployeeID` int(11) DEFAULT NULL,
  `OrderDate` datetime NOT NULL,
  `Status` enum('Pending','Processing','Received','Cancelled') NOT NULL DEFAULT 'Pending',
  `TotalAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Notes` text DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`PurchaseOrderID`),
  KEY `SupplierID` (`SupplierID`),
  KEY `EmployeeID` (`EmployeeID`),
  KEY `Status` (`Status`),
  KEY `OrderDate` (`OrderDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==============================================
-- PURCHASE ORDER ITEMS (Order Items Table)
-- ==============================================
CREATE TABLE IF NOT EXISTS `purchase_order_items` (
  `ItemEntryID` int(11) NOT NULL AUTO_INCREMENT,
  `PurchaseOrderID` int(11) NOT NULL,
  `ItemID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `UnitPrice` decimal(10,2) NOT NULL,
  `TotalAmount` decimal(10,2) NOT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ItemEntryID`),
  KEY `PurchaseOrderID` (`PurchaseOrderID`),
  KEY `ItemID` (`ItemID`),
  CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`PurchaseOrderID`) REFERENCES `purchase_orders_main` (`PurchaseOrderID`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`ItemID`) REFERENCES `items` (`ItemID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==============================================
-- SALES ORDERS (Main Order Table)
-- ==============================================
CREATE TABLE IF NOT EXISTS `sales_orders_main` (
  `OrderID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) NOT NULL,
  `EmployeeID` int(11) DEFAULT NULL,
  `OrderDate` datetime NOT NULL,
  `Status` enum('Pending','Processing','Delivered','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `TotalAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Notes` text DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`OrderID`),
  KEY `CustomerID` (`CustomerID`),
  KEY `EmployeeID` (`EmployeeID`),
  KEY `Status` (`Status`),
  KEY `OrderDate` (`OrderDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==============================================
-- SALES ORDER ITEMS (Order Items Table)
-- ==============================================
CREATE TABLE IF NOT EXISTS `sales_order_items` (
  `ItemEntryID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `ItemID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `UnitPrice` decimal(10,2) NOT NULL,
  `TotalAmount` decimal(10,2) NOT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ItemEntryID`),
  KEY `OrderID` (`OrderID`),
  KEY `ItemID` (`ItemID`),
  CONSTRAINT `sales_order_items_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `sales_orders_main` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `sales_order_items_ibfk_2` FOREIGN KEY (`ItemID`) REFERENCES `items` (`ItemID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
