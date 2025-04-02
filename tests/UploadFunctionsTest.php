<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../backend/Business_Logic/Function/upload.php'; // update path to your file

class UploadFunctionsTest extends TestCase
{
    protected $pdo;
    protected $stmt;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);

        global $pdo;
        $pdo = $this->pdo;
    }

    public function testCheckIfFileExistReturnsTrue()
    {
        global $pdo;

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

            $this->stmt->expects($this->exactly(2))
            ->method('bindParam');
        

        $this->stmt->expects($this->once())
            ->method('execute');

        $this->stmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1); 

        $result = CheckIfFileExist("model.obj", 1);
        $this->assertTrue($result);
    }

    public function testCheckIfFileExistReturnsFalse()
    {
        global $pdo;

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt->expects($this->exactly(2))
            ->method('bindParam');

        $this->stmt->expects($this->once())
            ->method('execute');

        $this->stmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(0); 

        $result = CheckIfFileExist("nonexistent.obj", 99);
        $this->assertFalse($result);
    }
}
