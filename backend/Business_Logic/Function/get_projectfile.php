<?php
header('Content-Type: application/json'); // Ensure JSON response

$response = ["success" => false, "message" => "Unknown error"];
require_once __DIR__ . '/../../../db/connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    ob_clean();
    // Get data from the POST request
    $version_id = isset($_POST['version_id']) ? $_POST['version_id'] : '';
    $project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
    $file = isset($_POST['file']) ? $_POST['file'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';

    // Validate data
    if (empty($version_id) || empty($project_id) || empty($file) || empty($description)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    // Prepare the SQL query
    $query = "INSERT INTO issues (version_id, project_id, file, description) 
              VALUES (:version_id, :project_id, :file, :description)";

    // Prepare the statement
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':version_id', $version_id);
    $stmt->bindParam(':project_id', $project_id);
    $stmt->bindParam(':file', $file);
    $stmt->bindParam(':description', $description);

    try {
        $stmt->execute();
        // Send JSON response after successful insertion
        echo json_encode(["success" => true]);
        exit;
    } catch (PDOException $e) {
        // Handle errors and send a JSON response with error message
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}

// getProjectFile.php
// if (isset($_GET['project_id']) && isset($_GET['commit_id'])) {
//     $project_id = $_GET['project_id'];
//     $commit_id = $_GET['commit_id'];
//     $query = "SELECT file_name, version FROM project_files WHERE project_id = ? AND commit_id = ?";
//     $stmt = $pdo->prepare($query);
//     $stmt->execute([$project_id, $commit_id]);
//     $result = $stmt->fetch(PDO::FETCH_ASSOC);

//     if ($result) {
//         echo json_encode([
//             'success' => true,
//             'file_name' => $result['file_name'],
//             'version' => $result['version']
//         ]);
//     } else {
//         echo json_encode(['success' => false]);
//     }
// } else {
//     echo json_encode(['success' => false, 'message' => 'Missing parameters']);
// }
?>
