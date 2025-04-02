<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../backend/Business_Logic/Function/functions.php';

class CreateUploadSessionTest extends TestCase
{
    public function testCreateUploadSessionInTestMode()
    {
        $result = createUploadSession("fake-token", "bucket123", "model.obj", 2, true);
        $this->assertArrayHasKey("uploadKey", $result);
        $this->assertCount(2, $result["urls"]);
    }
}
