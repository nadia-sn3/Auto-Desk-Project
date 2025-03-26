<?php

include_once("..\\..\\View\\Database_Connection.php");

include_once('..\\..\\Bussiness_Logic\\Function\\Upload.php');

$projectFileList = GetAllProjectFiles($_GET['projectId']);

return $projectFileList;

?>