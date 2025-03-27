<?php

include_once("..\\..\\View\\Database_Connection.php");
include_once("..\\..\\Bussiness_Logic\\Function\\Delete.php");
include_once("..\\..\\Bussiness_Logic\\Function\\Upload.php");

function DeleteCommitAfter($commitId, $projectId)
{
    global $db;
    
    $sql_1 = 
    'SELECT project_version 
    FROM Project_Commit 
    WHERE commit_id = :commit_id';
    
    $stmt_1 = $db->prepare($sql_1);
    $stmt_1->bindParam(':commit_id', $commitId, SQLITE3_INTEGER);
    $result= $stmt_1->execute()->fetchArray();
    $projectVersion = $result['project_version'];
    
    
    $sql_2 = 
    'DELETE FROM Project_Commit
    WHERE project_id = :project_id
    AND project_version > :project_version';
    
    $stmt_2 = $db->prepare($sql_2);
    $stmt_2->bindParam(':project_id', $projectId, SQLITE3_INTEGER);
    $stmt_2->bindParam(':project_version', $projectVersion, SQLITE3_INTEGER);
    $result= $stmt_2->execute();
    
    
    $sql_3 = 
    'UPDATE Project
    SET latest_version = :new_version
    WHERE project_id = :project_id';
    
    $stmt_3 = $db->prepare($sql_3);
    $stmt_3->bindParam(':new_version', $projectVersion, SQLITE3_INTEGER);
    $stmt_3->bindParam(':project_id', $projectId, SQLITE3_INTEGER);
    $result= $stmt_3->execute();
}

function DeleteExcessiveFiles($projectId, $accessToken, $bucketKey)
{
    global $db;

    $currentProjectVersion = GetProjectVersion($projectId);

    $sql_1 =
    'SELECT * FROM Bucket_File
    WHERE first_added_at_version > :current_project_version;';
    $stmt_1 = $db->prepare($sql_1);
    $stmt_1->bindParam(':current_project_version', $currentProjectVersion, SQLITE3_INTEGER);
    $result_1 = $stmt_1->execute();
    
    while($row=$result_1->fetchArray())
    {
        $objectKey = $row['object_key'];
        DeleteObject($accessToken, $bucketKey, $objectKey);
    }

    $sql_2 =
    'DELETE FROM Bucket_File
    WHERE first_added_at_version > :current_project_version;';
    $stmt_2 = $db->prepare($sql_2);
    $stmt_2->bindParam(':current_project_version', $currentProjectVersion, SQLITE3_INTEGER);
    $stmt_2->execute();

    $sql_3 =
    'DELETE FROM Project_File
    WHERE first_added_at_version > :current_project_version;';
    $stmt_3 = $db->prepare($sql_3);
    $stmt_3->bindParam(':current_project_version', $currentProjectVersion, SQLITE3_INTEGER);
    $stmt_3->execute();


    $sql_4 ='SELECT * FROM Project_File;';
    $stmt_4 = $db->prepare($sql_4);
    $result_4 = $stmt_4->execute();
    

    $sql_5 =
    'SELECT project_file_id, MAX(file_version) 
    FROM Bucket_File
    WHERE first_added_at_version <= :current_project_version
    AND project_file_id = :project_file_id;';
    
    $stmt_5 = $db->prepare($sql_5);
    $stmt_5->bindParam(':current_project_version', $currentProjectVersion, SQLITE3_INTEGER);
    
    $result_5 = [];
    while($row=$result_4->fetchArray())
    {
        $projectFileId = $row['project_file_id'];
        $stmt_5->bindParam(':project_file_id', $projectFileId, SQLITE3_INTEGER);
        $result_5 [] = $stmt_5->execute()->fetchArray();
    }

    $sql_6 =
    'UPDATE Project_File
    SET latest_version = :latest_version
    WHERE project_file_id = :project_file_id;';
    
    $stmt_6 = $db->prepare($sql_6);

    foreach($result_5 as $projectFile)
    {    
        $stmt_6->bindParam(':latest_version', $projectFile['MAX(file_version)'], SQLITE3_INTEGER);
        $stmt_6->bindParam(':project_file_id', $projectFile['project_file_id'], SQLITE3_INTEGER);
        $stmt_6->execute();
    }

}

?>