<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
// Include required files
require __DIR__ . '/../../../db/connection.php'; 
require("Delete.php");
require("upload.php");
require("uploaddatabase.php");
require("getAccessToken.php");
require("config.php");

// Function to delete commits and associated data after rollback
function DeleteCommitAfter($commitId, $projectId)
{
    global $pdo;
    
    try {
        // Validate commitId and projectId
        if (!is_numeric($commitId) || !is_numeric($projectId)) {
            throw new Exception("Invalid commit_id or project_id.");
        }
        
        // Fetch the project version related to the commit
        $sql_1 = 'SELECT project_version FROM Project_Commit WHERE commit_id = :commit_id';
        $stmt_1 = $pdo->prepare($sql_1);
        $stmt_1->bindParam(':commit_id', $commitId, PDO::PARAM_INT);
        $stmt_1->execute();
        $result = $stmt_1->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new Exception("Commit ID not found.");
        }
        $projectVersion = $result['project_version'];
        
        // Delete subsequent commits
        $sql_2 = 'DELETE FROM Project_Commit WHERE project_id = :project_id AND project_version > :project_version';
        $stmt_2 = $pdo->prepare($sql_2);
        $stmt_2->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt_2->bindParam(':project_version', $projectVersion, PDO::PARAM_INT);
        $stmt_2->execute();
        
        // Update the project to the previous version
        $sql_3 = 'UPDATE Project SET latest_version = :new_version WHERE project_id = :project_id';
        $stmt_3 = $pdo->prepare($sql_3);
        $stmt_3->bindParam(':new_version', $projectVersion, PDO::PARAM_INT);
        $stmt_3->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt_3->execute();
        
        // Log success
        error_log("Commit rollback successful for commit_id: $commitId, project_id: $projectId.");
        return 'success'; // Rollback successful
    } catch (Exception $e) {
        // Log the error
        error_log("Error during commit rollback: " . $e->getMessage());
        return 'failure'; // Return failure if anything goes wrong
    }
}

// Function to delete excessive files after rollback
function DeleteExcessiveFiles($projectId, $accessToken, $bucketKey)
{
    global $pdo;
    
    try {
        // Validate projectId
        if (!is_numeric($projectId)) {
            throw new Exception("Invalid project_id.");
        }

        $currentProjectVersion = GetProjectVersion($projectId);

        $sql_1 = 'SELECT * FROM Bucket_File WHERE first_added_at_version > :current_project_version';
        $stmt_1 = $pdo->prepare($sql_1);
        $stmt_1->bindParam(':current_project_version', $currentProjectVersion, PDO::PARAM_INT);
        $stmt_1->execute();

        while ($row = $stmt_1->fetch(PDO::FETCH_ASSOC)) {
            $objectKey = $row['object_key'];
            DeleteObject($accessToken, $bucketKey, $objectKey); // Deleting excessive files
        }

        $sql_2 = 'DELETE FROM Bucket_File WHERE first_added_at_version > :current_project_version';
        $stmt_2 = $pdo->prepare($sql_2);
        $stmt_2->bindParam(':current_project_version', $currentProjectVersion, PDO::PARAM_INT);
        $stmt_2->execute();

        $sql_3 = 'DELETE FROM Project_File WHERE first_added_at_version > :current_project_version';
        $stmt_3 = $pdo->prepare($sql_3);
        $stmt_3->bindParam(':current_project_version', $currentProjectVersion, PDO::PARAM_INT);
        $stmt_3->execute();

        // Handle updates for project files after rollback
        $sql_4 = 'SELECT * FROM Project_File';
        $stmt_4 = $pdo->prepare($sql_4);
        $stmt_4->execute();

        $sql_5 = 'SELECT project_file_id, MAX(file_version) FROM Bucket_File WHERE first_added_at_version <= :current_project_version AND project_file_id = :project_file_id';
        $stmt_5 = $pdo->prepare($sql_5);
        $stmt_5->bindParam(':current_project_version', $currentProjectVersion, PDO::PARAM_INT);

        $result_5 = [];
        while ($row = $stmt_4->fetch(PDO::FETCH_ASSOC)) {
            $projectFileId = $row['project_file_id'];
            $stmt_5->bindParam(':project_file_id', $projectFileId, PDO::PARAM_INT);
            $result_5[] = $stmt_5->execute()->fetch(PDO::FETCH_ASSOC);
        }

        $sql_6 = 'UPDATE Project_File SET latest_version = :latest_version WHERE project_file_id = :project_file_id';
        $stmt_6 = $pdo->prepare($sql_6);

        foreach ($result_5 as $projectFile) {
            $stmt_6->bindParam(':latest_version', $projectFile['MAX(file_version)'], PDO::PARAM_INT);
            $stmt_6->bindParam(':project_file_id', $projectFile['project_file_id'], PDO::PARAM_INT);
            $stmt_6->execute();
        }

        // Log success
        error_log("File rollback successful for project_id: $projectId.");
        return 'success'; // Rollback of files successful
    } catch (Exception $e) {
        // Log the error
        error_log("Error during file rollback: " . $e->getMessage());
        return 'failure'; // Return failure if anything goes wrong
    }
}

// Main Rollback Logic
header('Content-Type: application/json');

try {
    if (!isset($_GET['commit_id']) || !isset($_GET['project_id'])) {
        // Return a JSON response with an error if parameters are missing
        echo json_encode(['status' => 'failure', 'error' => 'Missing parameters']);
        exit;
    }

    $commitId = $_GET['commit_id'];
    $projectId = $_GET['project_id']; 
    $access_token = getAccessToken($client_id, $client_secret);
    error_log("Access token fetched: " . $access_token);

    // Perform rollback actions
    $rollbackCommitResult = DeleteCommitAfter($commitId, $projectId);
    error_log("Commit rollback result: " . $rollbackCommitResult);

    $rollbackFilesResult =  DeleteExcessiveFiles($projectId, $access_token, $bucket_key);
    error_log("File rollback result: " . $rollbackFilesResult);

    if ($rollbackCommitResult && $rollbackFilesResult) {
        echo json_encode(['status' => 'success']); // JSON response
    } else {
        echo json_encode(['status' => 'failure']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'failure', 'error' => $e->getMessage()]);
}

?>
