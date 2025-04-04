<?php
require_once '../../db/connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {

    $required = ['version_id', 'project_id', 'file', 'description'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $version_id = $_POST['version_id'];
    $project_id = (int)$_POST['project_id'];
    $file = htmlspecialchars($_POST['file']);
    $description = htmlspecialchars($_POST['description']);
    $raised_by = isset($_POST['raised_by']) ? (int)$_POST['raised_by'] : 0;
    $status = 'Open'; 

    $sql = "INSERT INTO issues (version_id, project_id, file, description, status, raised_by, date) 
            VALUES (:version_id, :project_id, :file, :description, :status, :raised_by, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':version_id', $version_id, PDO::PARAM_STR);
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->bindParam(':file', $file, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':raised_by', $raised_by, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Issue created successfully']);
    } else {
        throw new Exception('Failed to create issue');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}