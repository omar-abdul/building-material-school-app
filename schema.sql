/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.13-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: bmmss
-- ------------------------------------------------------
-- Server version	10.11.13-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `CategoryID` int(11) NOT NULL AUTO_INCREMENT,
  `CategoryName` varchar(100) NOT NULL,
  `Description` varchar(500) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`CategoryID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES
(8,'Uncategorized','Default category for uncategorized items','2025-08-01 14:54:26');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_balances`
--

DROP TABLE IF EXISTS `customer_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_balances`
--

LOCK TABLES `customer_balances` WRITE;
/*!40000 ALTER TABLE `customer_balances` DISABLE KEYS */;
INSERT INTO `customer_balances` VALUES
(1,20.00,30.00,10.00,'2025-08-01 00:00:00','2025-08-01 12:03:12');
/*!40000 ALTER TABLE `customer_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `CustomerID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerName` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`CustomerID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES
(1,'Test customer','064444444','email@email.com','ewew','2025-07-22 14:07:21');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES
(1,'TEST EMP','Manager',100.00,101,'me','0644444','email@email.com','ddas','2025-07-22 14:04:36');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `financial_transactions`
--

DROP TABLE IF EXISTS `financial_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `financial_transactions`
--

LOCK TABLES `financial_transactions` WRITE;
/*!40000 ALTER TABLE `financial_transactions` DISABLE KEYS */;
INSERT INTO `financial_transactions` VALUES
(2,'SALES_ORDER','1','order',30.00,NULL,'Completed','2025-07-22 11:16:41',NULL,1,NULL,NULL,0.00,30.00,'Sales Order #1',NULL,1,'2025-07-22 11:16:41','2025-07-22 11:16:41'),
(3,'INVENTORY_SALE','1','order',-15.00,NULL,'Completed','2025-07-22 14:22:59',NULL,NULL,NULL,NULL,0.00,0.00,'COGS for Order #1 - Item #1',NULL,1,'2025-07-22 11:22:59','2025-07-22 11:22:59'),
(4,'PURCHASE_ORDER','1','purchase',300.00,NULL,'Completed','2025-07-22 14:24:02',NULL,NULL,1,NULL,0.00,0.00,'Purchase Order #1',NULL,1,'2025-07-22 11:24:02','2025-07-22 11:24:02'),
(5,'SALES_PAYMENT','PAY-1754049792483','payment',-10.00,'Cash','Completed','2025-08-01 00:00:00',NULL,1,NULL,NULL,30.00,20.00,'weq','qwe',1,'2025-08-01 12:03:12','2025-08-01 12:03:12'),
(6,'PURCHASE_PAYMENT','PAY-1754049809569','payment',150.00,'Wallet','Completed','2025-08-01 00:00:00',NULL,NULL,1,NULL,300.00,450.00,'wew','we',1,'2025-08-01 12:03:29','2025-08-01 13:15:43'),
(7,'SALARY_PAYMENT','2','salary',-100.00,'Wallet','Completed','2025-08-01 12:06:54',NULL,NULL,NULL,1,0.00,-100.00,'Salary Payment #2',NULL,1,'2025-08-01 12:06:54','2025-08-01 13:15:43');
/*!40000 ALTER TABLE `financial_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory` (
  `InventoryID` int(11) NOT NULL AUTO_INCREMENT,
  `ItemID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 0,
  `Cost` decimal(10,2) DEFAULT 0.00,
  `LastUpdated` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`InventoryID`),
  KEY `ItemID` (`ItemID`),
  CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`ItemID`) REFERENCES `items` (`ItemID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
INSERT INTO `inventory` VALUES
(1,1,19,15.00,'2025-07-22 14:15:46');
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES
(1,'test item',4.00,8,NULL,1,'','fer','2025-07-22 00:00:00');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES
(1,1,1,1,1,1,30.00,'2025-07-22 00:00:00',30.00,'Delivered');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salaries`
--

DROP TABLE IF EXISTS `salaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salaries`
--

LOCK TABLES `salaries` WRITE;
/*!40000 ALTER TABLE `salaries` DISABLE KEYS */;
INSERT INTO `salaries` VALUES
(1,1,100.00,0.00,100.00,'Cash','2025-07-22','Paid','2025-07-22 12:22:42','2025-07-22 12:23:09'),
(2,1,100.00,0.00,100.00,'Wallet','2025-08-01','Paid','2025-08-01 12:06:54','2025-08-01 13:15:43');
/*!40000 ALTER TABLE `salaries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_balances`
--

DROP TABLE IF EXISTS `supplier_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_balances`
--

LOCK TABLES `supplier_balances` WRITE;
/*!40000 ALTER TABLE `supplier_balances` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `SupplierID` int(11) NOT NULL AUTO_INCREMENT,
  `SupplierName` varchar(100) NOT NULL,
  `ContactPerson` varchar(100) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` varchar(200) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`SupplierID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES
(2,'NEXT ','new customer','044','email@email.com','eewr','2025-08-01 15:27:24');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(6,'user','$2y$10$tDH9Mu5aaSVBLh6W/g.zbuX5xTLpcGTsJCkPp2Wbd6Kkv9yWKB7oi','user','2025-07-18 10:25:21'),
(7,'admin','$2y$12$0Hv8nyGZvPUMQnhOB/N1Le73o7evzUlP1Kcjs6b5aOqFQsm0Y9746','admin','2025-08-01 11:55:08');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-01 16:19:43
