<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../backend/Business_Logic/Function/functions.php';

class InsertBucketFileTest extends TestCase
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

    public function testInsertBucketFileWithValidProjectData()
    {
        global $pdo;

        $projectId = 1;
        $fileName = "model.obj";
        $objectId = "abc123";
        $objectKey = "box.obj";
        $entryPoint = 1;

        $this->pdo->expects($this->exactly(2))
        ->method('prepare')
        ->withConsecutive(
            [$this->stringContains("SELECT")],
            [$this->stringContains("INSERT INTO Bucket_File")]
        )
        ->willReturn($this->stmt, $this->stmt);
            

        $this->stmt->expects($this->atLeast(1))
            ->method('bindParam');

            $this->stmt->expects($this->exactly(2))
            ->method('execute');
        
        $this->stmt->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'project_file_id' => 10,
                'latest_version' => 2
            ]);

        

        $this->stmt->expects($this->atLeast(1))
            ->method('bindParam');

            $this->stmt->expects($this->exactly(2))
            ->method('execute');
        
        InsertBucketFile($fileName, $projectId, $objectId, $objectKey, $entryPoint);
    }
}
