<?php

/**
 * Salaries Backend API
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
        case 'getSalaries':
            getSalaries();
            break;
        case 'getSalary':
            getSalary();
            break;
        case 'getEmployeeDetails':
            getEmployeeDetails();
            break;
        case 'addSalary':
            addSalary();
            break;
        case 'updateSalary':
            updateSalary();
            break;
        case 'deleteSalary':
            deleteSalary();
            break;
        default:
            Utils::sendErrorResponse('Invalid action');
            break;
    }
} catch (Exception $e) {
    Utils::sendErrorResponse($e->getMessage());
}

function getSalaries()
{
    global $db;

    $search = $_GET['search'] ?? '';
    $employeeFilter = $_GET['employeeFilter'] ?? '';

    $query = "SELECT s.SalaryID, s.EmployeeID, e.EmployeeName, 
                     s.Amount, s.AdvanceSalary, s.NetSalary,
                     s.PaymentMethod, s.PaymentDate
              FROM salaries s
              JOIN Employees e ON s.EmployeeID = e.EmployeeID
              WHERE 1=1";

    $params = [];

    if (!empty($search)) {
        $query .= " AND (s.SalaryID LIKE ? OR e.EmployeeName LIKE ? OR s.PaymentMethod LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    }

    if (!empty($employeeFilter)) {
        $query .= " AND s.EmployeeID = ?";
        $params[] = $employeeFilter;
    }

    $query .= " ORDER BY s.PaymentDate DESC";

    try {
        $salaries = $db->fetchAll($query, $params);
        Utils::sendSuccessResponse('Salaries retrieved successfully', $salaries);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve salaries: ' . $e->getMessage());
    }
}

function getSalary()
{
    global $db;

    $salaryId = $_GET['salaryId'] ?? '';

    if (empty($salaryId)) {
        Utils::sendErrorResponse('Salary ID is required');
        return;
    }

    $query = "SELECT s.*, e.EmployeeName 
              FROM salaries s
              JOIN Employees e ON s.EmployeeID = e.EmployeeID
              WHERE s.SalaryID = ?";

    try {
        $salary = $db->fetchOne($query, [$salaryId]);

        if ($salary) {
            Utils::sendSuccessResponse('Salary retrieved successfully', $salary);
        } else {
            Utils::sendErrorResponse('Salary not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve salary: ' . $e->getMessage());
    }
}

function getEmployeeDetails()
{
    global $db;

    $employeeId = $_GET['employeeId'] ?? '';

    if (empty($employeeId)) {
        Utils::sendErrorResponse('Employee ID is required');
        return;
    }

    // This would come from your Employees table which should have salary info
    $query = "SELECT EmployeeName, BaseSalary FROM employees WHERE EmployeeID = ?";

    try {
        $employee = $db->fetchOne($query, [$employeeId]);

        if ($employee) {
            Utils::sendSuccessResponse('Employee details retrieved successfully', [
                'employeeName' => $employee['EmployeeName'],
                'baseSalary' => $employee['BaseSalary']
            ]);
        } else {
            Utils::sendErrorResponse('Employee not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve employee details: ' . $e->getMessage());
    }
}

function addSalary()
{
    global $db;

    $data = $_POST;

    // Validate required fields
    $required = ['employeeId', 'amount', 'paymentMethod', 'paymentDate'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            Utils::sendErrorResponse("$field is required");
            return;
        }
    }

    // Calculate net salary
    $advance = isset($data['advanceSalary']) ? floatval($data['advanceSalary']) : 0;
    $amount = floatval($data['amount']);
    $netSalary = $amount - $advance;

    // Prepare the query
    $query = "INSERT INTO alaries (EmployeeID, Amount, AdvanceSalary, NetSalary, 
                                   PaymentMethod, PaymentDate)
              VALUES (?, ?, ?, ?, ?, ?)";

    try {
        $db->query($query, [
            $data['employeeId'],
            $amount,
            $advance,
            $netSalary,
            $data['paymentMethod'],
            $data['paymentDate']
        ]);

        $salaryId = $db->lastInsertId();
        Utils::sendSuccessResponse('Salary added successfully', ['salaryId' => $salaryId]);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to add salary: ' . $e->getMessage());
    }
}

function updateSalary()
{
    global $db;

    $data = $_POST;

    // Validate required fields
    if (empty($data['salaryId'])) {
        Utils::sendErrorResponse('Salary ID is required');
        return;
    }

    $required = ['employeeId', 'amount', 'paymentMethod', 'paymentDate'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            Utils::sendErrorResponse("$field is required");
            return;
        }
    }

    // Calculate net salary
    $advance = isset($data['advanceSalary']) ? floatval($data['advanceSalary']) : 0;
    $amount = floatval($data['amount']);
    $netSalary = $amount - $advance;

    // Prepare the query
    $query = "UPDATE alaries 
              SET EmployeeID = ?, Amount = ?, AdvanceSalary = ?, NetSalary = ?,
                  PaymentMethod = ?, PaymentDate = ?
              WHERE SalaryID = ?";

    try {
        $db->query($query, [
            $data['employeeId'],
            $amount,
            $advance,
            $netSalary,
            $data['paymentMethod'],
            $data['paymentDate'],
            $data['salaryId']
        ]);

        Utils::sendSuccessResponse('Salary updated successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update salary: ' . $e->getMessage());
    }
}

function deleteSalary()
{
    global $db;

    $salaryId = $_GET['salaryId'] ?? '';

    if (empty($salaryId)) {
        Utils::sendErrorResponse('Salary ID is required');
        return;
    }

    $query = "DELETE FROM salaries WHERE SalaryID = ?";

    try {
        $db->query($query, [$salaryId]);
        Utils::sendSuccessResponse('Salary deleted successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete salary: ' . $e->getMessage());
    }
}
