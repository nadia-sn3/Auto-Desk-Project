<?php

include_once("..\\..\\Bussiness_Logic\\Function\\Rollback.php");
include_once("..\\..\\Bussiness_Logic\\Function\\Access_Token.php");

var_dump(isset($_GET['commitId']));

if(isset($_GET['commitId']))
{
    $commitId = $_GET['commitId'];
    $projectId = $_GET['projectId'];

    DeleteCommitAfter($commitId, $projectId);

    $accessToken = GetAccessToken()["access_token"];
    $bucketName = "aryan_bucket";

    DeleteExcessiveFiles($projectId, $accessToken, $bucketName);

    header("location: Commits_List.php?projectId=".$_GET['projectId']);
}

?>