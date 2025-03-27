<?php
require_once("config.php");
require_once("functions.php");
require_once("upload.php");
require_once("getAccessToken.php");
require_once("uploaddatabase.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Getting project ID and commit message from the form
    $project_id = $_GET['project_id']; 
    $commit_message = $_POST['commitMessage']; // Commit message
    $created_by = 1; // Example user ID
    
    if (isset($_POST['commitMessage'])) {
        $commit_message = $_POST['commitMessage'];
    } else {
        echo "Error: commitMessage is missing.";
        exit;
    }
    // Get project version and increment it
    $entryPoint = GetProjectVersion($project_id) + 1;
    
    // Get access token for Autodesk API
    $access_token = getAccessToken($client_id, $client_secret);
    

    // Constants
    $URL_LifeTime = 10; // URL lifetime
    $chunk_size = 10 * 1024 * 1024; // 10MB per chunk

    // Array to store newly added files
    $newlyAddFiles = [];
    
    // Check if files are uploaded
    if (isset($_FILES['file-upload']) && !empty($_FILES['file-upload']['name'][0])) {
        $file = $_FILES['file-upload'];
        $numberOfFileUploaded = count($file['name']);

        for ($fileIndex = 0; $fileIndex < $numberOfFileUploaded; $fileIndex++) {

            $fileFullName = $file['name'][$fileIndex];
            $fileTmpName = $file['tmp_name'][$fileIndex];

            // Extract file name and extension
            preg_match('/^(?<fileName>.*?)(?:\.(?<extension>[a-zA-Z0-9]+))?$/', $fileFullName, $matches);
            $fileName = $matches['fileName']; 
            $fileType = $matches['extension']; 
            $extension = isset($matches['extension']) ? $matches['extension'] : '';

            // Check if the file already exists in the project
            if (CheckIfFileExist($fileFullName, $project_id)) {
                AdjustFileVersion($fileFullName, $project_id);
                $latestVersion = GetLatestFileVersion($fileFullName, $project_id);
            } else {
                $newlyAddFiles[] = $fileFullName;
                InsertProjectFile($fileFullName, $fileType, $project_id, $entryPoint);
                $latestVersion = 1; // New file
            }

            // // Object key format: projectId_fileName_Version
             $objectKey = $fileFullName;

            // Get file size and calculate total parts for chunk upload
            $file_size = filesize($fileTmpName);
            $total_parts = ceil($file_size / $chunk_size);

            // Get signed URL for file upload
            $signedURL = createUploadSession($access_token, $bucket_key, $objectKey, $total_parts);
            $uploadKey = $signedURL["uploadKey"];
            $signedURLs = $signedURL["urls"];

            // Upload the file in chunks
            uploadFileToBucket($signedURLs, $fileTmpName, $chunk_size);

            // Finalize the upload
            $finalizeResult = completeUpload($access_token, $bucket_key, $objectKey, $uploadKey);

            // Check if upload is successful
            if (isset($finalizeResult['objectId'])) {
                $objectId = $finalizeResult['objectId'];
                $objectKey = $finalizeResult['objectKey'];
                $urn_source_file = base64UrlEncodeUnpadded($objectId);

                // Save file metadata in the database
                InsertBucketFile($fileFullName, $project_id, $urn_source_file, $objectKey, $entryPoint);
            } else {
                echo "Error: URN not found for $fileFullName.\n";
            }
        }

        $projectFileList_1 = GetAllProjectFiles($project_id);

        $projectFileList_2 = GetProjectFilesInList($newlyAddFiles, $project_id);

        $projectFileList = array_merge( $projectFileList_1,  $projectFileList_2);

        InsertNewCommit($project_id, $commit_message);

        $commitId = GetLastCommitId($project_id);

        InsertFilesForCommit($projectFileList, $commitId);


        // Redirect to the commit result page
        header("Location: file-list.php?commitStatus=true&project_id=$project_id");
        exit;
    } else {
        echo "Error: No files were uploaded.";
    }
}




// Function to get file extension
function getFileExtension($fileName) {
    return pathinfo($fileName, PATHINFO_EXTENSION);
}

// Function to get file type based on extension
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
