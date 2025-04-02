<?php

include("config.php");
include("getAccessToken.php");
//include("Download_Functions.php");

function ObtainSignedURL($accessToken, $bucketKey, $objectKey)
{
    $url = "https://developer.api.autodesk.com/oss/v2/buckets/$bucketKey/objects/$objectKey/signeds3download";

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
        . 'Function: ObtainSignedURL' . '<br>'
        . 'Response: ' . '<br>';
        var_dump($response);
        echo '<br> <br>';
        exit;
    }
    
    $status_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    
    if($status_code != 200 && $status_code != 201)
    {
        echo 'Autodesk error: <br>' 
        . 'Function: ObtainSignedURL' . '<br>'
        . 'Status code: ' . $status_code . '<br>'
        . 'Response: ' . '<br>';
        var_dump($response);
        echo '<br> <br>';
        exit;
    }
    
    return json_decode($response, true);
}

function DownloadFile($downloadURL, $saveAsFileName, $path_to_save)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $downloadURL,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_RETURNTRANSFER => true
    ]);

    $response = curl_exec($ch);

    curl_close($ch);

    if($response === false)
    {
        echo 'Curl error:' . curl_error($ch) . '<br>' 
        . 'Function: DownloadFile' . '<br>'
        . 'Response: ' . '<br>';
        var_dump($response);
        echo '<br> <br>';
        exit;
    }
    
    $status_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    
    if($status_code != 200)
    {
        echo 'Autodesk error: <br>' 
        . 'Function: DownloadFile' . '<br>'
        . 'Status code: ' . $status_code . '<br>'
        . 'Response: ' . '<br>';
        var_dump($response);
        echo '<br> <br>';
        exit;
    }

    $saveAsFilePath = $path_to_save."\\".$saveAsFileName;
    file_put_contents($saveAsFilePath, $response);

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accessToken = getAccessToken($client_id, $client_secret);

    $objectkey = $_GET['objectKey'];

    echo "<br> <br>";
    echo "ho";   
    echo "<br> <br>";

    $signedUrl = ObtainSignedURL($accessToken, $bucket_key, $objectkey);


    $downloadURL = $signedUrl["url"];

    $fileNameSaveAs = $_GET['fileName'];

    $path_to_save = "..\\..\\Business_Logic\\Uploaded_Process\\uploads";

    DownloadFile($downloadURL, $fileNameSaveAs, $path_to_save);
}
?>