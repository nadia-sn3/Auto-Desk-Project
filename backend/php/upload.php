<?php

if(isset($_FILES['file-upload']))
{
    require_once("config.php");
    require_once("functions.php");
    $file = $_FILES['file-upload'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];

    $access_token = getAccessToken($client_id, $client_secret);
   
    $signedURL = getSignedUrl($access_token, $bucket_key , $fileName);
    
    $uploadKey = json_decode($signedURL,true)["uploadKey"]; //$signedURL["uploadKey"];
    $signedURLs = json_decode($signedURL,true)["urls"]; //$signedURL["urls"][0];
    
    uploadFileToS3($signedURLs, $fileTmpName);
    
    $finalizeResult = completeUpload($access_token, $bucket_key, $fileName, $uploadKey);

    print_r($finalizeResult);

    //header("Location: index.php?uploadStatus=true");


    if (isset($finalizeResult['objectId'])) {
        $urn = $finalizeResult['objectId'];
    
        // URL-safe Base64 encode the URN without padding
        $encoded_urn = base64UrlEncodeUnpadded($urn);
    
        // Now you can use the encoded URN for further operations
        //echo "Encoded URN: " . $encoded_urn . "\n";
    
        // Proceed with further operations using $encoded_urn
        // Example: You might use this encoded URN in an API request
        // $next_response = someApiRequest($encoded_urn);
    } else {
        echo "URN not found in the response.\n";
    }
    
    
    $urn_source_file = base64UrlEncodeUnpadded($finalizeResult['objectId']);
    
    echo "URN: " . $urn_source_file;
    echo '<br> <br>';
    
    $job = StartTranslationJob($access_token, $urn_source_file);
    $job = json_decode($job, true);
    echo "Translation job started. Response: " . print_r($job, true) . "<br>";
    if (!isset($job['urn'])) {
        die("Error: Translation job failed to start. Response: " . print_r($job, true));
    }

    if (!is_array($job) || !isset($job['urn'])) {
        die("Error: Invalid response from StartTranslationJob. Response: " . print_r($job, true));
    }
    while(true)
    {
        $URL_safe_urn_Of_source_file = $job['urn'];
        $status = CheckJobStatus($access_token, $URL_safe_urn_Of_source_file);
        
        $status_data = json_decode($status,true);
    
        if(isset($status_data['status']) && $status_data['status'] == "success")
        {
            echo "Translation is completed";
           
            $translated_urn = $status_data['urn'];
    
            // Pass the translated URN to JavaScript by embedding it into the script
            if (!empty($translated_urn)) {
                echo "<script>
                        var translatedUrn = '{$translated_urn}';
                        console.log('Translated URN:', translatedUrn);
                      </script>";
            } else {
                echo "<script>
                        console.error('Error: Translated URN is undefined.');
                      </script>";
            }
            break;
        }
        sleep(30);
    }
    header("Location: /Auto-Desk-Project/view-asset-model.php?urn=" . urlencode($translated_urn) . "&objectKey=" . urlencode($fileName));
    
    exit;

} 
?>