<?php
// index.php

// Include config and functions
include 'config.php';
include 'functions.php';

// Get the access token
$access_token = getAccessToken($client_id, $client_secret);




// Create a bucket (if necessary)
createBucket($access_token, $bucket_key);

// Get signed URL for the file upload
$signed_urls = json_decode(getSignedUrl($access_token, $bucket_key, $file_name),true);

// Check if the signed URL was successfully obtained
if (is_array($signed_urls["urls"]) && !empty($signed_urls["urls"])) {
    $upload_response = uploadFileToS3($signed_urls["urls"], $file_path);
  
    //print_r($upload_response);
} else {
    die('Error: No signed URLs obtained.');
}

// Extract uploadKey from the response

$upload_key = $signed_urls['uploadKey'] ?? null;
if (!$upload_key) {
    die('Error: Missing uploadKey in signed upload response.');
}

// Upload the file using signed URL
$upload_result = uploadFileToS3($signed_urls['urls'], $file_path);

// Complete the upload process
$complete_response = completeUpload($access_token, $bucket_key, $file_name, $upload_key);

// Print the final response
print_r($complete_response);

echo '<br> <br>';
print_r($complete_response);
echo '<br> <br>';

// Extract the URN from the response (assuming it's in the response body, you might need to adjust the key depending on the actual response structure)
if (isset($complete_response['objectId'])) {
    $urn = $complete_response['objectId'];

    // URL-safe Base64 encode the URN without padding
    $encoded_urn = base64UrlEncodeUnpadded($urn);

    // Now you can use the encoded URN for further operations
    echo "Encoded URN: " . $encoded_urn . "\n";

    // Proceed with further operations using $encoded_urn
    // Example: You might use this encoded URN in an API request
    // $next_response = someApiRequest($encoded_urn);
} else {
    echo "URN not found in the response.\n";
}




$urn_source_file = base64UrlEncodeUnpadded($complete_response['objectId']);
echo "URN: " . $urn_source_file;
echo '<br> <br>';

$job = StartTranslationJob($access_token, $urn_source_file);
$job = json_decode($job, true);
if (!is_array($job) || !isset($job['urn'])) {
    die("Error: Invalid response from StartTranslationJob. Response: " . print_r($job, true));
}
while(true)
{
    $URL_safe_urn_Of_source_file = $job['urn'];
    $status = CheckJobStatus($access_token, $URL_safe_urn_Of_source_file);
    
    $status_data = json_decode($status,true);

    if($status_data['status'] == "success")
    {
        echo "Translation is completed";
       
        $translated_urn = $status_data['urn'];

        // Pass the translated URN to JavaScript by embedding it into the script
        echo "<script>
                var translatedUrn = '{$translated_urn}';
                console.log('Translated URN:', translatedUrn);
              </script>";
        break;
    }
    sleep(30);
}

echo '<br> <br>';
var_dump($status);
echo '<br> <br>';




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="css/main.css">
    <script src="js/main.js" defer></script>
    <link href="https://developer.api.autodesk.com/viewingservice/v1/viewers/style.css" rel="stylesheet">
    <script src="https://developer.api.autodesk.com/modelderivative/v2/viewers/7.*/viewer3D.min.js"></script>
    <title>AutoDesk | Project Name</title>
    <style>
        body {
            margin: 0;
        }
        #forgeViewer {
            width: 100%;
            height: 100%;
            margin: 0;
            background-color: #F0F8FF;
        }
    </style>
</head>
<body>
    <div id="forgeViewer"></div>
    <div id="viewables_dropdown" style="display: none;">
        <select id="viewables"></select>
    </div>
    
    <script>
        // Only output the token if it exists
        <?php if ($access_token): ?>
            var accessToken = "<?php echo htmlspecialchars($access_token, ENT_QUOTES, 'UTF-8'); ?>";
            console.log('Access Token:', accessToken);
        <?php else: ?>
            console.log('Error: Access token not retrieved.');
        <?php endif; ?>
    </script>
</body>
</html>
