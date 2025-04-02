<?php

require __DIR__ . '/../../../db/connection.php'; 
// Get the project_id from the GET request
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;

if ($project_id) {
    // Assuming you have a valid PDO connection in $pdo
    try {
        // Prepare and execute SQL query
        $stmt = $pdo->prepare("SELECT * FROM Project_Commit WHERE project_id = :project_id");
        $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch all results as an associative array
        $commits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the results as JSON
        echo json_encode($commits);
    } catch (PDOException $e) {
        // Handle query error
        echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    }
} else {
    // If no project_id is provided
    echo json_encode(['error' => 'Project ID is missing']);
}
?>
