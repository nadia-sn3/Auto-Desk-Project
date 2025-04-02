<?php
// Set the header to JSON
header('Content-Type: application/json');

// Retrieve the commit ID from the GET parameter
$commit_id = isset($_GET['commit_id']) ? $_GET['commit_id'] : null;

// Check if commit_id is provided
if ($commit_id === null) {
    echo json_encode(['error' => 'Commit ID is required']);
    exit;
}
 $sql = "SELECT file, issue, raised_by, raised_on FROM commit_issues WHERE commit_id = :commit_id";
    $stmt = $pdo->prepare($sql);

    // Bind the commit_id parameter
    $stmt->bindParam(':commit_id', $commit_id, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Fetch the results
    $commit_issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if there are any issues
    if ($commit_issues) {
        echo json_encode($commit_issues);
    } else {
        echo json_encode(['error' => 'No issues found for this commit']);
    }

?>
