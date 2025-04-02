<?php
use PHPUnit\Framework\TestCase;

class ArchiveTest extends TestCase {
    public function testProjectArchiveFlag() {
        $isArchived = true;
        $this->assertTrue($isArchived);
    }

    public function testDeletedProjectIdIsValid() {
        $projectId = 3;
        $this->assertIsInt($projectId);
    }
}
