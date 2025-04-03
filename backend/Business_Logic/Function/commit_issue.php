<?php
header('Content-Type: application/json');
$commit_id = isset($_GET['commit_id']) ? $_GET['commit_id'] : null;
if ($commit_id === null) {
    echo json_encode(['error' => 'Commit ID is required']);
    exit;
}
 $sql = "SELECT file, issue, raised_by, raised_on FROM commit_issues WHERE commit_id = :commit_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':commit_id', $commit_id, PDO::PARAM_INT);
    $stmt->execute();
    $commit_issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($commit_issues) {
        echo json_encode($commit_issues);
    } else {
        echo json_encode(['error' => 'No issues found for this commit']);
    }

?>
