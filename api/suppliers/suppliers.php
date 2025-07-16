<?php

/**
 * Suppliers REST API
 * Uses centralized database and utilities
 * 
 * GET    /api/suppliers/suppliers.php - Get all suppliers
 * GET    /api/suppliers/suppliers.php?id=X - Get specific supplier
 * POST   /api/suppliers/suppliers.php - Create new supplier
 * PUT    /api/suppliers/suppliers.php - Update supplier
 * DELETE /api/suppliers/suppliers.php - Delete supplier
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/utils.php';

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

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet();
            break;
        case 'POST':
            handlePost();
            break;
        case 'PUT':
            handlePut();
            break;
        case 'DELETE':
            handleDelete();
            break;
        default:
            Utils::sendErrorResponse('Method not allowed', 405);
            break;
    }
} catch (Exception $e) {
    Utils::sendErrorResponse($e->getMessage());
}

function handleGet()
{
    $supplierId = $_GET['id'] ?? '';

    if (!empty($supplierId)) {
        getSupplier($supplierId);
    } else {
        getSuppliers();
    }
}

function handlePost()
{
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        Utils::sendErrorResponse('Invalid JSON input');
        return;
    }

    $name = $input['name'] ?? '';
    $contactPerson = $input['contactPerson'] ?? '';
    $phone = $input['phone'] ?? '';
    $email = $input['email'] ?? '';
    $address = $input['address'] ?? '';

    if (empty($name) || empty($contactPerson) || empty($phone)) {
        Utils::sendErrorResponse('Required fields are missing');
        return;
    }

    createSupplier($name, $contactPerson, $phone, $email, $address);
}

function handlePut()
{
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        Utils::sendErrorResponse('Invalid JSON input');
        return;
    }

    $id = $input['id'] ?? 0;
    $name = $input['name'] ?? '';
    $contactPerson = $input['contactPerson'] ?? '';
    $phone = $input['phone'] ?? '';
    $email = $input['email'] ?? '';
    $address = $input['address'] ?? '';

    if (empty($id) || empty($name) || empty($contactPerson) || empty($phone)) {
        Utils::sendErrorResponse('Required fields are missing');
        return;
    }

    updateSupplier($id, $name, $contactPerson, $phone, $email, $address);
}

function handleDelete()
{
    $supplierId = $_GET['id'] ?? '';

    if (empty($supplierId)) {
        Utils::sendErrorResponse('Supplier ID is required');
        return;
    }

    deleteSupplier($supplierId);
}

function getSuppliers()
{
    global $db;

    $search = $_GET['search'] ?? '';

    $sql = "SELECT 
                SupplierID as id,
                CONCAT('SUP-', SupplierID) as supplierId,
                SupplierName as name,
                ContactPerson as contactPerson,
                Phone as phone,
                Email as email,
                Address as address,
                DATE_FORMAT(CreatedDate, '%Y-%m-%d') as dateAdded
            FROM suppliers";

    $params = [];

    if (!empty($search)) {
        $sql .= " WHERE SupplierName LIKE ? OR ContactPerson LIKE ? OR Phone LIKE ?";
        $searchTerm = "%$search%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
    }

    $sql .= " ORDER BY CreatedDate DESC";

    try {
        $suppliers = $db->fetchAll($sql, $params);
        Utils::sendSuccessResponse('Suppliers retrieved successfully', $suppliers);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve suppliers: ' . $e->getMessage());
    }
}

function getSupplier($supplierId)
{
    global $db;

    if (empty($supplierId)) {
        Utils::sendErrorResponse('Supplier ID is required');
        return;
    }

    $sql = "SELECT 
                SupplierID as id,
                CONCAT('SUP-', SupplierID) as supplierId,
                SupplierName as name,
                ContactPerson as contactPerson,
                Phone as phone,
                Email as email,
                Address as address,
                DATE_FORMAT(CreatedDate, '%Y-%m-%d') as dateAdded
            FROM suppliers 
            WHERE SupplierID = ?";

    try {
        $supplier = $db->fetchOne($sql, [$supplierId]);

        if ($supplier) {
            Utils::sendSuccessResponse('Supplier retrieved successfully', $supplier);
        } else {
            Utils::sendErrorResponse('Supplier not found', 404);
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve supplier: ' . $e->getMessage());
    }
}

function createSupplier($name, $contactPerson, $phone, $email, $address)
{
    global $db;

    $sql = "INSERT INTO suppliers 
            (SupplierName, ContactPerson, Phone, Email, Address)
            VALUES (?, ?, ?, ?, ?)";

    try {
        $db->query($sql, [$name, $contactPerson, $phone, $email, $address]);
        $newId = $db->lastInsertId();

        Utils::sendSuccessResponse('Supplier created successfully', [
            'id' => $newId,
            'supplierId' => 'SUP-' . $newId,
            'name' => $name,
            'contactPerson' => $contactPerson,
            'phone' => $phone,
            'email' => $email,
            'address' => $address
        ]);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to create supplier: ' . $e->getMessage());
    }
}

function updateSupplier($id, $name, $contactPerson, $phone, $email, $address)
{
    global $db;

    $sql = "UPDATE suppliers SET
            SupplierName = ?,
            ContactPerson = ?,
            Phone = ?,
            Email = ?,
            Address = ?
            WHERE SupplierID = ?";

    try {
        $result = $db->query($sql, [$name, $contactPerson, $phone, $email, $address, $id]);

        if ($result->rowCount() === 0) {
            Utils::sendErrorResponse('Supplier not found', 404);
            return;
        }

        Utils::sendSuccessResponse('Supplier updated successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update supplier: ' . $e->getMessage());
    }
}

function deleteSupplier($supplierId)
{
    global $db;

    if (empty($supplierId)) {
        Utils::sendErrorResponse('Supplier ID is required');
        return;
    }

    $sql = "DELETE FROM suppliers WHERE SupplierID = ?";

    try {
        $result = $db->query($sql, [$supplierId]);

        if ($result->rowCount() === 0) {
            Utils::sendErrorResponse('Supplier not found', 404);
            return;
        }

        Utils::sendSuccessResponse('Supplier deleted successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete supplier: ' . $e->getMessage());
    }
}
