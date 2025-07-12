<?php
require_once 'connection.php';

header('Content-Type: application/json');

// Get action from request
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_categories':
            $search = $_GET['search'] ?? '';
            $stmt = $conn->prepare("SELECT * FROM categories WHERE CategoryName LIKE :search OR Description LIKE :search");
            $stmt->bindValue(':search', "%$search%");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format the CategoryID to match your frontend (CAT-1001 format)
            foreach ($categories as &$category) {
                $category['CategoryID'] = 'CAT-' . $category['CategoryID'];
                $category['CreatedDate'] = date('Y-m-d', strtotime($category['CreatedDate']));
            }
            
            echo json_encode($categories);
            break;
            
        case 'get_category_items':
            $categoryId = $_GET['category_id'] ?? '';
            if (empty($categoryId)) {
                throw new Exception("Category ID is required");
            }
            
            // Remove prefix and validate
            $idValue = str_replace('CAT-', '', $categoryId);
            if (!is_numeric($idValue)) {
                throw new Exception("Invalid Category ID format");
            }
            
            $stmt = $conn->prepare("SELECT * FROM Items WHERE CategoryID = :category_id");
            $stmt->bindValue(':category_id', $idValue, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($items);
            break;
            
        case 'save_category':
            $categoryId = $_POST['category_id'] ?? '';
            $categoryName = $_POST['category_name'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if (empty($categoryName)) {
                throw new Exception("Category name is required");
            }
            
            if (strpos($categoryId, 'CAT-') !== false) {
                // Update existing category
                $id = str_replace('CAT-', '', $categoryId);
                $stmt = $conn->prepare("UPDATE Categories SET CategoryName = :name, Description = :desc WHERE CategoryID = :id");
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':name', $categoryName);
                $stmt->bindParam(':desc', $description);
                $stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
            } else {
                // Add new category
                $stmt = $conn->prepare("INSERT INTO Categories (CategoryName, Description) VALUES (:name, :desc)");
                $stmt->bindParam(':name', $categoryName);
                $stmt->bindParam(':desc', $description);
                $stmt->execute();
                
                $newId = $conn->lastInsertId();
                echo json_encode(['success' => true, 'message' => 'Category added successfully', 'category_id' => 'CAT-' . $newId]);
            }
            break;
            
        case 'delete_category':
            $categoryId = str_replace('CAT-', '', $_POST['category_id']);
            
            // First, update items in this category to uncategorized (assuming 0 is uncategorized)
            $stmt = $conn->prepare("UPDATE Items SET CategoryID = 0 WHERE CategoryID = :id");
            $stmt->bindParam(':id', $categoryId);
            $stmt->execute();
            
            // Then delete the category
            $stmt = $conn->prepare("DELETE FROM Categories WHERE CategoryID = :id");
            $stmt->bindParam(':id', $categoryId);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>