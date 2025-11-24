<?php

use PHPUnit\Framework\TestCase;
use App\Config\Database;
use App\Repositories\AdminRepository;
use App\Repositories\ProductRepository;
use App\Services\AuthService;
use App\Services\ProductService;
use App\Models\Product;

class AdminProductManagementTest extends TestCase
{
    private PDO $db;
    private AdminRepository $adminRepo;
    private ProductRepository $productRepo;
    private AuthService $authService;
    private ProductService $productService;

    protected function setUp(): void
    {
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

        // Create cart_items table
        $this->db->exec("
            CREATE TABLE cart_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id TEXT NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ");

        $this->adminRepo = new AdminRepository($this->db);
        $this->productRepo = new ProductRepository($this->db);
        $this->authService = new AuthService($this->adminRepo);
        $this->productService = new ProductService($this->productRepo);
    }

    public function test_admin_can_create_product()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock' => 10
        ];

        $product = $this->productService->createProduct($productData);

        $this->assertNotNull($product->getId());
        $this->assertEquals('Test Product', $product->getName());
        $this->assertEquals(99.99, $product->getPrice());
        $this->assertEquals(10, $product->getStock());
    }

    public function test_admin_can_update_product()
    {
        // Create product
        $product = $this->productService->createProduct([
            'name' => 'Original Name',
            'description' => 'Original Description',
            'price' => 50.00,
            'stock' => 5
        ]);

        // Update product
        $updated = $this->productService->updateProduct($product->getId(), [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'price' => 75.00,
            'stock' => 15
        ]);

        $this->assertTrue($updated);

        // Verify update
        $retrieved = $this->productService->getProductIncludingDeleted($product->getId());
        $this->assertEquals('Updated Name', $retrieved->getName());
        $this->assertEquals('Updated Description', $retrieved->getDescription());
        $this->assertEquals(75.00, $retrieved->getPrice());
        $this->assertEquals(15, $retrieved->getStock());
    }

    public function test_admin_can_soft_delete_product()
    {
        // Create product
        $product = $this->productService->createProduct([
            'name' => 'Product to Delete',
            'description' => 'Will be deleted',
            'price' => 100.00,
            'stock' => 20
        ]);

        $productId = $product->getId();

        // Soft delete
        $deleted = $this->productService->deleteProduct($productId);
        $this->assertTrue($deleted);

        // Should not appear in regular list
        $products = $this->productService->listProducts();
        $this->assertCount(0, $products);

        // Should appear in admin list (including deleted)
        $allProducts = $this->productService->listAllProductsIncludingDeleted();
        $this->assertCount(1, $allProducts);
        $this->assertTrue($this->productRepo->isDeleted($productId));
    }

    public function test_deleted_product_preserves_order_history()
    {
        // Create product
        $product = $this->productService->createProduct([
            'name' => 'Product for Order',
            'description' => 'Will be in order',
            'price' => 50.00,
            'stock' => 10
        ]);

        $productId = $product->getId();

        // Add to cart (simulating order history)
        $stmt = $this->db->prepare("
            INSERT INTO cart_items (session_id, product_id, quantity)
            VALUES (:session_id, :product_id, :quantity)
        ");
        $stmt->execute([
            'session_id' => 'test_session',
            'product_id' => $productId,
            'quantity' => 2
        ]);

        // Soft delete product
        $this->productService->deleteProduct($productId);

        // Verify product data still exists
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotNull($row);
        $this->assertEquals('Product for Order', $row['name']);
        $this->assertNotNull($row['deleted_at']);

        // Verify cart item still references product
        $stmt = $this->db->prepare("SELECT * FROM cart_items WHERE product_id = :product_id");
        $stmt->execute(['product_id' => $productId]);
        $cartRow = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotNull($cartRow);
        $this->assertEquals(2, $cartRow['quantity']);
    }

    public function test_admin_can_restore_deleted_product()
    {
        // Create and delete product
        $product = $this->productService->createProduct([
            'name' => 'Product to Restore',
            'description' => 'Will be restored',
            'price' => 30.00,
            'stock' => 5
        ]);

        $productId = $product->getId();
        $this->productService->deleteProduct($productId);
        $this->assertTrue($this->productRepo->isDeleted($productId));

        // Restore product
        $restored = $this->productRepo->restore($productId);
        $this->assertTrue($restored);
        $this->assertFalse($this->productRepo->isDeleted($productId));

        // Should be retrievable again
        $retrieved = $this->productService->getProduct($productId);
        $this->assertNotNull($retrieved);
        $this->assertEquals('Product to Restore', $retrieved->getName());
    }

    public function test_deleted_products_not_visible_to_customers()
    {
        // Create multiple products
        $product1 = $this->productService->createProduct([
            'name' => 'Product 1',
            'price' => 10.00,
            'stock' => 5
        ]);
        $product2 = $this->productService->createProduct([
            'name' => 'Product 2',
            'price' => 20.00,
            'stock' => 10
        ]);
        $product3 = $this->productService->createProduct([
            'name' => 'Product 3',
            'price' => 30.00,
            'stock' => 15
        ]);

        // Delete one product
        $this->productService->deleteProduct($product2->getId());

        // Customer view should only show non-deleted products
        $customerProducts = $this->productService->listProducts();
        $this->assertCount(2, $customerProducts);
        
        $productIds = array_map(fn($p) => $p->getId(), $customerProducts);
        $this->assertContains($product1->getId(), $productIds);
        $this->assertContains($product3->getId(), $productIds);
        $this->assertNotContains($product2->getId(), $productIds);

        // Admin view should show all products including deleted
        $adminProducts = $this->productService->listAllProductsIncludingDeleted();
        $this->assertCount(3, $adminProducts);
    }
}

