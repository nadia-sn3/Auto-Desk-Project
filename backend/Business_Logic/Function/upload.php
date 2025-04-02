<?php

function createUploadSession($access_token, $bucket_key, $file_name, $total_parts) 
{
    $url = "https://developer.api.autodesk.com/oss/v2/buckets/$bucket_key/objects/$file_name/signeds3upload?minutesExpiration=10&parts=$total_parts";

    echo "Requesting URL: $url\n"; 
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $access_token"
    ]);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        die('Error: ' . $error);
    }

    // echo "<br><br>Raw response: $response\n";
    $data = json_decode($response, true);

    if (isset($data['uploadKey']) && isset($data['urls']) && is_array($data['urls']) && !empty($data['urls'])) {
        $urls = $data['urls'];
        $total_signed_parts = count($urls);

        echo "Total parts requested: $total_parts\n";
        echo "Number of signed URLs provided: $total_signed_parts\n";

        if ($total_signed_parts != $total_parts) {
            die("Error: Number of signed URLs does not match total parts.");
        }

        return $data;
    } else {
        die("Error: Unable to create upload session or missing signed URLs.");
    }
}
    

function uploadFileTobucket($signed_urls, $file_path) {
    
    $file = fopen($file_path, 'r'); // Open the file for reading
    $part_number = 0; // Initialize part number
    $file_size = filesize($file_path);
    $chunk_size = 1024 * 1024 * 10; // 10MB per chunk
    $total_parts = ceil($file_size / $chunk_size); // Calculate total parts

    // Read the file in chunks and upload each chunk
    for ($part_number = 0; $part_number < $total_parts; $part_number++) {
        // Read the next chunk
        $chunk = fread($file, $chunk_size);
        $chunk_length = strlen($chunk); // Get the length of the chunk
        $signed_url = $signed_urls[$part_number]; // Get the signed URL for the current part

        $headers = [
            "Content-Type: application/octet-stream", // MIME type for binary data
            "Content-Length: $chunk_length", // Set the content length for the chunk
            "Expect: 100-continue" // Expect the server to acknowledge the request before sending the body
        ];

        $ch = curl_init(); // Initialize the cURL session
        curl_setopt_array($ch, [
            CURLOPT_URL => $signed_url, // Set the URL to the signed URL
            CURLOPT_CUSTOMREQUEST => "PUT", // Use PUT method for uploading
            CURLOPT_HTTPHEADER => $headers, // Set headers
            CURLOPT_RETURNTRANSFER => true, // Return the response as a string
            CURLOPT_POSTFIELDS => $chunk // Send the chunk in the request body
        ]);

        $response = curl_exec($ch); // Execute the cURL request
        $status_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE); // Get the HTTP response code

        // Error handling for cURL execution
        if ($response === false) {
            echo 'Curl error:' . curl_error($ch) . '<br>';
            fclose($file); // Close the file
            curl_close($ch); // Close the cURL session
            return;
        }

        // Check if the upload was successful (status code 200 means success)
        if ($status_code != 200) {
            echo "Error uploading chunk $part_number. Status code: $status_code\n";
            curl_close($ch); // Close the cURL session
            return;
        }

        curl_close($ch); // Close the cURL session after successful upload
        echo "Chunk $part_number uploaded successfully\n";
    }

    fclose($file); // Close the file after the upload is complete
    echo "File uploaded successfully in $total_parts chunks.\n";

}

