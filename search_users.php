<?php
require_once '../db/connection.php';

header('Content-Type: application/json');

try {
    $query = $_GET['query'] ?? '';
    
    if (empty($query)) {
        echo json_encode([]);
        exit;
    }

    $sql = "SELECT user_id, first_name, last_name, email 
            FROM users 
            WHERE email LIKE :query 
            OR first_name LIKE :query 
            OR last_name LIKE :query 
            LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':query', "%$query%");
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($users);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}