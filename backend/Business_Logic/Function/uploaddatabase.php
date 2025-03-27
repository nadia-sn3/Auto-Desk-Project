<?php 
require_once 'db/Database_Connection.php';

function GetProjectVersion($projectId)
{
    global $db;

    $sql = 'SELECT latest_version 
    FROM Project 
    WHERE project_id = :project_id';
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':project_id', $projectId, SQLITE3_INTEGER);
    $result= $stmt->execute()->fetchArray();
    $fileVersion = $result['latest_version'];
    
    return $fileVersion;
}

function GetLatestFileVersion($fileName, $projectId)
{
    global $db;

    $sql = 
    'SELECT latest_version 
    FROM Project_File 
    WHERE file_name = :file_name 
    AND project_id = :project_id';
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':file_name', $fileName, SQLITE3_TEXT);
    $stmt->bindParam(':project_id', $projectId, SQLITE3_INTEGER);
    $result= $stmt->execute()->fetchArray();
    $fileVersion = $result['latest_version'];
    
    return $fileVersion;
}

function GetAllProjectFiles($projectId)
{
    global $db;

    $sql_GetProjectVersion = 
    'SELECT latest_version 
    FROM Project 
    WHERE project_id = :project_id';
    $stmt_1 = $db->prepare($sql_GetProjectVersion);
    $stmt_1->bindParam(':project_id', $projectId, SQLITE3_INTEGER);
    $result= $stmt_1->execute()->fetchArray();
    $projectVersion = $result['latest_version'];
    
    $sql_GetLastCommit = 
    'SELECT commit_id
    FROM Project_Commit
    WHERE project_version = :project_version';
    $stmt_2 = $db->prepare($sql_GetLastCommit);
    $stmt_2->bindParam(':project_version', $projectVersion, SQLITE3_INTEGER);
    $result= $stmt_2->execute()->fetchArray();
    $commitId = $result['commit_id'];
    
    $sql_GetCommitFiles =
    'SELECT bucket_file_id
    FROM Commit_File
    WHERE commit_id = :commit_id';
    $stmt_3 = $db->prepare($sql_GetCommitFiles);
    $stmt_3->bindParam(':commit_id', $commitId, SQLITE3_INTEGER);
    $result= $stmt_3->execute();
    $commitedFileList = [];
    while($row=$result->fetchArray()){
        $commitedFileList [] = $row;
    }
    
    $sql_GetBucketFile =
    'SELECT project_file_id
    FROM Bucket_File
    WHERE bucket_file_id = :bucket_file_id';
    $stmt_4 = $db->prepare($sql_GetBucketFile);
    $bucketFileList = [];
    foreach($commitedFileList as $bucketFile)
    {
        $stmt_4->bindParam(':bucket_file_id', $bucketFile['bucket_file_id'], SQLITE3_INTEGER);
        $bucketFileList []= $stmt_4->execute()->fetchArray();
    }

    $sql_GetProjectFile =
    'SELECT * FROM Project_File
    WHERE project_file_id = :project_file_id';
    $stmt_5 = $db->prepare($sql_GetProjectFile);
    $projectFileList = [];
    foreach($bucketFileList as $projectFile)
    {
        $stmt_5->bindParam(':project_file_id', $projectFile['project_file_id'], SQLITE3_INTEGER);
        $projectFileList []= $stmt_5->execute()->fetchArray();
    }

    return $projectFileList;
}

function GetProjectFilesInList($fileList, $projectId)
{
    global $db;
    
    $sql = 
    'SELECT * FROM Project_File
    WHERE file_name = :file_name 
    AND project_id = :project_id;';
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':project_id', $projectId, SQLITE3_INTEGER);
    
    $projectFileList = [];
    foreach($fileList as $fileName)
    {
        $stmt->bindParam(':file_name', $fileName, SQLITE3_TEXT);
        $projectFileList []= $stmt->execute()->fetchArray();
    }

    return $projectFileList;
}

function InsertNewCommit($projectId, $commitMessage)
{
    global $db;

    $sql_1 = 'UPDATE Project
    SET latest_version = latest_version + 1
    WHERE project_id = :project_id';
    
    $stmt_1 = $db->prepare($sql_1);
    $stmt_1->bindParam(':project_id', $projectId, SQLITE3_INTEGER);
    $stmt_1->execute();
    

    $sql_2 = 'SELECT latest_version 
    FROM Project 
    WHERE project_id = :project_id';
    
    $stmt_2 = $db->prepare($sql_2);
    $stmt_2->bindParam(':project_id', $projectId, SQLITE3_INTEGER);
    $result= $stmt_2->execute()->fetchArray();
    $projectVersion = $result['latest_version'];


    $sql_3 = 
    'INSERT INTO Project_Commit(project_id, project_version, commit_message)
    VALUES (:project_id, :project_version, :commit_message)';
    
    $stmt_3 = $db->prepare($sql_3);
    $stmt_3->bindParam(':project_version', $projectVersion, SQLITE3_INTEGER);
    $stmt_3->bindParam(':project_id', $projectId, SQLITE3_INTEGER);
    $stmt_3->bindParam(':commit_message', $commitMessage, SQLITE3_TEXT);
    $stmt_3->execute();
}

