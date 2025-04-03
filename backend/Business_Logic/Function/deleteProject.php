<?php
require_once '../../db/connection.php';
require_once '../../Business_Logic/Function/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$project_id = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
if (!$project_id || $project_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid Project ID']);
    exit;
}

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT 1 FROM project_members pm
        JOIN project_roles pr ON pm.project_role_id = pr.project_role_id
        WHERE pm.project_id = :project_id 
        AND pm.user_id = :user_id
        AND pr.role_name = 'Project Admin'
    ");
    $stmt->execute([
        ':project_id' => $project_id,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Insufficient permissions']);
        exit;
    }

    $pdo->beginTransaction();
    
    $tables = [
        'commit_file' => 'commit_id IN (SELECT commit_id FROM project_commit WHERE project_id = :project_id)',
        'project_commit' => 'project_id = :project_id',
        'bucket_file' => 'project_file_id IN (SELECT project_file_id FROM project_file WHERE project_id = :project_id)',
        'project_file' => 'project_id = :project_id',
        'project_members' => 'project_id = :project_id',
        'issues' => 'project_id = :project_id',
        'project' => 'project_id = :project_id'
    ];
    
    foreach ($tables as $table => $condition) {
        $stmt = $pdo->prepare("DELETE FROM $table WHERE $condition");
        $stmt->execute([':project_id' => $project_id]);
    }
    
    $pdo->commit();
    
    logAction($_SESSION['user_id'], "Deleted project ID: $project_id", $project_id);
    
    echo json_encode(['status' => 'success', 'message' => 'Project deleted successfully']);
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}