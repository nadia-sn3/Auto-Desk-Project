<?php
require_once 'db/connection.php';

try {
    $query = $_GET['query'] ?? '';
    if (empty($query)) {
        echo json_encode([]);
        exit;
    }
    $sql = "SELECT user_id, first_name, last_name, email FROM users WHERE first_name LIKE :query OR last_name LIKE :query OR email LIKE :query";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);
} catch (Exception $e) {
    echo json_encode([]);
}
?>
