<?php

if(isset($_POST['submit']))
{
    //require_once("..\\Function\\Access_Token.php");
    require_once("..\\..\\Bussiness_Logic\\Function\\Access_Token.php");
    require_once("..\\..\\Bussiness_Logic\\Function\\Upload.php");
    require_once("..\\..\\Bussiness_Logic\\Function\\Helper.php");

    $projectId = $_GET['projectId'];
    $entryPoint = GetProjectVersion($projectId) + 1;

    $file = $_FILES['file'];
    $fileFullName = $file['name'];
    $fileTmpName = $file['tmp_name'];

    $accessToken = GetAccessToken()["access_token"];
    $bucketName = "aryan_bucket";
    
    $URL_LifeTime = 10;
    
    preg_match('/^(?<fileName>.*?)(?:\.(?<extension>[a-zA-Z0-9]+))?$/', $fileFullName, $matches);
    $fileName = $matches['fileName'];
    $extension = isset($matches['extension']) ? $matches['extension'] : '';
    

    $newlyAddFiles = [];
    if(CheckIfFileExist($fileFullName, $projectId))
    {
        AdjustFileVersion($fileFullName, $projectId);
        $latestVersion = GetLatestFileVersion($fileFullName, $projectId);
    }
    else
    {   
        $newlyAddFiles[0] = $fileFullName;
        InsertProjectFile($fileFullName, $projectId, $entryPoint);
        $latestVersion = 1;
    }

    $objectKey = $projectId.'_'.$fileName.'_V'.$latestVersion;
    
    $signedURL = GetSignedURL($accessToken, $bucketName , $URL_LifeTime, $objectKey);
    
    $uploadKey = $signedURL["uploadKey"];
    $signedURLs = $signedURL["urls"][0];
    
    UploadFiles($signedURLs, $fileTmpName);
    
    $finalizeResult = FinalizeUpload($accessToken, $bucketName, $objectKey, $uploadKey);
    
    $objectId = $finalizeResult['objectId'];
    $objectKey = $finalizeResult['objectKey'];
    
    
    InsertBucketFile($fileFullName, $projectId, $objectId, $objectKey, $entryPoint);

    $projectFileList_1 = GetAllProjectFiles($projectId);

    $projectFileList_2 = GetProjectFilesInList($newlyAddFiles, $projectId);

    $projectFileList = array_merge( $projectFileList_1,  $projectFileList_2);

    InsertNewCommit($projectId);

    $commitId = GetLastCommitId($projectId);

    InsertFilesForCommit($projectFileList, $commitId);

    header("Location: Commit_Result.php?commitStatus=true&projectId=$projectId");
}
?>