<?php
require '../../../db/connection.php';

try {
    if (!isset($_GET['project_id']) || empty($_GET['project_id'])) {
        echo json_encode(['error' => 'Project ID is missing']);
        exit;
    }

    $project_id = $_GET['project_id']; 

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT urn FROM project_files WHERE project_id = :project_id");
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && isset($result['urn'])) {
        echo json_encode(['urn' => $result['urn']]); 
    } else {
        echo json_encode(['urn' => null]); 
    }

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
