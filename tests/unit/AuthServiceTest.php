<?php

use PHPUnit\Framework\TestCase;
use App\Services\AuthService;
use App\Repositories\AdminRepository;
use App\Models\Admin;

class AuthServiceTest extends TestCase
{
    private PDO $db;
    private AdminRepository $adminRepo;
    private AuthService $authService;

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

        $this->adminRepo = new AdminRepository($this->db);
        $this->authService = new AuthService($this->adminRepo);
    }

    public function test_login_success()
    {
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        $this->db->exec("INSERT INTO admins (username, password_hash) VALUES ('admin', '{$passwordHash}')");
        
        $admin = $this->authService->login('admin', 'password123');
        
        $this->assertNotNull($admin);
        $this->assertEquals('admin', $admin->getUsername());
    }

    public function test_login_wrong_password()
    {
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        $this->db->exec("INSERT INTO admins (username, password_hash) VALUES ('admin', '{$passwordHash}')");
        
        $admin = $this->authService->login('admin', 'wrongpassword');
        
        $this->assertNull($admin);
    }

    public function test_login_wrong_username()
    {
        $admin = $this->authService->login('nonexistent', 'password123');
        
        $this->assertNull($admin);
    }

    public function test_is_authenticated_false()
    {
        // Start a new session for testing
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        $this->assertFalse($this->authService->isAuthenticated());
    }

    public function test_set_and_get_session()
    {
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        $this->db->exec("INSERT INTO admins (username, password_hash) VALUES ('admin', '{$passwordHash}')");
        
        $admin = $this->adminRepo->findByUsername('admin');
        $this->authService->setSession($admin);
        
        $this->assertTrue($this->authService->isAuthenticated());
        
        $currentAdmin = $this->authService->getCurrentAdmin();
        $this->assertNotNull($currentAdmin);
        $this->assertEquals('admin', $currentAdmin->getUsername());
    }

    public function test_logout()
    {
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        $this->db->exec("INSERT INTO admins (username, password_hash) VALUES ('admin', '{$passwordHash}')");
        
        $admin = $this->adminRepo->findByUsername('admin');
        $this->authService->setSession($admin);
        $this->assertTrue($this->authService->isAuthenticated());
        
        $this->authService->logout();
        $this->assertFalse($this->authService->isAuthenticated());
    }
}

