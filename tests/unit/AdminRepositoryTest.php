<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\AdminRepository;
use App\Models\Admin;

class AdminRepositoryTest extends TestCase
{
    private PDO $db;
    private AdminRepository $repository;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->db->exec("
            CREATE TABLE admins (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->repository = new AdminRepository($this->db);
    }

    public function test_find_by_username_exists()
    {
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        $this->db->exec("INSERT INTO admins (username, password_hash) VALUES ('admin', '{$passwordHash}')");
        
        $admin = $this->repository->findByUsername('admin');
        
        $this->assertNotNull($admin);
        $this->assertEquals('admin', $admin->getUsername());
        $this->assertTrue($admin->verifyPassword('password123'));
    }

    public function test_find_by_username_not_exists()
    {
        $admin = $this->repository->findByUsername('nonexistent');
        
        $this->assertNull($admin);
    }

    public function test_find_by_id_exists()
    {
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        $this->db->exec("INSERT INTO admins (username, password_hash) VALUES ('admin', '{$passwordHash}')");
        
        $admin = $this->repository->findById(1);
        
        $this->assertNotNull($admin);
        $this->assertEquals(1, $admin->getId());
        $this->assertEquals('admin', $admin->getUsername());
    }

    public function test_find_by_id_not_exists()
    {
        $admin = $this->repository->findById(999);
        
        $this->assertNull($admin);
    }
}

