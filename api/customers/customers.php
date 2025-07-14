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

// Handle different actions
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getCustomers':
            getCustomers();
            break;
        case 'addCustomer':
            addCustomer();
            break;
        case 'updateCustomer':
            updateCustomer();
            break;
        case 'deleteCustomer':
            deleteCustomer();
            break;
        case 'getCustomer':
            getCustomer();
            break;
        case 'getOrderHistory':
            getOrderHistory();
            break;
        case 'searchCustomers':
            searchCustomers();
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function getCustomers()
{
    global $db;

    $sql = "SELECT 
                CONCAT('CUST-', CustomerID) as CustomerID,
                CustomerName as Name,
                Phone,
                Email,
                Address,
                DATE_FORMAT(CreatedDate, '%Y-%m-%d') as DateAdded
            FROM customers
            ORDER BY CreatedDate DESC";

    try {
        $customers = $db->fetchAll($sql);
        Utils::sendSuccessResponse('Customers retrieved successfully', $customers);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve customers: ' . $e->getMessage());
    }
}

function addCustomer()
{
    global $db;

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

    try {
        $db->query($sql, [$name, $phone, $email, $address]);
        $newId = $db->lastInsertId();
        Utils::sendSuccessResponse('Customer added successfully', ['customerId' => 'CUST-' . $newId]);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to add customer: ' . $e->getMessage());
    }
}

function updateCustomer()
{
    global $db;

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

    try {
        $db->query($sql, [$name, $phone, $email, $address, $id]);
        Utils::sendSuccessResponse('Customer updated successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update customer: ' . $e->getMessage());
    }
}

function deleteCustomer()
{
    global $db;

    $id = str_replace('CUST-', '', $_GET['id'] ?? '');

    if (empty($id)) {
        Utils::sendErrorResponse('Customer ID is required');
        return;
    }

    $sql = "DELETE FROM customers WHERE CustomerID=?";

    try {
        $db->query($sql, [$id]);
        Utils::sendSuccessResponse('Customer deleted successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete customer: ' . $e->getMessage());
    }
}

function getCustomer()
{
    global $db;

    $id = str_replace('CUST-', '', $_GET['id'] ?? '');

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

    try {
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

// function getOrderHistory() {
//     global $conn;

//     $customerId = str_replace('CUST-', '', $_GET['id'] ?? '');

//     if (empty($customerId)) {
//         throw new Exception('Customer ID is required');
//     }

//     // This is a simplified example - you would need to join with your Orders table
//     $sql = "SELECT 
//                 CONCAT('ORD-', OrderID) as OrderID,
//                 DATE_FORMAT(OrderDate, '%Y-%m-%d') as OrderDate,
//                 (SELECT COUNT(*) FROM OrderItems WHERE OrderID = Orders.OrderID) as ItemsCount,
//                 TotalAmount as Total,
//                 Status
//             FROM Orders
//             WHERE CustomerID = ?
//             ORDER BY OrderDate DESC";

//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("i", $customerId);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     $orders = [];
//     if ($result->num_rows > 0) {
//         while($row = $result->fetch_assoc()) {
//             $orders[] = $row;
//         }
//     }

//     echo json_encode($orders);
// }

function searchCustomers()
{
    global $db;

    $searchTerm = $_GET['term'] ?? '';

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

    try {
        $customers = $db->fetchAll($sql, [$searchParam, $searchParam, $searchParam]);
        Utils::sendSuccessResponse('Customers search completed', $customers);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to search customers: ' . $e->getMessage());
    }
}

function getOrderHistory()
{
    global $db;

    $customerId = str_replace('CUST-', '', $_GET['id'] ?? '');

    if (empty($customerId)) {
        Utils::sendErrorResponse('Customer ID is required');
        return;
    }

    // Updated query to match your database schema
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

    try {
        $orders = $db->fetchAll($sql, [$customerId]);
        Utils::sendSuccessResponse('Order history retrieved successfully', $orders);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve order history: ' . $e->getMessage());
    }
}
