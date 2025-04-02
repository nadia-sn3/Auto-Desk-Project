<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../backend/Business_Logic/Function/functions.php';

class AdjustFileVersionTest extends TestCase
{
    protected $pdo;
    protected $stmt;

    protected function setUp(): void
    {
        global $pdo;
        $this->pdo = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $pdo = $this->pdo;
    }

    public function testAdjustFileVersionIncrementsVersion()
    {
        global $pdo;

        $fileName = "model.obj";
        $projectId = 1;
        $adjustmentValue = 1;

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains("UPDATE Project_File"))
            ->willReturn($this->stmt);

        $this->stmt->expects($this->exactly(3))
            ->method('bindParam');

        $this->stmt->expects($this->once())
            ->method('execute');

        AdjustFileVersion($fileName, $projectId, $adjustmentValue);
    }
}
