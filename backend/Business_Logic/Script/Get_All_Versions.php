<?php

include_once("..\\..\\View\\Database_Connection.php");

$sql = 
"SELECT Project_File.file_name, Bucket_File.object_id, Bucket_File.object_key
FROM Bucket_File
JOIN Project_File ON Project_File.project_file_id = Bucket_File.project_file_id
WHERE Bucket_File.project_file_id = :project_file_id;";    

$stmt = $db->prepare($sql);
$stmt->bindParam(':project_file_id', $_GET['fileId'], SQLITE3_INTEGER);
$result= $stmt->execute();
$arrayResult = [];
while($row=$result->fetchArray()){
    $arrayResult [] = $row;
}

return $arrayResult;

?>