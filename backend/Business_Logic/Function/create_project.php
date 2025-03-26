<?php
require_once __DIR__ . '/../../../db/Database_Connection.php';  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $db;  // Ensure $db is accessible
    $latest_version = 1;
    $project_name = $_POST['project-name'] ?? null;
    $description = $_POST['project-description'] ?? null;
    $created_by = 1;  // Change this based on user session

    if ($project_name && $description) {
        try {
            // Prepare SQL query
            $stmt = $db->prepare("INSERT INTO Project (project_name, description, created_by,latest_version) VALUES (:project_name, :description, :created_by, :latest_version);");
            
            // Bind parameters
            $stmt->bindValue(':project_name', $project_name, SQLITE3_TEXT);
            $stmt->bindValue(':description', $description, SQLITE3_TEXT);
            $stmt->bindValue(':created_by', $created_by, SQLITE3_INTEGER);
            $stmt->bindValue(':latest_version', $latest_version, SQLITE3_INTEGER);
            
            // Execute the query
            $result = $stmt->execute();

            if ($result) {
                $project_id = $db->lastInsertRowID(); // Get the last inserted project ID
                header("Location: view-project.php?project_id=$project_id");
                exit;
            } else {
                echo "Error: " . $db->lastErrorMsg();
            }

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Missing required fields.";
    }
}
?>
