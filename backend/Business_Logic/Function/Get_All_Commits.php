<?php
require_once __DIR__ . '/../../../db/connection.php';
var_dump($pdo);
if (!file_exists(__DIR__ . '/../../../db/connection.php')) {
    die("Error: connection.php file not found.");
}

if (!isset($pdo)) {
    die("Database connection failed: \$pdo is not set.");
}

$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

if ($project_id === 0) {
    die("Invalid project ID.");
}

$sql = "SELECT * FROM project_commit WHERE project_id = ?;";
$stmt = $pdo->prepare($sql);
$stmt->execute([$project_id]);

$commits = $stmt->fetchAll(PDO::FETCH_ASSOC);

var_dump($commits);

?>
