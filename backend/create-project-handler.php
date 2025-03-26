<?php
require '../../db/connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../signin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../create-project.php");
    exit();
}

$userId = $_SESSION['user_id'];
$orgId = $_POST['org_id'] ?? null;
$projectName = trim($_POST['project_name']);
$projectDescription = trim($_POST['project_description']);
$isPrivate = isset($_POST['is_private']) ? (int)$_POST['is_private'] : 1;
$members = $_POST['members'] ?? [];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO projects (org_id, project_name, description, created_by, is_private)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$orgId, $projectName, $projectDescription, $userId, $isPrivate]);
    $projectId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO project_members (project_id, user_id, project_role_id, added_by)
        VALUES (?, ?, 1, ?)
    ");
    $stmt->execute([$projectId, $userId, $userId]);

    foreach ($members as $memberId => $memberData) {
        $memberId = (int)$memberId;
        $roleId = (int)$memberData['role'];
        
        $stmt = $pdo->prepare("
            INSERT INTO project_members (project_id, user_id, project_role_id, added_by)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$projectId, $memberId, $roleId, $userId]);
    }

    $pdo->commit();

    header("Location: ../../view-project.php?id=$projectId");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Project creation failed: " . $e->getMessage());
    $_SESSION['error'] = "Failed to create project. Please try again.";
    header("Location: ../../create-project.php");
    exit();
}