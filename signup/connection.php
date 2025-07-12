<?php
// connection.php
$host = 'localhost';
$dbname = 'bmmss';
$username = 'root'; // Change to your MySQL username
$password = ''; // Change to your MySQL password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>