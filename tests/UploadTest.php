<?php
use PHPUnit\Framework\TestCase;

class UploadTest extends TestCase {
    public function testValidFileExtension() {
        $fileName = "model.obj";
        $this->assertTrue(in_array(pathinfo($fileName, PATHINFO_EXTENSION), ['obj', 'ipt', 'stl']));
    }

    public function testFileSizeLimit() {
        $fileSize = 1024 * 1024 * 5; 
        $this->assertLessThanOrEqual(10 * 1024 * 1024, $fileSize); 
    }
}
