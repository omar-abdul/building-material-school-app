
<?php
session_start();

// Xiriirka database-ka
$host = "localhost";
$dbname = "bmmss";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit;
}

// Foomka la helay
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    // Akhri user-ka
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['password']) {
        // Haddii aad isticmaaleyso password_hash:
        // if (password_verify($password, $user['password'])) {

        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        echo json_encode(["success" => true]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid username or password."
        ]);
    }
    exit;
}
?>












