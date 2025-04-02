<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../backend/Business_Logic/Function/functions.php';

class CompleteUploadTest extends TestCase
{
    public function testCompleteUploadSimulated()
    {
        $access_token = "fake-token";
        $bucket_key = "testbucket";
        $file_name = "model.obj";
        $upload_key = "mock-upload-key";

        $result = completeUpload($access_token, $bucket_key, $file_name, $upload_key, true);

        $this->assertIsArray($result);
        $this->assertEquals("success", $result["status"]);
    }
}
