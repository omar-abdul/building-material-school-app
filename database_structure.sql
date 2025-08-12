-- Database Structure for Building Material Management System (BMMS)
-- This file contains only table structures, no initial data

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Table structure for table `categories`
CREATE TABLE `categories` (
  `CategoryID` int(11) NOT NULL AUTO_INCREMENT,
  `CategoryName` varchar(100) NOT NULL,
  `Description` varchar(500) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`CategoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `customers`
CREATE TABLE `customers` (
  `CustomerID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerName` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`CustomerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `employees`
CREATE TABLE `employees` (
  `EmployeeID` int(11) NOT NULL AUTO_INCREMENT,
  `EmployeeName` varchar(100) NOT NULL,
  `Position` varchar(50) DEFAULT NULL,
  `BaseSalary` decimal(10,2) DEFAULT NULL,
  `ExpectedSalary` decimal(10,0) NOT NULL,
  `Guarantor` varchar(100) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`EmployeeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `suppliers`
CREATE TABLE `suppliers` (
  `SupplierID` int(11) NOT NULL AUTO_INCREMENT,
  `SupplierName` varchar(100) NOT NULL,
  `ContactPerson` varchar(100) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`SupplierID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `items`
CREATE TABLE `items` (
  `ItemID` int(11) NOT NULL AUTO_INCREMENT,
  `ItemName` varchar(100) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `CategoryID` int(11) DEFAULT NULL,
  `SupplierID` int(11) DEFAULT NULL,
  `RegisteredByEmployeeID` int(11) DEFAULT NULL,
  `Note` varchar(500) DEFAULT NULL,
  `Description` varchar(500) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`ItemID`),
  KEY `SupplierID` (`SupplierID`),
  KEY `RegisteredByEmployeeID` (`RegisteredByEmployeeID`),
  KEY `fk_cat_item_id` (`CategoryID`),
  CONSTRAINT `fk_cat_item_id` FOREIGN KEY (`CategoryID`) REFERENCES `categories` (`CategoryID`) ON DELETE SET NULL,
  CONSTRAINT `items_ibfk_1` FOREIGN KEY (`CategoryID`) REFERENCES `categories` (`CategoryID`),
  CONSTRAINT `items_ibfk_2` FOREIGN KEY (`SupplierID`) REFERENCES `suppliers` (`SupplierID`) ON DELETE SET NULL,
  CONSTRAINT `items_ibfk_3` FOREIGN KEY (`RegisteredByEmployeeID`) REFERENCES `employees` (`EmployeeID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `inventory`
CREATE TABLE `inventory` (
  `InventoryID` int(11) NOT NULL AUTO_INCREMENT,
  `ItemID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 0,
  `Cost` decimal(10,2) DEFAULT 0.00,
  `LastUpdated` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`InventoryID`),
  KEY `ItemID` (`ItemID`),
  CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`ItemID`) REFERENCES `items` (`ItemID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `orders`
CREATE TABLE `orders` (
  `OrderEntryID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `EmployeeID` int(11) DEFAULT NULL,
  `ItemID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1,
  `UnitPrice` decimal(10,2) NOT NULL,
  `OrderDate` datetime DEFAULT current_timestamp(),
  `TotalAmount` decimal(10,2) DEFAULT 0.00,
  `Status` varchar(20) DEFAULT 'Pending',
  PRIMARY KEY (`OrderEntryID`),
  KEY `CustomerID` (`CustomerID`),
  KEY `EmployeeID` (`EmployeeID`),
  KEY `ItemID` (`ItemID`),
  KEY `OrderID` (`OrderID`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`EmployeeID`) REFERENCES `employees` (`EmployeeID`),
  CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`ItemID`) REFERENCES `items` (`ItemID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `purchase_orders`
CREATE TABLE `purchase_orders` (
  `PurchaseOrderID` int(11) NOT NULL AUTO_INCREMENT,
  `SupplierID` int(11) NOT NULL,
  `EmployeeID` int(11) DEFAULT NULL,
  `ItemID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `UnitPrice` decimal(10,2) NOT NULL,
  `OrderDate` datetime NOT NULL,
  `TotalAmount` decimal(10,2) NOT NULL,
  `Status` enum('Pending','Processing','Received','Cancelled') NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (`PurchaseOrderID`),
  KEY `SupplierID` (`SupplierID`),
  KEY `EmployeeID` (`EmployeeID`),
  KEY `ItemID` (`ItemID`),
  CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`SupplierID`) REFERENCES `suppliers` (`SupplierID`) ON DELETE CASCADE,
  CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`EmployeeID`) REFERENCES `employees` (`EmployeeID`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_ibfk_3` FOREIGN KEY (`ItemID`) REFERENCES `items` (`ItemID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `salaries`
CREATE TABLE `salaries` (
  `SalaryID` int(11) NOT NULL AUTO_INCREMENT,
  `EmployeeID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `AdvanceSalary` decimal(10,2) DEFAULT 0.00,
  `NetSalary` decimal(10,2) NOT NULL,
  `PaymentMethod` varchar(50) NOT NULL,
  `PaymentDate` date NOT NULL,
  `Status` enum('Paid','Pending','Cancelled') DEFAULT 'Paid',
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`SalaryID`),
  KEY `idx_employee_id` (`EmployeeID`),
  KEY `idx_payment_date` (`PaymentDate`),
  KEY `idx_status` (`Status`),
  KEY `idx_employee_payment_date` (`EmployeeID`,`PaymentDate`),
  CONSTRAINT `salaries_ibfk_1` FOREIGN KEY (`EmployeeID`) REFERENCES `employees` (`EmployeeID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `financial_transactions`
CREATE TABLE `financial_transactions` (
  `TransactionID` int(11) NOT NULL AUTO_INCREMENT,
  `TransactionType` enum('SALES_ORDER','SALES_PAYMENT','SALES_REFUND','PURCHASE_ORDER','PURCHASE_PAYMENT','PURCHASE_REFUND','SALARY_PAYMENT','DIRECT_EXPENSE','DIRECT_INCOME','INVENTORY_PURCHASE','INVENTORY_SALE','INVENTORY_ADJUSTMENT') NOT NULL,
  `ReferenceID` varchar(50) NOT NULL,
  `ReferenceType` varchar(20) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `PaymentMethod` enum('Cash','Wallet') DEFAULT NULL,
  `Status` enum('Pending','Completed','Cancelled','Failed') NOT NULL,
  `TransactionDate` datetime NOT NULL,
  `DueDate` date DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `SupplierID` int(11) DEFAULT NULL,
  `EmployeeID` int(11) DEFAULT NULL,
  `PreviousBalance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `NewBalance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Description` text DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `CreatedBy` int(11) NOT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`TransactionID`),
  KEY `idx_type_date` (`TransactionType`,`TransactionDate`),
  KEY `idx_reference` (`ReferenceID`,`ReferenceType`),
  KEY `idx_customer` (`CustomerID`),
  KEY `idx_supplier` (`SupplierID`),
  KEY `idx_status` (`Status`),
  KEY `idx_date` (`TransactionDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `customer_balances`
CREATE TABLE `customer_balances` (
  `CustomerID` int(11) NOT NULL,
  `CurrentBalance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `TotalPurchases` decimal(10,2) NOT NULL DEFAULT 0.00,
  `TotalPayments` decimal(10,2) NOT NULL DEFAULT 0.00,
  `LastTransactionDate` datetime DEFAULT NULL,
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`CustomerID`),
  CONSTRAINT `customer_balances_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `supplier_balances`
CREATE TABLE `supplier_balances` (
  `SupplierID` int(11) NOT NULL,
  `CurrentBalance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `TotalPurchases` decimal(10,2) NOT NULL DEFAULT 0.00,
  `TotalPayments` decimal(10,2) NOT NULL DEFAULT 0.00,
  `LastTransactionDate` datetime DEFAULT NULL,
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`SupplierID`),
  CONSTRAINT `supplier_balances_ibfk_1` FOREIGN KEY (`SupplierID`) REFERENCES `suppliers` (`SupplierID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `transactions`
CREATE TABLE `transactions` (
  `TransactionID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `PaymentMethod` varchar(50) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `Balance` decimal(10,2) DEFAULT 0.00,
  `TransactionDate` datetime DEFAULT current_timestamp(),
  `Status` varchar(20) DEFAULT 'Completed',
  PRIMARY KEY (`TransactionID`),
  KEY `OrderID` (`OrderID`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


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
