<?php
function getAccessToken($client_id, $client_secret){
    $token_url = 'https://developer.api.autodesk.com/authentication/v2/token';
    $auth_string = base64_encode($client_id . ':' . $client_secret);

    $data = [
        "grant_type" => "client_credentials",
        "scope" => "data:write data:read data:create bucket:create bucket:delete bucket:read"
    ];

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

    if ($http_status !== 200) {
        die("Error: Failed to get access token. HTTP Status: $http_status. Response: " . json_encode($response_data));
    }

    if (!isset($response_data['access_token'])) {
        die("Error: No access token received. Response: " . json_encode($response_data));
    }
  
    return $response_data['access_token'];
    
}
?>