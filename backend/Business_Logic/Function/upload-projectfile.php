<?php
require_once("config.php");
require_once("functions.php");
require_once("upload.php");
require_once("getAccessToken.php");
require_once("uploaddatabase.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $project_id = $_GET['project_id']; 
    $commit_message = $_POST['commitMessage']; 
    $created_by = 1;
    
    if (isset($_POST['commitMessage'])) {
        $commit_message = $_POST['commitMessage'];
    } else {
        echo "Error: commitMessage is missing.";
        exit;
    }
    $entryPoint = GetProjectVersion($project_id) + 1;
    
    $access_token = getAccessToken($client_id, $client_secret);
    

    $URL_LifeTime = 10; 
    $chunk_size = 10 * 1024 * 1024; 

    $newlyAddFiles = [];
    
    if (isset($_FILES['file-upload']) && !empty($_FILES['file-upload']['name'][0])) {
        $file = $_FILES['file-upload'];
        $numberOfFileUploaded = count($file['name']);

        for ($fileIndex = 0; $fileIndex < $numberOfFileUploaded; $fileIndex++) {

            $fileFullName = $file['name'][$fileIndex];
            $fileTmpName = $file['tmp_name'][$fileIndex];

            preg_match('/^(?<fileName>.*?)(?:\.(?<extension>[a-zA-Z0-9]+))?$/', $fileFullName, $matches);
            $fileName = $matches['fileName']; 
            $fileType = $matches['extension']; 
            $extension = isset($matches['extension']) ? $matches['extension'] : '';

            if (CheckIfFileExist($fileFullName, $project_id)) {
                AdjustFileVersion($fileFullName, $project_id);
                $latestVersion = GetLatestFileVersion($fileFullName, $project_id);
            } else {
                $newlyAddFiles[] = $fileFullName;
                InsertProjectFile($fileFullName, $fileType, $project_id, $entryPoint);
                $latestVersion = 1; 
            }

            $objectKeyForUpload = $fileFullName;

            $objectKeyForVersionControl = $project_id . '_' . $fileName . '_V' . $latestVersion;

            $file_size = filesize($fileTmpName);
            $total_parts = ceil($file_size / $chunk_size);

            $signedURL = createUploadSession($access_token, $bucket_key, $objectKeyForUpload, $total_parts);
            $uploadKey = $signedURL["uploadKey"];
            $signedURLs = $signedURL["urls"];

            uploadFileToBucket($signedURLs, $fileTmpName, $chunk_size);

            
            $finalizeResult = completeUpload($access_token, $bucket_key, $objectKeyForUpload, $uploadKey);

            if (isset($finalizeResult['objectId'])) {
                $objectId = $finalizeResult['objectId'];
                $objectKey = $finalizeResult['objectKey'];
                $urn_source_file = base64UrlEncodeUnpadded($objectId);
                InsertBucketFile($fileFullName, $project_id, $urn_source_file, $objectKeyForVersionControl, $entryPoint);
            } else {
                echo "Error: URN not found for $fileFullName.\n";
            }
            require_once("Generate_Thumbnail.php");
            
        }


        $projectFileList_1 = GetAllProjectFiles($project_id);

        $projectFileList_2 = GetProjectFilesInList($newlyAddFiles, $project_id);

        $projectFileList = array_merge( $projectFileList_1,  $projectFileList_2);

        InsertNewCommit($project_id, $commit_message);

        $commitId = GetLastCommitId($project_id);

        InsertFilesForCommit($projectFileList, $commitId);


        header("Location: file-list.php?commitStatus=true&project_id=$project_id");
        exit;
    } else {
        echo "Error: No files were uploaded.";
    }
}




function getFileExtension($fileName) {
    return pathinfo($fileName, PATHINFO_EXTENSION);
}

function getFileType($extension) {
    switch (strtolower($extension)) {
        case 'pdf':
            return 'pdf';
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return 'image';
        case 'obj':
            return '3d_model';
        case 'doc':
        case 'docx':
            return 'document';
        default:
            return 'unknown';
    }
}



?>