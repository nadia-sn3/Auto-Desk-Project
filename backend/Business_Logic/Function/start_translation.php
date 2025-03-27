<?php
require_once 'functions.php';
require_once 'config.php';
require_once 'upload.php';
require_once 'getAccessToken.php';
require_once 'uploaddatabase.php';
require_once 'upload-projectfile.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['urn']) && !empty($_GET['urn'])) {
        $urn = $_GET['urn'];  
        $accessToken = getAccessToken($client_id, $client_secret);
        try {
            $result = StartTranslationJob($accessToken, $urn);
            echo json_encode(["status" => "success", "result" => $result]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'URN parameter is missing or invalid']);
    }
}
?>
