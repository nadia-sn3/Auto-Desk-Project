<?php
require '../../../db/connection.php';

try {
    // Check if the project_id is passed
    if (!isset($_GET['project_id']) || empty($_GET['project_id'])) {
        echo json_encode(['error' => 'Project ID is missing']);
        exit;
    }

    $project_id = $_GET['project_id'];  // Getting project_id from query parameters

    // Database connection
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare SQL query
    $stmt = $pdo->prepare("SELECT urn FROM project_files WHERE project_id = :project_id");
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Fetch result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && isset($result['urn'])) {
        echo json_encode(['urn' => $result['urn']]); // Return urn if found
    } else {
        echo json_encode(['urn' => null]); // No URN found for the project_id
    }

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
