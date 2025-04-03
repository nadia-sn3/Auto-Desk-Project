<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'upload.php';
require_once 'getAccessToken.php';


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $urn = $_GET['urn']; 
    $accessToken = getAccessToken($client_id, $client_secret);

    try {
        $result = CheckJobStatus($accessToken, $urn);
        echo json_encode(["status" => "success", "result" => $result]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}



?>