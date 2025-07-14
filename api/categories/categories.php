<?php

/**
 * Categories Backend API
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

// Get action from request
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getCategories':
            getCategories();
            break;
        case 'getCategoryItems':
            getCategoryItems();
            break;
        case 'saveCategory':
            saveCategory();
            break;
        case 'deleteCategory':
            deleteCategory();
            break;
        default:
            Utils::sendErrorResponse('Invalid action');
            break;
    }
} catch (Exception $e) {
    Utils::sendErrorResponse($e->getMessage());
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

function getCategoryItems()
{
    global $db;

    $categoryId = $_GET['category_id'] ?? '';

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

function saveCategory()
{
    global $db;

    $categoryId = $_POST['category_id'] ?? '';
    $categoryName = $_POST['category_name'] ?? '';
    $description = $_POST['description'] ?? '';

    if (empty($categoryName)) {
        Utils::sendErrorResponse('Category name is required');
        return;
    }

    try {
        if (strpos($categoryId, 'CAT-') !== false) {
            // Update existing category
            $id = str_replace('CAT-', '', $categoryId);
            $sql = "UPDATE ategories SET CategoryName = ?, Description = ? WHERE CategoryID = ?";
            $db->query($sql, [$categoryName, $description, $id]);
            Utils::sendSuccessResponse('Category updated successfully');
        } else {
            // Add new category
            $sql = "INSERT INTO ategories (CategoryName, Description) VALUES (?, ?)";
            $db->query($sql, [$categoryName, $description]);
            $newId = $db->lastInsertId();
            Utils::sendSuccessResponse('Category added successfully', ['category_id' => 'CAT-' . $newId]);
        }
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to save category: ' . $e->getMessage());
    }
}

function deleteCategory()
{
    global $db;

    $categoryId = str_replace('CAT-', '', $_POST['category_id']);

    if (empty($categoryId)) {
        Utils::sendErrorResponse('Category ID is required');
        return;
    }

    try {
        // First, update items in this category to uncategorized (assuming 0 is uncategorized)
        $sql = "UPDATE tems SET CategoryID = 0 WHERE CategoryID = ?";
        $db->query($sql, [$categoryId]);

        // Then delete the category
        $sql = "DELETE FROM categories WHERE CategoryID = ?";
        $db->query($sql, [$categoryId]);

        Utils::sendSuccessResponse('Category deleted successfully');
    } catch (Exception $e) {
        Utils::sendErrorResponse('Failed to delete category: ' . $e->getMessage());
    }
}
