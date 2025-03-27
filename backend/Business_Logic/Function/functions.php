<?php


// Function to URL-safe Base64 encode and remove padding (RFC 6920)
function base64UrlEncodeUnpadded($data) {
    // Base64 encode the data
    $base64 = base64_encode($data);
    
    // Replace characters to make it URL-safe
    $urlSafeBase64 = strtr($base64, '+/', '-_');

    // Remove the padding (=)
    return rtrim($urlSafeBase64, '=');
}


// function StartTranslationJob($access_token, $urn)
// {
//     $url = "https://developer.api.autodesk.com/modelderivative/v2/designdata/job";
//
//     $header = [
//         "Authorization: Bearer $access_token",
//         "Content-Type: application/json"
//     ];
//  
//     $data = json_encode([
//         "input" => [
//             "urn" => $urn
//         ],
//         "output" => [
//             "formats" => [
//                 [
//                     "type" => "svf2",
//                     "views" => [
//                         "2d",
//                         "3d"
//                     ]
//                 ]
//             ]
//         ]
//     ]);
//
//     echo '<br> <br>';
//     print_r($data);
//     echo '<br> <br>';    
//
//     $ch = curl_init();
//
//     curl_setopt_array($ch, [
//         CURLOPT_URL => $url,
//         CURLOPT_CUSTOMREQUEST => "POST",
//         CURLOPT_POSTFIELDS => $data,
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_HTTPHEADER => $header
//     ]);
//
//     $response = curl_exec($ch);
//     $status_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
//
//     curl_close($ch);
//
//     if($response == false)
//     {
//         echo 'Curl error:' . curl_error($ch) . '<br>' ;
//         echo 'Status code: ' . $status_code . '<br>';
//         var_dump($response);
//         echo '<br> <br>';
//         exit;
//     }
// 
//     if($status_code != 200 && $status_code != 201)
//     {
//         echo 'Autodesk error: <br>' 
//         . 'Function: StartTranslationJob' . '<br>'
//         . 'Status code: ' . $status_code . '<br>'
//         . 'Response: ' . '<br>';
//         var_dump($response);
//         echo '<br> <br>';
//         exit;
//     }
//
//     return $response;
// }


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

    if ($response === false) {
        echo json_encode(['error' => 'Curl error: ' . curl_error($ch), 'status_code' => $status_code]);
        exit;
    }
    
    $responseData = json_decode($response, true);
    curl_close($ch);

    if ($status_code !== 200 && $status_code !== 201) {
        echo json_encode(['error' => 'Autodesk error', 'status_code' => $status_code, 'response' => $responseData]);
        exit;
    }

    // Decode the response JSON to check if there are additional details
    
    // Return the response in JSON format (no extra output)
    return $response;
}

function StartTranslationJob_Thumbnail($access_token, $urn)
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
                    "type" => "thumbnail"
                ]
            ]
        ]
    ]);

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

    if ($response === false) {
        echo json_encode(['error' => 'Curl error: ' . curl_error($ch), 'status_code' => $status_code]);
        exit;
    }

    curl_close($ch);

    if ($status_code !== 200 && $status_code !== 201) {
        echo json_encode(['error' => 'Autodesk error', 'status_code' => $status_code, 'response' => $response]);
        exit;
    }

    // Decode the response JSON to check if there are additional details
    $responseData = json_decode($response, true);
    
    // Return the response in JSON format (no extra output)
    return $responseData;
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


