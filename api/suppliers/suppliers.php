<?php

/**
 * Suppliers Backend API
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

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getSuppliers':
            getSuppliers();
            break;
        case 'addSupplier':
            addSupplier();
            break;
        case 'updateSupplier':
            updateSupplier();
            break;
        case 'deleteSupplier':
            deleteSupplier();
            break;
        case 'getSupplier':
            getSupplier();
            break;
        default:
            Utils::sendErrorResponse('Invalid action');
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
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

function getSupplier()
{
    global $db;

    $id = $_GET['id'] ?? 0;

    if (empty($id)) {
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
        $supplier = $db->fetchOne($sql, [$id]);

        if ($supplier) {
            Utils::sendSuccessResponse('Supplier retrieved successfully', $supplier);
        } else {
            Utils::sendErrorResponse('Supplier not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve supplier: ' . $e->getMessage());
    }
}

function addSupplier()
{
    global $db;

    $data = json_decode(file_get_contents('php://input'), true);

    $name = $data['name'] ?? '';
    $contactPerson = $data['contactPerson'] ?? '';
    $phone = $data['phone'] ?? '';
    $email = $data['email'] ?? '';
    $address = $data['address'] ?? '';

    if (empty($name) || empty($contactPerson) || empty($phone)) {
        Utils::sendErrorResponse('Required fields are missing');
        return;
    }

    $sql = "INSERT INTO uppliers 
            (SupplierName, ContactPerson, Phone, Email, Address)
            VALUES (?, ?, ?, ?, ?)";

    try {
        $db->query($sql, [$name, $contactPerson, $phone, $email, $address]);
        $newId = $db->lastInsertId();
        Utils::sendSuccessResponse('Supplier added successfully', ['id' => $newId]);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to add supplier: ' . $e->getMessage());
    }
}

function updateSupplier()
{
    global $db;

    $data = json_decode(file_get_contents('php://input'), true);

    $id = $data['id'] ?? 0;
    $name = $data['name'] ?? '';
    $contactPerson = $data['contactPerson'] ?? '';
    $phone = $data['phone'] ?? '';
    $email = $data['email'] ?? '';
    $address = $data['address'] ?? '';

    if (empty($id) || empty($name) || empty($contactPerson) || empty($phone)) {
        Utils::sendErrorResponse('Required fields are missing');
        return;
    }

    $sql = "UPDATE uppliers SET
            SupplierName = ?,
            ContactPerson = ?,
            Phone = ?,
            Email = ?,
            Address = ?
            WHERE SupplierID = ?";

    try {
        $db->query($sql, [$name, $contactPerson, $phone, $email, $address, $id]);
        Utils::sendSuccessResponse('Supplier updated successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update supplier: ' . $e->getMessage());
    }
}

function deleteSupplier()
{
    global $db;

    $id = $_GET['id'] ?? 0;

    if (empty($id)) {
        Utils::sendErrorResponse('Supplier ID is missing');
        return;
    }

    $sql = "DELETE FROM suppliers WHERE SupplierID = ?";

    try {
        $db->query($sql, [$id]);
        Utils::sendSuccessResponse('Supplier deleted successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete supplier: ' . $e->getMessage());
    }
}
