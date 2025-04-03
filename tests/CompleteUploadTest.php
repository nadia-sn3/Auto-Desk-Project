<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../backend/Business_Logic/Function/functions.php';

class CompleteUploadTest extends TestCase
{
    public function testCompleteUploadInTestMode()
    {
        $result = completeUpload("fake-token", "bucket123", "model.obj", "upload-key", true);
        $this->assertEquals("success", $result["status"]);
    }
}
