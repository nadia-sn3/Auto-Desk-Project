<?php
require_once __DIR__ . '/../../../db/connection.php';  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $pdo;  
    $latest_version = 1;
    $project_name = $_POST['project-name'] ?? null;
    $description = $_POST['project-description'] ?? null;
    
    session_start();
    $created_by = $_SESSION['user_id'] ?? null;  

    if ($project_name && $description && $created_by) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Project (project_name, description, created_by, latest_version) VALUES (:project_name, :description, :created_by, :latest_version)");
            
            $stmt->bindParam(':project_name', $project_name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':created_by', $created_by, PDO::PARAM_INT);
            $stmt->bindParam(':latest_version', $latest_version, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $project_id = $pdo->lastInsertId();
                header("Location: view-project.php?project_id=$project_id");
                exit;
            } else {
                echo "Error: Failed to insert project.";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Missing required fields or user not logged in.";
    }
}
?>