<?php

/**
 * Employees Backend API
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

// Get all employees
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['id'])) {
    try {
        $sql = "SELECT * FROM employees ORDER BY EmployeeName";
        $employeesData = $db->fetchAll($sql);

        $employees = array();
        foreach ($employeesData as $row) {
            $employees[] = array(
                'id' => 'EMP-' . $row['EmployeeID'],
                'employeeId' => $row['EmployeeID'],
                'name' => $row['EmployeeName'],
                'position' => $row['Position'],
                'baseSalary' => $row['BaseSalary'],
                'expectedSalary' => number_format($row['ExpectedSalary'], 2),
                'phone' => $row['Phone'],
                'email' => $row['Email'],
                'guarantor' => $row['Guarantor'],
                'address' => $row['Address'],
                'dateAdded' => $row['CreatedDate'],
                'status' => 'Active' // Assuming all are active for this example
            );
        }
        Utils::sendSuccessResponse('Employees retrieved successfully', $employees);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve employees: ' . $e->getMessage());
    }
}

// Get single employee
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $id = str_replace('EMP-', '', $_GET['id']);
        $sql = "SELECT * FROM employees WHERE EmployeeID = ?";
        $row = $db->fetchOne($sql, [$id]);

        if ($row) {
            $employee = array(
                'id' => 'EMP-' . $row['EmployeeID'],
                'name' => $row['EmployeeName'],
                'position' => $row['Position'],
                'baseSalary' => $row['BaseSalary'],
                'expectedSalary' => number_format($row['ExpectedSalary'], 2),
                'phone' => $row['Phone'],
                'email' => $row['Email'],
                'guarantor' => $row['Guarantor'],
                'address' => $row['Address'],
                'dateAdded' => $row['CreatedDate'],
                'status' => 'Active'
            );
            Utils::sendSuccessResponse('Employee retrieved successfully', $employee);
        } else {
            Utils::sendErrorResponse('Employee not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve employee: ' . $e->getMessage());
    }
}

// Add new employee
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        $name = $data['name'] ?? '';
        $position = $data['position'] ?? '';
        $baseSalary = $data['baseSalary'] ?? 0;
        $expectedSalary = $data['expectedSalary'] ?? 0;
        $phone = $data['phone'] ?? '';
        $email = $data['email'] ?? '';
        $guarantor = $data['guarantor'] ?? '';
        $address = $data['address'] ?? '';

        if (empty($name) || empty($position)) {
            Utils::sendErrorResponse('Name and position are required');
            return;
        }

        $sql = "INSERT INTO employees (EmployeeName, Position, BaseSalary, ExpectedSalary, Phone, Email, Guarantor, Address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $db->query($sql, [$name, $position, $baseSalary, $expectedSalary, $phone, $email, $guarantor, $address]);
        Utils::sendSuccessResponse('Employee added successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to add employee: ' . $e->getMessage());
    }
}

// Update employee
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        $id = isset($data['employeeId']) ? str_replace('EMP-', '', $data['employeeId']) : null;
        $name = $data['name'] ?? '';
        $position = $data['position'] ?? '';
        $baseSalary = $data['baseSalary'] ?? 0;
        $expectedSalary = $data['expectedSalary'] ?? 0;
        $phone = $data['phone'] ?? '';
        $email = $data['email'] ?? '';
        $guarantor = $data['guarantor'] ?? '';
        $address = $data['address'] ?? '';

        if (empty($id) || empty($name) || empty($position)) {
            Utils::sendErrorResponse('Employee ID, name and position are required');
            return;
        }

        $sql = "UPDATE employees SET EmployeeName=?, Position=?, BaseSalary=?, ExpectedSalary=?, Phone=?, Email=?, Guarantor=?, Address=? WHERE EmployeeID=?";
        $db->query($sql, [$name, $position, $baseSalary, $expectedSalary, $phone, $email, $guarantor, $address, $id]);
        Utils::sendSuccessResponse('Employee updated successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update employee: ' . $e->getMessage());
    }
}

// Delete employee
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? str_replace('EMP-', '', $data['id']) : null;

        if (empty($id)) {
            Utils::sendErrorResponse('Employee ID is required');
            return;
        }

        $sql = "DELETE FROM employees WHERE EmployeeID = ?";
        $db->query($sql, [$id]);
        Utils::sendSuccessResponse('Employee deleted successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete employee: ' . $e->getMessage());
    }
}
