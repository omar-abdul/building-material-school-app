<?php

/**
 * Customers Backend API
 * Uses centralized database and utilities
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/utils.php';

// Set headers for API
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$db = Database::getInstance();

// Get all customers
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['id']) && !isset($_GET['term'])) {
    try {
        $sql = "SELECT 
                    CONCAT('CUST-', CustomerID) as CustomerID,
                    CustomerName as Name,
                    Phone,
                    Email,
                    Address,
                    DATE_FORMAT(CreatedDate, '%Y-%m-%d') as DateAdded
                FROM customers
                ORDER BY CreatedDate DESC";

        $customers = $db->fetchAll($sql);
        Utils::sendSuccessResponse('Customers retrieved successfully', $customers);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve customers: ' . $e->getMessage());
    }
}

// Get single customer
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && !isset($_GET['term'])) {
    try {
        $id = str_replace('CUST-', '', $_GET['id']);

        if (empty($id)) {
            Utils::sendErrorResponse('Customer ID is required');
            return;
        }

        $sql = "SELECT 
                    CONCAT('CUST-', CustomerID) as CustomerID,
                    CustomerName as Name,
                    Phone,
                    Email,
                    Address,
                    DATE_FORMAT(CreatedDate, '%Y-%m-%d') as DateAdded
                FROM customers
                WHERE CustomerID=?";

        $customer = $db->fetchOne($sql, [$id]);

        if ($customer) {
            Utils::sendSuccessResponse('Customer retrieved successfully', $customer);
        } else {
            Utils::sendErrorResponse('Customer not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve customer: ' . $e->getMessage());
    }
}

// Search customers
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['term'])) {
    try {
        $searchTerm = $_GET['term'];

        if (empty($searchTerm)) {
            Utils::sendErrorResponse('Search term is required');
            return;
        }

        $searchParam = '%' . $searchTerm . '%';

        $sql = "SELECT 
                    CONCAT('CUST-', CustomerID) as CustomerID,
                    CustomerName as Name,
                    Phone,
                    Email,
                    Address,
                    DATE_FORMAT(CreatedDate, '%Y-%m-%d') as DateAdded
                FROM customers
                WHERE CustomerName LIKE ? OR Phone LIKE ? OR Email LIKE ?
                ORDER BY CustomerName";

        $customers = $db->fetchAll($sql, [$searchParam, $searchParam, $searchParam]);
        Utils::sendSuccessResponse('Customers search completed', $customers);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to search customers: ' . $e->getMessage());
    }
}

// Get order history
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && isset($_GET['history'])) {
    try {
        $customerId = str_replace('CUST-', '', $_GET['id']);

        if (empty($customerId)) {
            Utils::sendErrorResponse('Customer ID is required');
            return;
        }

        $sql = "SELECT 
                    CONCAT('ORD-', o.OrderID) as OrderID,
                    DATE_FORMAT(o.OrderDate, '%Y-%m-%d') as OrderDate,
                    o.TotalAmount as Total,
                    o.Status,
                    COUNT(t.TransactionID) as TransactionsCount,
                    IFNULL(SUM(t.Amount), 0) as PaidAmount
                FROM orders o
                LEFT JOIN Transactions t ON o.OrderID = t.OrderID
                WHERE o.CustomerID = ?
                GROUP BY o.OrderID
                ORDER BY o.OrderDate DESC";

        $orders = $db->fetchAll($sql, [$customerId]);
        Utils::sendSuccessResponse('Order history retrieved successfully', $orders);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve order history: ' . $e->getMessage());
    }
}

// Add new customer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        $name = $data['name'] ?? '';
        $phone = $data['phone'] ?? '';
        $email = $data['email'] ?? '';
        $address = $data['address'] ?? '';

        if (empty($name) || empty($phone)) {
            Utils::sendErrorResponse('Name and phone are required');
            return;
        }

        $sql = "INSERT INTO customers (CustomerName, Phone, Email, Address) VALUES (?, ?, ?, ?)";
        $db->query($sql, [$name, $phone, $email, $address]);
        $newId = $db->lastInsertId();
        Utils::sendSuccessResponse('Customer added successfully', ['customerId' => 'CUST-' . $newId]);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to add customer: ' . $e->getMessage());
    }
}

// Update customer
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        $id = str_replace('CUST-', '', $data['id'] ?? '');
        $name = $data['name'] ?? '';
        $phone = $data['phone'] ?? '';
        $email = $data['email'] ?? '';
        $address = $data['address'] ?? '';

        if (empty($id) || empty($name) || empty($phone)) {
            Utils::sendErrorResponse('ID, name and phone are required');
            return;
        }

        $sql = "UPDATE customers SET CustomerName=?, Phone=?, Email=?, Address=? WHERE CustomerID=?";
        $db->query($sql, [$name, $phone, $email, $address, $id]);
        Utils::sendSuccessResponse('Customer updated successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update customer: ' . $e->getMessage());
    }
}

// Delete customer
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? str_replace('CUST-', '', $data['id']) : null;

        if (empty($id)) {
            Utils::sendErrorResponse('Customer ID is required');
            return;
        }

        $sql = "DELETE FROM customers WHERE CustomerID=?";
        $db->query($sql, [$id]);
        Utils::sendSuccessResponse('Customer deleted successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete customer: ' . $e->getMessage());
    }
}
