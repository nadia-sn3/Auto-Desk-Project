<?php
require_once '../db/connection.php';
session_start();

header('Content-Type: application/json');

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method!");
    }

    $project_id = $_POST['project_id'] ?? null;
    $target_user_id = $_POST['user_id'] ?? null;
    $current_user_id = $_SESSION['user_id'] ?? null;

    if (!$project_id || !$target_user_id || !$current_user_id) {
        throw new Exception("Missing required parameters!");
    }

    if ($target_user_id == $current_user_id) {
        throw new Exception("You cannot remove yourself from the project!");
    }

    $sql = "SELECT pr.project_role_id, pr.role_name 
            FROM project_members pm
            JOIN project_roles pr ON pm.project_role_id = pr.project_role_id
            WHERE pm.project_id = :project_id AND pm.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':project_id' => $project_id, ':user_id' => $current_user_id]);
    $current_user_role = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current_user_role || ($current_user_role['project_role_id'] != 1 && $current_user_role['project_role_id'] != 2)) {
        throw new Exception("Unauthorized: You don't have permission to remove collaborators!");
    }

    $sql = "SELECT pr.project_role_id, pr.role_name 
            FROM project_members pm
            JOIN project_roles pr ON pm.project_role_id = pr.project_role_id
            WHERE pm.project_id = :project_id AND pm.user_id = :target_user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':project_id' => $project_id, ':target_user_id' => $target_user_id]);
    $target_user_role = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$target_user_role) {
        throw new Exception("Target user not found in this project!");
    }

    if ($target_user_role['project_role_id'] == 1) {
        throw new Exception("Cannot remove administrators from the project!");
    }

    if ($target_user_role['project_role_id'] == 2 && $current_user_role['project_role_id'] != 1) {
        throw new Exception("Only administrators can remove project managers!");
    }

    $sql = "DELETE FROM project_members WHERE project_id = :project_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':project_id' => $project_id, ':user_id' => $target_user_id]);

    echo json_encode([
        "success" => true,
        "message" => "User removed successfully!"
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>