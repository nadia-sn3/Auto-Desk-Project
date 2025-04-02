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
        header('Location: /collaborators.php?project_id=' . $project_id);
        exit;
    }

    if ($username) {
        $sql = "SELECT user_id FROM users WHERE email = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            header('Location: /collaborators.php?project_id=' . $project_id);
            exit;
        }
        
        $user_id = $user['user_id'];
    } elseif ($email) {
        $sql = "SELECT user_id FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            header('Location: /collaborators.php?project_id=' . $project_id);
            exit;
        }
        
        $user_id = $user['user_id'];
    } else {
        header('Location: /collaborators.php?project_id=' . $project_id);
        exit;
    }

    $sql = "SELECT project_member_id FROM project_members 
            WHERE project_id = :project_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        header('Location: /collaborators.php?project_id=' . $project_id);
        exit;
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

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    } else {
        header('Location: /collaborators.php?project_id=' . $project_id);
        exit;
    }

} catch (Exception $e) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    } else {
        header('Location: /collaborators.php?project_id=' . $project_id);
        exit;
    }
}