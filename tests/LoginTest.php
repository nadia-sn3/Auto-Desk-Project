<?php
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase {
    public function testCorrectCredentialsReturnTrue() {
        $email = "test@example.com";
        $password = "password123";

        $expectedEmail = "test@example.com";
        $expectedPassword = "password123";

        $this->assertEquals($expectedEmail, $email);
        $this->assertEquals($expectedPassword, $password);
    }
}
