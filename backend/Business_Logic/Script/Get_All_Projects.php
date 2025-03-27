<?php

include_once("..\\..\\View\\Database_Connection.php");

$sql = 'SELECT * FROM Project';    
$stmt = $db->prepare($sql);
$result= $stmt->execute();
$arrayResult = [];
while($row=$result->fetchArray()){
    $arrayResult [] = $row;
}

return $arrayResult;

?>