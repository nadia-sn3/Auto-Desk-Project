<?php

include_once("..\\..\\View\\Database_Connection.php");

$sql = 
'SELECT * FROM Project_Commit 
WHERE project_id = :project_id;';

$stmt = $db->prepare($sql);
$stmt->bindParam(':project_id', $_GET['projectId'], SQLITE3_INTEGER);
$result= $stmt->execute();
$arrayResult = [];
while($row=$result->fetchArray()){
    $arrayResult [] = $row;
}

return $arrayResult;

?>