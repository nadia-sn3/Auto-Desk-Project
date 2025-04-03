<?php

require __DIR__ . '/../../../db/connection.php'; 
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;

if ($project_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM Project_Commit WHERE project_id = :project_id");
        $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->execute();
        $commits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($commits);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Project ID is missing']);
}
?>
