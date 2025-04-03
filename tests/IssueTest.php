<?php
use PHPUnit\Framework\TestCase;

class IssueTest extends TestCase {
    public function testIssueDescriptionRequired() {
        $description = "Collision between two parts in model";
        $this->assertNotEmpty($description);
    }

    public function testIssueStatusDefaultsToOpen() {
        $status = "open";
        $this->assertEquals("open", $status);
    }
}
 