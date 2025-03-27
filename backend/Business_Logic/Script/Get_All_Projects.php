<?php

require_once __DIR__ . '/../../../db/connection.php'; 

$sql = 'SELECT * FROM Project';    
// $sql = 
// 'SELECT p.project_id, p.project_name, p.description,
// p.created_by, p.latest_version, p.thumbnail_path
// FROM project_members pm 
// JOIN project p ON pm.project_id = p.project_id
// WHERE pm.user_id = ?;';    
$stmt = $pdo->prepare($sql);
$stmt->execute();
$arrayResult= $stmt->fetchAll(PDO::FETCH_ASSOC);

return $arrayResult;

?>