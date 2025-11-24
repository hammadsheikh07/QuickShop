<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\AdminRepository;
use App\Services\AuthService;
use App\Repositories\ProductRepository;
use App\Services\ProductService;

class AdminAuthenticationTest extends TestCase
{
    private PDO $db;
    private AdminRepository $adminRepo;
    private AuthService $authService;
    private ProductRepository $productRepo;
    private ProductService $productService;

    protected function setUp(): void
    {
        // Clear any existing session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create admins table
        $this->db->exec("
            CREATE TABLE admins (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Create products table
        $this->db->exec("
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                price REAL NOT NULL,
                stock INTEGER NOT NULL DEFAULT 0,
                deleted_at TIMESTAMP NULL
            )
        ");

        $this->adminRepo = new AdminRepository($this->db);
        $this->authService = new AuthService($this->adminRepo);
        $this->productRepo = new ProductRepository($this->db);
        $this->productService = new ProductService($this->productRepo);
    }

    public function test_admin_login_workflow()
    {
        // Create admin user
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $this->db->exec("INSERT INTO admins (username, password_hash) VALUES ('admin', '{$passwordHash}')");

        // Login
        $admin = $this->authService->login('admin', 'admin123');
        $this->assertNotNull($admin);

        // Set session
        $this->authService->setSession($admin);
        $this->assertTrue($this->authService->isAuthenticated());

        // Get current admin
        $currentAdmin = $this->authService->getCurrentAdmin();
        $this->assertNotNull($currentAdmin);
        $this->assertEquals('admin', $currentAdmin->getUsername());
    }

    public function test_admin_can_manage_products_after_login()
    {
        // Create admin and login
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $this->db->exec("INSERT INTO admins (username, password_hash) VALUES ('admin', '{$passwordHash}')");
        
        $admin = $this->authService->login('admin', 'admin123');
        $this->authService->setSession($admin);

        // Admin should be able to create product
        $product = $this->productService->createProduct([
            'name' => 'Admin Created Product',
            'description' => 'Created by admin',
            'price' => 100.00,
            'stock' => 20
        ]);

        $this->assertNotNull($product->getId());
        $this->assertEquals('Admin Created Product', $product->getName());

        // Admin should be able to see all products including deleted
        $allProducts = $this->productService->listAllProductsIncludingDeleted();
        $this->assertCount(1, $allProducts);
    }

    public function test_unauthorized_access_prevents_product_management()
    {
        // Ensure no session exists - clear any session data
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();
        }
        
        // Start a fresh session to test
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = []; // Clear any session data
        
        // Try to access admin functions without login
        $this->assertFalse($this->authService->isAuthenticated());
        
        $currentAdmin = $this->authService->getCurrentAdmin();
        $this->assertNull($currentAdmin);
    }

    public function test_admin_logout_workflow()
    {
        // Create admin and login
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $this->db->exec("INSERT INTO admins (username, password_hash) VALUES ('admin', '{$passwordHash}')");
        
        $admin = $this->authService->login('admin', 'admin123');
        $this->authService->setSession($admin);
        $this->assertTrue($this->authService->isAuthenticated());

        // Logout
        $this->authService->logout();
        $this->assertFalse($this->authService->isAuthenticated());
        $this->assertNull($this->authService->getCurrentAdmin());
    }
}

