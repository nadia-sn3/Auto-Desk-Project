<?php

include_once("..\\..\\View\\Database_Connection.php");

$sql = 
"SELECT latest_version 
FROM Project_File 
WHERE file_name = :file_name;";    

$stmt = $db->prepare($sql);
$stmt->bindParam(':file_name', $fileFullName, SQLITE3_INTEGER);
$result= $stmt->execute();
while($row=$result->fetchArray()){
    $arrayResult [] = $row;
}

if(count($arrayResult)==0)
{
    $latestVersion = 0;
}
else
{
    $latestVersion = $arrayResult[0];
}

return $latestVersion;

?>