<?php
use PHPUnit\Framework\TestCase;

class AddUserTest extends TestCase {
    public function testValidUserId() {
        $userId = 5;
        $this->assertIsInt($userId);
        $this->assertGreaterThan(0, $userId);
    }

    public function testValidRoleAssignment() {
        $role = "viewer";
        $this->assertContains($role, ['viewer', 'editor', 'admin']);
    }
}
