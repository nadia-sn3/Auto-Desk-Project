<?php

// // Autodesk API credentials
// $client_id = '5CnZAO6JkAGbxem7pYWfKzXGRM2FaGUKQl43poynBAAjsEUE'; // Replace with your actual client ID
// $client_secret = 'd3scpFqIOVQr46N92UZthApShchdn8VsPkQ7DNkQWu9lfp2YA88nXO8oc1oxGAMG'; // Replace with your actual client secret

// //File to upload
// $file_path = "D:/Downloads/box.obj";
// $bucket_key = "mybucket_2025";
// $file_name = basename($file_path);

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

    // Function to get signed URL for uploading the file
function getSignedUrl($access_token, $bucket_key, $file_name) {
    $url = "https://developer.api.autodesk.com/oss/v2/buckets/$bucket_key/objects/$file_name/signeds3upload?minutesExpiration=10";
    
    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $access_token"
    ]);

    // Execute cURL request and capture response
    $response = curl_exec($ch);
    curl_close($ch);

    // Handle errors
    if ($response === false) {
        die('Error: ' . curl_error($ch));
    }

    echo "Raw response : $response\n";
    $data = json_decode($response, true);

    if(isset($data['urls'])&& is_array($data['urls']) && !empty($data['urls'])){
        return $response;
        //return $data['urls'];
    }else {
        die("Error: Unable to get signed URL for upload.");
    }
    
}


/*function uploadFileToS3($signed_urls, $file_path) {
    if (empty($signed_urls) || !is_array($signed_urls)) {
        die('Error: Signed URLs are not available.');
    }

    $file = fopen($file_path, 'r');
    $file_size = filesize($file_path);
    $part_size = ceil($file_size / count($signed_urls)); // Calculate the size of each part

    $responses = [];
    
    // Loop through signed URLs and upload file parts
    foreach ($signed_urls as $index => $signed_url) {
        // Seek to the correct part of the file based on the part index
        fseek($file, $index * $part_size);

        // Prepare part data
        $part_data = fread($file, $part_size);
        $chunk_size = 1024 * 1024 * 5; 
        // Set cURL options for the multipart upload
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $signed_url);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/octet-stream"
        ]);

        // Loop to split the file into smaller parts
        for ($i = 0; $i < strlen($part_data); $i += $chunk_size) {
            // Extract the current chunk
            $chunk = substr($part_data, $i, $chunk_size);

            // Open a memory stream for each chunk
            $tempStream = fopen('php://temp', 'r+');
            fwrite($tempStream, $chunk);
            rewind($tempStream);
            curl_setopt($ch,CURLOPT_INFILE,$tempStream);
            curl_setopt($ch, CURLOPT_INFILESIZE, strlen($part_data));
            

       
        // Execute the upload request
        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($response === false) {
            die('Error uploading part: ' . curl_error($ch) . ' error code: ' . curl_errno($ch));
        }
        // Check for cURL errors
        if(curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            echo 'Upload successful: ' . $response;
        }

        // Check if the upload was successful (HTTP 200)
        if ($http_status === 200) {
            echo "Upload response for part " . ($index + 1) . ": HTTP Status Code: $http_status\n";
            echo "Raw response: $response\n";
        } else {
            echo "Error uploading part " . ($index + 1) . " with status $http_status\n";
        }

        fclose($tempStream);
    }
        // Store the response for each part to check later
        $responses[] = json_decode($response, true);
        curl_close($ch);
    }

    fclose($file); // Close the file after upload
    return $responses;
}*/

function uploadFileToS3($signed_urls, $file_path)
{
    $file_data = file_get_contents($file_path);
    $file_size = filesize($file_path);
      
    $headers = [
        "Content-Type: application/octet-stream",
        "Content-Length: $file_size"
    ];


    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $signed_urls[0],
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $file_data
    ]); 
    
    
    $response = curl_exec($ch);
    
    curl_close($ch);

    if($response === false)
    {
        echo 'Curl error:' . curl_error($ch) . '<br>' 
        . 'Function: UploadFiles' . '<br>'
        . 'Response: ' . '<br>';
        var_dump($response);
        echo '<br> <br>';
        exit;

    }
    
    $status_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    
    if($status_code != 200)
    {
        echo 'Autodesk error: <br>' 
        . 'Function: UploadFiles' . '<br>'
        . 'Status code: ' . $status_code . '<br>'
        . 'Response: ' . '<br>';
        var_dump($response);
        echo '<br> <br>';
        exit;
    }

}


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


