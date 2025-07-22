<?php

/**
 * Categories Management Page
 * Uses centralized authentication system
 */

require_once __DIR__ . '/../config/base_url.php';
require_once __DIR__ . '/../config/auth.php';

$auth = new Auth();
$auth->requireAuth();

$role = $auth->getUserRole(); // 'admin' or 'user'
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMMS - Categories Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="page-content">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main>
            <div class="header">
                <i class="fa-solid fa-bars bar-item"></i>
                <div class="search">
                    <input type="search" placeholder="Search categories..." />
                    <i class="fa-solid fa-search"></i>
                </div>
                <div class="profile">
                    <span class="bell"><i class="fa-regular fa-bell"></i></span>
                </div>
            </div>

            <div class="main-content">
                <div class="title">
                    <h1><i class="fas fa-tags"></i> Categories Management</h1>
                </div>

                <div class="action-buttons" style="margin-bottom: 20px;">
                    <button class="btn btn-primary" id="addCategoryBtn">
                        <i class="fas fa-plus"></i> Add New Category
                    </button>
                </div>

                <!-- Search and Filter -->
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search categories...">
                    </div>
                </div>

                <!-- Categories Table -->
                <table class="categories-table">
                    <thead>
                        <tr>
                            <th>Category ID</th>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded dynamically via JavaScript -->
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination">
                    <button class="page-btn"><i class="fas fa-angle-left"></i></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn"><i class="fas fa-angle-right"></i></button>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Category Modal -->
    <div class="modal" id="categoryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New Category</h3>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <form id="categoryForm">
                <input type="hidden" id="categoryId">

                <div class="form-group">
                    <label for="categoryName">Category Name</label>
                    <input type="text" id="categoryName" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="DateAdded">Date Added</label>
                    <input type="date" id="DateAdded" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-danger" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-success" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Items Modal -->
    <div class="modal" id="viewItemsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Items in Category: <span id="categoryNameTitle">Electronics</span></h3>
                <button class="close-btn" id="closeViewItemsModal">&times;</button>
            </div>
            <div class="items-list">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Item Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody id="itemsListBody">
                        <!-- Items will be populated here -->
                    </tbody>
                </table>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" id="closeViewItemsBtn">Close</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Confirm Delete</h3>
                <button class="close-btn" id="closeDeleteModal">&times;</button>
            </div>
            <div class="delete-message">
                <p>Are you sure you want to delete this category?</p>
                <p><strong>Category ID:</strong> <span id="deleteCategoryId">CAT-1001</span> - <span id="deleteCategoryName">Electronics</span></p>
                <p class="warning-text">All items in this category will be moved to 'Uncategorized'.</p>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                <button type="button" class="btn" id="cancelDeleteBtn">Cancel</button>
            </div>
        </div>
    </div>




        <!-- JavaScript Configuration -->\n    <script src="<?= BASE_URL ?>config/js-config.php"></script>\n    <script src="categories.js"></script>
</body>

</html>