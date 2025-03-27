<?php 
require_once __DIR__ . '/../../../db/connection.php'; 

function GetProjectVersion($projectId)
{
    global $pdo;

    $sql = 'SELECT latest_version FROM Project WHERE project_id = :project_id';
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['latest_version'] : null;
}

function GetLatestFileVersion($fileName, $projectId)
{
    global $pdo;

    $sql = 'SELECT latest_version 
            FROM Project_File 
            WHERE file_name = :file_name 
            AND project_id = :project_id';

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':file_name', $fileName, PDO::PARAM_STR);
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['latest_version'] : null;
}

function GetAllProjectFiles($projectId)
{
    global $pdo;

    $sql_GetProjectVersion = 'SELECT latest_version FROM Project WHERE project_id = :project_id';
    $stmt_1 = $pdo->prepare($sql_GetProjectVersion);
    $stmt_1->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt_1->execute();
    $result = $stmt_1->fetch(PDO::FETCH_ASSOC);
    if (!$result) return []; 
    $projectVersion = $result['latest_version'];

    $sql_GetLastCommit = 'SELECT commit_id FROM Project_Commit WHERE project_version = :project_version';
    $stmt_2 = $pdo->prepare($sql_GetLastCommit);
    $stmt_2->bindParam(':project_version', $projectVersion, PDO::PARAM_INT);
    $stmt_2->execute();
    $result = $stmt_2->fetch(PDO::FETCH_ASSOC);
    if (!$result) return []; 
    $commitId = $result['commit_id'];

    $sql_GetCommitFiles = 'SELECT bucket_file_id FROM commit_file WHERE commit_id = :commit_id';
    $stmt_3 = $pdo->prepare($sql_GetCommitFiles);
    $stmt_3->bindParam(':commit_id', $commitId, PDO::PARAM_INT);
    $stmt_3->execute();
    $commitedFileList = $stmt_3->fetchAll(PDO::FETCH_ASSOC);

    if (!$commitedFileList) return [];

    $sql_GetBucketFile = 'SELECT project_file_id FROM Bucket_File WHERE bucket_file_id = :bucket_file_id';
    $stmt_4 = $pdo->prepare($sql_GetBucketFile);
    $bucketFileList = [];

    foreach ($commitedFileList as $bucketFile) {
        $stmt_4->bindParam(':bucket_file_id', $bucketFile['bucket_file_id'], PDO::PARAM_INT);
        $stmt_4->execute();
        $result = $stmt_4->fetch(PDO::FETCH_ASSOC);
        if ($result) $bucketFileList[] = $result;
    }

    if (!$bucketFileList) return [];

    $sql_GetProjectFile = 'SELECT * FROM Project_File WHERE project_file_id = :project_file_id';
    $stmt_5 = $pdo->prepare($sql_GetProjectFile);
    $projectFileList = [];

    foreach ($bucketFileList as $projectFile) {
        $stmt_5->bindParam(':project_file_id', $projectFile['project_file_id'], PDO::PARAM_INT);
        $stmt_5->execute();
        $result = $stmt_5->fetch(PDO::FETCH_ASSOC);
        if ($result) $projectFileList[] = $result;
    }

    return $projectFileList;
}


function GetProjectFilesInList($fileList, $projectId) 
{
    global $pdo;
    $sql = 'SELECT * FROM Project_File WHERE file_name = :file_name AND project_id = :project_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    
    $projectFileList = [];
    foreach ($fileList as $fileName) {
        $stmt->bindParam(':file_name', $fileName, PDO::PARAM_STR);
        $stmt->execute();
        $projectFileList[] = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    return $projectFileList;
}

function InsertNewCommit($projectId, $commitMessage)
{
    global $pdo;
    $sql_1 = 'UPDATE Project SET latest_version = latest_version + 1 WHERE project_id = :project_id';
    $stmt_1 = $pdo->prepare($sql_1);
    $stmt_1->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt_1->execute();

    $sql_2 = 'SELECT latest_version FROM Project WHERE project_id = :project_id';
    $stmt_2 = $pdo->prepare($sql_2);
    $stmt_2->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt_2->execute();
    $projectVersion = $stmt_2->fetch(PDO::FETCH_ASSOC)['latest_version'];

    $sql_3 = 'INSERT INTO Project_Commit(project_id, project_version, commit_message) VALUES (:project_id, :project_version, :commit_message)';
    $stmt_3 = $pdo->prepare($sql_3);
    $stmt_3->bindParam(':project_version', $projectVersion, PDO::PARAM_INT);
    $stmt_3->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt_3->bindParam(':commit_message', $commitMessage, PDO::PARAM_STR);
    $stmt_3->execute();
}

function GetLastCommitId($projectId)
{
    global $pdo;
    $sql = 'SELECT commit_id FROM Project_Commit WHERE project_id = :project_id ORDER BY project_version DESC LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['commit_id'] ?? null;
}

function InsertFilesForCommit($fileList, $commitId)
{
    global $pdo;
    $sql_GetBucketFile = 'SELECT bucket_file_id FROM Bucket_File WHERE project_file_id = :project_file_id AND file_version = :file_version';
    $stmt_1 = $pdo->prepare($sql_GetBucketFile);
    $bucketFileList = [];
    
    foreach ($fileList as $file) {
        $stmt_1->bindParam(':project_file_id', $file['project_file_id'], PDO::PARAM_INT);
        $stmt_1->bindParam(':file_version', $file['latest_version'], PDO::PARAM_INT);
        $stmt_1->execute();
        $bucketFileList[] = $stmt_1->fetch(PDO::FETCH_ASSOC);
    }

    $sql_InsertCommitFile = 'INSERT INTO commit_file(commit_id, bucket_file_id) VALUES (:commit_id, :bucket_file_id)';
    $stmt_2 = $pdo->prepare($sql_InsertCommitFile);
    
    foreach ($bucketFileList as $file) {
        if ($file) {
            $stmt_2->bindParam(':commit_id', $commitId, PDO::PARAM_INT);
            $stmt_2->bindParam(':bucket_file_id', $file['bucket_file_id'], PDO::PARAM_INT);
            $stmt_2->execute();
        }
    }
}

function GetAllProjectFiles2($projectId)
{
    global $pdo;
    $sql = 'SELECT latest_version FROM Project WHERE project_id = :project_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) return [];
    $projectVersion = $result['latest_version'];
    
    $sql = 'SELECT commit_id FROM Project_Commit WHERE project_version = :project_version ORDER BY commit_id DESC LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':project_version', $projectVersion, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) return [];
    
    $commitId = $result['commit_id'];
    
    $sql = 'SELECT bucket_file_id FROM commit_file WHERE commit_id = :commit_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':commit_id', $commitId, PDO::PARAM_INT);
    $stmt->execute();
    $committedFileList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($committedFileList)) return [];
    
    $sql = 'SELECT pf.project_file_id, pf.file_name, bf.object_id FROM Bucket_File bf JOIN Project_File pf ON pf.project_file_id = bf.project_file_id WHERE bf.bucket_file_id = :bucket_file_id';
    $stmt = $pdo->prepare($sql);
    $bucketFileList = [];
    
    foreach ($committedFileList as $bucketFile) {
        $stmt->bindParam(':bucket_file_id', $bucketFile['bucket_file_id'], PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $bucketFileList[] = $result;
        }
    }
    
    return $bucketFileList;
}




?>