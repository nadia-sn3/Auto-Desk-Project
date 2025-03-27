<?php
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
    
    if ($http_code == 409) { 
        echo "Bucket already exists, proceeding...\n";
        return true;
    } elseif ($http_code == 200) {
        echo "Bucket created successfully.\n";
        return true;
    } else {
        die("Error creating bucket: " . json_encode($response));
    }
}

function printBucketList($accessToken)
{
    $url = 'https://developer.api.autodesk.com/oss/v2/buckets';
    
    $header = [
        "Authorization: Bearer $accessToken"
    ];

    $ch = curl_init();
    
    curl_setopt_array($ch,[
        CURLOPT_URL=> $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $header
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        echo "Bucket List: \n";
        echo '<br> <br>';
        var_dump($response);
        echo '<br> <br>';
    } else {
        die("Error bucket: " . json_decode($response));
    }

}

function printBucketDetail($accessToken, $bucketKey)
{
    $url = "https://developer.api.autodesk.com/oss/v2/buckets/$bucketKey/details";
    
    $header = [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    
    curl_setopt_array($ch,[
        CURLOPT_URL=> $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $header
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        echo "Bucket Details: \n";
        echo '<br> <br>';
        var_dump($response);
        echo '<br> <br>';
    } else {
        die("Error bucket: " . json_decode($response));
    }
}

function printBucketObjectList($accessToken, $bucketKey)
{
    $url = "https://developer.api.autodesk.com/oss/v2/buckets/$bucketKey/objects";
    
    $header = [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    
    curl_setopt_array($ch,[
        CURLOPT_URL=> $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $header
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        echo "Bucket Object List: \n";
        echo '<br> <br>';
        var_dump($response);
        echo '<br> <br>';
    } else {
        die("Error bucket: " . json_decode($response));
    }
}

function GetBucketObjectList($accessToken, $bucketKey)
{
    $url = "https://developer.api.autodesk.com/oss/v2/buckets/$bucketKey/objects";
    
    $header = [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    
    curl_setopt_array($ch,[
        CURLOPT_URL=> $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $header
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
    } else {
        die("Error bucket: " . json_decode($response));
    }

    return json_decode($response, true);
}



?>