//set_time_limit(int $seconds):bool
function completeUpload($access_token, $bucket_key, $file_name, $upload_key) {
    $url = "https://developer.api.autodesk.com/oss/v2/buckets/$bucket_key/objects/$file_name/signeds3upload";
    
    $data = json_encode([
        "uploadKey" => $upload_key
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $access_token",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_status == 200) {
        echo "Upload completed successfully.\n";
        return json_decode($response, true);
    } else {
        die("Error completing upload: HTTP Status $http_status\n Response: $response");
    }
}


function CheckIfFileExist($fileName, $projectId)
{
    global $pdo;
    
    $sql = "SELECT * FROM Project_File WHERE file_name = :file_name AND project_id = :project_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':file_name', $fileName, PDO::PARAM_STR);
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->execute();
    
    return ($stmt->rowCount() == 1);
}

function AdjustFileVersion($fileName, $projectId, $adjustmentValue = 1)
{
    global $pdo;
    
    $sql = 
    "UPDATE Project_File 
    SET latest_version = latest_version + :adjustmentValue 
    WHERE file_name = :file_name 
    AND project_id = :project_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':adjustmentValue', $adjustmentValue, PDO::PARAM_INT);
    $stmt->bindParam(':file_name', $fileName, PDO::PARAM_STR);
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->execute();
}

function InsertProjectFile($fileName, $fileType, $projectId, $entryPoint)
{
    global $pdo;
    
    $latest_version = 1;

    $sql = "INSERT INTO Project_File(project_id, file_name, latest_version, first_added_at_version, file_type) VALUES (:project_id, :file_name, :latest_version, :first_added_at_version, :file_type)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->bindParam(':file_name', $fileName, PDO::PARAM_STR);
    $stmt->bindParam(':latest_version', $latest_version, PDO::PARAM_INT);
    $stmt->bindParam(':first_added_at_version', $entryPoint, PDO::PARAM_INT);
    $stmt->bindParam(':file_type', $fileType, PDO::PARAM_STR);
    $stmt->execute();
}

function InsertBucketFile($fileName, $projectId, $objectId, $objectKey, $entryPoint)
{
    global $pdo;
    
    $sql = "SELECT project_file_id, MAX(latest_version) as latest_version FROM Project_File WHERE file_name = :file_name AND project_id = :project_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->bindParam(':file_name', $fileName, PDO::PARAM_STR);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        return; // No matching record found
    }
    
    // $sql = "INSERT INTO Bucket_File(project_file_id, file_version, object_id, object_key, first_added_at_version) VALUES (:project_file_id, :file_version, :object_id, :object_key, :first_added_at_version)";
    // $stmt = $pdo->prepare($sql);
    // $stmt->bindParam(':project_file_id', $result['project_file_id'], PDO::PARAM_INT);
    // $stmt->bindParam(':file_version', $result['latest_version'], PDO::PARAM_INT);
    // $stmt->bindParam(':object_id', $objectId, PDO::PARAM_STR);
    // $stmt->bindParam(':object_key', $objectKey, PDO::PARAM_STR);
    // $stmt->bindParam(':first_added_at_version', $entryPoint, PDO::PARAM_INT);
    // $stmt->execute();  
    
    $sql = "INSERT INTO Bucket_File(project_file_id, file_version, object_id, object_key, first_added_at_version) 
            VALUES (:project_file_id, :file_version, :object_id, :object_key, :first_added_at_version)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':project_file_id', $result['project_file_id'], PDO::PARAM_INT);
    $stmt->bindParam(':file_version', $result['latest_version'], PDO::PARAM_INT);
    $stmt->bindParam(':object_id', $objectId, PDO::PARAM_STR);
    $stmt->bindParam(':object_key', $objectKey, PDO::PARAM_STR);
    $stmt->bindParam(':first_added_at_version', $entryPoint, PDO::PARAM_INT);
    $stmt->execute(); 

}

// function EncodeObjectKey($objectKey) {
//     $encoded="";
//     for ($i = 0; $i < strlen($objectKey); $i++) {
//         if ($objectKey[$i] >= '0' && $objectKey[$i] <= '9') {
//             $encoded .= chr((ord($objectKey[$i])-ord('0')) + ord('a'));
//         } else {
//             $encoded .= $objectKey[$i]; // Keep non-numeric characters as is
//         }
//     }
//     return $encoded;
// }

?>