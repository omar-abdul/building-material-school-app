<?php
header('Content-Type: application/json');
require_once 'connection.php';

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_suppliers':
            getSuppliers();
            break;
        case 'add_supplier':
            addSupplier();
            break;
        case 'update_supplier':
            updateSupplier();
            break;
        case 'delete_supplier':
            deleteSupplier();
            break;
        case 'get_supplier':
            getSupplier();
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

function getSuppliers() {
    global $conn;
    
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
            FROM Suppliers";
    
    if (!empty($search)) {
        $sql .= " WHERE SupplierName LIKE :search OR ContactPerson LIKE :search OR Phone LIKE :search";
    }
    
    $sql .= " ORDER BY CreatedDate DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $stmt->bindParam(':search', $searchTerm);
    }
    
    $stmt->execute();
    $suppliers = $stmt->fetchAll();
    
    echo json_encode(['status' => 'success', 'data' => $suppliers]);
}

function getSupplier() {
    global $conn;
    
    $id = $_GET['id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT 
                                SupplierID as id,
                                CONCAT('SUP-', SupplierID) as supplierId,
                                SupplierName as name,
                                ContactPerson as contactPerson,
                                Phone as phone,
                                Email as email,
                                Address as address,
                                DATE_FORMAT(CreatedDate, '%Y-%m-%d') as dateAdded
                            FROM Suppliers 
                            WHERE SupplierID = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $supplier = $stmt->fetch();
    
    if ($supplier) {
        echo json_encode(['status' => 'success', 'data' => $supplier]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Supplier not found']);
    }
}

function addSupplier() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = $data['name'] ?? '';
    $contactPerson = $data['contactPerson'] ?? '';
    $phone = $data['phone'] ?? '';
    $email = $data['email'] ?? '';
    $address = $data['address'] ?? '';
    
    if (empty($name) || empty($contactPerson) || empty($phone)) {
        echo json_encode(['status' => 'error', 'message' => 'Required fields are missing']);
        return;
    }
    
    $stmt = $conn->prepare("INSERT INTO Suppliers 
                            (SupplierName, ContactPerson, Phone, Email, Address)
                            VALUES (:name, :contactPerson, :phone, :email, :address)");
    
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':contactPerson', $contactPerson);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':address', $address);
    
    if ($stmt->execute()) {
        $newId = $conn->lastInsertId();
        echo json_encode(['status' => 'success', 'id' => $newId]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add supplier']);
    }
}

function updateSupplier() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? 0;
    $name = $data['name'] ?? '';
    $contactPerson = $data['contactPerson'] ?? '';
    $phone = $data['phone'] ?? '';
    $email = $data['email'] ?? '';
    $address = $data['address'] ?? '';
    
    if (empty($id) || empty($name) || empty($contactPerson) || empty($phone)) {
        echo json_encode(['status' => 'error', 'message' => 'Required fields are missing']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE Suppliers SET
                            SupplierName = :name,
                            ContactPerson = :contactPerson,
                            Phone = :phone,
                            Email = :email,
                            Address = :address
                            WHERE SupplierID = :id");
    
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':contactPerson', $contactPerson);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':address', $address);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update supplier']);
    }
}

function deleteSupplier() {
    global $conn;
    
    $id = $_GET['id'] ?? 0;
    
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Supplier ID is missing']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM Suppliers WHERE SupplierID = :id");
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete supplier']);
    }
}
?>