<?php
require_once '../Business_Logic/Function/config.php';
require_once '../../db/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$project_id = $_POST['project_id'] ?? null;
$user_id = $_POST['user_id'] ?? null;

if (!$project_id || !$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Check if the user is the last admin (prevent removing the last admin)
    $sql = "SELECT COUNT(*) as admin_count 
            FROM project_members 
            WHERE project_id = :project_id 
            AND project_role_id = 1"; // Role ID 1 is Project Admin
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['admin_count'] <= 1) {
        // Check if the user being removed is an admin
        $sql = "SELECT project_role_id 
                FROM project_members 
                WHERE project_id = :project_id 
                AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user_role = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_role && $user_role['project_role_id'] == 1) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot remove the last admin']);
            exit;
        }
    }

    // Remove the collaborator
    $sql = "DELETE FROM project_members 
            WHERE project_id = :project_id 
            AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'Collaborator removed successfully']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}