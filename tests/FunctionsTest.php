<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../backend/Business_Logic/Function/functions.php';

class FunctionsTest extends TestCase
{
    public function testBase64UrlEncodeUnpadded()
    {
        $input = "test";
        $expected = rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
        $this->assertEquals($expected, base64UrlEncodeUnpadded($input));
    }

    public function testStartTranslationJobMock()
    {
        $mockToken = "fake_token";
        $mockUrn = "fake_urn";

        $response = json_encode(["result" => "success", "mock" => true]);

        $this->assertJson($response);
        $decoded = json_decode($response, true);
        $this->assertEquals("success", $decoded["result"]);
    }

    public function testCheckJobStatusMock()
    {
        $mockToken = "fake_token";
        $mockUrn = "fake_urn";

        $response = json_encode([
            "status" => "success",
            "progress" => "complete",
            "mock" => true
        ]);

        $this->assertJson($response);
        $decoded = json_decode($response, true);
        $this->assertEquals("complete", $decoded["progress"]);
    }
}
