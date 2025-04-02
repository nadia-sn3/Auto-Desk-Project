<?php
require_once __DIR__ . '/../../../db/connection.php'; 
function DeleteObject($accessToken, $bucketKey, $objectKey)
{
    $url = "https://developer.api.autodesk.com/oss/v2/buckets/$bucketKey/objects/$objectKey";
    
    $header = [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json;charset=UTF-8"
    ];

    $ch = curl_init();
    
    curl_setopt_array($ch,[
        CURLOPT_URL=> $url,
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $header
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (strpos($response, "<br />") !== false) {
        error_log("Unexpected response from OSS API: " . $response);
    }

    curl_close($ch);

    // Check for valid JSON before returning
    json_decode($response);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Invalid JSON response: " . $response);
        return null; // or handle it accordingly
    }
    return $response;
    // echo "Deleted item: $objectKey";
    // echo '<br> <br>';
    // echo 'Response Code: '.$http_code;
    // echo '<br> <br>';
    // echo 'Response received: ';
     echo '<br> <br>';
     var_dump($response);
    echo '<br> <br>';
}

function DeleteTableData($tableName)
{
    global $pdo;

    $sql = "DELETE FROM :tableName;";    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':tableName', $tableName, SQLITE3_TEXT);
    $result= $stmt->execute();

    
}

?>