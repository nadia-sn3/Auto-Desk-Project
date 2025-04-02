<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../backend/Business_Logic/Function/upload.php';

class UploadFileToBucketTest extends TestCase
{
    public function testUploadFileTobucketSimulated()
    {
        $signed_urls = [
            "https://fake-url.com/part1",
            "https://fake-url.com/part2"
        ];

        $file_path = __DIR__ . '/mock_file.txt';
        $data = str_repeat("A", 1024 * 1024 * 2); // 2MB
        file_put_contents($file_path, $data);

        ob_start();
        uploadFileTobucket($signed_urls, $file_path);
        ob_end_clean();

        unlink($file_path);

        $this->assertTrue(true); 
    }
}
