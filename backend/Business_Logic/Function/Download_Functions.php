<?php
require_once "db/connection.php";


function ObtainSignedCookie($accessToken, $urnSourceFile, $urnObjFile)
{
    $url = "https://developer.api.autodesk.com/modelderivative/v2/designdata/$urnSourceFile/manifest/$urnObjFile/signedcookies";

    $headers = [
        "Authorization: Bearer $accessToken"
    ];

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_HEADER => true,
        CURLOPT_RETURNTRANSFER => true
    ]);

    $response = curl_exec($ch);

    curl_close($ch);

    if($response == false)
    {
        echo 'Curl error:' . curl_error($ch) . '<br>' 
        . 'Function: StartTranslationJob' . '<br>'
        . 'Response: ' . '<br>';
        var_dump($response);
        echo '<br> <br>';
        exit;
    }
    
    $status_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    
    if($status_code != 200 && $status_code != 201)
    {
        echo 'Autodesk error: <br>' 
        . 'Function: ObtainSignedCookie' . '<br>'
        . 'Status code: ' . $status_code . '<br>'
        . 'Response: ' . '<br>';
        var_dump($response);
        echo '<br> <br>';
        exit;
    }

    $headerSize = curl_getinfo($ch,CURLINFO_HEADER_SIZE);    
    $header = substr($response, 0, $headerSize);
    $body =json_decode(substr($response, $headerSize),true);
    
    $response =
    [
        "header"=>$header,
        "body"=>$body
    ];

    return $response;
}

function DownloadThumbnail($downloadURL, $cookiesList, $saveAsFileName)
{
    var_dump($downloadURL);
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $downloadURL,
        CURLOPT_CUSTOMREQUEST => "GET",
        //CURLOPT_HTTPHEADER => $headers,
        CURLOPT_COOKIE => $cookiesList[0].'; '.$cookiesList[1].'; '.$cookiesList[2],
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
    
    $saveAsFilePath = "backend\\Download_Process\\thumbnail\\$saveAsFileName";
    file_put_contents($saveAsFilePath, $response);

    return $saveAsFilePath;
}


    function SaveThumbnailData($downloadURL, $saveAsFilePath, $projectId) 
    {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE Project SET thumbnail_path = ? WHERE project_id = ?;");
        if ($stmt === false) {
            die("Database statement preparation failed.");
        }
        
        if (!$stmt->execute([$saveAsFilePath, $projectId])) {
            die("Database insertion failed: " . $stmt->error);
        }
        return true;
    }
    

?>