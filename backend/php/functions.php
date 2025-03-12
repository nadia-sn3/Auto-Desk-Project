<?php
// Function to get an access token
function getAccessToken($client_id, $client_secret){
    $token_url = 'https://developer.api.autodesk.com/authentication/v2/token';
    // Base64 encode the client_id and client_secret
    $auth_string = base64_encode($client_id . ':' . $client_secret);

    //Data to send in the request 
    $data = [
        "grant_type" => "client_credentials",
        "scope" => "data:write data:read bucket:create bucket:delete bucket:read"
    ];

    // Set up cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/x-www-form-urlencoded",
        "Accept: application/json",
        "Authorization: Basic $auth_string"
    ]);

    
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        die("cURL Error: " . curl_error($ch));
    }

    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response, true);

    // Check if we got a valid token
    if ($http_status !== 200) {
        die("Error: Failed to get access token. HTTP Status: $http_status. Response: " . json_encode($response_data));
    }

    if (!isset($response_data['access_token'])) {
        die("Error: No access token received. Response: " . json_encode($response_data));
    }
  
    return $response_data['access_token'];
    
}


//Function to create a Bucket
function createBucket($access_token, $bucket_key){
    $url = 'https://developer.api.autodesk.com/oss/v2/buckets';
    $data = json_encode([
        "bucketKey" => $bucket_key,
        "policyKey" => "transient"
    ]);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization:Bearer $access_token"
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 409) { // Bucket already exists
        echo "Bucket already exists, proceeding...\n";
        return true;
    } elseif ($http_code == 200) {
        echo "Bucket created successfully.\n";
        return true;
    } else {
        die("Error creating bucket: " . json_encode($response));
    }
}
    
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


// Function to URL-safe Base64 encode and remove padding (RFC 6920)
function base64UrlEncodeUnpadded($data) {
    // Base64 encode the data
    $base64 = base64_encode($data);
    
    // Replace characters to make it URL-safe
    $urlSafeBase64 = strtr($base64, '+/', '-_');
    
    // Remove the padding (=)
    return rtrim($urlSafeBase64, '=');
}


function StartTranslationJob($access_token, $urn)
{
    $url = "https://developer.api.autodesk.com/modelderivative/v2/designdata/job";

    $header = [
        "Authorization: Bearer $access_token",
        "Content-Type: application/json"
    ];
    
    $data = json_encode([
        "input" => [
            "urn" => $urn
        ],
        "output" => [
            "formats" => [
                [
                    "type" => "svf2",
                    "views" => [
                        "2d",
                        "3d"
                    ]
                ]
            ]
        ]
    ]);

    echo '<br> <br>';
    print_r($data);
    echo '<br> <br>';    

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $header
    ]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

    curl_close($ch);

    if($response == false)
    {
        echo 'Curl error:' . curl_error($ch) . '<br>' ;
        echo 'Status code: ' . $status_code . '<br>';
        var_dump($response);
        echo '<br> <br>';
        exit;
    }
    
 
    
    if($status_code != 200 && $status_code != 201)
    {
        echo 'Autodesk error: <br>' 
        . 'Function: StartTranslationJob' . '<br>'
        . 'Status code: ' . $status_code . '<br>'
        . 'Response: ' . '<br>';
        var_dump($response);
        echo '<br> <br>';
        exit;
    }

    return $response;
}


function CheckJobStatus($accessToken, $urn)
{
    $url = "https://developer.api.autodesk.com/modelderivative/v2/designdata/$urn/manifest";

    $headers = [
        "Authorization: Bearer $accessToken"
    ];

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true
    ]);

    $response = curl_exec($ch);

    curl_close($ch);

    if($response == false)
    {
        echo 'Curl error:' . curl_error($ch) . '<br>' 
        . 'Function: CheckJobStatus' . '<br>'
        . 'Response: ' . '<br>';
        var_dump($response);
        echo '<br> <br>';
        exit;
    }
    
    $status_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    
    if($status_code != 200)
    {
        echo 'Autodesk error: <br>' 
        . 'Function: CheckJobStatus' . '<br>'
        . 'Status code: ' . $status_code . '<br>'
        . 'Response: ' . '<br>';
        var_dump($response);
        echo '<br> <br>';
        exit;
    }

    return $response;
}




?>


