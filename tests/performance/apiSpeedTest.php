<?php
$start = microtime(true);

require_once "backend/Business_Logic/Function/config.php";
require_once "backend/Business_Logic/Function/functions.php";

$access_token = getAccessToken($client_id, $client_secret);
$fileName = "test.obj";
$bucket_key = "bucket-key"; 
$response = getSignedUrl($access_token, $bucket_key, $fileName);

$end = microtime(true);
$duration = round(($end - $start) * 1000, 2);

echo " API response time: $duration ms\n";
