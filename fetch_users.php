<?php

$host = 'localhost';
$dbname = 'auto_desk';
$username = 'root'; 
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$searchTerm = $_GET['term'] ?? '';

$query = "SELECT user_id, username, email FROM users WHERE username LIKE :searchTerm OR email LIKE :searchTerm";
$stmt = $pdo->prepare($query);
$stmt->execute(['searchTerm' => "%$searchTerm%"]);

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($users);