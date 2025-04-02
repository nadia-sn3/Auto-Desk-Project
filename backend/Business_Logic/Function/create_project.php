<?php
require_once __DIR__ . '/../../../db/connection.php';  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $pdo;  
    $latest_version = 1;
    $project_name = $_POST['project-name'] ?? null;
    $description = $_POST['project-description'] ?? null;
    $commit_message = "Initial project creation";
    
    session_start();
    $created_by = $_SESSION['user_id'] ?? null;  

    if ($project_name && $description && $created_by) {
        try {
            $pdo->beginTransaction();
            
            $org_id = null;
            $org_stmt = $pdo->prepare("SELECT org_id FROM organisation_members WHERE user_id = :user_id LIMIT 1");
            $org_stmt->execute([':user_id' => $created_by]);
            $org_result = $org_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($org_result) {
                $org_id = $org_result['org_id'];
            }
            
            $stmt = $pdo->prepare("INSERT INTO Project (project_name, description, created_by, latest_version, org_id) 
                                  VALUES (:project_name, :description, :created_by, :latest_version, :org_id)");
            
            $stmt->execute([
                ':project_name' => $project_name,
                ':description' => $description,
                ':created_by' => $created_by,
                ':latest_version' => $latest_version,
                ':org_id' => $org_id
            ]);
            
            $project_id = $pdo->lastInsertId();
            
            $commit_stmt = $pdo->prepare("INSERT INTO Project_Commit (commit_message, project_id, project_version) 
                                         VALUES (:commit_message, :project_id, :project_version)");
            
            $commit_stmt->execute([
                ':commit_message' => $commit_message,
                ':project_id' => $project_id,
                ':project_version' => $latest_version
            ]);
            
            $commit_id = $pdo->lastInsertId();
            
            $admin_role_id = 1; 
            $member_stmt = $pdo->prepare("INSERT INTO project_members 
                                        (project_id, user_id, project_role_id, added_at, added_by) 
                                        VALUES (:project_id, :user_id, :role_id, NOW(), :added_by)");
            
            $member_stmt->execute([
                ':project_id' => $project_id,
                ':user_id' => $created_by,
                ':role_id' => $admin_role_id,
                ':added_by' => $created_by
            ]);
            
            $pdo->commit();
            header("Location: view-project.php?project_id=$project_id");
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Missing required fields or user not logged in.";
    }
}
?>