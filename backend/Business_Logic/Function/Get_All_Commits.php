<?php
require __DIR__ . '/../../../db/connection.php'; 
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;

if ($project_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT * 
            FROM Project_Commit 
            WHERE project_id = :project_id
            ORDER BY commit_date DESC
        ");
        $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->execute();
        $commits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($commits as &$commit) {
            $stmt = $pdo->prepare("
                SELECT 
                    i.issues_id as id,
                    i.file,
                    i.description,
                    LOWER(REPLACE(i.status, ' ', '_')) as status,
                    CONCAT(u.first_name, ' ', u.last_name) as raised_by,
                    i.date
                FROM issues i
                JOIN users u ON i.raised_by = u.user_id
                WHERE i.version_id = :commit_id AND i.project_id = :project_id
            ");
            $stmt->execute([
                ':commit_id' => $commit['commit_id'],
                ':project_id' => $project_id
            ]);
            $commit['issues'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode($commits);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Project ID is missing']);
}
?>