function GetLastCommitId($projectId)
{
    global $db;
    
    $sql_GetProjectVersion = 
    'SELECT latest_version 
    FROM Project 
    WHERE project_id = :project_id';
    $stmt_1 = $db->prepare($sql_GetProjectVersion);
    $stmt_1->bindParam(':project_id', $projectId, SQLITE3_INTEGER);
    $result= $stmt_1->execute()->fetchArray();
    $projectVersion = $result['latest_version'];
    
    $sql_GetLastCommit = 
    'SELECT commit_id
    FROM Project_Commit
    WHERE project_version = :project_version 
    AND project_id = :project_id';
    $stmt_2 = $db->prepare($sql_GetLastCommit);
    $stmt_2->bindParam(':project_version', $projectVersion, SQLITE3_INTEGER);
    $stmt_2->bindParam(':project_id', $projectId, SQLITE3_INTEGER);
    $result= $stmt_2->execute()->fetchArray();
    $commitId = $result['commit_id'];
    
    return $commitId;
}


function InsertFilesForCommit($fileList, $commitId)
{
    global $db;

    $sql_GetBucketFile =
    'SELECT bucket_file_id
    FROM Bucket_File
    WHERE project_file_id = :project_file_id AND
    file_version = :file_version';

    $stmt_1 = $db->prepare($sql_GetBucketFile);
    $bucketFileList = [];

    foreach($fileList as $file)
    {
        $stmt_1->bindParam(':project_file_id', $file['project_file_id'], SQLITE3_INTEGER);
        $stmt_1->bindParam(':file_version', $file['latest_version'], SQLITE3_INTEGER);
        $bucketFileList []= $stmt_1->execute()->fetchArray();
    }


    $sql_InsertCommitFile = 
    'INSERT INTO Commit_File(commit_id, bucket_file_id)
    VALUES (:commit_id, :bucket_file_id)';

    $stmt_2 = $db->prepare($sql_InsertCommitFile);

    foreach($bucketFileList as $file)
    {
        $stmt_2->bindParam(':commit_id', $commitId, SQLITE3_INTEGER);
        $stmt_2->bindParam(':bucket_file_id', $file['bucket_file_id'], SQLITE3_INTEGER);
        $stmt_2->execute();
    }

}

function GetAllProjectFiles2($projectId)
{
    global $db;

    // Get latest project version
    $sql_GetProjectVersion = 
        'SELECT latest_version 
         FROM Project 
         WHERE project_id = :project_id';
    
    $stmt_1 = $db->prepare($sql_GetProjectVersion);
    $stmt_1->bindValue(':project_id', $projectId, SQLITE3_INTEGER);
    $result = $stmt_1->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$result || empty($result['latest_version'])) {
        return []; // Return empty if no version found
    }
    
    $projectVersion = $result['latest_version'];

    // Get latest commit ID
    $sql_GetLastCommit = 
        'SELECT commit_id
         FROM Project_Commit
         WHERE project_version = :project_version';
    
    $stmt_2 = $db->prepare($sql_GetLastCommit);
    $stmt_2->bindValue(':project_version', $projectVersion, SQLITE3_INTEGER);
    $result = $stmt_2->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$result || empty($result['commit_id'])) {
        return []; // Return empty if no commit found
    }

    $commitId = $result['commit_id'];

    // Get list of bucket files associated with the commit
    $sql_GetCommitFiles =
        'SELECT bucket_file_id
         FROM Commit_File
         WHERE commit_id = :commit_id';
    
    $stmt_3 = $db->prepare($sql_GetCommitFiles);
    $stmt_3->bindValue(':commit_id', $commitId, SQLITE3_INTEGER);
    $result = $stmt_3->execute();

    $commitedFileList = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $commitedFileList[] = $row;
    }

    if (empty($commitedFileList)) {
        return []; // Return empty if no files are found
    }

    // Get file details from Bucket_File and Project_File
    $sql_GetBucketFile =
        "SELECT pf.project_file_id, pf.file_name, bf.object_id
         FROM Bucket_File bf
         JOIN Project_File pf ON pf.project_file_id = bf.project_file_id
         WHERE bf.bucket_file_id = :bucket_file_id";
    
    $stmt_4 = $db->prepare($sql_GetBucketFile);
    $bucketFileList = [];

    foreach ($commitedFileList as $bucketFile) {
        $stmt_4->bindValue(':bucket_file_id', $bucketFile['bucket_file_id'], SQLITE3_INTEGER);
        $result = $stmt_4->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result) { // Ensure data exists before adding
            // Add file details to the result list
            $bucketFileList[] = [
                'project_file_id' => $result['project_file_id'],
                'file_name' => $result['file_name'],
                'object_id' => $result['object_id']
            ];
        }
    }

    return $bucketFileList;
}




?>