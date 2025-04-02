<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../backend/Business_Logic/Function/functions.php';

class InsertProjectFileTest extends TestCase
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

    public function testInsertProjectFileExecutesWithCorrectBindings()
    {
        global $pdo;

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO Project_File'))
            ->willReturn($this->stmt);

        $this->stmt->expects($this->exactly(5)) // 5 bindParams
            ->method('bindParam');

        $this->stmt->expects($this->once())
            ->method('execute');

        InsertProjectFile("model.obj", "3D", 1, 1);
    }
}
