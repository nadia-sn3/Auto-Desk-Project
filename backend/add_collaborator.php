<?php
require_once '../db/connection.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /collaborators.php?project_id=' . $_POST['project_id']);
    exit;
}

try {
    $project_id = $_POST['project_id'] ?? null;
    $username = $_POST['collaborator-username'] ?? null;
    $email = $_POST['collaborator-email'] ?? null;
    $role_id = $_POST['collaborator-role'] ?? null;
    $added_by = $_SESSION['user_id'] ?? null;

    if (!$project_id || !$role_id || !$added_by) {
        throw new Exception("Required fields are missing");
    }

    if ($username) {
        $sql = "SELECT user_id FROM users WHERE email = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception("User not found");
        }
        
        $user_id = $user['user_id'];
    } elseif ($email) {
        $sql = "SELECT user_id FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception("User not found. Invitation functionality not implemented yet.");
        }
        
        $user_id = $user['user_id'];
    } else {
        throw new Exception("Either username or email must be provided");
    }

    // Check if user is already a collaborator
    $sql = "SELECT project_member_id FROM project_members 
            WHERE project_id = :project_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        throw new Exception("User is already a collaborator on this project");
    }

    $sql = "INSERT INTO project_members 
            (project_id, user_id, project_role_id, added_by) 
            VALUES (:project_id, :user_id, :role_id, :added_by)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':role_id', $role_id, PDO::PARAM_INT);
    $stmt->bindValue(':added_by', $added_by, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: /collaborators.php?project_id=' . $project_id . '&success=1');
    exit;

} catch (Exception $e) {
    header('Location: /collaborators.php?project_id=' . $project_id . '&error=' . urlencode($e->getMessage()));
    exit;
}