<?php
require_once 'functions.php';
require_once 'config.php';
require_once 'upload.php';
require_once 'getAccessToken.php';
require_once 'uploaddatabase.php';
//require_once 'upload-projectfile.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Retrieve the URN from the URL query string
    if (isset($_GET['urn']) && !empty($_GET['urn'])) {
        $urn = $_GET['urn'];  // Get the URN from the query parameter
        // Get the access token
        $accessToken = getAccessToken($client_id, $client_secret);

        // Call the function to start translation
        try {
           // echo json_encode(["status" => "success", "result" => $result]);
            $result = StartTranslationJob($accessToken, $urn);
            echo json_encode(["status" => "success", "result" => $result]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    } else {
        // URN is not provided, return an error
        echo json_encode(['error' => 'URN parameter is missing or invalid']);
    }
}
?>
