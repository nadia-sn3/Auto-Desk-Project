<?php
require_once 'config.php';
require_once 'db/connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access');
    }

    $project_id = $_GET['project_id'] ?? null;
    if (!$project_id) {
        throw new Exception('Project ID is required');
    }

    $stmt = $pdo->prepare("SELECT pm.user_id 
                          FROM project_members pm
                          JOIN project_roles pr ON pm.project_role_id = pr.project_role_id
                          WHERE pm.project_id = :project_id 
                          AND pm.user_id = :user_id
                          AND pr.role_name = 'Project Admin'");
    $stmt->bindParam(':project_id', $project_id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        throw new Exception('You do not have permission to delete this project');
    }

    $pdo->beginTransaction();

    $tables = [
        'issues',
        'bucket_file',
        'project_file',
        'project_commit',
        'project_members',
        'project'
    ];

    foreach ($tables as $table) {
        $sql = "DELETE FROM $table WHERE project_id = :project_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
    }

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Project deleted successfully']);
    
} catch (Exception $e) {
    if (isset($pdo) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}