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

// ==============================================
// HELPER FUNCTIONS
// ==============================================

function getNextSalaryID()
{
    global $db;
    $result = $db->fetchOne("SELECT MAX(SalaryID) as maxId FROM salaries");
    $nextId = ($result['maxId'] ?? 0) + 1;
    return $nextId;
}

// ==============================================
// API ENDPOINTS
// ==============================================

// Get all salaries
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['salaryId']) && !isset($_GET['employeeId'])) {
    try {
        $statusFilter = $_GET['status'] ?? '';
        $searchTerm = $_GET['search'] ?? '';

        $query = "
            SELECT s.SalaryID, s.EmployeeID, e.EmployeeName,
                   s.Amount, s.AdvanceSalary, s.NetSalary,
                   s.PaymentMethod, s.PaymentDate, s.Status
            FROM salaries s
            LEFT JOIN employees e ON s.EmployeeID = e.EmployeeID
            WHERE 1=1
        ";

        $params = [];

        if ($statusFilter) {
            $query .= " AND s.Status = ?";
            $params[] = $statusFilter;
        }

        if ($searchTerm) {
            $query .= " AND (e.EmployeeName LIKE ? OR s.PaymentMethod LIKE ? OR s.SalaryID LIKE ?)";
            $params[] = "%$searchTerm%";
            $params[] = "%$searchTerm%";
            $params[] = "%$searchTerm%";
        }

        $query .= " ORDER BY s.PaymentDate DESC";

        $salaries = $db->fetchAll($query, $params);
        Utils::sendSuccessResponse('Salaries retrieved successfully', $salaries);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve salaries: ' . $e->getMessage());
    }
}

// Get single salary
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['salaryId'])) {
    try {
        $salaryId = $_GET['salaryId'];

        if (empty($salaryId)) {
            Utils::sendErrorResponse('Salary ID is required');
            return;
        }

        $salary = $db->fetchOne("
            SELECT s.*, e.EmployeeName, e.BaseSalary
            FROM salaries s
            LEFT JOIN employees e ON s.EmployeeID = e.EmployeeID
            WHERE s.SalaryID = ?
        ", [$salaryId]);

        if ($salary) {
            Utils::sendSuccessResponse('Salary retrieved successfully', $salary);
        } else {
            Utils::sendErrorResponse('Salary not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve salary: ' . $e->getMessage());
    }
}

// Get employee details (for autocomplete)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['employeeId'])) {
    try {
        $employeeId = $_GET['employeeId'];

        if (empty($employeeId)) {
            Utils::sendErrorResponse('Employee ID is required');
            return;
        }

        $employee = $db->fetchOne("
            SELECT EmployeeID, EmployeeName, BaseSalary 
            FROM employees 
            WHERE EmployeeID = ?
        ", [$employeeId]);

        if ($employee) {
            Utils::sendSuccessResponse('Employee details retrieved successfully', $employee);
        } else {
            Utils::sendErrorResponse('Employee not found');
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve employee details: ' . $e->getMessage());
    }
}

// Search employees (for autocomplete)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['searchEmployees'])) {
    try {
        $searchTerm = $_GET['searchEmployees'] ?? '';

        if (empty($searchTerm)) {
            Utils::sendSuccessResponse('Employees retrieved successfully', []);
            return;
        }

        $employees = $db->fetchAll("
            SELECT EmployeeID, EmployeeName, BaseSalary
            FROM employees 
            WHERE EmployeeName LIKE ? OR EmployeeID LIKE ?
            ORDER BY EmployeeName
            LIMIT 10
        ", ["%$searchTerm%", "%$searchTerm%"]);

        Utils::sendSuccessResponse('Employees retrieved successfully', $employees);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to search employees: ' . $e->getMessage());
    }
}

// Create salary
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }

        if (empty($data['employee_id']) || empty($data['amount']) || empty($data['payment_method']) || empty($data['payment_date'])) {
            Utils::sendErrorResponse('Employee ID, amount, payment method, and payment date are required');
            return;
        }

        // Clean up IDs - remove prefixes and convert to integers
        $employeeId = is_string($data['employee_id']) ? (int)str_replace('EMP-', '', $data['employee_id']) : (int)$data['employee_id'];

        $db->beginTransaction();

        try {
            // Calculate net salary
            $amount = floatval($data['amount']);
            $advanceSalary = isset($data['advance_salary']) ? floatval($data['advance_salary']) : 0;
            $netSalary = $amount - $advanceSalary;

            $salaryId = getNextSalaryID();

            // Insert salary
            $db->query("INSERT INTO salaries 
                (SalaryID, EmployeeID, Amount, AdvanceSalary, NetSalary, PaymentMethod, PaymentDate, Status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
                $salaryId,
                $employeeId,
                $amount,
                $advanceSalary,
                $netSalary,
                $data['payment_method'],
                $data['payment_date'],
                $data['status'] ?? 'Paid'
            ]);

            $db->commit();
            Utils::sendSuccessResponse('Salary created successfully', ['salary_id' => $salaryId]);
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to create salary: ' . $e->getMessage());
    }
}

// Update salary
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }

        if (empty($data['salary_id'])) {
            Utils::sendErrorResponse('Salary ID is required');
            return;
        }

        if (empty($data['employee_id']) || empty($data['amount']) || empty($data['payment_method']) || empty($data['payment_date'])) {
            Utils::sendErrorResponse('Employee ID, amount, payment method, and payment date are required');
            return;
        }

        // Clean up IDs - remove prefixes and convert to integers
        $employeeId = is_string($data['employee_id']) ? (int)str_replace('EMP-', '', $data['employee_id']) : (int)$data['employee_id'];
        $salaryId = $data['salary_id'];

        $db->beginTransaction();

        try {
            // Calculate net salary
            $amount = floatval($data['amount']);
            $advanceSalary = isset($data['advance_salary']) ? floatval($data['advance_salary']) : 0;
            $netSalary = $amount - $advanceSalary;

            // Update salary
            $db->query("UPDATE salaries SET 
                EmployeeID = ?, Amount = ?, AdvanceSalary = ?, NetSalary = ?, 
                PaymentMethod = ?, PaymentDate = ?, Status = ?
                WHERE SalaryID = ?", [
                $employeeId,
                $amount,
                $advanceSalary,
                $netSalary,
                $data['payment_method'],
                $data['payment_date'],
                $data['status'] ?? 'Paid',
                $salaryId
            ]);

            $db->commit();
            Utils::sendSuccessResponse('Salary updated successfully', ['salary_id' => $salaryId]);
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update salary: ' . $e->getMessage());
    }
}

// Delete salary
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $salaryId = isset($data['salaryId']) ? $data['salaryId'] : ($_GET['salaryId'] ?? '');

        if (empty($salaryId)) {
            Utils::sendErrorResponse('Salary ID is required');
            return;
        }

        $db->beginTransaction();

        try {
            // Check if salary exists
            $salary = $db->fetchOne("SELECT SalaryID FROM salaries WHERE SalaryID = ?", [$salaryId]);

            if (empty($salary)) {
                Utils::sendErrorResponse('Salary not found');
                return;
            }

            // Delete the salary
            $db->query("DELETE FROM salaries WHERE SalaryID = ?", [$salaryId]);

            $db->commit();
            Utils::sendSuccessResponse('Salary deleted successfully');
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete salary: ' . $e->getMessage());
    }
}
