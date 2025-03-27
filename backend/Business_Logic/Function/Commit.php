<?php

if(isset($_POST['submit']))
{
    require_once("..\\..\\Bussiness_Logic\\Function\\Access_Token.php");
    require_once("..\\..\\Bussiness_Logic\\Function\\Upload.php");
    require_once("..\\..\\Bussiness_Logic\\Function\\Helper.php");

    $projectId = $_GET['projectId'];
    $entryPoint = GetProjectVersion($projectId) + 1;

    $accessToken = GetAccessToken()["access_token"];
    $bucketName = "aryan_bucket";

    $URL_LifeTime = 10;
    $chunkSize = 50 * 1024 * 1024;

    $newlyAddFiles = [];
    $file = $_FILES['file'];
    var_dump($file);
    $numberOfFileUploaded = count($file['name']);

    for($fileIndex = 0; $fileIndex < $numberOfFileUploaded; $fileIndex++)
    {
        $fileFullName = $file['name'][$fileIndex];
        $fileTmpName = $file['tmp_name'][$fileIndex];
        
        preg_match('/^(?<fileName>.*?)(?:\.(?<extension>[a-zA-Z0-9]+))?$/', $fileFullName, $matches);
        $fileName = $matches['fileName'];
        $extension = isset($matches['extension']) ? $matches['extension'] : '';
        
        if(CheckIfFileExist($fileFullName, $projectId))
        {
            AdjustFileVersion($fileFullName, $projectId);
            $latestVersion = GetLatestFileVersion($fileFullName, $projectId);
        }
        else
        {   
            $newlyAddFiles [] = $fileFullName;
            InsertProjectFile($fileFullName, $projectId, $entryPoint);
            $latestVersion = 1;
        }

        $objectKey = $projectId.'_'.$fileName.'_V'.$latestVersion;
        
        $fileSize = filesize($fileTmpName);
        $totalParts = ceil($fileSize / $chunkSize);
        
        $signedURL = GetSignedURL($accessToken, $bucketName , $URL_LifeTime, $objectKey, $totalParts);
        
        $uploadKey = $signedURL["uploadKey"];
        $signedURLs = $signedURL["urls"];
        
        UploadFiles($signedURLs, $fileTmpName, $chunkSize);
        
        $finalizeResult = FinalizeUpload($accessToken, $bucketName, $objectKey, $uploadKey);
        
        $objectId = $finalizeResult['objectId'];
        $objectKey = $finalizeResult['objectKey'];
        
        InsertBucketFile($fileFullName, $projectId, $objectId, $objectKey, $entryPoint);
    }    

    $projectFileList_1 = GetAllProjectFiles($projectId);

    $projectFileList_2 = GetProjectFilesInList($newlyAddFiles, $projectId);

    $projectFileList = array_merge( $projectFileList_1,  $projectFileList_2);

    InsertNewCommit($projectId);

    $commitId = GetLastCommitId($projectId);

    InsertFilesForCommit($projectFileList, $commitId);

    header("Location: Commit_Result.php?commitStatus=true&projectId=$projectId");
}
?>