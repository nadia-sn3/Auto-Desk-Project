<?php
require_once __DIR__ . '/../../../db/connection.php';

header('Content-Type: application/json');

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $required = ['version_id', 'project_id', 'file_name', 'description'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $raised_by = $_SESSION['user_id'] ?? null;
    if (!$raised_by) {
        throw new Exception("User not authenticated");
    }

    $stmt = $pdo->prepare("
        INSERT INTO issues (
            version_id, 
            project_id, 
            file, 
            description, 
            status, 
            raised_by, 
            date
        ) VALUES (
            :version_id, 
            :project_id, 
            :file, 
            :description, 
            'Open', 
            :raised_by, 
            NOW()
        )
    ");

    $stmt->bindParam(':version_id', $_POST['version_id']);
    $stmt->bindParam(':project_id', $_POST['project_id'], PDO::PARAM_INT);
    $stmt->bindParam(':file', $_POST['file_name']);
    $stmt->bindParam(':description', $_POST['description']);
    $stmt->bindParam(':raised_by', $raised_by, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Issue created successfully'
        ]);
    } else {
        throw new Exception("Failed to create issue");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}