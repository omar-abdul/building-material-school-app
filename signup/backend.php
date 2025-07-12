<?php
include 'connection.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_users':
            handleGetUsers($conn);
            break;
        case 'get_user':
            handleGetUser($conn);
            break;
        case 'add_user':
            handleAddUser($conn);
            break;
        case 'edit_user':
            handleEditUser($conn);
            break;
        case 'delete_user':
            handleDeleteUser($conn);
            break;
        case 'signup_user':
            handleSignupUser($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

function handleGetUsers($conn) {
    $search = $_POST['search'] ?? '';
    $roleFilter = $_POST['roleFilter'] ?? '';
    
    $query = "SELECT id, username, role, created_at FROM users WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $query .= " AND username LIKE :search";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($roleFilter)) {
        $query .= " AND role = :role";
        $params[':role'] = $roleFilter;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'users' => $users]);
}



function handleDeleteUser($conn) {
    $id = $_POST['id'] ?? 0;
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
    }
}

function handleSignupUser($conn) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Validate inputs
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        return;
    }
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        return;
    }
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user with default 'user' role
    $role = 'user';
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':role', $role);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User signed up successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to sign up user']);
    }
}



function handleGetUser($conn) {
    $id = $_POST['id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT id, username, role, password, created_at FROM users WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Return the actual password (not recommended for security reasons)
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
}





// 1. handleAddUser (Ku dar user cusub)
function handleAddUser($conn) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        return;
    }
    
    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        return;
    }
    
    
    // Ku kaydi password-ka oo qoran (plain text)
    // =====================================
    $plainPassword = $password; // No hashing
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $plainPassword); // Plain text password
    $stmt->bindParam(':role', $role);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add user']);
    }
}

// =====================================
// 2. handleEditUser (Cusboonaysii user)

function handleEditUser($conn) {
    $id = $_POST['id'] ?? 0;
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        return;
    }
    
    if (!empty($password) && $password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        return;
    }
    
    // Check if username exists (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        return;
    }
    
   
    // Haddii password la bixin, isticmaal plain text
    
    if (!empty($password)) {
        $plainPassword = $password; // No hashing
        $query = "UPDATE users SET username = :username, password = :password, role = :role WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $plainPassword); // Plain text password
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $id);
    } else {
        $query = "UPDATE users SET username = :username, role = :role WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update user']);
    }
}


?>