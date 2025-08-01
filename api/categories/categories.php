<?php

/**
 * Categories REST API
 * Uses centralized database and utilities
 * 
 * GET    /api/categories/categories.php - Get all categories
 * GET    /api/categories/categories.php?id=X - Get specific category
 * GET    /api/categories/categories.php?category_id=X&items=true - Get category items
 * POST   /api/categories/categories.php - Create new category
 * PUT    /api/categories/categories.php - Update category
 * DELETE /api/categories/categories.php - Delete category
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
    $categoryId = $_GET['id'] ?? '';
    $categoryIdForItems = $_GET['category_id'] ?? '';
    $getItems = isset($_GET['items']) && $_GET['items'] === 'true';

    if (!empty($categoryIdForItems) && $getItems) {
        getCategoryItems($categoryIdForItems);
    } elseif (!empty($categoryId)) {
        getCategory($categoryId);
    } else {
        getCategories();
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

    $categoryName = $input['category_name'] ?? '';
    $description = $input['description'] ?? '';
    $categoryId = $input['category_id'] ?? '';

    if (empty($categoryName)) {
        Utils::sendErrorResponse('Category name is required');
        return;
    }
    if (!empty($categoryId)) {
        updateCategory($categoryId, $categoryName, $description);
    } else {
        createCategory($categoryName, $description);
    }
}

function handlePut()
{
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        Utils::sendErrorResponse('Invalid JSON input');
        return;
    }

    $categoryId = $input['category_id'] ?? '';
    $categoryName = $input['category_name'] ?? '';
    $description = $input['description'] ?? '';

    if (empty($categoryId) || empty($categoryName)) {
        Utils::sendErrorResponse('Category ID and name are required');
        return;
    }

    updateCategory($categoryId, $categoryName, $description);
}

function handleDelete()
{
    $categoryId = json_decode(file_get_contents('php://input'), true)['category_id'] ?? '';

    if (empty($categoryId)) {
        Utils::sendErrorResponse('Category ID is required');
        return;
    }

    deleteCategory($categoryId);
}

function getCategories()
{
    global $db;

    $search = $_GET['search'] ?? '';

    $sql = "SELECT * FROM categories";
    $params = [];

    if (!empty($search)) {
        $sql .= " WHERE CategoryName LIKE ? OR Description LIKE ?";
        $searchParam = "%$search%";
        $params = [$searchParam, $searchParam];
    }

    $sql .= " ORDER BY CategoryName";

    try {
        $categories = $db->fetchAll($sql, $params);

        // Format the CategoryID to match your frontend (CAT-1001 format)
        foreach ($categories as &$category) {
            $category['CategoryID'] = 'CAT-' . $category['CategoryID'];
            $category['CreatedDate'] = date('Y-m-d', strtotime($category['CreatedDate']));
        }

        Utils::sendSuccessResponse('Categories retrieved successfully', $categories);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve categories: ' . $e->getMessage());
    }
}

function getCategory($categoryId)
{
    global $db;

    // Remove prefix and validate
    $idValue = str_replace('CAT-', '', $categoryId);
    if (!is_numeric($idValue)) {
        Utils::sendErrorResponse('Invalid Category ID format');
        return;
    }

    $sql = "SELECT * FROM categories WHERE CategoryID = ?";

    try {
        $category = $db->fetchOne($sql, [$idValue]);

        if (!$category) {
            Utils::sendErrorResponse('Category not found', 404);
            return;
        }

        // Format the CategoryID
        $category['CategoryID'] = 'CAT-' . $category['CategoryID'];
        $category['CreatedDate'] = date('Y-m-d', strtotime($category['CreatedDate']));

        Utils::sendSuccessResponse('Category retrieved successfully', $category);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve category: ' . $e->getMessage());
    }
}

function getCategoryItems($categoryId)
{
    global $db;

    if (empty($categoryId)) {
        Utils::sendErrorResponse('Category ID is required');
        return;
    }

    // Remove prefix and validate
    $idValue = str_replace('CAT-', '', $categoryId);
    if (!is_numeric($idValue)) {
        Utils::sendErrorResponse('Invalid Category ID format');
        return;
    }

    $sql = "SELECT * FROM items WHERE CategoryID = ?";

    try {
        $items = $db->fetchAll($sql, [$idValue]);
        Utils::sendSuccessResponse('Category items retrieved successfully', $items);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to retrieve category items: ' . $e->getMessage());
    }
}

function createCategory($categoryName, $description)
{
    global $db;

    try {
        $sql = "INSERT INTO categories (CategoryName, Description) VALUES (?, ?)";
        $db->query($sql, [$categoryName, $description]);
        $newId = $db->lastInsertId();

        Utils::sendSuccessResponse('Category created successfully', [
            'category_id' => 'CAT-' . $newId,
            'category_name' => $categoryName,
            'description' => $description
        ]);
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to create category: ' . $e->getMessage());
    }
}

function updateCategory($categoryId, $categoryName, $description)
{
    global $db;

    try {
        // Remove prefix
        $id = str_replace('CAT-', '', $categoryId);

        $sql = "UPDATE categories SET CategoryName = ?, Description = ? WHERE CategoryID = ?";
        $result = $db->query($sql, [$categoryName, $description, $id]);

        if ($result->rowCount() === 0) {
            Utils::sendErrorResponse('Category not found', 404);
            return;
        }

        Utils::sendSuccessResponse('Category updated successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to update category: ' . $e->getMessage());
    }
}

function deleteCategory($categoryId)
{
    global $db;

    // Remove prefix
    $idValue = str_replace('CAT-', '', $categoryId);

    if (empty($idValue)) {
        Utils::sendErrorResponse('Category ID is required');
        return;
    }

    try {
        // First, update items in this category to uncategorized (assuming 0 is uncategorized)
        $uncategorizedCategoryId = $db->fetchOne("SELECT CategoryID FROM categories WHERE LOWER(CategoryName) = LOWER('Uncategorized') LIMIT 1");
        if (!$uncategorizedCategoryId) {
            Utils::sendErrorResponse('Uncategorized category not found');
            return;
        }
        $sql = "UPDATE items SET CategoryID = ? WHERE CategoryID = ?";
        $db->query($sql, [$uncategorizedCategoryId['CategoryID'], $idValue]);

        // Then delete the category
        $sql = "DELETE FROM categories WHERE CategoryID = ?";
        $result = $db->query($sql, [$idValue]);

        if ($result->rowCount() === 0) {
            Utils::sendErrorResponse('Category not found', 404);
            return;
        }

        Utils::sendSuccessResponse('Category deleted successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete category: ' . $e->getMessage());
    }
}
