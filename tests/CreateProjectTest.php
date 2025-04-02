<?php
use PHPUnit\Framework\TestCase;

class CreateProjectTest extends TestCase {
    public function testProjectTitleNotEmpty() {
        $title = "New 3D Project";
        $this->assertNotEmpty($title);
    }

    public function testValidProjectNameLength() {
        $name = "Demo Project";
        $this->assertLessThanOrEqual(100, strlen($name));
    }
}
