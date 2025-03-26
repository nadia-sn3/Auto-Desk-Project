<?php
require_once("config.php");
require_once("functions.php");
require_once("upload.php");
require_once("getAccessToken.php");
require_once("../../../db/connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $project_id = $_POST['project_id']; // Get the project ID from the form
    $commit_message = $_POST['commitMessage']; // Get the commit message from the form
    $created_by = 1;

            // Step 2: Check if a file is uploaded
            if (isset($_FILES['file-upload']) && !empty($_FILES['file-upload']['name'][0]))  {
                $file = $_FILES['file-upload'];
                // Loop through each file in the upload
                foreach ($_FILES['file-upload']['name'] as $index => $fileName) {
                    $fileTmpName = $_FILES['file-upload']['tmp_name'][$index];
                    if (!$fileTmpName || !file_exists($fileTmpName)) {
                        die("Error: Temporary file for $fileName is not valid.");
                    }

                    // Check if the temporary file path is valid
                    if (empty($fileTmpName)) {
                        die("Error: File path is empty for $fileName.\n");
                    } else {
                        echo "Temporary file path: $fileTmpName for file: $fileName\n";  // Debugging message
                    }

                    // Get file extension
                    $fileExtension = getFileExtension($fileName);
                    
                    // Check file type
                    switch (strtolower($fileExtension)) {
                        case 'pdf':
                            $fileType = 'pdf';
                            break;
                        case 'jpg':
                        case 'jpeg':
                        case 'png':
                        case 'gif':
                            $fileType = 'image';
                            break;
                        case 'obj':
                            $fileType = '3d_model';
                            break;
                        case 'doc':
                        case 'docx':
                            $fileType = 'document';
                            break;
                        default:
                            $fileType = 'unknown';
                            break;
                    }

                    // Process file based on type
                    if ($fileType === '3d_model') {
                        // Step 3: Upload 3D model to Autodesk bucket
                        $file_size = filesize($fileTmpName);
                        $chunk_size = 10 * 1024 * 1024; // 10MB chunk size
                        $total_parts = ceil($file_size / $chunk_size);

                        // Get access token
                        $access_token = getAccessToken($client_id, $client_secret);

                        // Get signed URL for file upload
                        $signedURL = createUploadSession($access_token, $bucket_key, $fileName, $total_parts);
                        $uploadKey = $signedURL["uploadKey"];
                        $signedURLs = $signedURL["urls"];

                        if (count($signedURLs) < $total_parts) {
                            die("Error: Not enough signed URLs provided for the file parts for $fileName.\n");
                        }

                        // Upload the file in chunks
                        uploadFileToBucket($signedURLs, $fileTmpName);

                        // Complete the upload
                        $finalizeResult = completeUpload($access_token, $bucket_key, $fileName, $uploadKey);

                        // Check if upload is successful
                        if (isset($finalizeResult['objectId'])) {
                            $urn = $finalizeResult['objectId'];
                            $urn_source_file = base64UrlEncodeUnpadded($urn);

                            // Step 4: Save file metadata in the database
                            saveFileMetadataToDatabase($fileName, $urn_source_file, $project_id, $fileType); // Save file type
                            echo "URN for $fileName: " . $urn_source_file . "\n";
                        } else {
                            echo "URN not found for $fileName in the response.\n";
                        }
                    } else {
                        // Step 5: Save non-3D files (images, PDFs, etc.) locally
                        $uploadDir = '../Uploaded_Process/uploads/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);  // Create the upload directory if it doesn't exist
                        }

                        $newFilePath = $uploadDir . basename($fileName);

                        if (move_uploaded_file($fileTmpName, $newFilePath)) {
                            // Step 6: Save metadata for local files in the database
                            saveFileMetadataToDatabase($fileName, $newFilePath, $project_id, $fileType); // Save file path
                            saveCommitMessage($project_id, $commit_message, $created_by); // Save commit message

                            echo "File $fileName saved locally at $newFilePath\n";
                        } else {
                            echo "Error saving file $fileName locally.\n";
                        }
                    }
                }
                // Redirect to file list page after saving metadata
                header("Location: ../../../file-list.php?project_id=$project_id&uploadStatus=true");
                exit;
            
    } else {
        echo "Missing required fields.";
    }
}


function saveFileMetadataToDatabase($file_name, $file_path, $project_id, $file_type) {
    global $pdo;

    // Debugging output
    echo "Inserting data into project_files table...<br>";
    echo "File Name: $file_name, Path: $file_path, Project ID: $project_id, File Type: $file_type<br>";

    $created_at = date('Y-m-d H:i:s');

    // Prepare the SQL query for inserting data
    $sql = "INSERT INTO project_files (file_name, urn, project_id, file_type, created_at) 
            VALUES (:file_name, :urn, :project_id, :file_type, :created_at)";
    $stmt = $pdo->prepare($sql);

    // Bind the values to the prepared statement
    $stmt->bindParam(':file_name', $file_name);
    $stmt->bindParam(':urn', $file_path); // For 3D models, this will be the URN, for others, the file path
    $stmt->bindParam(':project_id', $project_id);
    $stmt->bindParam(':file_type', $file_type);  
    $stmt->bindParam(':created_at', $created_at);

    try {
        // Execute the statement
        $stmt->execute();
        echo "Data inserted successfully into project_files table!<br>"; // Debugging output
    } catch (PDOException $e) {
        // If there's an error, catch it and display it
        echo "Error inserting data into project_files: " . $e->getMessage() . "<br>";
    }
}


// Function to save commit message into commit table
function saveCommitMessage($project_id, $commit_message, $created_by) {
    global $pdo;

    $committed_at = date('Y-m-d H:i:s');

    $sql = "INSERT INTO commit (project_id, commit_message, committed_by, committed_at) 
            VALUES (:project_id, :commit_message, :committed_by, :committed_at)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':project_id', $project_id);
    $stmt->bindParam(':commit_message', $commit_message);
    $stmt->bindParam(':committed_by', $created_by); // Hardcoded as 1 (you can adjust based on your session)
    $stmt->bindParam(':committed_at', $committed_at);

    try {
        $stmt->execute();
        echo "Commit message inserted successfully into commit table!\n"; // Debugging output
    } catch (PDOException $e) {
        echo "Error inserting commit message into commit: " . $e->getMessage() . "\n";
    }
}




// Function to get file extension
function getFileExtension($fileName) {
    return pathinfo($fileName, PATHINFO_EXTENSION);
}



?>
